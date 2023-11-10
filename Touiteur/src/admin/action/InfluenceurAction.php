<?php

namespace iutnc\BackOffice\action;

use iutnc\BackOffice\db\ConnectionFactory;
use iutnc\BackOffice\action\Action;
use PDO;

class InfluenceurAction extends Action
{

    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $influenceur = $this->Influenceur($db);

        $result = '<h2>Top Utilisateur</h2>';
        if (!empty($influenceur)) {
            $result .= '<ul>';
            foreach ($influenceur as $i) {
                $result .= '<li>' . $i['nom'] . ' ' . $i['prénom'] . ' ' . $i['email'] . ' suivi par ' . $i['influenceur'] . ' personnes</li>';
            }
            $result .= '</ul>';
        } else {
            $result .= '<p>Aucun tag trouvé.</p>';
        }

        return $result;
    }

    private function Influenceur($db)
    {
        // Requête SQL pour obtenir la liste des utilisateurs les plus influents
        $stmt = $db->prepare("SELECT u.id_utilisateur, u.nom, u.prénom, u.email, COUNT(a.id_abonnement) AS influenceur
                FROM user u
                LEFT JOIN abonnement a ON u.id_utilisateur = a.id_utilisateurSuivi
                GROUP BY u.id_utilisateur
                ORDER BY influenceur DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}