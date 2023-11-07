<?php
namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\db\User;
use iutnc\touiteur\exception\AuthException;

class SignInAction extends Action
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
    <label for="user_email">Email de l\'utilisateur</label>
    <input type="email" name="user_email" required>
    <br>
    <label for="user_passwd">Mot de passe</label>
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
                    $message = 'Authentification réussie.'.'<br>'. 'Bienvenue ' .  $userEmail . ' ! ' .'<br>';
                    $message .= '<form method="GET" action="index.php">';
                    $message .= '<input type="hidden" name="action" value="Touite">';
                    $message .= '<button type="submit">' . "Créer un touite" . '</button>';

                } else {
                    $message = 'Authentification échouée. Vérifiez votre email et votre mot de passe.';
                    $message .= '<br>';
                    $message .= '<a href="index.php?action=signin">Retour à la page de connexion</a>';

                }
                return $message;
            } catch (AuthException $e) {

                return 'Authentification échouée : ' . $e->getMessage();
            }
        } else {
            return 'Veuillez remplir tous les champs.';
        }
    }
}
?>