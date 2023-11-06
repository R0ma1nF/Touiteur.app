<?php
namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\exception\AuthException;

class AddUserAction extends Action {
    public function execute(): string {
        if ($this->http_method === 'GET') {
            return $this->handleGetRequest();
        } elseif ($this->http_method === 'POST') {
            return $this->handlePostRequest();
        }
        return '';
    }
    public function handleGetRequest(): string {
        // Code pour gérer les requêtes GET ici
        return $this->getRegistrationForm();
    }

    public function handlePostRequest(): string {
        // Code pour gérer les requêtes POST ici
        return $this->processRegistration();
    }
  private  function getRegistrationForm(): string
  {
        return <<<HTML
    <form method="POST" >
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>
        <label for="password">Mot de passe:</label>
        <input type="password" name="password" id="password" required><br>
        <label for="password_confirm">Confirmez le mot de passe:</label>
        <input type="password" name="password_confirm" id="password_confirm" required><br>
        <input type="submit" value="S'inscrire">
    </form>
HTML;
    }
    private function processRegistration(): string
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $password_confirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_STRING);
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        if ($password !== $password_confirm) {
            $message = "Les mots de passe ne correspondent pas. Veuillez réessayer.";
            $message .= "<br>";
            $message .= '<a href="index.php?action=add-user">Retour à la page d\'inscription</a>';
            return $message;
        }else {
            $auth = new Auth();
            try {
                $auth->register($email, $password, $db);
                return "L'utilisateur $email a été ajouté avec succès.";
            } catch (AuthException $e) {
                return "L'utilisateur $email n'a pas pu être ajouté : " . $e->getMessage();
            }
        }

    }


}