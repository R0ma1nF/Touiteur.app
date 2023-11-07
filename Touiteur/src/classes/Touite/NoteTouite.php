<?php
//classe permettant de noter un touite
namespace iutnc\touiteur\Touite;
use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
use iutnc\touiteur\exception\AuthException;

class NoteTouite
{
    //structure de la table notetouite : id_note, id_utilisateur, id_touite, note
    public static function likeTouite(int $userID, int $touiteID): bool
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $stmt = $db->prepare("INSERT INTO notetouite (id_utilisateur, id_touite, note) VALUES (?, ?, 1)");
        if ($stmt->execute([$userID, $touiteID])) {
            // L'enregistrement a réussi
            return true;
        } else {
            throw new AuthException("L'enregistrement a échoué.");
        }
    }

    public static function dislikeTouite(int $userID, int $touiteID): bool
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $stmt = $db->prepare("INSERT INTO notetouite (id_utilisateur, id_touite, note) VALUES (?, ?, -1)");
        if ($stmt->execute([$userID, $touiteID])) {
            // L'enregistrement a réussi
            return true;
        } else {
            throw new AuthException("L'enregistrement a échoué.");
        }
    }

    public static function getNoteTouite(int $touiteID): int
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $stmt = $db->prepare("SELECT SUM(note) FROM notetouite WHERE id_touite = ?");
        $stmt->execute([$touiteID]);
        $note = $stmt->fetchColumn();
        return $note;
    }

}