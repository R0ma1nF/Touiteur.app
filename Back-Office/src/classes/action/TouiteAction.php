<?php

namespace iutnc\touiteur\action;

use iutnc\BackOffice\auth\Auth;
use iutnc\BackOffice\db\ConnectionFactory;
use iutnc\BackOffice\db\User;
use iutnc\BackOffice\exception\AuthException;
use iutnc\BackOffice\Touite\PublierTouite;

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
        $res='';
        $errorMessages = isset($_GET['error']) ? $_GET['error'] : '';
        if (!empty($errorMessages)) {
            $res .= '<div style="color: red;">' . htmlspecialchars($errorMessages) . '</div>';
        }
       $res .= '<form method="POST" enctype="multipart/form-data" >
    <label for="contenu">Texte du touite</label>
    <input type="text" name="contenu" required>
    <br>
    <label for="image">Image du touite</label>
    <input type="file" name="image" accept="image/png, image/jpeg" >
    <br>
    <button type="submit">Post</button>
</form>

';
        return $res;
    }


    public function handlePostRequest(): string
    {
        $contenu = filter_input(INPUT_POST, 'contenu', FILTER_SANITIZE_STRING);
        $contenu = htmlspecialchars_decode($contenu);

        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowedTypes = ['image/png', 'image/jpeg'];
            $uploadDirectory = 'uploads/';
            $uploadedFile = $uploadDirectory . basename($_FILES['image']['name']);
            $fileType = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
               //raffiche la requete get et un message d'erreur si le type de fichier n'est pas autorisé
                header('Location: index.php?action=Publier+Touit&error=Le%20type%20de%20fichier%20nest%20pas%20autorisé.%20Veuillez%20choisir%20une%20image%20au%20format%20PNG%20ou%20JPEG.');
            }

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