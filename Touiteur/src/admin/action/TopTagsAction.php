<?php

namespace admin\touiteur\action;

use admin\touiteur\db\ConnectionFactory;
use iutnc\touiteur\action\Action;
use PDO;

/**
 * Classe TopTagsAction
 *
 * Cette classe représente une action qui récupère et affiche les tags les plus populaires.
 */
class TopTagsAction extends Action
{
    /**
     * Exécute l'action et retourne le résultat sous forme de chaîne de caractères HTML.
     *
     * @return string Le résultat HTML de l'action.
     */
    public function execute(): string
    {
        // Établir la connexion à la base de données
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Récupérer les tags les plus populaires
        $topTags = $this->getTopTags($db);

        // Construire le résultat HTML
        $result = '<h1>Top Tags</h1>';
        if (!empty($topTags)) {
            $result .= '<div class="adminTag"><ul>';
            foreach ($topTags as $tag) {
                $result .= '<li>' . $tag['tag'] . ': ' . $tag['count'] . ' fois</li>';
            }
            $result .= '</ul></div>';
        } else {
            $result .= '<h1>Aucun tag trouvé.</h1>';
        }

        return $result;
    }

    /**
     * Récupère les tags les plus populaires depuis la base de données.
     *
     * @param PDO $db L'objet PDO représentant la connexion à la base de données.
     * @return array Un tableau associatif contenant les tags et leur nombre d'occurrences.
     */
    private function getTopTags($db)
    {
        // Préparer et exécuter la requête SQL pour obtenir les tags les plus populaires
        $stmt = $db->prepare("SELECT lt.ID_Tag, t.Libelle AS tag, COUNT(lt.ID_Tag) AS count
                             FROM listetouitetag lt
                             JOIN tag t ON lt.ID_Tag = t.ID_Tag
                             GROUP BY lt.ID_Tag, t.Libelle
                             ORDER BY count DESC ");
        $stmt->execute();

        // Retourner le résultat sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
