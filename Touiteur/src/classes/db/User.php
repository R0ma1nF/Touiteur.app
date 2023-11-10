<?php

namespace iutnc\touiteur\db;

/**
 * La classe User représente un utilisateur dans la base de données.
 */
class User
{
    /**
     * @var int Identifiant unique de l'utilisateur.
     */
    private $id;

    /**
     * @var string Adresse email de l'utilisateur.
     */
    private $email;

    /**
     * @var string Mot de passe de l'utilisateur.
     */
    private $passwd;

    /**
     * @var string Rôle de l'utilisateur.
     */
    private $role;
}

?>
