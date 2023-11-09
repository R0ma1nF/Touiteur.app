<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\NoteTouite;
use iutnc\touiteur\follow\UserFollow;
use PDO;

class UserDetail extends Action
{
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $userId = isset($_GET['userID']) ? (int)$_GET['userID'] : 0; // Get the touiteID from the query parameter
        $liste = $this->listeTouiteUser($db, $userId);
        return 'Bienvenue sur Touiter' . '<br><br>' . $liste;
    }

    public function listeTouiteUser($db, $userId)
    {
        $stmtUser = $db->prepare("SELECT nom, prénom FROM user WHERE id_utilisateur = ?");
        $stmtUser->execute([$userId]);
        $userData = $stmtUser->fetch();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the current page number
        $totalTouites = $this->getTotalUserTouitesCount($db, $userId); // Get the total number of user touites
        $itemsPerPage = 10; // Set the number of touites to display per page
        $offset = ($page - 1) * $itemsPerPage; // Calculate the offset for the SQL query

        $stmt = $db->prepare("SELECT t.contenu, t.datePublication, u.nom, u.prénom, t.id_touite
            FROM touite t
            JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
            JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
            WHERE u.id_utilisateur = :userId
            ORDER BY t.datePublication DESC
            LIMIT :itemsPerPage OFFSET :offset");

        $stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();


        $res = '';
        $res.= '<h1>'.$userData['prénom'].' '.$userData['nom'].'</h1>';
        $res .= '<form method="POST" action="?action=userDetail&userID=' . $userId . '">';
        $res.='<input type="hidden" name="userID" value="' . $userId . '">';
        $res .= '<button type="submit" name="followUser">Follow</button>';
        $res .= '<button type="submit" name="unfollowUser">Unfollow</button>';
        $res.='</form>';
        if (isset($_POST['followUser'])) {
            $followResult = UserFollow::followUser($_SESSION['user']['id'], $userId);
            if (!$followResult) {
                if ($userId == $_SESSION['user']['id']){
                    $res.="vous ne pouvez pas vous suivre vous même";
                }else {
                    $res .= '<div>Vous suivez déjà cet utilisateur.</div>';
                }
            }
        } elseif (isset($_POST['unfollowUser'])) {
            $unfollowResult = UserFollow::unfollowUser($_SESSION['user']['id'], $userId);
            if (!$unfollowResult) {
                $res.= '<div>Vous ne suivez pas cet utilisateur.</div>';
            }
        }
        $touites = $stmt->fetchAll(PDO::FETCH_ASSOC);



        foreach ($touites as $data) {
            // Extraction des données du touite
            $touiteID = $data['id_touite'] ;
            $contenu = $data['contenu'] ;
            $datePublication = $data['datePublication'] ;

            // Récupérer le chemin de l'image associée au touite depuis la base de données
            $tmp = new DefaultAction();
            $imagePath = $tmp->getImagePathForTouite($db, $touiteID);



            // Affichage des informations du touite
            $res .= '<div onclick="window.location=\'?action=userDetail&userID=' . $userId . '\';" style="cursor: pointer;"><p>' .'</div>';
            $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div><br>';
            if ($imagePath != 'chemin_image_par_defaut.jpg') {
                $res .= '<img src="' . $imagePath . '" alt="Touite Image"><br>';
            }
            $res .= '<form method="POST" action="?action=Default">
        <input type="hidden" name="touiteID" value="' . $touiteID . '">
        <button type="submit" name="likeTouite">Like</button>
        <button type="submit" name="dislikeTouite">Dislike</button>
    </form>';

            // Gestion des actions Like et Dislike à l'intérieur de la boucle
            if (isset($_POST['touiteID']) && $_POST['touiteID'] == $touiteID) {
                if (isset($_POST['likeTouite'])) {
                    $this->Likebutton($touiteID);
                } elseif (isset($_POST['dislikeTouite'])) {
                    $this->Dislikebutton($touiteID);
                }
            }

            // Affiche la note actuelle du touite
            $note = NoteTouite::getNoteTouite($touiteID) ?? null;
            $res .= 'Note: ' . $note . '<br><br>';
        }
        $totalPages = ceil($totalTouites / $itemsPerPage);

        for ($i = 1; $i <= $totalPages; $i++) {
            $res .= '<a href="?action=userDetail&userID=' . $userId . '&page=' . $i . '">' . $i . '</a> ';
        }
        return $res;
    }

    private function getTotalUserTouitesCount($db, $userId)
    {
        $stmt = $db->prepare("SELECT COUNT(*) as total
                         FROM touite t
                         JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                         JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                         WHERE u.id_utilisateur = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'];
    }


    /**
     * @throws AuthException
     */
    public function Likebutton($touiteID) {
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
    public function Dislikebutton($touiteID) {
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
}