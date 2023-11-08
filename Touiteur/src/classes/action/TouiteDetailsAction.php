<?php

namespace iutnc\touiteur\action;

use http\Params;
use iutnc\touiteur\action\Action;
use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\Touite\NoteTouite;

class TouiteDetailsAction extends Action
{
    public function execute(): string
    {
        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();
        $liste = $this->touiteDetail($db , $touiteID);
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

        $details = ''; // Une chaîne pour stocker les détails des touites

        while ($data = $stmt->fetch()) {
            $details .= 'Contenu: ' . $data['contenu'] . "<br>";
            $details .= 'Date de Publication: ' . $data['datePublication'] . "<br>";
            $details .= 'Nom: ' . $data['nom'] . "<br>";
            $details .= 'Prénom: ' . $data['prénom'] . "<br>";
            $details .= 'ID Touite: ' . $data['id_touite'] . "<br>";
        }

        return $details; // Retourne une seule chaîne contenant les détails des touites
    }



}