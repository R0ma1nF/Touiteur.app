<?php
namespace iutnc\touiteur\Touite;
use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;
class NoteTouite {
    private $db;

    public function __construct() {
        // Initialisez la connexion à la base de données en utilisant ConnexionFactory ou votre propre méthode de connexion
        $this->db = ConnectionFactory::setConfig('db.config.ini');
        $this->db = ConnectionFactory::makeConnection();
    }

    public function likeTouite($userID, $touiteID) {
        if (!$this->hasUserRatedTouite($userID, $touiteID)) {
            $query = "INSERT INTO NoteTouite (ID_Touite, ID_Utilisateur, Note) VALUES (?, ?, +1)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$touiteID, $userID]);
        }else{
            $query = "UPDATE NoteTouite SET Note = +1 WHERE ID_Touite = ? AND ID_Utilisateur = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$touiteID, $userID]);
        }
    }

    public function dislikeTouite($userID, $touiteID) {
        if (!$this->hasUserRatedTouite($userID, $touiteID)) {
            $query = "INSERT INTO NoteTouite (ID_Touite, ID_Utilisateur, Note) VALUES (?, ?, -1)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$touiteID, $userID]);
        }else{
            $query = "UPDATE NoteTouite SET Note = -1 WHERE ID_Touite = ? AND ID_Utilisateur = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$touiteID, $userID]);
        }
    }

    public function hasUserRatedTouite($userID, $touiteID) {
        $query = "SELECT COUNT(*) FROM NoteTouite WHERE ID_Touite = ? AND ID_Utilisateur = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$touiteID, $userID]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }
}

?>
