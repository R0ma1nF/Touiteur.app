<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\follow\UserFollow;
use iutnc\touiteur\Touite\NoteTouite;

class TouiteDetailsAction extends Action
{
    /**
     * Exécute l'action pour afficher les détails d'un Touite.
     *
     * @return string Le contenu HTML à afficher.
     */
    public function execute(): string
    {
        // Établir une connexion à la base de données
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Récupérer l'ID du Touite depuis les paramètres de l'URL
        $touiteID = isset($_GET['touiteID']) ? (int)$_GET['touiteID'] : 0;

        // Obtenir les détails du Touite
        $liste = $this->touiteDetail($db, $touiteID);

        // Retourner le contenu HTML
        return 'Bienvenue sur Touiter' . '<br>' . $liste;
    }

    /**
     * Obtient les détails d'un Touite en fonction de son ID.
     *
     * @param mixed $db L'objet de connexion à la base de données.
     * @param int $idTouite L'ID du Touite.
     *
     * @return string Le contenu HTML des détails du Touite.
     */
    public function touiteDetail($db, $idTouite)
    {
        // Préparer la requête SQL pour obtenir les détails du Touite
        $stmt = $db->prepare("SELECT t.contenu, t.datePublication, u.nom, u.prénom,u.id_utilisateur, t.id_touite
                    FROM touite t
                    JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                    JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                    WHERE t.id_touite = ?
                    ORDER BY t.datePublication DESC");
        $stmt->execute([$idTouite]);

        // Initialiser la variable pour stocker les détails du Touite
        $details = '';

        // Récupérer les données du Touite depuis la base de données
        $data = $stmt->fetch();

        // Construire le contenu HTML des détails du Touite
        $details .= 'Contenu: ' . $data['contenu'] . "<br>";
        $details .= 'Date de Publication: ' . $data['datePublication'] . "<br>";
        $details .= 'Nom: ' . $data['nom'] . "<br>";
        $details .= 'Prénom: ' . $data['prénom'] . "<br>";
        $details .= 'ID Touite: ' . $data['id_touite'] . "<br>";
        $details .= 'Note : ' . NoteTouite::getNoteTouite($idTouite);

        // Récupérer l'ID de l'utilisateur associé au Touite
        $userId = $data['id_utilisateur'];

        // Obtenir le chemin de l'image associée au Touite
        $touiteID = $data['id_touite'];
        $tmp = new DefaultAction();
        $imagePath = $tmp->getImagePathForTouite($db, $touiteID);

        // Afficher l'image s'il y en a une
        if ($imagePath != 'chemin_image_par_defaut.jpg'){
            $details .= '<img src="' . $imagePath . '" alt="Image du touite" width="200" height="200">';
        }

        // Afficher le nom et prénom de l'utilisateur
        $details.= '<h1>'.$data['prénom'].' '.$data['nom'].'</h1>';

        // Afficher le formulaire pour suivre ou ne pas suivre l'utilisateur
        $details .= '<form method="POST" action="?action=userDetail&userID=' . $userId . '">';
        $details.='<input type="hidden" name="userID" value="' . $userId . '">';
        $details .= '<button type="submit" name="followUser">Follow</button>';
        $details .= '<button type="submit" name="unfollowUser">Unfollow</button>';
        $details.='</form>';

        // Vérifier le rôle de l'utilisateur et afficher un message approprié
        $userrole = $_SESSION['user']['role'];
        if($userrole == '10'){
            $details.= "Vous devez être connecté pour pouvoir suivre un utilisateur.";
        }else {
            // Traiter les actions de suivi et de non-suivi
            if (isset($_POST['followUser'])) {
                $followResult = UserFollow::followUser($_SESSION['user']['id'], $userId);
                if (!$followResult) {
                    if ($userId == $_SESSION['user']['id']) {
                        $details .= "Vous ne pouvez pas vous suivre vous-même.";
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

        // Retourner le contenu HTML des détails du Touite
        return $details;
    }
}
