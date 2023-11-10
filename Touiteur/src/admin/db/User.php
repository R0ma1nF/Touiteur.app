<?php

namespace admin\touiteur\db;

/**
 * La classe User représente un utilisateur dans la base de données.
 */
class User
{
    /**
     * @var int L'identifiant unique de l'utilisateur.
     */
    private $id;

    /**
     * @var string L'adresse email de l'utilisateur.
     */
    private $email;

    /**
     * @var string Le mot de passe de l'utilisateur.
     */
    private $passwd;

    /**
     * @var string Le rôle de l'utilisateur.
     */
    private $role;
}

