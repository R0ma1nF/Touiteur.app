<?php
//classe permettant de noter un touite
namespace iutnc\BackOffice\Touite;
use iutnc\BackOffice\db\ConnectionFactory as ConnectionFactory;
use iutnc\BackOffice\exception\AuthException;

class NoteTouite
{
    public static function likeTouite(int $userID, int $touiteID): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        $role = $_SESSION["user"]["role"];

        // Vérifiez le rôle de l'utilisateur
        if ($role == 100 || $role == 1) {
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
        } else {
            //faire en sorte que le html suivant s'affiche apres le header
            echo '<h2>Erreur</h2>';
            echo "Vous n'avez pas le droit de noter un touite si vous n'êtes pas autorisé (role = $role)";
            echo '<br>';
            echo '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';

        }
        return false;
    }


    public static function dislikeTouite(int $userID, int $touiteID): bool
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $role = $_SESSION["user"]["role"];
        if ($role == 100 || $role == 1) {
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
        } else {
            //faire en sorte que le html suivant s'affiche apres le header
            echo '<h2>Erreur</h2>';
            echo "Vous n'avez pas le droit de noter un touite si vous n'êtes pas autorisé (role = $role)";
            echo '<br>';
            echo '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';

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