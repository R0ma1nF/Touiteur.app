<?php

// Définition du namespace de la classe
namespace admin\touiteur\action;

// Importation de la classe Action du namespace iutnc\touiteur\action
use iutnc\touiteur\action\Action;

// Définition de la classe DefaultActionBO qui étend la classe Action
class DefaultActionBO extends Action {

    /**
     * Exécute l'action par défaut du BackOffice de Touiteur.
     *
     * @return string Le message de bienvenue sur la page d'accueil du BackOffice de Touiteur.
     */
    public function execute(): string {
        return 'Bienvenue sur la page d\'accueil du BackOffice de Touiteur';
    }
}
