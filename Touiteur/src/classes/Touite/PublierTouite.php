<?php
namespace iutnc\touiteur\Touite;
use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\tag\SaveTag;

class PublierTouite
{
    public static function touite(string $contenu, $db): bool
    {
        $DatePublication = date('Y-m-d H:i:s');
        $stmt = $db->prepare("INSERT INTO touite (contenu, DatePublication) VALUES (?, ?)");
        $SaveTag = new SaveTag();

        if ($stmt->execute([$contenu, $DatePublication])) {
            $idTouite = $db->lastInsertId();
            $tags = $SaveTag->extractHashtags($contenu);
            $SaveTag->saveTagsToDatabase($tags, $idTouite, $db);
            $query = "INSERT INTO listetouiteutilisateur (id_utilisateur, ID_Touite) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $userID = $_SESSION["user"]["id"];
            $stmt->execute([$userID, $idTouite]);

            // L'enregistrement a réussi
            return true;
        } else {
            throw new AuthException("L'enregistrement a échoué.");
        }
    }

}

?>
