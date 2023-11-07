<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\NoteTouite;

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
        $stmt = $db->prepare("SELECT t.id_touite, t.contenu, t.datePublication, u.nom, u.prénom
                        FROM touite t
                        JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                        JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                        ORDER BY t.datePublication DESC");
        $stmt->execute();
        $res = '';
        while ($data = $stmt->fetch()) {
            $touiteID = $data['id_touite'];
            //affiche le nom et le prénom de l'utilisateur qui a publié le touite
            $res .= $data['prénom'] . ' ' . $data['nom'] ;
            // Boutons Like et Dislike spécifiques au touite actuel
            $res .= '<p>' . $data['contenu'] . '</p>' . $data['datePublication'] . '<br>';
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
        }

        return $res;
    }



    /**
     * @throws AuthException
     */
    public function Likebutton($touiteID) {
        $userID = $_SESSION['user']['id'];
        // Bouton Like cliqué, ajoutez ici la logique pour gérer le Like
        NoteTouite::likeTouite($userID, $touiteID);
    }

    public function Dislikebutton($touiteID) {
        $userID = $_SESSION['user']['id'];
        // Bouton Dislike cliqué, ajoutez ici la logique pour gérer le Dislike
        NoteTouite::dislikeTouite($userID, $touiteID);
    }
}
