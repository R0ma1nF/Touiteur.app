<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\AddUserAction;
use iutnc\touiteur\action\DefaultAction;
use iutnc\touiteur\action\DisconnectAction;
use iutnc\touiteur\action\narcissisticuserAction;
use iutnc\touiteur\action\SearchTagAction;
use iutnc\touiteur\action\SignInAction;
use iutnc\touiteur\action\TagAction;
use iutnc\touiteur\action\TouiteAction;
use iutnc\touiteur\action\TouiteDetailsAction;
use iutnc\touiteur\action\UserDetail;
use iutnc\touiteur\exception\AuthException;

/**
 * La classe Dispatcher gère la distribution des actions dans l'application Touiteur.
 */
class Dispatcher
{
    /** @var string $action L'action demandée par l'utilisateur. */
    private string $action;

    /** @var array $actionMappings Les mappings d'actions associés aux rôles d'utilisateurs. */
    private array $actionMappings;

    /**
     * Constructeur de la classe Dispatcher.
     */
    public function __construct()
    {
        // Récupérer l'action demandée depuis les paramètres GET, par défaut 'default'.
        $action = $_GET['action'] ?? 'default';
        $this->action = $action;

        // Initialiser les mappings d'actions pour différents rôles d'utilisateurs.
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
                'mes abonnés' => narcissisticuserAction::class,
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
                'mes abonnés' => narcissisticuserAction::class,
                'searchTag' => SearchTagAction::class,
            ],
        ];
    }

    /**
     * Exécute l'action demandée et affiche le contenu de la page résultante.
     */
    public function run(): void
    {
        // Récupérer le rôle de l'utilisateur depuis la session, par défaut '10' (invité).
        $userRole = $_SESSION['user']['role'] ?? $_SESSION['user']['role'] = '10';

        // Vérifier si l'action demandée est définie pour le rôle de l'utilisateur.
        if (isset($this->actionMappings[$userRole][$this->action])) {
            $actionClass = $this->actionMappings[$userRole][$this->action];
            $actionObject = new $actionClass();
            $pageContent = $actionObject();
        } else {
            // Si l'action n'est pas définie, utiliser l'action par défaut.
            $defaultAction = new DefaultAction();
            $pageContent = $defaultAction();
        }

        // Afficher la page résultante.
        $this->renderPage($pageContent);
    }

    /**
     * Affiche une page HTML avec le contenu fourni.
     *
     * @param string $html Le contenu HTML de la page.
     */
    public function renderPage(string $html): void
    {
        // Construire le code HTML de la page.
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
        $userRole = $_SESSION['user']['role'] ?? $_SESSION['user']['role'] = '10';
        // Formulaire de recherche de tag.
        $res .= '<form class="nav-form" method="GET" action="index.php">';
        $res .= '<input type="hidden" name="action" value="searchTag">';
        $res .= '<input type="text" name="tag" placeholder="Rechercher un tag">';
        $res .= '<button type="submit">Rechercher</button>';
        $res .= '</form>';

        // Afficher les liens d'action disponibles pour le rôle de l'utilisateur.
        if (isset($this->actionMappings[$userRole]) && is_array($this->actionMappings[$userRole])) {
            foreach ($this->actionMappings[$userRole] as $actionName => $actionClass) {
                // Exclure certaines actions du menu de navigation.
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

        // Afficher le contenu de la page.
        $res .= $html;
        $res .= '</body>';
        $res .= '</html>';

        // Afficher la page complète.
        echo $res;
    }
}
