<?php

// Dispatcher.php
namespace iutnc\touiteur\dispatch;


use iutnc\touiteur\action\ActionTouite;
use iutnc\touiteur\action\AddUserAction;
use iutnc\touiteur\action\DefaultAction;
use iutnc\touiteur\action\DisconnectAction;
use iutnc\touiteur\action\SignInAction;

class Dispatcher
{
    private string $action;
    private array $actionMappings;

    public function __construct()
    {
        $action = $_GET['action'] ?? 'default';
        $this->action = $action;

        // Initialize the action mappings as before, mapping action names to their corresponding Action classes
        $this->actionMappings = [
            'Inscription' => AddUserAction::class,
            'Connexion' => SignInAction::class,
            'Accueil' => DefaultAction::class,
            'Deconnexion' => DisconnectAction::class,
            'PublierTouit' => ActionTouite::class,

            // Add other actions as needed
        ];
    }

    public function run(): void
    {
        // Check if the action exists in the mappings
        if (isset($this->actionMappings[$this->action])) {
            $actionClass = $this->actionMappings[$this->action];
            $actionObject = new $actionClass();

            $pageContent = $actionObject();
        } else {
            // Default action for unknown actions
            $defaultAction = new DefaultAction();
            $pageContent = $defaultAction();
        }

        $this->renderPage($pageContent);
    }

    public function renderPage(string $html): void
    {
        echo '<!DOCTYPE html>';
        echo '<html lang="fr">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<link rel="stylesheet" type="text/css" href="css/index.css">';
        echo '<title>Touiteur</title>';
        // N'insérez pas de balises de titre (h1) ici, car vous avez déjà ajouté un titre dans la balise head.

        echo '</head>';
        echo '<body>';
        echo '<header>';
        echo '<h1>Touiteur</h1>';
        echo '<div class="top-bar">';

        // Laissez le reste de votre code inchangé
        foreach ($this->actionMappings as $actionName => $actionClass) {
            echo '<form method="GET" action="index.php">';
            echo '<input type="hidden" name="action" value="' . $actionName . '">';
            echo '<button type="submit">' . ucwords(str_replace("-", " ", $actionName)) . '</button>';
            echo '</form>';
        }

        echo '</div>'; // Fermez la div du bandeau supérieur
        echo '</header>';

        echo $html; // Affiche le contenu de la page généré par l'action
        echo '</body>';
        echo '</html>';
    }

}
