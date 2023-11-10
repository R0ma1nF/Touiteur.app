<?php

namespace admin\touiteur\action;

use admin\touiteur\db\ConnectionFactory;
use admin\touiteur\db\User;
use admin\touiteur\auth\Auth;
use iutnc\touiteur\action\Action;

/**
 * Classe SignInActionBO
 *
 * Cette classe gère l'action de connexion de l'administrateur.
 *
 * @package admin\touiteur\action
 */
class SignInActionBO extends Action
{
    /**
     * Exécute l'action en fonction de la méthode HTTP utilisée.
     *
     * @return string Résultat de l'exécution de l'action.
     */
    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            return $this->handleGetRequest();
        } else {
            return $this->handlePostRequest();
        }
    }

    /**
     * Gère une requête HTTP de type GET en affichant le formulaire de connexion.
     *
     * @return string Le formulaire HTML de connexion.
     */
    public function handleGetRequest(): string
    {
        return '<div class="adminCo"><form method="POST" >
    <label for="user_email">Email de l\'administrateur</label>
    <input type="email" name="user_email" required>
    <br>
    <label for="user_passwd">Mot de passe de l\'administrateur</label>
    <input type="password" name="user_passwd" required>
    <br>
    <button type="submit">Se connecter</button>
</form></div>';
    }

    /**
     * Gère une requête HTTP de type POST en traitant les données du formulaire de connexion.
     *
     * @return string Résultat de l'authentification.
     */
    public function handlePostRequest(): string
    {
        $userEmail = filter_input(INPUT_POST, 'user_email', FILTER_SANITIZE_EMAIL);
        $userPasswd = filter_input(INPUT_POST, 'user_passwd', FILTER_SANITIZE_STRING);

        if ($userEmail && $userPasswd) {
            try {
                $db = ConnectionFactory::setConfig('db.config.ini');
                $db = ConnectionFactory::makeConnection();
                $isAuthenticated = Auth::authenticate($userEmail, $userPasswd, $db);

                if ($isAuthenticated) {
                    $user = new User();
                    return '<div class="adminInf"> Authentification réussie.' . '<br>' . 'Bienvenue ' . $userEmail . ' ! ' . '<br></div>';
                } else {
                    $message = '';
                    $message .= '<div class="adminInf"> Authentification échouée. Vérifiez votre email et votre mot de passe. ou vous n\'êtes pas administrateur';
                    $message .= '<br>';
                    $message .= '<a href="admin.php?action=Connexion">Retour à la page de connexion</a></div>';

                    return $message;
                }
            } catch (AuthException $e) {

                return '<div class="adminInf"> Authentification échouée : ' . $e->getMessage() . '</div>';
            }
        } else {
            return 'Veuillez remplir tous les champs.';
        }
    }
}
