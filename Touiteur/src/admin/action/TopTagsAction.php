<?php

namespace iutnc\BackOffice\action;

use iutnc\BackOffice\db\ConnectionFactory;
use PDO;

class TopTagsAction extends Action
{
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $topTags = $this->getTopTags($db);

        $result = '<h2>Top Tags</h2>';
        if (!empty($topTags)) {
            $result .= '<ul>';
            foreach ($topTags as $tag) {
                $result .= '<li>' . $tag['tag'] . ': ' . $tag['count'] . ' fois</li>';
            }
            $result .= '</ul>';
        } else {
            $result .= '<p>Aucun tag trouvé.</p>';
        }

        return $result;
    }

    private function getTopTags($db)
    {
        $stmt = $db->prepare("SELECT lt.ID_Tag, t.Libelle AS tag, COUNT(lt.ID_Tag) AS count
                         FROM listetouitetag lt
                         JOIN tag t ON lt.ID_Tag = t.ID_Tag
                         GROUP BY lt.ID_Tag, t.Libelle
                         ORDER BY count DESC ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
