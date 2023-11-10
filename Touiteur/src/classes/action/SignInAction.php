<?php
namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\db\User;
use iutnc\touiteur\exception\AuthException;

/**
 * Classe représentant l'action de connexion (sign-in) d'un utilisateur.
 */
class SignInAction extends Action
{
    /**
     * Exécute l'action en fonction de la méthode HTTP (GET ou POST).
     *
     * @return string Le résultat de l'exécution de l'action.
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

    /**
     * Gère une requête HTTP de type POST en traitant les données de connexion.
     *
     * @return string Le résultat de la tentative d'authentification.
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
                    return 'Authentification réussie.'.'<br>'. 'Bienvenue ' .  $userEmail . ' ! ' .'<br>' ;
                } else {
                    $message = 'Authentification échouée. Vérifiez votre email et votre mot de passe.';
                    $message .= '<br>';
                    $message .= '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';

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
?>
