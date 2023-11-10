<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\action\DefaultAction;
use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\follow\tagfollow;
use iutnc\touiteur\follow\UserFollow;
use iutnc\touiteur\tag\SaveTag;
use iutnc\touiteur\Touite\NoteTouite;

class TagAction extends Action
{
    /**
     * @throws \Exception
     */
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

    public function listeTouiteByTag($db, $tag): string
    {
        $stmt = $db->prepare("SELECT t.id_touite, t.contenu, t.datePublication, u.nom, u.prénom
            FROM touite t
            JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
            JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
            JOIN listetouitetag ltt ON t.id_touite = ltt.ID_Touite
            JOIN tag ON ltt.ID_Tag = tag.ID_Tag
            WHERE tag.Libelle = :tag
            ORDER BY t.datePublication DESC");
        $stmt->bindValue(':tag', $tag, \PDO::PARAM_STR);
        $stmt->execute();

        $res = '';
        $res .= '<h1>' . $tag . '</h1>';
        $res .= '<form method="POST" action="?action=tagList&tag=' . $tag . '">';
        $res .= '<input type="hidden" name="tag" value="' . $tag . '">';
        $res .= '<button type="submit" name="followTag">Follow</button>';
        $res .= '<button type="submit" name="unfollowTag">Unfollow</button>';
        $res .= '</form>';
        $stmtidtag = $db->prepare("SELECT ID_Tag FROM tag WHERE Libelle = :tag");
        $stmtidtag->bindValue(':tag', $tag, \PDO::PARAM_STR);
        $stmtidtag->execute();
        $data = $stmtidtag->fetch();
        $tag = $data['ID_Tag'];
        $userrole = $_SESSION['user']['role'];
        if ($userrole == '10') {
            $res .= "vous devez être connecté pour pouvoir suivre un tag";
        } else {
            if (isset($_POST['followTag'])) {
                //recuperer l'id du tag

                $followResult = tagfollow::followTag($_SESSION['user']['id'], $tag);
                if (!$followResult) {
                    $res .= '<div>Vous suivez déjà ce tag.</div>';
                } else {
                    $res .= '<div>Vous suivez maintenant ce tag.</div>';
                }
            } elseif (isset($_POST['unfollowTag'])) {
                $unfollowResult = tagfollow::unfollowTag($_SESSION['user']['id'], $tag);
                if (!$unfollowResult) {
                    $res .= '<div>Vous ne suivez pas ce tag.</div>';
                }
            }
        }
        while ($data = $stmt->fetch()) {
            $SaveTag = new SaveTag();
            $touiteID = $data['id_touite'];
            $res .= $data['prénom'] . ' ' . $data['nom'];
            $contenu = $SaveTag->transformTagsToLinks($data['contenu']);
            $datePublication = $data['datePublication'];


            $res .= '<div onclick="window.location=\'?action=testdetail&touiteID=' . $touiteID . '\';" style="cursor: pointer;"><p>' . $contenu . '</p>' . $datePublication . '</div><br>';
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
