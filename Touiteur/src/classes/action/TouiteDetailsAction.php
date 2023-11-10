<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\follow\UserFollow;
use iutnc\touiteur\Touite\NoteTouite;

class TouiteDetailsAction extends Action
{
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $touiteID = isset($_GET['touiteID']) ? (int)$_GET['touiteID'] : 0;
        $liste = $this->touiteDetail($db, $touiteID);
        return 'Bienvenue sur Touiter' . '<br>' . $liste;
    }

    public function touiteDetail($db, $idTouite)
    {
        $stmt = $db->prepare("SELECT t.contenu, t.datePublication, u.nom, u.prénom,u.id_utilisateur, t.id_touite
                    FROM touite t
                    JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                    JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                    WHERE t.id_touite = ?
                    ORDER BY t.datePublication DESC");
        $stmt->execute([$idTouite]);


        $details = '';
        $data = $stmt->fetch();
        $details .= 'Contenu: ' . $data['contenu'] . "<br>";
        $details .= 'Date de Publication: ' . $data['datePublication'] . "<br>";
        $details .= 'Nom: ' . $data['nom'] . "<br>";
        $details .= 'Prénom: ' . $data['prénom'] . "<br>";
        $details .= 'ID Touite: ' . $data['id_touite'] . "<br>";
        $details .= 'Note : ' . NoteTouite::getNoteTouite($idTouite);
        $userId = $data['id_utilisateur'];

        $touiteID = $data['id_touite'];
        $tmp = new DefaultAction();
        $imagePath = $tmp->getImagePathForTouite($db, $touiteID);

        if ($imagePath != 'chemin_image_par_defaut.jpg'){
            $details .= '<img src="' . $imagePath . '" alt="Image du touite" width="200" height="200">';
        }
        $details.= '<h1>'.$data['prénom'].' '.$data['nom'].'</h1>';
        $details .= '<form method="POST" action="?action=userDetail&userID=' . $userId . '">';
        $details.='<input type="hidden" name="userID" value="' . $userId . '">';
        $details .= '<button type="submit" name="followUser">Follow</button>';
        $details .= '<button type="submit" name="unfollowUser">Unfollow</button>';
        $details.='</form>';
        $userrole = $_SESSION['user']['role'];
        if($userrole == '10'){
            $details.= "vous devez être connecté pour pouvoir suivre un utilisateur";
        }else {
            if (isset($_POST['followUser'])) {
                $followResult = UserFollow::followUser($_SESSION['user']['id'], $userId);
                if (!$followResult) {
                    if ($userId == $_SESSION['user']['id']) {
                        $details .= "vous ne pouvez pas vous suivre vous même";
                    } else {
                        $details .= '<div>Vous suivez déjà cet utilisateur.</div>';
                    }
                }
            } elseif (isset($_POST['unfollowUser'])) {
                $unfollowResult = UserFollow::unfollowUser($_SESSION['user']['id'], $userId);
                if (!$unfollowResult) {
                    $details .= '<div>Vous ne suivez pas cet utilisateur.</div>';
                }
            }
        }

        return $details;
    }
}
