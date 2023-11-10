<?php
namespace iutnc\touiteur\auth;

use iutnc\touiteur\exception\AuthException;
use PDO;

class Auth
{
    public static function register(string $nom, string $prenom, string $email, string $password, $db): void
    {

        if (strlen($password) < 10) {
            $res = '';
            $res .= "Le mot de passe doit contenir au moins 10 caractères.";
            $res .= '<br>';
            $res .= '<a href="index.php?action=add-user">Retour à la page d\'inscription</a>';
            echo $res;
            exit();
        }else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $userCount = $stmt->fetchColumn();

            if ($userCount > 0) {
                $res ='';
                $res .= "L'utilisateur existe déjà.";
                $res .= '<br>';
                $res .= '<a href="index.php?action=add-user">Retour à la page d\'inscription</a>';
                echo $res;
                exit();
            } else {

               $options = [
                    'cost' => 12,
                ];
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, $options);

                $stmt = $db->prepare("INSERT INTO user (nom, prénom, email, passwd, role) VALUES (?, ?, ?, ?, 1)");
                if ($stmt->execute([$nom, $prenom, $email, $hashedPassword])) {
                    return;
                } else {
                    throw new AuthException("L'enregistrement a échoué.");
                }
            }
        }
    }


    public static function authenticate(string $email, string $password, $db): bool
    {
        $stmt = $db->prepare("SELECT id_utilisateur, email, passwd, role FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['passwd'])) {

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }


            $_SESSION['user'] = [
                'id' => $user['id_utilisateur'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            return true;
        }

        return false;
    }


}
?>
