<?php
namespace iutnc\touiteur\action;

use iutnc\touiteur\Touite\PublierTouite;
use iutnc\touiteur\Touite\NoteTouite;

class ActionTouite extends Action
{
    private $publierTouite;
    private $noteTouite;

    public function __construct() {
        $this->publierTouite = new PublierTouite();
        $this->noteTouite = new NoteTouite();
    }

    public function execute(): string
    {
        if ($this->http_method === 'POST') {
            $userID = $_SESSION['user']['id'];
            $contenu = $_POST['contenu'];
            $actionType = $_POST['action_type'];

            if ($actionType === 'Publication') {
                $imageID = $_POST['image_id']; // Si votre publication de Touite inclut une image
                $this->publierTouite->publierTouite($userID, $contenu, $imageID);
            } elseif ($actionType === 'Evaluation') {
                $touiteID = $_POST['touite_id'];
                $note = $_POST['note'];

                if ($note > 0) {
                    $this->noteTouite->likeTouite($userID, $touiteID);
                } elseif ($note < 0) {
                    $this->noteTouite->dislikeTouite($userID, $touiteID);
                }
            }
        }

        // Afficher le formulaire pour publier un Touite
        $form = $this->getTouiteForm();

        // Afficher les Touites existants (vous devez implémenter cette fonction)

        return $form;
    }

    public function getTouiteForm(): string
    {
        return <<<HTML
<form method="POST" >
    <label for="contenu">Contenu du Touite</label>
    <input type="text" name="contenu" required>
    <br>
    <label for="image_id">ID de l'image</label>
    <input type="number" name="image_id">
    <br>
    <button type="submit" name="action_type" value="Publication">Publier</button>
HTML;

    }
}
