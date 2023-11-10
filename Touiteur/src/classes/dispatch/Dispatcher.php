<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\AddUserAction;
use iutnc\touiteur\action\DefaultAction;
use iutnc\touiteur\action\DisconnectAction;
use iutnc\touiteur\action\NarcissisticUserAction;
use iutnc\touiteur\action\SearchTagAction;
use iutnc\touiteur\action\SignInAction;
use iutnc\touiteur\action\TagAction;
use iutnc\touiteur\action\TouiteAction;
use iutnc\touiteur\action\TouiteDetailsAction;
use iutnc\touiteur\action\UserDetail;
use iutnc\touiteur\exception\AuthException;

class Dispatcher
{
    private string $action;
    private array $actionMappings;

    public function __construct()
    {
        $action = $_GET['action'] ?? 'default';
        $this->action = $action;

        // Define actions associated with roles
        $this->actionMappings = [
            '10' => [
                'Inscription' => AddUserAction::class,
                'Connexion' => SignInAction::class,
                'Accueil' => DefaultAction::class,
                'testdetail' => TouiteDetailsAction::class,
                'userDetail' => UserDetail::class,
                'tagList' => TagAction::class,
                'searchTag' => SearchTagAction::class,

                // Add guest actions as needed
            ],
            '1' => [
                'Inscription' => AddUserAction::class,
                'Connexion' => SignInAction::class,
                'Accueil' => DefaultAction::class,
                'Deconnexion' => DisconnectAction::class,
                'Publier-Touit' => TouiteAction::class,
                'testdetail' => TouiteDetailsAction::class,
                'userDetail' => UserDetail::class,
                'tagList' => TagAction::class,
                'mes abonnés' => narcissisticUserAction::class,
                'searchTag' => SearchTagAction::class,
                // Add user actions as needed
            ],
            '100' => [
                'Inscription' => AddUserAction::class,
                'Connexion' => SignInAction::class,
                'Accueil' => DefaultAction::class,
                'Deconnexion' => DisconnectAction::class,
                'Publier Touit' => TouiteAction::class,
                'testdetail' => TouiteDetailsAction::class,
                'userDetail' => UserDetail::class,
                'tagList' => TagAction::class,
                'mes abonnés' => narcissisticUserAction::class,
                'searchTag' => SearchTagAction::class,
            ],
        ];
    }

    public function run(): void
    {
        $userRole = $_SESSION['user']['role'] ?? $_SESSION['user']['role'] = '10';

        // Check if the action exists in the user's role actions
        if (isset($this->actionMappings[$userRole][$this->action])) {
            $actionClass = $this->actionMappings[$userRole][$this->action];
            $actionObject = new $actionClass();
            $pageContent = $actionObject();
        } else {
            // Default action for unknown actions or unauthorized actions
            $defaultAction = new DefaultAction();
            $pageContent = $defaultAction();
        }

        $this->renderPage($pageContent);
    }

    /**
     * @throws AuthException
     */
    public function renderPage(string $html): void
    {
        echo '<!DOCTYPE html>';
        echo '<html lang="fr">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<link rel="stylesheet" type="text/css" href="css/index.css">';
        echo '<title>Touiteur</title>';
        echo '</head>';
        echo '<body>';
        echo '<header>';
        echo '<h1>Touiteur</h1>';
        echo '<div class="top-bar">';
        $userRole = $_SESSION['user']['role'] ?? 'guest';

// Render the search bar for tags
        echo '<form class="nav-form" method="GET" action="index.php">';
        echo '<input type="hidden" name="action" value="searchTag">';
        echo '<input type="text" name="tag" placeholder="Search for a tag">';
        echo '<button type="submit">Search</button>';
        echo '</form>';

// Render the appropriate action links based on the user's role
        if (isset($this->actionMappings[$userRole]) && is_array($this->actionMappings[$userRole])) {
            foreach ($this->actionMappings[$userRole] as $actionName => $actionClass) {
                if ($actionName !== 'testdetail' && $actionName !== 'userDetail' && $actionName !== 'tagList' && $actionName !== 'searchTag') {
                    echo '<form method="GET" action="index.php">';
                    echo '<input type="hidden" name="action" value="' . $actionName . '">';
                    echo '<button type="submit">' . ucwords(str_replace("-", " ", $actionName)) . '</button>';
                    echo '</form>';
                }
            }
        }

        echo '</div>';
        echo '</header>';

        echo $html; // Display the content generated by the action
        echo '</body>';
        echo '</html>';
    }

}
