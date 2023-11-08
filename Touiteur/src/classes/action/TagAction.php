<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\tag\SaveTag;
use iutnc\touiteur\Touite\NoteTouite;
use iutnc\touiteur\action\DefaultAction;
class TagAction extends Action
{
    public function execute(): string
    {
        if (isset($_GET['tag'])) {
            $tag = $_GET['tag'];
            $db = ConnectionFactory::setConfig('db.config.ini');
            $db = ConnectionFactory::makeConnection();
            $liste = $this->listeTouiteByTag($db, $tag);
            return 'Touites avec le tag ' . $tag . '<br>' . $liste;
        } else {
            return 'Aucun tag spécifié.';
        }
    }

    public function listeTouiteByTag($db, $tag)
    {
        // Utilisez la variable $tag pour filtrer les touites liés au tag
        $stmt = $db->prepare("SELECT t.id_touite, t.contenu, t.datePublication, u.nom, u.prénom
                    FROM touite t
                    JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                    JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                    WHERE t.contenu LIKE :tag
                    ORDER BY t.datePublication DESC");
        $stmt->bindValue(':tag', "%#$tag%", \PDO::PARAM_STR);
        $stmt->execute();

        $res = '';
        while ($data = $stmt->fetch()) {
            $SaveTag = new SaveTag();
            $touiteID = $data['id_touite'];
            $res .= $data['prénom'] . ' ' . $data['nom'];
            $contenu = $SaveTag->transformTagsToLinks($data['contenu']);
            $datePublication = $data['datePublication'];

            $res .=  '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div><br>';
            $res .= '<form method="POST" action="?action=Default">
            <input type="hidden" name="touiteID" value="' . $touiteID . '">
            <button type="submit" name="likeTouite">Like</button>
            <button type="submit" name="dislikeTouite">Dislike</button>
        </form>';

            $note = NoteTouite::getNoteTouite($touiteID);
            $res .= 'Note: ' . $note . '<br><br>';
        }

        return $res;
    }
}
