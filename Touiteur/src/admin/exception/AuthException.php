<?php
namespace admin\touiteur\exception;

/**
 * Classe AuthException
 *
 * Cette classe étend la classe \Exception et représente une exception liée à l'authentification.
 */
class AuthException extends \Exception {

    /**
     * Constructeur de la classe AuthException.
     *
     * @param string         $message   Le message de l'exception. Par défaut : "Erreur d'authentification".
     * @param int            $code      Le code de l'exception. Par défaut : 0.
     * @param \Throwable|null $previous  Exception précédente, si disponible. Par défaut : null.
     */
    public function __construct($message = "Erreur d'authentification", $code = 0, \Throwable $previous = null) {
        // Appelle le constructeur de la classe parente (\Exception).
        parent::__construct($message, $code, $previous);
    }
}
?>
