<?php
namespace admin\touiteur\auth;

use admin\touiteur\exception\AuthException;
use PDO;

class Auth
{


    public static function authenticate(string $email, string $password, $db): bool
    {
        $stmt = $db->prepare("SELECT id_utilisateur, email, passwd, role FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['passwd'])) {
            // Initialise la session si elle ne l'est pas déjà
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if ($user['role'] != '100') {
                return false;
            }
            // Stocke l'utilisateur en session
            $_SESSION['user'] = [
                'id' => $user['id_utilisateur'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            return true; // Mot de passe valide
        }

        return false;
    }


}
?>
