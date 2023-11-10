<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;

/**
 * La classe SearchTagAction représente une action pour rechercher des touites par tag.
 */
class SearchTagAction extends Action
{
    /**
     * Exécute l'action de recherche de touites par tag.
     *
     * @return string Le résultat de l'action sous forme de chaîne de caractères.
     */
    public function execute(): string
    {
        // Récupère le tag depuis les paramètres de la requête GET
        $tag = isset($_GET['tag']) ? $_GET['tag'] : '';

        // Vérifie si un tag a été spécifié
        if (!empty($tag)) {
            // Initialise la connexion à la base de données
            $db = ConnectionFactory::setConfig('db.config.ini');
            $db = ConnectionFactory::makeConnection();

            // Vérifie si le tag existe dans la base de données
            if ($this->tagExists($db, $tag)) {
                // Récupère la liste des touites associées au tag
                $tagList = new TagAction();
                $tagList = $tagList->listeTouiteByTag($db, $tag);

                // Vérifie s'il y a des touites associées au tag
                if ($tagList != '') {
                    return '<h1>Touites avec le tag ' . $tag . '</h1><br>' . $tagList;
                } else {
                    return '<h1> Pas de touites avec le tag ' . $tag .'</h1>';
                }
            } else {
                return '<h1>Le tag ' . $tag . ' n\'existe pas.' . '</h1>';
            }
        } else {
            return '<h1> Aucun tag spécifié. </h1>';
        }
    }

    /**
     * Vérifie si un tag existe dans la base de données.
     *
     * @param \PDO $db La connexion à la base de données.
     * @param string $tag Le tag à vérifier.
     * @return bool Retourne vrai si le tag existe, sinon faux.
     */
    private function tagExists($db, $tag): bool
    {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM tag WHERE Libelle = :tag");
        $stmt->bindValue(':tag', $tag, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }
}
