<?php

namespace iutnc\touiteur\follow;

use iutnc\touiteur\db\ConnectionFactory;
use PDO;

/**
 * Classe UserFollow gérant les opérations de suivi/désabonnement d'utilisateurs.
 */
class userfollow
{
    /**
     * Permet à un utilisateur de suivre un autre utilisateur.
     *
     * @param int $followerID L'ID de l'utilisateur qui souhaite suivre.
     * @param int $followedID L'ID de l'utilisateur suivi.
     *
     * @return bool Retourne true si le suivi a été effectué avec succès, sinon false.
     */
    public static function followUser($followerID, $followedID): bool
    {
        // Établir la connexion à la base de données
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        // Vérifier si l'utilisateur suit déjà l'autre utilisateur
        $stmtCheck = $db->prepare("SELECT * FROM abonnement WHERE ID_Utilisateur = :followerID AND ID_UtilisateurSuivi = :followedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':followedID', $followedID, PDO::PARAM_INT);
        $stmtCheck->execute();

        // Vérifier si l'utilisateur tente de se suivre lui-même
        if ($followerID == $followedID) {
            return false;
        } elseif ($stmtCheck->rowCount() > 0) {
            // L'utilisateur suit déjà l'autre utilisateur
            return false;
        } else {
            // Effectuer le suivi en ajoutant une entrée dans la table d'abonnement
            $stmt = $db->prepare("INSERT INTO abonnement (ID_Utilisateur, ID_UtilisateurSuivi) VALUES (:followerID, :followedID)");
            $stmt->bindParam(':followerID', $followerID, PDO::PARAM_INT);
            $stmt->bindParam(':followedID', $followedID, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        }
    }

    /**
     * Permet à un utilisateur de cesser de suivre un autre utilisateur.
     *
     * @param int $followerID L'ID de l'utilisateur qui souhaite cesser de suivre.
     * @param int $followedID L'ID de l'utilisateur suivi.
     *
     * @return bool Retourne true si le désabonnement a été effectué avec succès, sinon false.
     */
    public static function unfollowUser($followerID, $followedID): bool
    {
        // Établir la connexion à la base de données
        $db = ConnectionFactory::setConfig("db.config.ini");
        $db = ConnectionFactory::makeConnection();

        // Vérifier si l'utilisateur suit l'autre utilisateur
        $stmtCheck = $db->prepare("SELECT * FROM abonnement WHERE ID_Utilisateur = :followerID AND ID_UtilisateurSuivi = :followedID");
        $stmtCheck->bindParam(':followerID', $followerID, PDO::PARAM_INT);
        $stmtCheck->bindParam(':followedID', $followedID, PDO::PARAM_INT);
        $stmtCheck->execute();

        // Si l'utilisateur suit l'autre utilisateur, effectuer le désabonnement
        if ($stmtCheck->rowCount() > 0) {
            $stmt = $db->prepare("DELETE FROM abonnement WHERE ID_Utilisateur = :followerID AND ID_UtilisateurSuivi = :followedID");
            $stmt->bindParam(':followerID', $followerID, PDO::PARAM_INT);
            $stmt->bindParam(':followedID', $followedID, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } else {
            // L'utilisateur ne suit pas l'autre utilisateur
            return false;
        }
    }
}
