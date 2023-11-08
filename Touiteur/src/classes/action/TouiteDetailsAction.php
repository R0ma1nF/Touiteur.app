<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\Touite\NoteTouite;

class TouiteDetailsAction extends Action
{
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $touiteID = isset($_GET['touiteID']) ? (int)$_GET['touiteID'] : 0; // Get the touiteID from the query parameter
        $liste = $this->touiteDetail($db, $touiteID);
        return 'Bienvenue sur Touiter' . '<br>' . $liste;
    }

    public function touiteDetail($db, $idTouite)
    {
        $stmt = $db->prepare("SELECT t.contenu, t.datePublication, u.nom, u.prénom, n.id_touite
                    FROM touite t
                    JOIN listetouiteutilisateur ltu ON t.id_touite = ltu.ID_Touite
                    JOIN user u ON ltu.id_utilisateur = u.id_utilisateur
                    JOIN notetouite n ON t.id_touite = n.id_touite
                    WHERE n.id_touite = ?
                    ORDER BY t.datePublication DESC");
        $stmt->execute([$idTouite]);

        $details = ''; // A string to store the details of the touite

        $data = $stmt->fetch();
        $details .= 'Contenu: ' . $data['contenu'] . "<br>";
        $details .= 'Date de Publication: ' . $data['datePublication'] . "<br>";
        $details .= 'Nom: ' . $data['nom'] . "<br>";
        $details .= 'Prénom: ' . $data['prénom'] . "<br>";
        $details .= 'ID Touite: ' . $data['id_touite'] . "<br>";
        $details .= 'Note : ' . NoteTouite::getNoteTouite($idTouite);
        
        return $details; // Returns a single string containing the details of the touite
    }
}
