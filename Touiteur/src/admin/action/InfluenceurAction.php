<?php

namespace admin\touiteur\action;

use admin\touiteur\db\ConnectionFactory;
use iutnc\touiteur\action\Action;
use PDO;

/**
 * Classe représentant une action pour afficher les influenceurs sur Touiteur.
 */
class InfluenceurAction extends Action
{
    /**
     * Exécute l'action et retourne le résultat sous forme de chaîne de caractères HTML.
     *
     * @return string Résultat de l'action sous forme de HTML.
     */
    public function execute(): string
    {
        // Établir la connexion à la base de données.
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        // Récupérer les influenceurs à partir de la base de données.
        $influenceurs = $this->getInfluenceurs($db);

        // Construire le résultat HTML.
        $result = '<h2>Top Utilisateurs</h2>';
        if (!empty($influenceurs)) {
            $result .= '<ul>';
            foreach ($influenceurs as $influenceur) {
                $result .= '<li>' . $influenceur['nom'] . ' ' . $influenceur['prénom'] . ' ' . $influenceur['email'] . ' suivi par ' . $influenceur['influenceur'] . ' personnes</li>';
            }
            $result .= '</ul>';
        } else {
            $result .= '<p>Aucun utilisateur trouvé.</p>';
        }

        return $result;
    }

    /**
     * Récupère les influenceurs à partir de la base de données.
     *
     * @param PDO $db Connexion à la base de données.
     * @return array Tableau associatif des influenceurs.
     */
    private function getInfluenceurs($db)
    {
        $stmt = $db->prepare("SELECT u.id_utilisateur, u.nom, u.prénom, u.email, COUNT(a.id_abonnement) AS influenceur
                FROM user u
                LEFT JOIN abonnement a ON u.id_utilisateur = a.id_utilisateurSuivi
                GROUP BY u.id_utilisateur
                ORDER BY influenceur DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
