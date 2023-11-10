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


        $this->actionMappings = [
            '10' => [
                'Inscription' => AddUserAction::class,
                'Connexion' => SignInAction::class,
                'Accueil' => DefaultAction::class,
                'testdetail' => TouiteDetailsAction::class,
                'userDetail' => UserDetail::class,
                'tagList' => TagAction::class,
                'searchTag' => SearchTagAction::class,

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

        if (isset($this->actionMappings[$userRole][$this->action])) {
            $actionClass = $this->actionMappings[$userRole][$this->action];
            $actionObject = new $actionClass();
            $pageContent = $actionObject();
        } else {
            $defaultAction = new DefaultAction();
            $pageContent = $defaultAction();
        }

        $this->renderPage($pageContent);
    }

    public function renderPage(string $html): void
    {
        $res = '';
        $res .= '<!DOCTYPE html>';
        $res .= '<html lang="fr">';
        $res .= '<head>';
        $res .= '<meta charset="UTF-8">';
        $res .= '<link rel="stylesheet" type="text/css" href="css/index.css">';
        $res .= '<title>Touiteur</title>';
        $res .= '</head>';
        $res .= '<body>';
        $res .= '<header>';
        $res .= '<h1>Touiteur</h1>';
        $res .= '<div class="top-bar">';
        $userRole = $_SESSION['user']['role'] ?? 'guest';

        $res .= '<form class="nav-form" method="GET" action="index.php">';
        $res .= '<input type="hidden" name="action" value="searchTag">';
        $res .= '<input type="text" name="tag" placeholder="Search for a tag">';
        $res .= '<button type="submit">Search</button>';
        $res .= '</form>';

        if (isset($this->actionMappings[$userRole]) && is_array($this->actionMappings[$userRole])) {
            foreach ($this->actionMappings[$userRole] as $actionName => $actionClass) {
                if ($actionName !== 'testdetail' && $actionName !== 'userDetail' && $actionName !== 'tagList' && $actionName !== 'searchTag') {
                   $res .= '<form method="GET" action="index.php">';
                   $res .= '<input type="hidden" name="action" value="' . $actionName . '">';
                   $res .= '<button type="submit">' . ucwords(str_replace("-", " ", $actionName)) . '</button>';
                   $res .= '</form>';
                }
            }
        }

       $res .= '</div>';
       $res .= '</header>';

       $res .= $html;
       $res .= '</body>';
       $res .= '</html>';
       echo $res;
    }

}
