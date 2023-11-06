<?php
namespace iutnc\touiteur\exception;

class AuthException extends \Exception {
    public function __construct($message = "Erreur d'authentification", $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
?>
