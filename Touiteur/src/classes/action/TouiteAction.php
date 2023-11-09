<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\db\User;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\PublierTouite;

class TouiteAction extends Action
{

    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            return $this->handleGetRequest();
        } else {
            return $this->handlePostRequest();
        }
    }

    public function handleGetRequest(): string
    {
        return '<form method="POST" enctype="multipart/form-data">
    <label for="contenu">Texte du touite</label>
    <input type="text" name="contenu" required>
    <br>
    <label for="image">Image du touite</label>
    <input type="file" name="image">
    <br>
    <button type="submit">Post</button>
</form>
';
    }


    public function handlePostRequest(): string
    {
        $contenu = filter_input(INPUT_POST, 'contenu', FILTER_SANITIZE_STRING);
        $contenu = htmlspecialchars_decode($contenu);

        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDirectory = 'uploads/';
            $uploadedFile = $uploadDirectory . basename($_FILES['image']['name']);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadedFile)) {
                $imagePath = $uploadedFile;
            } else {
                return "Erreur lors du téléchargement de l'image.";
            }
        }

        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        $PublierTouite = new PublierTouite();
        try {
            $PublierTouite->touite($contenu, $imagePath, $db);
            // Afficher l'image avec le texte du touite
            $imageTag = '';
            if (!empty($imagePath)) {
                $imageTag = '<img src="' . $imagePath . '" alt="Touite Image">';
            }
            return "Le touite $contenu a été ajouté avec succès.<br>$imageTag";
        } catch (AuthException $e) {
            return "Le touite $contenu n'a pas pu être ajouté : " . $e->getMessage();
        }
    }

}