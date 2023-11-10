<?php
namespace admin\touiteur\auth;

use admin\touiteur\exception\AuthException;
use PDO;

/**
 * Classe Auth pour la gestion de l'authentification des utilisateurs.
 */
class Auth
{
    /**
     * Authentifie un utilisateur en vérifiant les informations d'identification dans la base de données.
     *
     * @param string $email    L'adresse email de l'utilisateur.
     * @param string $password Le mot de passe de l'utilisateur.
     * @param PDO    $db       L'objet PDO représentant la connexion à la base de données.
     *
     * @return bool Retourne vrai si l'authentification est réussie, faux sinon.
     */
    public static function authenticate(string $email, string $password, $db): bool
    {
        // Prépare la requête SQL pour récupérer les informations de l'utilisateur en fonction de l'email fourni.
        $stmt = $db->prepare("SELECT id_utilisateur, email, passwd, role FROM user WHERE email = ?");
        // Exécute la requête avec l'email en paramètre.
        $stmt->execute([$email]);
        // Récupère les données de l'utilisateur sous forme de tableau associatif.
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie si l'utilisateur existe et si le mot de passe correspond.
        if ($user && password_verify($password, $user['passwd'])) {
            // Démarre la session si elle n'est pas déjà démarrée.
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Vérifie si le rôle de l'utilisateur est administrateur (role = '100').
            if ($user['role'] != '100') {
                // L'utilisateur n'a pas le rôle d'administrateur, l'authentification échoue.
                return false;
            }

            // Enregistre les informations de l'utilisateur dans la session.
            $_SESSION['user'] = [
                'id'    => $user['id_utilisateur'],
                'email' => $user['email'],
                'role'  => $user['role']
            ];

            // L'authentification est réussie.
            return true;
        }

        // L'authentification a échoué.
        return false;
    }
}
?>
