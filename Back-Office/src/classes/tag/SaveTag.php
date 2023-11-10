<?php

namespace iutnc\BackOffice\tag;

class SaveTag
{

    function saveTagsToDatabase($tags, $touiteId, $db)
    {
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

    function transformTagsToLinks($text)
    {
        $pattern = '/#(\w+)/';
        $replace = '<a href="?action=tagList&tag=$1">#$1</a>';
        return preg_replace($pattern, $replace, $text);
    }

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