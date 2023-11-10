<?php

namespace iutnc\BackOffice\Touite;

use iutnc\BackOffice\db\ConnectionFactory as ConnectionFactory;

class SupprimerTouite
{
    public static function supprimerTouite(int $userID, int $touiteID): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        $role = $_SESSION["user"]["role"];

        // Vérifiez le rôle de l'utilisateur
        if ($role == 100 ) {
            // Supprimer d'abord les références dans listetouitetag
            $stmt = $db->prepare("DELETE FROM listetouitetag WHERE ID_Touite = ?");
            $stmt->execute([$touiteID]);

// Ensuite, supprimer les références dans notetouite
            $stmt = $db->prepare("DELETE FROM notetouite WHERE ID_Touite = ?");
            $stmt->execute([$touiteID]);

// Enfin, supprimer la ligne dans listetouiteutilisateur
            $stmt = $db->prepare("DELETE FROM listetouiteutilisateur WHERE ID_Touite = ?");
            $stmt->execute([$touiteID]);

// Enfin, supprimer la ligne dans touite
            $stmt = $db->prepare("DELETE FROM touite WHERE ID_Touite = ?");
            $stmt->execute([$touiteID]);

            $res = '<p>Touite supprimé</p>';
            return $res;

        } elseif ($role == 1) {
            $usercurrent = $_SESSION['user']['id'];
            if ($usercurrent != $userID) {
                $res = '<h2>Erreur</h2>';
                $res .= "Vous n'avez pas le droit de supprimer un touite si vous n'êtes pas l'auteur";
                $res .= '<br>';
                $res .= '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
                return $res;
            }else {
                // Supprimer d'abord les références dans listetouitetag
                $stmt = $db->prepare("DELETE FROM listetouitetag WHERE ID_Touite = ?");
                $stmt->execute([$touiteID]);

// Ensuite, supprimer les références dans notetouite
                $stmt = $db->prepare("DELETE FROM notetouite WHERE ID_Touite = ?");
                $stmt->execute([$touiteID]);

// Enfin, supprimer la ligne dans listetouiteutilisateur
                $stmt = $db->prepare("DELETE FROM listetouiteutilisateur WHERE ID_Touite = ?");
                $stmt->execute([$touiteID]);

// Enfin, supprimer la ligne dans touite
                $stmt = $db->prepare("DELETE FROM touite WHERE ID_Touite = ?");
                $stmt->execute([$touiteID]);

                $res = '<p>Touite supprimé</p>';
                return $res;

            }

        }else
        {
            $res = '<h2>Erreur</h2>';
            $res .= "Vous n'avez pas le droit de supprimer un touite si vous n'êtes pas l'auteur";
            $res .= '<br>';
            $res .= '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
            return $res;
        }
    }
}
