<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\tag\SaveTag;
use iutnc\touiteur\Touite\NoteTouite;
use iutnc\touiteur\Touite\SupprimerTouite;
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

            $stmt = $db->prepare("SELECT t.id_touite, t.contenu, t.datePublication, u.nom, u.prénom, u.id_utilisateur
                        FROM touite t
                        JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                        JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                        ORDER BY t.datePublication DESC");
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

                // Récupérer le chemin de l'image associée au touite depuis la base de données
                $imagePath = $this->getImagePathForTouite($db, $touiteID);

                $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' . $nom . ' ' . $prenom . '</p>' . '</div>' . '</a>';
                $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div>' . '</a><br>';
                $res .= '<img src="' . $imagePath . '" alt="Touite Image">'; // Affiche l'image associée au touite
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

            return $res;
        }

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
        $requetesuivi = "select t.* , u.nom, u.prénom, u.id_utilisateur
        from touite as t
        left join listetouiteutilisateur as lu on t.ID_Touite = lu.ID_Touite
        left join listetouitetag as lt on t.ID_Touite = lt.ID_Touite
        left join abonnement as a on lu.ID_Utilisateur = a.ID_UtilisateurSuivi
        left join abonnementtag as at on lt.ID_Tag = at.ID_Tag
        left join user as u on lu.ID_Utilisateur = u.id_utilisateur
        where a.ID_Utilisateur = :userID or at.ID_Utilisateur = :userID
        order by t.DatePublication desc";
        $requeteautre = "select t.* , u.nom, u.prénom, u.id_utilisateur
        from touite as t
        left join listetouiteutilisateur as lu on t.ID_Touite = lu.ID_Touite
        left join listetouitetag as lt on t.ID_Touite = lt.ID_Touite
        left join user as u on lu.ID_Utilisateur = u.id_utilisateur
        where t.ID_Touite not in (select t.ID_Touite
        from touite as t
        left join listetouiteutilisateur as lu on t.ID_Touite = lu.ID_Touite
        left join listetouitetag as lt on t.ID_Touite = lt.ID_Touite
        left join abonnement as a on lu.ID_Utilisateur = a.ID_UtilisateurSuivi
        left join abonnementtag as at on lt.ID_Tag = at.ID_Tag
            where (a.ID_Utilisateur = :userID or at.ID_Utilisateur = :userID))
            order by t.DatePublication desc";
        // Requête pour récupérer les touites des personnes et des tags suivis par l'utilisateur
        $stmt1 = $db->prepare($requetesuivi);

        $stmt1->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt1->execute();
        $res = '';
        $touites = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        if ($touites != null) {
            list($data, $touiteID, $contenu, $datePublication, $prenom, $nom, $userId, $res, $note) = $this->extracted($touites, $res);
        }
        // Requête pour récupérer tous les touites de la base de données
        $stmt2 = $db->prepare("$requeteautre");

        $stmt2->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt2->execute();
        $touites = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $res1 = '';
        if ($touites != null) {

            list($data, $touiteID, $contenu, $datePublication, $prenom, $nom, $userId, $res1, $note) = $this->extracted($touites, $res1);
        }
        return $res . $res1;
    }

    /**
     * @param $touites
     * @param string $res
     * @return array
     */
    public function extracted($touites, string $res): array
    {

        $db = ConnectionFactory::makeConnection();

        foreach ($touites as $data) {
            // Extraction des données du touite
            $touiteID = $data['ID_Touite'] ?? null;
            $SaveTag = new SaveTag();
            $contenu = $SaveTag->transformTagsToLinks($data['Contenu']) ?? null;
            $datePublication = $data['DatePublication'] ?? null;
            $prenom = $data['prénom'] ?? null;
            $nom = $data['nom'] ?? null;
            $userId = $data['id_utilisateur'] ?? null;

            // Récupérer le chemin de l'image associée au touite depuis la base de données
            $imagePath = $this->getImagePathForTouite($db, $touiteID);

            // Affichage des informations du touite
            $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' . $nom . ' ' . $prenom . '</p></div>';
            $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div><br>';
            $res .= '<img src="' . $imagePath . '" alt="Touite Image">'; // Affiche l'image associée au touite
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