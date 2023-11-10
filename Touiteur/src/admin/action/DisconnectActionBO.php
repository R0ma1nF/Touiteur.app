<?php
// DisconnectAction.php

// Namespace pour l'action Disconnect
namespace admin\touiteur\action;

// Utilisation de l'espace de noms de l'action générale
use iutnc\touiteur\action\Action;

// Classe DisconnectActionBO qui étend la classe Action
class DisconnectActionBO extends Action
{
    /**
     * Exécute l'action de déconnexion.
     *
     * @return string Le message de déconnexion.
     */
    public function execute(): string
    {
        // Détruit toutes les données de session
        $_SESSION = [];

        // Ferme l'écriture de la session
        session_write_close();

        // Retourne un message indiquant que l'utilisateur est déconnecté
        return "<h1> Vous étes deconnecter.  <a id='admin' href=\"admin.php\">Retour A L'Accueil</a> </h1>";
    }
}
