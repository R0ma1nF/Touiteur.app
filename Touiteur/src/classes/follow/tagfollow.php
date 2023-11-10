<?php

namespace iutnc\touiteur\follow;

use iutnc\touiteur\db\ConnectionFactory;
use PDO;

/**
 * Classe tagfollow permettant de gérer les abonnements aux tags.
 */
class tagfollow
{
    /**
     * Fonction pour suivre un tag.
     *
     * @param int $followerID ID de l'utilisateur qui souhaite suivre le tag.
     * @param int $tagfollowedID ID du tag à suivre.
     * @return bool Retourne true si l'opération est réussie, false sinon.
     */
    public static function followTag($followerID, $tagfollowedID): bool
    {
        // Établir la connexion à la base de données.
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        // Vérifier si l'utilisateur suit déjà le tag.
        $stmtCheck = $db->prepare("SELECT * FROM abonnementtag WHERE ID_Utilisateur = :followerID AND ID_Tag = :tagfollowedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            // L'utilisateur suit déjà le tag, retourner false.
            return false;
        } else {
            // L'utilisateur ne suit pas encore le tag, l'ajouter à la liste des abonnements.
            $stmt = $db->prepare("INSERT INTO abonnementtag (ID_Utilisateur, ID_Tag) VALUES (:followerID, :tagfollowedID)");
            $stmt->bindParam(':followerID', $followerID, PDO::PARAM_INT);
            $stmt->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        }
    }

    /**
     * Fonction pour ne plus suivre un tag.
     *
     * @param int $followerID ID de l'utilisateur qui souhaite cesser de suivre le tag.
     * @param int $tagfollowedID ID du tag à ne plus suivre.
     * @return bool Retourne true si l'opération est réussie, false sinon.
     */
    public static function unfollowTag($followerID, $tagfollowedID): bool
    {
        // Établir la connexion à la base de données.
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        // Vérifier si l'utilisateur suit le tag avant de le désabonner.
        $stmtCheck = $db->prepare("SELECT * FROM abonnementtag WHERE ID_Utilisateur = :followerID AND ID_Tag = :tagfollowedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            // L'utilisateur suit le tag, le retirer de la liste des abonnements.
            $stmt = $db->prepare("DELETE FROM abonnementtag WHERE ID_Utilisateur = :followerID AND ID_Tag = :tagfollowedID");
            $stmt->bindParam(':followerID', $followerID, PDO::PARAM_INT);
            $stmt->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } else {
            // L'utilisateur ne suit pas le tag, retourner false.
            return false;
        }
    }
}
