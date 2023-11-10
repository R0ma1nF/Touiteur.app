<?php

// Classe permettant de noter un touite
namespace iutnc\touiteur\Touite;

use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
use iutnc\touiteur\exception\AuthException;

class NoteTouite
{
    /**
     * Fonction pour "aimer" un touite.
     *
     * @param int $userID ID de l'utilisateur qui note le touite.
     * @param int $touiteID ID du touite à noter.
     *
     * @return bool|string Retourne true si la note a été ajoutée avec succès, false sinon.
     *
     * @throws AuthException Lancée si l'enregistrement échoue.
     */
    public static function likeTouite(int $userID, int $touiteID): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Récupération du rôle de l'utilisateur depuis la session
        $role = $_SESSION["user"]["role"];

        // Vérification des droits de l'utilisateur pour noter un touite
        if ($role == 100 || $role == 1) {
            // Vérification si l'utilisateur a déjà noté ce touite
            $stmt = $db->prepare("SELECT note FROM notetouite WHERE id_utilisateur = ? AND id_touite = ?");
            $stmt->execute([$userID, $touiteID]);
            $existingNote = $stmt->fetchColumn();

            if ($existingNote === false) {
                // Ajout d'une nouvelle note (1 pour "aimer")
                $stmt = $db->prepare("INSERT INTO notetouite (id_utilisateur, id_touite, note) VALUES (?, ?, 1)");
                if ($stmt->execute([$userID, $touiteID])) {
                    return true;
                } else {
                    throw new AuthException("L'enregistrement a échoué.");
                }
            }

            return false;
        } else {
            // Affichage d'une erreur si l'utilisateur n'a pas les droits nécessaires
            echo '<h2>Erreur</h2>';
            echo "Vous n'avez pas le droit de noter un touite si vous n'êtes pas autorisé (role = $role)";
            echo '<br>';
            echo '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
        }

        return false;
    }

    /**
     * Fonction pour "ne pas aimer" un touite.
     *
     * @param int $userID ID de l'utilisateur qui note le touite.
     * @param int $touiteID ID du touite à noter.
     *
     * @return bool Retourne true si la note a été ajoutée avec succès, false sinon.
     *
     * @throws AuthException Lancée si l'enregistrement échoue.
     */
    public static function dislikeTouite(int $userID, int $touiteID): bool
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Récupération du rôle de l'utilisateur depuis la session
        $role = $_SESSION["user"]["role"];

        // Vérification des droits de l'utilisateur pour noter un touite
        if ($role == 100 || $role == 1) {
            // Vérification si l'utilisateur a déjà noté ce touite
            $stmt = $db->prepare("SELECT note FROM notetouite WHERE id_utilisateur = ? AND id_touite = ?");
            $stmt->execute([$userID, $touiteID]);
            $existingNote = $stmt->fetchColumn();

            if ($existingNote === false) {
                // Ajout d'une nouvelle note (-1 pour "ne pas aimer")
                $stmt = $db->prepare("INSERT INTO notetouite (id_utilisateur, id_touite, note) VALUES (?, ?, -1)");
                if ($stmt->execute([$userID, $touiteID])) {
                    return true;
                } else {
                    throw new AuthException("L'enregistrement a échoué.");
                }
            }

            return false;
        } else {
            // Affichage d'une erreur si l'utilisateur n'a pas les droits nécessaires
            echo '<h2>Erreur</h2>';
            echo "Vous n'avez pas le droit de noter un touite si vous n'êtes pas autorisé (role = $role)";
            echo '<br>';
            echo '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
        }

        return false;
    }

    /**
     * Fonction pour obtenir la note totale d'un touite.
     *
     * @param int $touiteID ID du touite dont on souhaite obtenir la note.
     *
     * @return int Retourne la somme des notes pour le touite donné.
     */
    public static function getNoteTouite(int $touiteID): int
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Récupération de la somme des notes pour le touite spécifié
        $stmt = $db->prepare("SELECT SUM(note) FROM notetouite WHERE id_touite = ?");
        $stmt->execute([$touiteID]);
        $note = $stmt->fetchColumn();

        // Si aucune note n'est trouvée, on renvoie 0
        return $note !== false ? (int) $note : 0;
    }
}
