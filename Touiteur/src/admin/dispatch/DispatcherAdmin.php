<?php

namespace admin\touiteur\dispatch;
use admin\touiteur\action\DefaultActionBO;
use admin\touiteur\action\InfluenceurAction;
use admin\touiteur\action\SignInActionBO;
use admin\touiteur\action\TopTagsAction;
use admin\touiteur\action\DisconnectActionBO;
use admin\touiteur\exception\AuthException;

class DispatcherAdmin
{
    private string $action;
    private array $actionMappings;

    public function __construct()
    {
        $action = $_GET['action'] ?? 'default';
        $this->action = $action;

        $this->actionMappings = [
            '10' => [
                'Connexion' => SignInActionBO::class,
                'Déconnexion' => DisconnectActionBO::class,
            ],
            '100' => [
                'Déconnexion' => DisconnectActionBO::class,
                'Top Influenceurs' => InfluenceurAction::class,
                'Top Tags' => TopTagsAction::class,
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
            $defaultAction = new SignInActionBO();
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

        if (isset($this->actionMappings[$userRole]) && is_array($this->actionMappings[$userRole])) {
            foreach ($this->actionMappings[$userRole] as $actionName => $actionClass) {
                   $res .= '<form method="GET" action="admin.php">';
                   $res .= '<input type="hidden" name="action" value="' . $actionName . '">';
                   $res .= '<button type="submit">' . ucwords(str_replace("-", " ", $actionName)) . '</button>';
                   $res .= '</form>';
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
