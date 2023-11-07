<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\db\User;
use iutnc\touiteur\exception\AuthException;
use iutnc\touiteur\Touite\PublierTouite;
use iutnc\touiteur\Touite\NoteTouite;

class ActionTouite extends Action
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
        $form = '<form method="POST">
            <label for="contenu">Texte du touite</label>
            <input type="text" name="contenu" required>
            <br>
            <label for="image">Image du touite</label>
            <input type="file" name="image">
            <br>
            <button type="submit">Post</button>
        </form>';

        // Afficher les Touites existants ici (vous devez implémenter cette partie)

        return $form;
    }

    public function handlePostRequest(): string
    {
        $contenu = filter_input(INPUT_POST, 'contenu', FILTER_SANITIZE_STRING);

        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();

        $PublierTouite = new PublierTouite();
        $NoteTouite = new NoteTouite();

        try {
            $PublierTouite->touite($contenu, $db);
            $touiteID = $db->lastInsertId();
            $message = "Le Touite '$contenu' a été ajouté avec succès.";

            // Afficher le Touite
            $message .= "<br><br>Touite : $contenu";

            // Ajouter les boutons Like et Dislike
            $message .= "<br><form method='POST'>";
            $message .= "<input type='hidden' name='touite_id' value='$touiteID'>";
            $message .= "<button type='submit' name='action' value='like'>Like</button>";
            $message .= "<button type='submit' name='action' value='dislike'>Dislike</button>";
            $message .= "</form>";

            if (isset($_POST['action']) && in_array($_POST['action'], ['like', 'dislike'])) {
                $action = $_POST['action'];
                $userID = $_SESSION['user']['id'];

                if ($action === 'like') {
                    $NoteTouite->likeTouite($userID, $touiteID);
                } elseif ($action === 'dislike') {
                    $NoteTouite->dislikeTouite($userID, $touiteID);
                }
            }

            return $message;
        } catch (AuthException $e) {
            return "Le Touite '$contenu' n'a pas pu être ajouté : " . $e->getMessage();
        }
    }
}
