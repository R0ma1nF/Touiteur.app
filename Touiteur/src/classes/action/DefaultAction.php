<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\NoteTouite;
use PDO;

class DefaultAction extends Action
{
    /**
     * @throws \Exception
     */
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $liste = $this->listeTouite($db);
        header("Refresh:10");
        return 'Bienvenue sur Touiter ' . '<br>' . $liste;
    }

    /**
     * @throws \Exception
     */
    /**
     * @throws \Exception
     */
    /**
     * @throws \Exception
     */
    public function listeTouite($db)
    {
        $roleuser = $_SESSION["user"]["role"];
        if ($roleuser == 100 || $roleuser == 1) {
            $liste = $this->getUserWallTouites($_SESSION['user']['id']);
            return $liste;
        } else {

            $stmt = $db->prepare("SELECT t.id_touite, t.contenu, t.datePublication, u.nom, u.prénom
                        FROM touite t
                        JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                        JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                        ORDER BY t.datePublication DESC");
            $stmt->execute();
            $res = '';
            while ($data = $stmt->fetch()) {
                $touiteID = $data['id_touite'];
                //affiche le nom et le prénom de l'utilisateur qui a publié le touite
                $res .= $data['prénom'] . ' ' . $data['nom'];
                // Boutons Like et Dislike spécifiques au touite actuel
                $contenu = $data['contenu'];
                $datePublication = $data['datePublication'];

                $res .= '<a href="?action=testdetail&touiteID=' . $touiteID . '" ><p>' . $contenu . '</p>' . $datePublication . '</a><br>';
                $res .= '<form method="POST" action="?action=Default">
        <input type="hidden" name="touiteID" value="' . $touiteID . '">
        <button type="submit" name="likeTouite">Like</button>
        <button type="submit" name="dislikeTouite">Dislike</button>
    </form>';

                // Affiche la note actuelle du touite
                $note = NoteTouite::getNoteTouite($touiteID);
                $res .= 'Note: ' . $note . '<br><br>';
            }

            // Gestion des actions Like et Dislike en dehors de la boucle
            if (isset($_POST['touiteID'])) {
                $touiteID = (int)$_POST['touiteID']; // Assurez-vous qu'il s'agit d'un entier
                if (isset($_POST['likeTouite'])) {
                    $this->Likebutton($touiteID);
                } elseif (isset($_POST['dislikeTouite'])) {
                    $this->Dislikebutton($touiteID);
                }
            }

            return $res;
        }

    }



    /**
     * @throws AuthException
     */
    public function Likebutton($touiteID) {
        $userID = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        if ($userID == null) {
            // Redirigez l'utilisateur vers la page de connexion s'il n'est pas connecté (ou vers la page d'inscription s'il n'a pas de compte)
            //en lui proposant de se connecter ou de s'inscrire pour pouvoir liker un touite et afficher un message d'erreur
            echo '<h2>Erreur</h2>';
            echo "Vous devez être connecté pour pouvoir liker un touite.";
            echo '<br>';
            echo '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
            echo '<br>';
            echo '<a href="index.php?action=Inscription">Retour à la page d\'inscription</a>';
            exit();
        }
        // Bouton Like cliqué, ajoutez ici la logique pour gérer le Like
        NoteTouite::likeTouite($userID, $touiteID);
    }
    public function Dislikebutton($touiteID) {
        $userID = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

        if ($userID == null) {
            // Redirigez l'utilisateur vers la page de connexion s'il n'est pas connecté (ou vers la page d'inscription s'il n'a pas de compte)
            //en lui proposant de se connecter ou de s'inscrire pour pouvoir liker un touite et afficher un message d'erreur
            echo '<h2>Erreur</h2>';
            echo "Vous devez être connecté pour pouvoir disliker un touite.";
            echo '<br>';
            echo '<a href="index.php?action=Connexion">Retour à la page de connexion</a>';
            echo '<br>';
            echo '<a href="index.php?action=Inscription">Retour à la page d\'inscription</a>';
            exit();
        }
        // Bouton Dislike cliqué, ajoutez ici la logique pour gérer le Dislike
        NoteTouite::dislikeTouite($userID, $touiteID);
    }

    public function getUserWallTouites($userID)
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        $stmt = $db->prepare("SELECT Touite.ID_Touite, Touite.Contenu, Touite.DatePublication
        FROM (
            SELECT T.ID_Touite, T.Contenu, T.DatePublication
            FROM Touite T
            inner join ListeTouiteUtilisateur LTU ON T.ID_Touite = LTU.ID_Touite
            INNER JOIN Abonnement A ON LTU.ID_Utilisateur = A.ID_UtilisateurSuivi
            WHERE A.ID_Utilisateur = :userID
            UNION
            SELECT T.ID_Touite, T.Contenu, T.DatePublication
            FROM Touite T
            INNER JOIN ListeTouiteTag LTT ON T.ID_Touite = LTT.ID_Touite
            INNER JOIN AbonnementTag ABT ON ABT.ID_Tag = LTT.ID_Tag
            WHERE ABT.ID_Utilisateur = :userID
        ) AS Touite
        INNER JOIN ListeTouiteUtilisateur LTU ON Touite.ID_Touite = LTU.ID_Touite
        ORDER BY Touite.DatePublication DESC");

        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();

        $res = '';

        while ($data = $stmt->fetch()) {
            // Vous pouvez formater ici le contenu de chaque touite comme vous le souhaitez
            $touiteID = $data['ID_Touite'];
            $contenu = $data['Contenu'];
            $datePublication = $data['DatePublication'];

            // Ajoutez ici le code pour afficher le contenu du touite sur le mur de l'utilisateur
            $res .= '<a href="?action=testdetail&touiteID=' . $touiteID . '"><p>' . $contenu . '</p>' . $datePublication .  '</a><br>';
        }

        return $res;
    }







}