<?php
namespace iutnc\touiteur\auth;

use iutnc\touiteur\exception\AuthException;
use PDO;

class Auth
{
    public static function register(string $email, string $password, $db): void
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
                $stmt = $db->prepare("INSERT INTO User (email, passwd, role) VALUES (?, ?, 1)");
                if ($stmt->execute([$email, $hashedPassword])) {
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

    public static function canAccessPlaylist(int $playlistId, $db): bool
    {
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];

            // Vérifier si l'utilisateur a le rôle ADMIN
            if ($user['role'] == 100) {
                return true;
            }

            // Vérifier si la playlist appartient à l'utilisateur
            $stmt = $db->prepare("SELECT COUNT(*) FROM Playlist p INNER JOIN user2playlist u2p ON p.id = u2p.id_pl WHERE p.id = ? AND u2p.id_user = ?");
            $stmt->execute([$playlistId, $user['id']]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                return true;
            }
        }

        return false;
    }



}
?>
