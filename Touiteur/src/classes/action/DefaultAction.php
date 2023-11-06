<?php

namespace iutnc\touiteur\action;

class DefaultAction extends Action
{
    public function execute(): string
    {
        return 'Bienvenue sur la page d\'accueil de Touiteur'; // Code pour gérer les requêtes GET ici
    }
}