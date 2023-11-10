<?php
namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\exception\AuthException;

/**
 * Classe représentant l'action d'ajout d'un utilisateur.
 */
class AddUserAction extends Action {

    /**
     * Exécute l'action en fonction de la méthode HTTP.
     *
     * @return string Résultat de l'action.
     */
    public function execute(): string {
        if ($this->http_method === 'GET') {
            return $this->handleGetRequest();
        } elseif ($this->http_method === 'POST') {
            return $this->handlePostRequest();
        }
        return '';
    }

    /**
     * Gère les requêtes GET pour afficher le formulaire d'inscription.
     *
     * @return string Formulaire d'inscription HTML.
     */
    public function handleGetRequest(): string {
        return $this->getRegistrationForm();
    }

    /**
     * Gère les requêtes POST pour traiter l'inscription de l'utilisateur.
     *
     * @return string Résultat du traitement de l'inscription.
     */
    public function handlePostRequest(): string {
        return $this->processRegistration();
    }

    /**
     * Génère le formulaire d'inscription HTML.
     *
     * @return string Formulaire d'inscription HTML.
     */
    private function getRegistrationForm(): string {
        return <<<HTML
    <form method="POST" >
        <label for="name">Nom:</label>
        <input type="text" name="name" id="name" required><br>
        <label for="firstname">Prénom:</label>
        <input type="text" name="firstname" id="firstname" required><br>
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

    /**
     * Traite la soumission du formulaire d'inscription.
     *
     * @return string Résultat du traitement de l'inscription.
     */
    private function processRegistration(): string {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $password_confirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_STRING);

        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        if ($password !== $password_confirm) {
            $message = "Les mots de passe ne correspondent pas. Veuillez réessayer.";
            $message .= "<br>";
            $message .= '<a href="index.php?action=Inscription">Retour à la page d\'inscription</a>';
            return $message;
        } else {
            $auth = new Auth();
            try {
                $auth->register($name, $firstname, $email, $password, $db);
                return "L'utilisateur $email a été ajouté avec succès.";
            } catch (AuthException $e) {
                return "L'utilisateur $email n'a pas pu être ajouté : " . $e->getMessage();
            }
        }
    }
}
