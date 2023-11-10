<?php


namespace iutnc\touiteur\follow;

use iutnc\touiteur\db\ConnectionFactory;
use PDO;

class tagfollow
{
    public static function followTag($followerID, $tagfollowedID): bool
    {
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        $stmtCheck = $db->prepare("SELECT * FROM abonnementtag WHERE ID_Utilisateur = :followerID AND ID_Tag = :tagfollowedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
        $stmtCheck->execute();

         if ($stmtCheck->rowCount() > 0) {
            return false;
        } else {
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

        $stmtCheck = $db->prepare("SELECT * FROM abonnementtag WHERE ID_Utilisateur = :followerID AND ID_Tag = :tagfollowedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':tagfollowedID', $tagfollowedID, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
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
