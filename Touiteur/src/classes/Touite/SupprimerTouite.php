<?php

namespace iutnc\touiteur\Touite;

use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;

class SupprimerTouite
{
    public static function supprimerTouite(int $userID, int $touiteID): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        $role = $_SESSION["user"]["role"];

        if ($role == 100 ) {
            $stmt = $db->prepare("DELETE FROM listetouitetag WHERE ID_Touite = ?");
            $stmt->execute([$touiteID]);
            $stmt = $db->prepare("DELETE FROM notetouite WHERE ID_Touite = ?");
            $stmt->execute([$touiteID]);
            $stmt = $db->prepare("DELETE FROM listetouiteutilisateur WHERE ID_Touite = ?");
            $stmt->execute([$touiteID]);
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
                $stmt = $db->prepare("DELETE FROM listetouitetag WHERE ID_Touite = ?");
                $stmt->execute([$touiteID]);
                $stmt = $db->prepare("DELETE FROM notetouite WHERE ID_Touite = ?");
                $stmt->execute([$touiteID]);
                $stmt = $db->prepare("DELETE FROM listetouiteutilisateur WHERE ID_Touite = ?");
                $stmt->execute([$touiteID]);
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
