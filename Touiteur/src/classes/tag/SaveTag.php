<?php

namespace iutnc\touiteur\tag;

class SaveTag {

    function saveTagsToDatabase($tags, $touiteId, $db) {
        foreach ($tags as $tag) {
            // Vérifie si le tag existe déjà dans la table "Tag"
            $query = $db->prepare("SELECT ID_Tag FROM Tag WHERE Libelle = :tag");
            $query->execute(['tag' => $tag]);
            $result = $query->fetch();

            if ($result) {
                $tagId = $result['ID_Tag'];
            } else {
                // Si le tag n'existe pas, l'insérer dans la table "Tag"
                $query = $db->prepare("INSERT INTO Tag (Libelle) VALUES (:tag)");
                $query->execute(['tag' => $tag]);
                $tagId = $db->lastInsertId();
            }

            // Insérer la relation entre le touite et le tag dans la table "listTouiteTag"
            $query = $db->prepare("INSERT INTO listTouiteTag (ID_Tag, ID_Touite) VALUES (:tagId, :touiteId)");
            $query->execute(['tagId' => $tagId, 'touiteId' => $touiteId]);
        }
    }

    function transformTagsToLinks($text) {
        // Utilisez une expression régulière pour trouver tous les hashtags dans le texte
        $pattern = '/#(\w+)/';
        $replace = '<a href="tag.php?tag=$1">#$1</a>';
        $textAvecLiens = preg_replace($pattern, $replace, $text);

        return $textAvecLiens;
    }


}