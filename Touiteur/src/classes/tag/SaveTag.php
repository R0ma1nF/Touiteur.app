<?php

namespace iutnc\touiteur\tag;

class SaveTag {

    function saveTagsToDatabase($tags, $touiteId, $db) {
        foreach ($tags as $tag) {
            // Vérifie si le tag existe déjà dans la table "Tag"
            $query = $db->prepare("SELECT ID_Tag FROM Tag WHERE Libelle = ?");
            $query->execute([$tag]);
            $result = $query->fetch();

            if ($result) {
                $tagId = $result['ID_Tag'];
            } else {
                // Si le tag n'existe pas, l'insérer dans la table "Tag"
                $query = $db->prepare("INSERT INTO Tag (Libelle) VALUES (?)");
                $query->execute([$tag]);
                $tagId = $db->lastInsertId();
            }

            // Insérer la relation entre le touite et le tag dans la table "listTouiteTag"
            $query = $db->prepare("INSERT INTO listeTouiteTag (ID_Tag, ID_Touite) VALUES (?, ?)");
            $query->execute([$tagId, $touiteId]);
        }
    }

    function transformTagsToLinks($text) {
        // Utilisez une expression régulière pour trouver tous les hashtags dans le texte
        $pattern = '/#(\w+)/';
        $replace = '<a href="?action=tagListe&$1">#$1</a>';
        $textAvecLiens = preg_replace($pattern, $replace, $text);

        return $textAvecLiens;
    }

    function extractHashtags($text) {
        $hashtags = array();
        // Utilisez une expression régulière pour trouver tous les hashtags dans le texte
        $pattern = '/#(\w+)/';
        preg_match_all($pattern, $text, $togs);

        // Les hashtags extraits sont stockés dans $matches[1]
        if (!empty($togs[1])) {
            $hashtags = $togs[1];
        }

        return $hashtags;
    }


}