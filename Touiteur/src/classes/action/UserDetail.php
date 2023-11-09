<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\NoteTouite;

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
        $stmt = $db->prepare("SELECT t.contenu, t.datePublication, u.nom, u.prénom, t.id_touite
                    FROM touite t
                    JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                    JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                    WHERE u.id_utilisateur = ?
                    ORDER BY t.datePublication DESC");
        $stmt->execute([$userId]);
        $res = '';
        while ($data = $stmt->fetch()) {
            $touiteID = $data['id_touite'];
            //affiche le nom et le prénom de l'utilisateur qui a publié le touite
            $res .= $data['prénom'] . ' ' . $data['nom'] ;
            // Boutons Like et Dislike spécifiques au touite actuel
            $contenu = $data['contenu'];
            $datePublication = $data['datePublication'];

            $res .=  '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication .  '</div>' . '</a>';
            $res .= '<form method="POST" action="?action=Default">
        <input type="hidden" name="touiteID" value="' . $touiteID . '">
        <button type="submit" name="likeTouite">Like</button>
        <button type="submit" name="dislikeTouite">Dislike</button>
    </form>';

            // Affiche la note actuelle du touite
            $note = NoteTouite::getNoteTouite($touiteID);
            $res .= 'Note: ' . $note . '<br><br>';
        }

        // Gestion des actions Like et Dislike à l'intérieur de la boucle
        if (isset($_POST['touiteID']) && $_POST['touiteID'] == $touiteID) {
            if (isset($_POST['likeTouite'])) {
                $this->Likebutton($touiteID);
            } elseif (isset($_POST['dislikeTouite'])) {
                $this->Dislikebutton($touiteID);
            }
        }


        return $res;
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