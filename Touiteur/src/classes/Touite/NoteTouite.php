<?php
//classe permettant de noter un touite
namespace iutnc\touiteur\Touite;
use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
use iutnc\touiteur\exception\AuthException;

class NoteTouite
{
    public static function likeTouite(int $userID, int $touiteID): bool
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Vérifier si l'utilisateur a déjà noté le touite
        $stmt = $db->prepare("SELECT note FROM notetouite WHERE id_utilisateur = ? AND id_touite = ?");
        $stmt->execute([$userID, $touiteID]);
        $existingNote = $stmt->fetchColumn();

        if ($existingNote === false) {
            // L'utilisateur n'a pas encore noté le touite, ajoutez une note "like"
            $stmt = $db->prepare("INSERT INTO notetouite (id_utilisateur, id_touite, note) VALUES (?, ?, 1)");
            if ($stmt->execute([$userID, $touiteID])) {
                return true;
            } else {
                throw new AuthException("L'enregistrement a échoué.");
            }
        }

        return false;
    }

    public static function dislikeTouite(int $userID, int $touiteID): bool
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Vérifier si l'utilisateur a déjà noté le touite
        $stmt = $db->prepare("SELECT note FROM notetouite WHERE id_utilisateur = ? AND id_touite = ?");
        $stmt->execute([$userID, $touiteID]);
        $existingNote = $stmt->fetchColumn();

        if ($existingNote === false) {
            // L'utilisateur n'a pas encore noté le touite, ajoutez une note "dislike"
            $stmt = $db->prepare("INSERT INTO notetouite (id_utilisateur, id_touite, note) VALUES (?, ?, -1)");
            if ($stmt->execute([$userID, $touiteID])) {
                return true;
            } else {
                throw new AuthException("L'enregistrement a échoué.");
            }
        }

        return false;
    }

    public static function getNoteTouite(int $touiteID): int
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $stmt = $db->prepare("SELECT SUM(note) FROM notetouite WHERE id_touite = ?");
        $stmt->execute([$touiteID]);
        $note = $stmt->fetchColumn();

        return $note !== false ? (int) $note : 0;
    }
}



