<?php

namespace iutnc\BackOffice\action;

use iutnc\BackOffice\db\ConnectionFactory;

class SearchTagAction extends Action
{
    public function execute(): string
    {
        $tag = isset($_GET['tag']) ? $_GET['tag'] : '';

        if (!empty($tag)) {
            $db = ConnectionFactory::setConfig('db.config.ini');
            $db = ConnectionFactory::makeConnection();

            // Check if the tag exists before attempting to get the list
            if ($this->tagExists($db, $tag)) {
                $tagList = new TagAction();
                $tagList = $tagList->listeTouiteByTag($db, $tag);

                if ($tagList != '') {
                    return 'Touites avec le tag ' . $tag . '<br>' . $tagList;
                } else {
                    return 'Pas de touites avec le tag ' . $tag;
                }
            } else {
                return 'Le tag ' . $tag . ' n\'existe pas.';
            }
        } else {
            return 'Aucun tag spécifié.';
        }
    }

    private function tagExists($db, $tag): bool
    {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM tag WHERE Libelle = :tag");
        $stmt->bindValue(':tag', $tag, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }
}
