<?php

namespace iutnc\touiteur\follow;

use iutnc\touiteur\db\ConnectionFactory;
use PDO;

class UserFollow
{
    public static function followUser($followerID, $followedID):bool
    {
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        // Check if the user is already following
        $stmtCheck = $db->prepare("SELECT * FROM abonnement WHERE ID_Utilisateur = :followerID AND ID_UtilisateurSuivi = :followedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':followedID', $followedID, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($followerID == $followedID){
            return false;
        }else if ($stmtCheck->rowCount() > 0) {

            return false;
        } else {
            $stmt = $db->prepare("INSERT INTO abonnement (ID_Utilisateur, ID_UtilisateurSuivi) VALUES (:followerID, :followedID)");
            $stmt->bindParam(':followerID', $followerID, PDO::PARAM_INT);
            $stmt->bindParam(':followedID', $followedID, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        }
    }


    public static function unfollowUser($followerID, $followedID):bool
    {
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        $stmtCheck = $db->prepare("SELECT * FROM abonnement WHERE ID_Utilisateur = :followerID AND ID_UtilisateurSuivi = :followedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':followedID', $followedID, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            $stmt = $db->prepare("DELETE FROM abonnement WHERE ID_Utilisateur = :followerID AND ID_UtilisateurSuivi = :followedID");
            $stmt->bindParam(':followerID', $followerID, PDO::PARAM_INT);
            $stmt->bindParam(':followedID', $followedID, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } else {
            return false;
        }
    }

}
