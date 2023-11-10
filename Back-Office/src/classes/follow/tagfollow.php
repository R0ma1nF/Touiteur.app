<?php


namespace iutnc\BackOffice\follow;

use iutnc\BackOffice\db\ConnectionFactory;
use PDO;

class tagfollow
{
    public static function followTag($followerID, $tagfollowedID): bool
    {
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        // Check if the user is already following
        $stmtCheck = $db->prepare("SELECT * FROM abonnementtag WHERE ID_Utilisateur = :followerID AND ID_Tag = :tagfollowedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
        $stmtCheck->execute();

         if ($stmtCheck->rowCount() > 0) {
            // User is already following, handle it accordingly (e.g., show a message)
            return false;
        } else {
            // User is not following, insert a new record
            $stmt = $db->prepare("INSERT INTO abonnementtag (ID_Utilisateur, ID_Tag) VALUES (:followerID, :tagfollowedID)");
            $stmt->bindParam(':followerID', $followerID, PDO::PARAM_INT);
            $stmt->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        }
    }


    public static function unfollowTag($followerID, $tagfollowedID): bool
    {
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        // Check if the user is currently following
        $stmtCheck = $db->prepare("SELECT * FROM abonnementtag WHERE ID_Utilisateur = :followerID AND ID_Tag = :tagfollowedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            // User is following, delete the record
            $stmt = $db->prepare("DELETE FROM abonnementtag WHERE ID_Utilisateur = :followerID AND ID_Tag = :tagfollowedID");
            $stmt->bindParam(':followerID', $followerID, PDO::PARAM_INT);
            $stmt->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } else {
            return false;
        }
    }

}
