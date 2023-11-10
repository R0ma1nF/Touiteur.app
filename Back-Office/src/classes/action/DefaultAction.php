<?php

namespace iutnc\BackOffice\action;

use iutnc\BackOffice\db\ConnectionFactory;
use iutnc\BackOffice\exception\AuthException;
use iutnc\BackOffice\tag\SaveTag;
use iutnc\BackOffice\Touite\NoteTouite;
use iutnc\BackOffice\Touite\SupprimerTouite;
use PDO;

class DefaultAction extends Action
{

    /**
     * @throws \Exception
     */
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $liste = $this->listeTouite($db);
        header("Refresh:10 ");
        return 'Bienvenue sur Touiter ' . '<br>' . $liste;
    }

    /**
     * @throws \Exception
     */
    /**
     * @throws \Exception
     */
    /**
     * @throws \Exception
     */
    public function listeTouite($db)
    {
        $roleuser = $_SESSION["user"]["role"];
        if ($roleuser == 100 || $roleuser == 1) {
            $liste = $this->getUserWallTouites($_SESSION['user']['id']);
            return $liste;
        } else {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the current page number
            $itemsPerPage = 10; // Set the number of touites to display per page
            $offset = ($page - 1) * $itemsPerPage; // Calculate the offset for the SQL query

            $stmt = $db->prepare("SELECT t.id_touite, t.contenu, t.datePublication, u.nom, u.prénom, u.id_utilisateur
                    FROM touite t
                    JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                    JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                    ORDER BY t.datePublication DESC
                    LIMIT :itemsPerPage OFFSET :offset");

            $stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $res = '';
            while ($data = $stmt->fetch()) {
                $touiteID = $data['id_touite'];
                $userId = $data["id_utilisateur"];
                //affiche le nom et le prénom de l'utilisateur qui a publié le touite
                $prenom = $data['prénom'];
                $nom = $data['nom'];
                // Boutons Like et Dislike spécifiques au touite actuel
                $SaveTag = new SaveTag();
                $contenu = $SaveTag->transformTagsToLinks($data['contenu']);
                $datePublication = $data['datePublication'];



                $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' . $nom . ' ' . $prenom . '</p>' . '</div>' . '</a>';
                $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div>' . '</a><br>';

                $res .= '<form method="POST" action="?action=Default">
                <input type="hidden" name="touiteID" value="' . $touiteID . '">
                <button type="submit" name="likeTouite">Like</button>
                <button type="submit" name="dislikeTouite">Dislike</button>
                </form>';

                // Affiche la note actuelle du touite
                $note = NoteTouite::getNoteTouite($touiteID);
                $res .= 'Note: ' . $note . '<br><br>';
            }


            if (isset($_POST['touiteID'])) {
                $touiteID = (int)$_POST['touiteID']; // Assurez-vous qu'il s'agit d'un entier
                $userID = isset($_POST['userID']) ? (int)$_POST['userID'] : 0;

                if (isset($_POST['likeTouite'])) {
                    $this->Likebutton($touiteID, $userID);
                } elseif (isset($_POST['dislikeTouite'])) {
                    $this->Dislikebutton($touiteID, $userID);
                } elseif (isset($_POST['deleteTouite'])) {
                    $res .= SupprimerTouite::supprimerTouite($userID, $touiteID);
                }
            }
            $totalTouites = $this->getTotalTouitesCount($db); // Get the total number of touites
            $totalPages = ceil($totalTouites / $itemsPerPage);

            for ($i = 1; $i <= $totalPages; $i++) {
                $res .= '<a href="?action=Default&page=' . $i . '">' . $i . '</a> ';
            }


            return $res;
        }

    }
    private function getTotalTouitesCount($db)
    {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM touite");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'];
    }

    /**
     * @throws AuthException
     */
    public function Likebutton($touiteID)
    {
        $userID = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        if ($userID == null) {
            // Redirigez l'utilisateur vers la page de connexion s'il n'est pas connecté (ou vers la page d'inscription s'il n'a pas de compte)
            //en lui proposant de se connecter ou de s'inscrire pour pouvoir liker un touite et afficher un message d'erreur
            echo '<h2>Erreur</h2>';
            echo "Vous devez être connecté pour pouvoir liker un touite.";
            echo '<br>';
            echo '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
            echo '<br>';
            echo '<a href="index.php?action=Inscription">Retour à la page d\'inscription</a>';
            exit();
        }
        // Bouton Like cliqué, ajoutez ici la logique pour gérer le Like
        NoteTouite::likeTouite($userID, $touiteID);
    }

    /**
     * @throws AuthException
     */
    public function Dislikebutton($touiteID)
    {
        $userID = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

        if ($userID == null) {
            // Redirigez l'utilisateur vers la page de connexion s'il n'est pas connecté (ou vers la page d'inscription s'il n'a pas de compte)
            //en lui proposant de se connecter ou de s'inscrire pour pouvoir liker un touite et afficher un message d'erreur
            echo '<h2>Erreur</h2>';
            echo "Vous devez être connecté pour pouvoir disliker un touite.";
            echo '<br>';
            echo '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
            echo '<br>';
            echo '<a href="index.php?action=Inscription">Retour à la page d\'inscription</a>';
            exit();
        }
        // Bouton Dislike cliqué, ajoutez ici la logique pour gérer le Dislike
        NoteTouite::dislikeTouite($userID, $touiteID);
    }

    public function getUserWallTouites($userID): string
    {
        $db = ConnectionFactory::makeConnection();

        $itemsPerPage = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;

        $requetesuivi = "SELECT t.*, u.nom, u.prénom, u.id_utilisateur
            FROM touite AS t
            LEFT JOIN listetouiteutilisateur AS lu ON t.ID_Touite = lu.ID_Touite
            LEFT JOIN listetouitetag AS lt ON t.ID_Touite = lt.ID_Touite
            LEFT JOIN abonnement AS a ON lu.ID_Utilisateur = a.ID_UtilisateurSuivi
            LEFT JOIN abonnementtag AS at ON lt.ID_Tag = at.ID_Tag
            LEFT JOIN user AS u ON lu.ID_Utilisateur = u.id_utilisateur
            WHERE a.ID_Utilisateur = :userID OR at.ID_Utilisateur = :userID
            ORDER BY t.DatePublication DESC
            LIMIT :offset, :itemsPerPage";

        $requeteautre = "SELECT t.*, u.nom, u.prénom, u.id_utilisateur
            FROM touite AS t
            LEFT JOIN listetouiteutilisateur AS lu ON t.ID_Touite = lu.ID_Touite
            LEFT JOIN listetouitetag AS lt ON t.ID_Touite = lt.ID_Touite
            LEFT JOIN user AS u ON lu.ID_Utilisateur = u.id_utilisateur
            WHERE t.ID_Touite NOT IN (
                SELECT t.ID_Touite
                FROM touite AS t
                LEFT JOIN listetouiteutilisateur AS lu ON t.ID_Touite = lu.ID_Touite
                LEFT JOIN listetouitetag AS lt ON t.ID_Touite = lt.ID_Touite
                LEFT JOIN abonnement AS a ON lu.ID_Utilisateur = a.ID_UtilisateurSuivi
                LEFT JOIN abonnementtag AS at ON lt.ID_Tag = at.ID_Tag
                WHERE (a.ID_Utilisateur = :userID OR at.ID_Utilisateur = :userID)
            )
            ORDER BY t.DatePublication DESC
            LIMIT :offset, :itemsPerPage";

        $stmt1 = $db->prepare($requetesuivi);
        $stmt1->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt1->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt1->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
        $stmt1->execute();
        $touitesSuivi = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        $stmt2 = $db->prepare("$requeteautre");
        $stmt2->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt2->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt2->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
        $stmt2->execute();
        $touitesAutre = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $res = $this->generateTouiteHTML($touitesSuivi);
        $res .= $this->generateTouiteHTML($touitesAutre);

        // Génération des liens de pagination
        $totalTouites = $this->getTotalTouitesCount($db); // Get the total number of touites
        $totalPages = ceil($totalTouites / $itemsPerPage);

        for ($i = 1; $i <= $totalPages; $i++) {
            $res .= '<a href="?action=Default&page=' . $i . '">' . $i . '</a> ';
        }

        return $res;
    }

    private function generateTouiteHTML($touites)
    {
        $res = '';

        foreach ($touites as $data) {
            // Extraction des données du touite
            $touiteID = $data['ID_Touite'] ?? null;
            $SaveTag = new SaveTag();
            $contenu = $SaveTag->transformTagsToLinks($data['Contenu']) ?? null;
            $datePublication = $data['DatePublication'] ?? null;
            $prenom = $data['prénom'] ?? null;
            $nom = $data['nom'] ?? null;
            $userId = $data['id_utilisateur'] ?? null;

            // Affichage des informations du touite
            $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' . $nom . ' ' . $prenom . '</p></div>';
            $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div><br>';

            $res .= '<form method="POST" action="?action=Default">
            <input type="hidden" name="touiteID" value="' . $touiteID . '">
            <input type="hidden" name="userID" value="' . $userId . '">
            <button type="submit" name="likeTouite">Like</button>
            <button type="submit" name="dislikeTouite">Dislike</button>
            <button type="submit" name="deleteTouite">Delete</button>
        </form>';

            // Affiche la note actuelle du touite
            $note = NoteTouite::getNoteTouite($touiteID) ?? null;
            $res .= 'Note: ' . $note . '<br><br>';

            if (isset($_POST['touiteID'])) {
                $touiteID = (int)$_POST['touiteID'];
                $userID = isset($_POST['userID']) ? (int)$_POST['userID'] : 0;

                if (isset($_POST['likeTouite'])) {
                    $this->Likebutton($touiteID, $userID);
                } elseif (isset($_POST['dislikeTouite'])) {
                    $this->Dislikebutton($touiteID, $userID);
                } elseif (isset($_POST['deleteTouite'])) {
                    $res .= SupprimerTouite::supprimerTouite($userID, $touiteID);
                }
            }
        }

        return $res;
    }





    /**
     * @param $touites
     * @param string $res
     * @return array
     */
    public function extracted($touites, string $res): array
    {

        $db = ConnectionFactory::makeConnection();
        $itemsPerPage = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;

        foreach ($touites as $data) {
            // Extraction des données du touite
            $touiteID = $data['ID_Touite'] ?? null;
            $SaveTag = new SaveTag();
            $contenu = $SaveTag->transformTagsToLinks($data['Contenu']) ?? null;
            $datePublication = $data['DatePublication'] ?? null;
            $prenom = $data['prénom'] ?? null;
            $nom = $data['nom'] ?? null;
            $userId = $data['id_utilisateur'] ?? null;



            // Affichage des informations du touite
            $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' . $nom . ' ' . $prenom . '</p></div>';
            $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div><br>';

            $res .= '<form method="POST" action="?action=Default">
        <input type="hidden" name="touiteID" value="' . $touiteID . '">
        <input type="hidden" name="userID" value="' . $userId . '">
        <button type="submit" name="likeTouite">Like</button>
        <button type="submit" name="dislikeTouite">Dislike</button>
        <button type="submit" name="deleteTouite">Delete</button>
    </form>';

            // Affiche la note actuelle du touite
            $note = NoteTouite::getNoteTouite($touiteID) ?? null;
            $res .= 'Note: ' . $note . '<br><br>';

            if (isset($_POST['touiteID'])) {
                $touiteID = (int)$_POST['touiteID']; // Assurez-vous qu'il s'agit d'un entier
                $userID = isset($_POST['userID']) ? (int)$_POST['userID'] : 0;

                if (isset($_POST['likeTouite'])) {
                    $this->Likebutton($touiteID, $userID);
                } elseif (isset($_POST['dislikeTouite'])) {
                    $this->Dislikebutton($touiteID, $userID);
                } elseif (isset($_POST['deleteTouite'])) {
                    $res .= SupprimerTouite::supprimerTouite($userID, $touiteID);
                }
            }


        }
        $userID=isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        $totalTouites = $this->getTotalTouitesCount($db); // Get the total number of touites
        $totalPages = ceil($totalTouites / $itemsPerPage);

        for ($i = 1; $i <= $totalPages; $i++) {
            $res .= '<a href="?action=Default&page=' . $i . '">' . $i . '</a> ';
        }

// ... (autres parties du code)

        return array($data, $touiteID, $contenu, $datePublication, $prenom, $nom, $userId, $res, $note);
    }

    public function getImagePathForTouite($db, $touiteID)
    {
        $stmt = $db->prepare("SELECT ID_Image FROM touite WHERE id_touite = ?");
        $stmt->execute([$touiteID]);

        $imageID = $stmt->fetchColumn();

        if (!$imageID) {
            // Si le touite n'a pas d'image associée, retournez un chemin d'image par défaut
            return 'chemin_image_par_defaut.jpg';
        }

        // Récupérer le chemin de l'image depuis la table image
        $stmt = $db->prepare("SELECT CheminFichier FROM image WHERE ID_Image = ?");
        $stmt->execute([$imageID]);
        $imagePath = $stmt->fetchColumn();

        if (!$imagePath) {
            // Si le chemin de l'image n'est pas trouvé, retournez un chemin d'image par défaut
            return 'chemin_image_par_defaut.jpg';
        }

        return $imagePath;
    }


}