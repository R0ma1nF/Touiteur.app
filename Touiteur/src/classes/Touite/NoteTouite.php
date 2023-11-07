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
        //verifier si l'utilisateur a deja like le touite
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM notetouite WHERE id_utilisateur = ? AND id_touite = ? and note = 1");
        $stmt2->execute([$userID, $touiteID]);
        $result = $stmt2->fetch();
        if ($result > 0) {
            //si l'utilisateur a deja like le touite, on supprime son like
            $stmt3 = $db->prepare("DELETE FROM notetouite WHERE id_utilisateur = ? AND id_touite = ?");
            $stmt3->execute([$userID, $touiteID]);
            return true;
        }else {

            if ($stmt->execute([$userID, $touiteID])) {
                // L'enregistrement a réussi
                return true;
            } else {
                throw new AuthException("L'enregistrement a échoué.");
            }
        }
    }

    public static function dislikeTouite(int $userID, int $touiteID): bool
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $stmt = $db->prepare("INSERT INTO notetouite (id_utilisateur, id_touite, note) VALUES (?, ?, -1)");
        //verifier si l'utilisateur a deja dislike le touite
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM notetouite WHERE id_utilisateur = ? AND id_touite = ? and note = -1");
        $stmt2->execute([$userID, $touiteID]);
        $result = $stmt2->fetch();
        if ($result > 0) {
            //si l'utilisateur a deja dislike le touite, on supprime son dislike
            $stmt3 = $db->prepare("DELETE FROM notetouite WHERE id_utilisateur = ? AND id_touite = ?");
            $stmt3->execute([$userID, $touiteID]);
            return true;
        }else {
            if ($stmt->execute([$userID, $touiteID])) {
                // L'enregistrement a réussi
                return true;
            } else {
                throw new AuthException("L'enregistrement a échoué.");
            }
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