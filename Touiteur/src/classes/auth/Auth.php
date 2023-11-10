<?php
namespace iutnc\touiteur\auth;

use iutnc\touiteur\exception\AuthException;
use PDO;

class Auth
{
    /**
     * Enregistre un nouvel utilisateur dans la base de données.
     *
     * @param string $nom Nom de l'utilisateur.
     * @param string $prenom Prénom de l'utilisateur.
     * @param string $email Adresse email de l'utilisateur.
     * @param string $password Mot de passe de l'utilisateur.
     * @param PDO $db Objet PDO représentant la connexion à la base de données.
     *
     * @throws AuthException En cas d'échec de l'enregistrement.
     */
    public static function register(string $nom, string $prenom, string $email, string $password, $db): void
    {
        // Vérifier la longueur du mot de passe
        if (strlen($password) < 10) {
            $res = '';
            $res .= "Le mot de passe doit contenir au moins 10 caractères.";
            $res .= '<br>';
            $res .= '<a href="index.php?action=add-user">Retour à la page d\'inscription</a>';
            echo $res;
            exit();
        } else {
            // Vérifier si l'utilisateur existe déjà
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
                // Hasher le mot de passe avant de l'enregistrer
                $options = [
                    'cost' => 12,
                ];
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, $options);

                // Insérer l'utilisateur dans la base de données
                $stmt = $db->prepare("INSERT INTO user (nom, prénom, email, passwd, role) VALUES (?, ?, ?, ?, 1)");
                if ($stmt->execute([$nom, $prenom, $email, $hashedPassword])) {
                    return;
                } else {
                    throw new AuthException("L'enregistrement a échoué.");
                }
            }
        }
    }

    /**
     * Authentifie l'utilisateur en vérifiant les informations d'identification.
     *
     * @param string $email Adresse email de l'utilisateur.
     * @param string $password Mot de passe de l'utilisateur.
     * @param PDO $db Objet PDO représentant la connexion à la base de données.
     *
     * @return bool Retourne true si l'authentification réussit, sinon false.
     */
    public static function authenticate(string $email, string $password, $db): bool
    {
        // Récupérer les informations de l'utilisateur depuis la base de données
        $stmt = $db->prepare("SELECT id_utilisateur, email, passwd, role FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['passwd'])) {
            // Démarrer la session si elle n'est pas déjà démarrée
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Stocker les informations de l'utilisateur dans la session
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
