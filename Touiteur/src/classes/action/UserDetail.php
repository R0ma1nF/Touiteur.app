<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\NoteTouite;
use iutnc\touiteur\follow\UserFollow;
use PDO;

/**
 * Classe représentant l'action de détail d'utilisateur.
 */
class UserDetail extends Action
{
    /**
     * Exécute l'action et renvoie une chaîne de caractères représentant la vue.
     *
     * @return string Vue de la page utilisateur détaillée.
     */
    public function execute(): string
    {
        // Connexion à la base de données
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Récupération de l'ID de l'utilisateur à afficher
        $userId = isset($_GET['userID']) ? (int)$_GET['userID'] : 0;

        // Récupération de la liste des touites de l'utilisateur
        $liste = $this->listeTouiteUser($db, $userId);

        // Construction de la vue
        return 'Bienvenue sur Touiter' . '<br><br>' . $liste;
    }

    /**
     * Récupère la liste des touites d'un utilisateur donné.
     *
     * @param PDO   $db     Connexion à la base de données.
     * @param int   $userId ID de l'utilisateur.
     *
     * @return string Chaîne représentant la vue des touites de l'utilisateur.
     */
    public function listeTouiteUser($db, $userId)
    {
        // Requête pour récupérer les informations de l'utilisateur
        $stmtUser = $db->prepare("SELECT nom, prénom FROM user WHERE id_utilisateur = ?");
        $stmtUser->execute([$userId]);
        $userData = $stmtUser->fetch();

        // Pagination des touites
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $totalTouites = $this->getTotalUserTouitesCount($db, $userId);
        $itemsPerPage = 10;
        $offset = ($page - 1) * $itemsPerPage;

        // Requête pour récupérer les touites de l'utilisateur avec pagination
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

        // Construction de la vue des touites
        $res = '';
        $res .= '<h1>'.$userData['prénom'].' '.$userData['nom'].'</h1>';
        $res .= '<form method="POST" action="?action=userDetail&userID=' . $userId . '">';
        $res.='<input type="hidden" name="userID" value="' . $userId . '">';
        $res .= '<button type="submit" name="followUser">Follow</button>';
        $res .= '<button type="submit" name="unfollowUser">Unfollow</button>';
        $res.='</form>';
        $userrole = $_SESSION['user']['role'];
        if ($userrole == '10'){
            $res.= "vous devez être connecté pour pouvoir suivre un utilisateur";
        }else {
            if (isset($_POST['followUser'])) {
                $followResult = UserFollow::followUser($_SESSION['user']['id'], $userId);
                if (!$followResult) {
                    if ($userId == $_SESSION['user']['id']) {
                        $res .= "vous ne pouvez pas vous suivre vous même";
                    } else {
                        $res .= '<div>Vous suivez déjà cet utilisateur.</div>';
                    }
                }
            } elseif (isset($_POST['unfollowUser'])) {
                $unfollowResult = UserFollow::unfollowUser($_SESSION['user']['id'], $userId);
                if (!$unfollowResult) {
                    $res .= '<div>Vous ne suivez pas cet utilisateur.</div>';
                }
            }
        }
        $touites = $stmt->fetchAll(PDO::FETCH_ASSOC);



        foreach ($touites as $data) {

            $touiteID = $data['id_touite'] ;
            $contenu = $data['contenu'] ;
            $datePublication = $data['datePublication'] ;


            $tmp = new DefaultAction();
            $imagePath = $tmp->getImagePathForTouite($db, $touiteID);




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


            if (isset($_POST['touiteID']) && $_POST['touiteID'] == $touiteID) {
                if (isset($_POST['likeTouite'])) {
                    $this->Likebutton($touiteID);
                } elseif (isset($_POST['dislikeTouite'])) {
                    $this->Dislikebutton($touiteID);
                }
            }


            $note = NoteTouite::getNoteTouite($touiteID) ?? null;
            $res .= 'Note: ' . $note . '<br><br>';
        }
        $totalPages = ceil($totalTouites / $itemsPerPage);

        for ($i = 1; $i <= $totalPages; $i++) {
            $res .= '<a href="?action=userDetail&userID=' . $userId . '&page=' . $i . '">' . $i . '</a> ';
        }
        return $res;
    }

    /**
     * Récupère le nombre total de touites d'un utilisateur.
     *
     * @param PDO   $db     Connexion à la base de données.
     * @param int   $userId ID de l'utilisateur.
     *
     * @return int Nombre total de touites de l'utilisateur.
     */
    private function getTotalUserTouitesCount($db, $userId)
    {
        // Requête pour récupérer le nombre total de touites de l'utilisateur
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
     * @param $touiteID int L'ID du touite à liker.
     * @return void
     * @throws AuthException Si l'utilisateur n'est pas connecté.
     */
    public function Likebutton($touiteID) {
        $userID = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        if ($userID == null) {
            $res = '';
            $res .= '<h2>Erreur</h2>';
            $res .= "Vous devez être connecté pour pouvoir liker un touite.";
            $res .= '<br>';
            $res .= '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
            $res .= '<br>';
            $res .= '<a href="index.php?action=Inscription">Retour à la page d\'inscription</a>';
            echo $res;
            exit();
        }
        NoteTouite::likeTouite($userID, $touiteID);
    }

    /**
     * @param $touiteID int L'ID du touite à disliker.
     * @return void
     * @throws AuthException Si l'utilisateur n'est pas connecté.
     */
    public function Dislikebutton($touiteID) {
        $userID = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

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
        NoteTouite::dislikeTouite($userID, $touiteID);
    }
}