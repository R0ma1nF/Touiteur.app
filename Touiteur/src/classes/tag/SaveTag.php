<?php

namespace iutnc\touiteur\tag;

class SaveTag {

    function saveTagsToDatabase($tags, $touiteId, $db) {
        foreach ($tags as $tag) {
            $query = $db->prepare("SELECT ID_Tag FROM Tag WHERE Libelle = ?");
            $query->execute([$tag]);
            $result = $query->fetch();

            if ($result) {
                $tagId = $result['ID_Tag'];
            } else {
                $query = $db->prepare("INSERT INTO Tag (Libelle) VALUES (?)");
                $query->execute([$tag]);
                $tagId = $db->lastInsertId();
            }

            $query = $db->prepare("INSERT INTO listeTouiteTag (ID_Tag, ID_Touite) VALUES (?, ?)");
            $query->execute([$tagId, $touiteId]);
        }
    }

    function transformTagsToLinks($text) {
        $pattern = '/#(\w+)/';
        $replace = '<a href="">#$1</a>';
        $textAvecLiens = preg_replace($pattern, $replace, $text);

        return $textAvecLiens;
    }


    function extractHashtags($text) {
        $hashtags = array();
        $pattern = '/#(\w+)/';
        preg_match_all($pattern, $text, $togs);

        if (!empty($togs[1])) {
            $hashtags = $togs[1];
        }

        return $hashtags;
    }


}