<?php

namespace admin\touiteur\action;
use admin\touiteur\db\ConnectionFactory;
use admin\touiteur\db\User;
use admin\touiteur\auth\Auth;
use iutnc\touiteur\action\Action;

class SignInActionBO extends Action
{
    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            return $this->handleGetRequest();
        } else {
            return $this->handlePostRequest();
        }
    }

    public function handleGetRequest(): string
    {
        return '<form method="POST" >
    <label for="user_email">Email de l\'administrateur</label>
    <input type="email" name="user_email" required>
    <br>
    <label for="user_passwd">Mot de passe de l\'administrateur</label>
    <input type="password" name="user_passwd" required>
    <br>
    <button type="submit">Se connecter</button>
</form>';
    }

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
                    return 'Authentification réussie.' . '<br>' . 'Bienvenue ' . $userEmail . ' ! ' . '<br>';
                } else {
                    $message = 'Authentification échouée. Vérifiez votre email et votre mot de passe. ou vous n\'êtes pas administrateur';
                    $message .= '<br>';
                    $message .= '<a href="admin.php?action=Connexion">Retour à la page de connexion</a>';

                    return $message;
                }
            } catch (AuthException $e) {

                return 'Authentification échouée : ' . $e->getMessage();
            }
        } else {
            return 'Veuillez remplir tous les champs.';
        }
    }
}