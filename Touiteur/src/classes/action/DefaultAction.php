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
            // Votre code pour afficher la chronologie de l'utilisateur
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
