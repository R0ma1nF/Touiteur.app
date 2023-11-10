<?php
namespace iutnc\touiteur\Touite;

use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\tag\SaveTag;

/**
 * La classe PublierTouite permet de publier un touite avec un contenu et une image associée.
 */
class PublierTouite
{
    /**
     * Publie un touite avec le contenu, l'image spécifiée, et enregistre les informations dans la base de données.
     *
     * @param string $contenu Le contenu du touite.
     * @param string $imagePath Le chemin de l'image associée au touite.
     * @param mixed $db L'objet de connexion à la base de données.
     *
     * @return bool Retourne true si le touite a été publié avec succès, sinon lance une exception.
     *
     * @throws AuthException Lancée si l'enregistrement échoue.
     */
    public static function touite(string $contenu, string $imagePath, $db): bool
    {
        // Récupération de la date actuelle pour la publication du touite.
        $DatePublication = date('Y-m-d H:i:s');

        // Insertion du chemin de l'image dans la table 'image'.
        $stmt = $db->prepare("INSERT INTO image (CheminFichier) VALUES (?)");
        $stmt->execute([$imagePath]);

        // Récupération de l'ID de l'image insérée.
        $imageId = $db->lastInsertId();

        // Insertion du touite dans la table 'touite'.
        $stmt = $db->prepare("INSERT INTO touite (contenu, DatePublication, ID_Image) VALUES (?, ?, ?)");
        $SaveTag = new SaveTag();

        // Vérification de la réussite de l'insertion du touite.
        if ($stmt->execute([$contenu, $DatePublication, $imageId])) {
            // Récupération de l'ID du touite inséré.
            $idTouite = $db->lastInsertId();

            // Extraction des hashtags du contenu du touite.
            $tags = $SaveTag->extractHashtags($contenu);

            // Enregistrement des hashtags dans la base de données.
            $SaveTag->saveTagsToDatabase($tags, $idTouite, $db);

            // Insertion de l'association utilisateur-touite dans la table 'listetouiteutilisateur'.
            $query = "INSERT INTO listetouiteutilisateur (id_utilisateur, ID_Touite) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $userID = $_SESSION["user"]["id"];
            $stmt->execute([$userID, $idTouite]);

            return true; // Le touite a été publié avec succès.
        }

        throw new AuthException("L'enregistrement a échoué.");
    }
}
?>
