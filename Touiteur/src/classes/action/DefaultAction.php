<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\tag\SaveTag;
use iutnc\touiteur\Touite\NoteTouite;
use iutnc\touiteur\Touite\SupprimerTouite;
use PDO;

/**
 * Classe DefaultAction
 *
 * @package iutnc\touiteur\action
 */
class DefaultAction extends Action
{
    /**
     * Exécute l'action par défaut, affichant la liste de Touites.
     *
     * @return string HTML généré pour afficher la liste de Touites.
     */
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $liste = $this->listeTouite($db);
        header("Refresh:10 ");
        return '<h1> Bienvenue sur Touiter </h1>' . '<br>' . '<div class="touite-list">' . $liste . '</div>';
    }

    /**
     * Récupère la liste des Touites à afficher en fonction du rôle de l'utilisateur.
     *
     * @param PDO $db Connexion à la base de données.
     * @return string HTML généré pour afficher la liste de Touites.
     */
    public function listeTouite($db): string
    {
        // Récupération du rôle de l'utilisateur
        $roleuser = $_SESSION["user"]["role"];

        // Si l'utilisateur est administrateur ou utilisateur normal, afficher son mur
        if ($roleuser == 100 || $roleuser == 1) {
            $liste = $this->getUserWallTouites($_SESSION['user']['id']);
            return $liste;
        } else {
            // Si l'utilisateur est simple utilisateur, afficher le fil public
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $itemsPerPage = 10;
            $offset = ($page - 1) * $itemsPerPage;

            // Requête pour récupérer les Touites du fil public
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
                // Récupération des données du Touite
                $touiteID = $data['id_touite'];
                $userId = $data["id_utilisateur"];
                $prenom = $data['prénom'];
                $nom = $data['nom'];
                $SaveTag = new SaveTag();
                $contenu = $SaveTag->transformTagsToLinks($data['contenu']);
                $datePublication = $data['datePublication'];

                // Construction du HTML pour afficher le Touite
                $res .= '<div class="touite">';
                $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' . $nom . ' ' . $prenom . '</p></div>';
                $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div><br>';
                $res .= '<form method="POST" action="?action=Default">
                <input type="hidden" name="touiteID" value="' . $touiteID . '">
                <button type="submit" name="likeTouite">Like</button>
                <button type="submit" name="dislikeTouite">Dislike</button>
                </form></div>';

                $note = NoteTouite::getNoteTouite($touiteID);
                $res .= 'Note: ' . $note . '<br><br>';
            }

            // Gestion des actions (like, dislike, suppression) sur les Touites
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

            // Pagination
            $totalTouites = $this->getTotalTouitesCount($db);
            $totalPages = ceil($totalTouites / $itemsPerPage);

            for ($i = 1; $i <= $totalPages; $i++) {
                $res .= '<a href="?action=Default&page=' . $i . '">' . $i . '</a> ';
            }

            return $res;
        }
    }

    /**
     * @param $db PDO Connexion à la base de données.
     * @return mixed Récupère le nombre total de Touites dans la base de données.

     */
private function getTotalTouitesCount($db)
    {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM touite");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'];
    }

    /**
     * @param $touiteID int Identifiant du Touite.
     * @return void Récupère l'identifiant de l'utilisateur connecté et ajoute un Like au Touite.
     * @throws AuthException Si l'utilisateur n'est pas connecté.
     */
    public function Likebutton($touiteID)
    {
        // Récupération de l'identifiant de l'utilisateur connecté
        $userID = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        if ($userID == null) {
            $res = '';
             $res .= '<h2>Erreur</h2>';
            $res .=  "Vous devez être connecté pour pouvoir liker un touite.";
            $res .=  '<br>';
            $res .=  '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
            $res .=  '<br>';
            $res .=  '<a href="index.php?action=Inscription">Retour à la page d\'inscription</a>';
            echo $res;
            exit();
        }
        // Ajout du Like au Touite
        NoteTouite::likeTouite($userID, $touiteID);
    }

    /**
     * @param $touiteID int Identifiant du Touite.
     * @return void Récupère l'identifiant de l'utilisateur connecté et ajoute un Dislike au Touite.
     * @throws AuthException Si l'utilisateur n'est pas connecté.
     */
    public function Dislikebutton($touiteID)
    {
        // Récupération de l'identifiant de l'utilisateur connecté
        $userID = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        // verification si l'utilisateur est connecté
        if ($userID == null) {
           $res = '';
           $res .= '<h2>Erreur</h2>';
            $res .= "Vous devez être connecté pour pouvoir disliker un touite.";
            $res .= '<br>';
            $res .= '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
            $res .= '<br>';
            $res .= '<a href="index.php?action=Inscription">Retour à la page d\'inscription</a>';
            echo $res;
            exit();
        }
        // Ajout du Dislike au Touite
        NoteTouite::dislikeTouite($userID, $touiteID);
    }

    /**
     * @param $userID int Identifiant de l'utilisateur.
     * @return string Récupère les Touites à afficher sur le mur de l'utilisateur.
     * @throws \Exception Si l'utilisateur n'existe pas.
     */
    public function getUserWallTouites($userID): string
    {
        // Récupération des Touites de l'utilisateur
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        $itemsPerPage = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;
        // Requête pour récupérer les Touites des personnes suivies et des tags suivis par l'utilisateur
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
        //requete pour récupérer les autres touites
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
        // Pagination
        $totalTouites = $this->getTotalTouitesCount($db);
        $totalPages = ceil($totalTouites / $itemsPerPage);

        for ($i = 1; $i <= $totalPages; $i++) {
            $res .= '<a href="?action=Default&page=' . $i . '">' . $i . '</a> ';
        }

        return $res;
    }

    /**
     * @param $touites array Tableau contenant les données des Touites.
     * @return string Génère le HTML pour afficher les Touites.
     * @throws AuthException Si l'utilisateur n'est pas connecté.
     */
    private function generateTouiteHTML($touites)
    {
        $res = '';
        //boucle pour afficher les touites
        foreach ($touites as $data) {
            $touiteID = $data['ID_Touite'] ?? null;
            $SaveTag = new SaveTag();
            $contenu = $SaveTag->transformTagsToLinks($data['Contenu']) ?? null;
            $datePublication = $data['DatePublication'] ?? null;
            $prenom = $data['prénom'] ?? null;
            $nom = $data['nom'] ?? null;
            $userId = $data['id_utilisateur'] ?? null;


            $res .= '<div class="touite">';
            $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' . $nom . ' ' . $prenom . '</p></div>';
            $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '<br><br>' .
                '<form method="POST" action="?action=Default">
            <input type="hidden" name="touiteID" value="' . $touiteID . '">
            <input type="hidden" name="userID" value="' . $userId . '">
            <button type="submit" name="likeTouite">Like</button>
            <button type="submit" name="dislikeTouite">Dislike</button>
            <button type="submit" name="deleteTouite">Delete</button>
            </form> ' .'</div></div>';


            $note = NoteTouite::getNoteTouite($touiteID) ?? null;
            $res .= 'Note: ' . $note . '<br><br>';
            //verification si il y a un touite grace a l'id
            if (isset($_POST['touiteID'])) {
                $touiteID = (int)$_POST['touiteID'];
                $userID = isset($_POST['userID']) ? (int)$_POST['userID'] : 0;
                //verification si l'utilisateur a liker ou disliker un touite
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
     * @param $touites array Tableau contenant les données des Touites.
     * @param string $res Résultat de l'exécution de l'action.
     * @return array Tableau contenant les données des Touites.
     * @throws AuthException Si l'utilisateur n'est pas connecté.
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

            $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' . $nom . ' ' . $prenom . '</p></div>';
            $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div><br>';

            $res .= '<form method="POST" action="?action=Default">
            <input type="hidden" name="touiteID" value="' . $touiteID . '">
            <input type="hidden" name="userID" value="' . $userId . '">
            <button type="submit" name="likeTouite">Like</button>
            <button type="submit" name="dislikeTouite">Dislike</button>
            <button type="submit" name="deleteTouite">Delete</button>
            </form>';

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
        $userID=isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        $totalTouites = $this->getTotalTouitesCount($db);
        $totalPages = ceil($totalTouites / $itemsPerPage);

        for ($i = 1; $i <= $totalPages; $i++) {
            $res .= '<a href="?action=Default&page=' . $i . '">' . $i . '</a> ';
        }


        return array($data, $touiteID, $contenu, $datePublication, $prenom, $nom, $userId, $res, $note);
    }

    /**
     * @param $db PDO Connexion à la base de données.
     * @param $touiteID int Identifiant du Touite.
     * @return string Chemin de l'image du Touite.
     */
    public function getImagePathForTouite($db, $touiteID)
    {
        $stmt = $db->prepare("SELECT ID_Image FROM touite WHERE id_touite = ?");
        $stmt->execute([$touiteID]);

        $imageID = $stmt->fetchColumn();

        if (!$imageID) {
            return 'chemin_image_par_defaut.jpg';
        }

        $stmt = $db->prepare("SELECT CheminFichier FROM image WHERE ID_Image = ?");
        $stmt->execute([$imageID]);
        $imagePath = $stmt->fetchColumn();

        if (!$imagePath) {
            return 'chemin_image_par_defaut.jpg';
        }

        return $imagePath;
    }


}