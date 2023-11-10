<?php
namespace admin\touiteur\action;
use iutnc\touiteur\action\Action;

class DefaultActionBO extends Action{
    public function execute(): string
    {
        return 'Bienvenue sur la page d\'accueil du BackOffice de Touiteur';
    }
}
