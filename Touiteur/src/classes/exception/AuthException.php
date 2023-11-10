<?php

namespace iutnc\touiteur\exception;

/**
 * Classe AuthException
 *
 * Une exception spécifique pour les erreurs d'authentification dans l'application Touiteur.
 *
 * @package iutnc\touiteur\exception
 */
class AuthException extends \Exception {

    /**
     * Constructeur de la classe AuthException.
     *
     * @param string         $message  Message de l'exception. Par défaut, "Erreur d'authentification".
     * @param int            $code     Code d'erreur. Par défaut, 0.
     * @param \Throwable|null $previous Exception précédente, si applicable. Par défaut, null.
     */
    public function __construct($message = "Erreur d'authentification", $code = 0, \Throwable $previous = null) {
        // Appelle le constructeur de la classe parente (\Exception) avec les paramètres fournis.
        parent::__construct($message, $code, $previous);
    }
}
?>
