<?php

namespace iutnc\touiteur\tag;

class SaveTag
{

    /**
     * Enregistre les tags dans la base de données.
     *
     * @param array $tags Liste des tags à enregistrer.
     * @param int $touiteId ID du touite auquel les tags sont associés.
     * @param object $db Instance de la connexion à la base de données.
     */
    function saveTagsToDatabase($tags, $touiteId, $db)
    {
        foreach ($tags as $tag) {
            // Vérifier si le tag existe déjà dans la base de données.
            $query = $db->prepare("SELECT ID_Tag FROM tag WHERE Libelle = ?");
            $query->execute([$tag]);
            $result = $query->fetch();

            if ($result) {
                // Si le tag existe, récupérer son ID.
                $tagId = $result['ID_Tag'];
            } else {
                // Si le tag n'existe pas, l'insérer dans la base de données.
                $query = $db->prepare("INSERT INTO tag (Libelle) VALUES (?)");
                $query->execute([$tag]);
                $tagId = $db->lastInsertId();
            }

            // Associer le tag au touite dans la table de liaison.
            $query = $db->prepare("INSERT INTO listetouitetag (ID_Tag, ID_Touite) VALUES (?, ?)");
            $query->execute([$tagId, $touiteId]);
        }
    }

    /**
     * Transforme les hashtags dans le texte en liens pointant vers la liste de tags.
     *
     * @param string $text Texte contenant des hashtags.
     * @return string Texte modifié avec des liens vers la liste de tags.
     */
    function transformTagsToLinks($text)
    {
        $pattern = '/#(\w+)/';
        $replace = '<a href="?action=tagList&tag=$1">#$1</a>';
        return preg_replace($pattern, $replace, $text);
    }

    /**
     * Extrait les hashtags d'un texte et les retourne sous forme de tableau.
     *
     * @param string $text Texte à analyser.
     * @return array Liste des hashtags extraits.
     */
    function extractHashtags($text): array
    {
        $hashtags = array();
        $pattern = '/#(\w+)/';
        preg_match_all($pattern, $text, $tabTag);

        if (!empty($tabTag[1])) {
            $hashtags = $tabTag[1];
        }

        return $hashtags;
    }
}
