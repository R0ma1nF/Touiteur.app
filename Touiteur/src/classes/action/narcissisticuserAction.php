<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;

/**
 * Classe représentant l'action pour un utilisateur narcissique sur Touiter.
 */
class narcissisticUserAction extends Action
{
    /**
     * Exécute l'action et retourne les détails pour un utilisateur narcissique.
     *
     * @return string Les détails de l'utilisateur narcissique.
     */
    public function execute(): string
    {
        // Établir la connexion à la base de données
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Récupérer l'ID de l'utilisateur en session
        $userID = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;

        // Récupérer la moyenne des scores des Touites de l'utilisateur
        $averageScore = $this->getAverageScore($db, $userID);

        // Récupérer les followers de l'utilisateur
        $followers = $this->getFollowers($db, $userID);

        // Récupérer l'identité de l'utilisateur
        $identite = $this->getIdentite($db, $userID);

        // Construire les détails à afficher
        $details = 'Bienvenue sur Touiter' . '<br>';
        $details .= $identite . '<br>';
        $details .= 'Moyenne des Scores de Vos Touites: ' . $averageScore . '<br>';
        $details .= 'Utilisateurs qui vous suivent: ' . '<br>' . $followers;

        return $details;
    }

    /**
     * Calcule et retourne la moyenne des scores des Touites de l'utilisateur.
     *
     * @param \PDO $db La connexion à la base de données.
     * @param int $userID L'ID de l'utilisateur.
     * @return float La moyenne des scores.
     */
    public function getAverageScore($db, $userID)
    {
        $stmt = $db->prepare("
            SELECT AVG(nt.Note) AS MoyenneScore
            FROM listetouiteutilisateur lt
            LEFT JOIN notetouite nt ON lt.ID_Touite = nt.ID_Touite
            WHERE lt.ID_Utilisateur = :userID
        ");
        $stmt->execute([':userID' => $userID]);
        $data = $stmt->fetch();
        $moynenne = round($data['MoyenneScore'], 1);

        return $moynenne;
    }

    /**
     * Récupère et retourne les followers de l'utilisateur.
     *
     * @param \PDO $db La connexion à la base de données.
     * @param int $userID L'ID de l'utilisateur.
     * @return string Les détails des followers.
     */
    public function getFollowers($db, $userID)
    {
        $stmt = $db->prepare("
            SELECT u.nom, u.prénom
            FROM user u
            JOIN abonnement a ON u.id_utilisateur = a.ID_Utilisateur
            WHERE a.ID_UtilisateurSuivi = :userID
        ");
        $stmt->execute([':userID' => $userID]);

        $followers = '';

        while ($data = $stmt->fetch()) {
            $followers .= '<div>';
            $followers .= 'Nom: ' . $data['nom'] . '<br>';
            $followers .= 'Prénom: ' . $data['prénom'] . '<br>';
            $followers .= '</div>';
        }

        return $followers;
    }

    /**
     * Récupère et retourne l'identité de l'utilisateur.
     *
     * @param \PDO $db La connexion à la base de données.
     * @param int $userID L'ID de l'utilisateur.
     * @return string L'identité de l'utilisateur.
     */
    private function getIdentite(\PDO $db, int $userID)
    {
        $requete = "SELECT nom, prénom FROM user WHERE id_utilisateur = :userID";
        $stmt = $db->prepare($requete);
        $stmt->execute([':userID' => $userID]);
        $data = $stmt->fetch();
        $identite = 'Nom: ' . $data['nom'] . '<br>';
        $identite .= 'Prénom: ' . $data['prénom'] . '<br>';

        return $identite;
    }
}
