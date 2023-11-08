<?php
namespace iutnc\touiteur\auth;

use iutnc\touiteur\exception\AuthException;
use PDO;

class Auth
{
    public static function register(string $nom, string $prenom, string $email, string $password, $db): void
    {
        // Vérifier la longueur du mot de passe
        if (strlen($password) < 10) {
            //afficher un message d'erreur si le mot de passe est trop court et arrêter l'exécution du script avec exit() et bouton retournant à la page d'incription
            echo "Le mot de passe doit contenir au moins 10 caractères.";
            echo '<br>';
            echo '<a href="index.php?action=add-user">Retour à la page d\'inscription</a>';
            exit();
        }else {

            // Vérifier si l'utilisateur existe déjà
            $stmt = $db->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $userCount = $stmt->fetchColumn();

            if ($userCount > 0) {
                //les echo s'affiche apres les header
                echo "L'utilisateur existe déjà.";
                echo '<br>';
                echo '<a href="index.php?action=add-user">Retour à la page d\'inscription</a>';
                exit();
            } else {

                // Utiliser les mêmes paramètres d'encodage que ceux utilisés pour les mots de passe existants
                $options = [
                    'cost' => 12, // Le coût de hachage doit être le même que celui utilisé pour les mots de passe existants
                ];
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, $options);

                // Insérer le nouvel utilisateur dans la base de données avec le rôle 1 (ou votre rôle par défaut)
                $stmt = $db->prepare("INSERT INTO user (nom, prénom, email, passwd, role) VALUES (?, ?, ?, ?, 1)");
                if ($stmt->execute([$nom, $prenom, $email, $hashedPassword])) {
                    // L'enregistrement a réussi
                    return;
                } else {
                    throw new AuthException("L'enregistrement a échoué.");
                }
            }
        }
    }

    /**
     * @throws AuthException
     */
    /**
     * @throws AuthException
     */
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
