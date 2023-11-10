<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\db\User;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\PublierTouite;

/**
 * La classe TouiteAction gère les actions liées aux "touites" (messages courts).
 */
class TouiteAction extends Action
{

    /**
     * Exécute l'action appropriée en fonction de la méthode HTTP (GET ou POST).
     *
     * @return string Le résultat de l'action.
     */
    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            return $this->handleGetRequest();
        } else {
            return $this->handlePostRequest();
        }
    }

    /**
     * Gère une requête HTTP de type GET pour afficher le formulaire de publication de touite.
     *
     * @return string Le code HTML du formulaire.
     */
    public function handleGetRequest(): string
    {
        $res = '';
        $errorMessages = isset($_GET['error']) ? $_GET['error'] : '';
        if (!empty($errorMessages)) {
            $res .= '<div style="color: red;">' . htmlspecialchars($errorMessages) . '</div>';
        }
        $res .= '<div class="publier"><form method="POST" enctype="multipart/form-data" >
        <label for="contenu">Texte du touite</label>
        <input type="text" name="contenu" required>
        <br>
        <label for="image">Image du touite</label>
        <input type="file" name="image" accept="image/png, image/jpeg" >
        <br>
        <button type="submit">Post</button>
        </form></div>';

        return $res;
    }

    /**
     * Gère une requête HTTP de type POST pour publier un nouveau touite.
     *
     * @return string Le résultat de la publication du touite.
     */
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
