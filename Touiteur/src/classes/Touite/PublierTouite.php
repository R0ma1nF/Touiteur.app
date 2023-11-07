<?php
namespace iutnc\touiteur\Touite;
use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;

class PublierTouite {
    private $db;

    public function __construct() {
        // Initialisez la connexion à la base de données en utilisant ConnexionFactory ou votre propre méthode de connexion
        $this->db = ConnectionFactory::setConfig('db.config.ini');
        $this->db = ConnectionFactory::makeConnection();
    }

    public function publierTouite($userID, $contenu, $imageID) {
        // Insérez un nouveau Touite dans la table Touite
        $datePublication = date('Y-m-d H:i:s'); // Obtenez la date et l'heure actuelles
        $query = "INSERT INTO Touite (Contenu, DatePublication, id_utilisateur, ID_Image) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$contenu, $datePublication, $userID, $imageID]);
        $query = "INSERT INTO listetouiteutilisateur (id_utilisateur, ID_Touite) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userID, $this->db->lastInsertId()]);
    }
}

?>
