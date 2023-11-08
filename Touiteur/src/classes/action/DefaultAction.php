<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\NoteTouite;

class DefaultAction extends Action
{
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $userRole = $_SESSION['user']['role'] ?? '0';

        if ($userRole == 100) {
            return $this->adminDashboard($db);
        } elseif ($userRole == 1) {
            return $this->userDashboard($db);
        } else {
            return $this->guestDashboard($db);
        }
    }

    public function adminDashboard($db)
    {
        return 'Bienvenue sur la page d\'accueil de l\'administrateur';
    }

    public function userDashboard($db)
    {
        $timeline = $this->getUserTimeline($db);
        return 'Bienvenue sur votre page d\'accueil utilisateur' . '<br>' . $timeline;
    }

    public function guestDashboard($db)
    {
        $timeline = $this->getGuestTimeline($db);
        return 'Bienvenue sur la page d\'accueil des invités' . '<br>' . $timeline;
    }

    public function getUserTimeline($db)
    {
        $stmt = $db->prepare("SELECT t.id_touite, t.contenu, t.datePublication, u.nom, u.prénom
                        FROM touite t
                        JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                        JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                        ORDER BY t.datePublication DESC");
        $stmt->execute();
        $res = '';
        while ($data = $stmt->fetch()) {
<<<<<<< HEAD
            // Votre code pour afficher la chronologie de l'utilisateur
=======
            $touiteID = $data['id_touite'];
            //affiche le nom et le prénom de l'utilisateur qui a publié le touite
            $res .= $data['prénom'] . ' ' . $data['nom'] ;
            // Boutons Like et Dislike spécifiques au touite actuel
            $contenu = $data['contenu'];
            $datePublication = $data['datePublication'];

            $res .=  '<a href="?action=testdetail" ><p>' . $contenu . '</p>' . $datePublication .  '</a><br>';
            $res .= '<form method="POST" action="?action=Default">
        <input type="hidden" name="touiteID" value="' . $touiteID . '">
        <button type="submit" name="likeTouite">Like</button>
        <button type="submit" name="dislikeTouite">Dislike</button>
    </form>';

            // Affiche la note actuelle du touite
            $note = NoteTouite::getNoteTouite($touiteID);
            $res .= 'Note: ' . $note . '<br><br>';
        }

        // Gestion des actions Like et Dislike en dehors de la boucle
        if (isset($_POST['touiteID'])) {
            $touiteID = (int) $_POST['touiteID']; // Assurez-vous qu'il s'agit d'un entier
            if (isset($_POST['likeTouite'])) {
                $this->Likebutton($touiteID);
            } elseif (isset($_POST['dislikeTouite'])) {
                $this->Dislikebutton($touiteID);
            }
>>>>>>> 1a4102b0a83f4411919be98dd366e3cf76fd3a07
        }

        return $res;
    }

    public function getGuestTimeline($db)
    {
        // Logique pour afficher la chronologie des invités
    }

    public function Likebutton($touiteID)
    {
        $userID = $_SESSION['user']['id'];
        // Logique pour gérer le bouton Like
        NoteTouite::likeTouite($userID, $touiteID);
    }

    public function Dislikebutton($touiteID)
    {
        $userID = $_SESSION['user']['id'];
        // Logique pour gérer le bouton Dislike
        NoteTouite::dislikeTouite($userID, $touiteID);
    }
}
