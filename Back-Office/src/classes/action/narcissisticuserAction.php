<?php

namespace iutnc\BackOffice\action;

use iutnc\BackOffice\db\ConnectionFactory;

class narcissisticUserAction extends Action
{
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $userID = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0; // Get the userID from the session

        // Première requête pour récupérer la moyenne des notes des touites de l'utilisateur actuel
        $averageScore = $this->getAverageScore($db, $userID);

        // Deuxième requête pour afficher les noms et prénoms des utilisateurs qui le suivent
        $followers = $this->getFollowers($db, $userID);
        $identite = $this->getIdentite($db, $userID);
        $details = 'Bienvenue sur Touiter' . '<br>';
        $details .= $identite . '<br>';
        $details .= 'Moyenne des Scores de Vos Touites: ' . $averageScore . '<br>';
        $details .= 'Utilisateurs qui vous suivent: ' . '<br>'. $followers ;

        return $details;
    }

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
        //recuperer la moyenne des notes des touites de l'utilisateur actuel et l'aroundir à 1 chiffres après la virgule
        $moynenne = round($data['MoyenneScore'], 1);

        return $moynenne;
    }

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
