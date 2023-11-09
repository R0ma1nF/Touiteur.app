<?php
namespace iutnc\touiteur\Touite;
use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\tag\SaveTag;

class PublierTouite
{
    public static function touite(string $contenu, string $imagePath, $db): bool
    {
        $DatePublication = date('Y-m-d H:i:s');
        // Insérer d'abord le chemin du fichier image dans la table 'image'
        $stmt = $db->prepare("INSERT INTO image (CheminFichier) VALUES (?)");
        $stmt->execute([$imagePath]);

        $imageId = $db->lastInsertId(); // Récupérer l'ID de l'image insérée

        // Insérer ensuite le touite dans la table 'touite' avec l'ID de l'image
        $stmt = $db->prepare("INSERT INTO touite (contenu, DatePublication, ID_Image) VALUES (?, ?, ?)");
        $SaveTag = new SaveTag();

        if ($stmt->execute([$contenu, $DatePublication, $imageId])) {
            $idTouite = $db->lastInsertId();
            $tags = $SaveTag->extractHashtags($contenu);
            $SaveTag->saveTagsToDatabase($tags, $idTouite, $db);

            // Insérer également l'enregistrement dans la table 'listetouiteutilisateur'
            $query = "INSERT INTO listetouiteutilisateur (id_utilisateur, ID_Touite) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $userID = $_SESSION["user"]["id"];
            $stmt->execute([$userID, $idTouite]);

            // L'enregistrement a réussi
            return true;
        }

        throw new AuthException("L'enregistrement a échoué.");
    }

}

?>
