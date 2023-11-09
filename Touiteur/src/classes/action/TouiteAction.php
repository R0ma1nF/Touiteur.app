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
        return '<form method="POST" >
    <label for="contenu">Texte du touite</label>
    <input type="text" name="contenu" required>
    <br>
    <label for="image">Image du touite</label>
    <input type="file" name="image">
    <br>
    <button type="submit">Post</button>
</form>';
    }

    public function handlePostRequest(): string
    {
        $contenu = filter_input(INPUT_POST, 'contenu', FILTER_SANITIZE_STRING);
        $contenu = htmlspecialchars_decode($contenu);

        $db = ConnectionFactory::setConfig('db.config.ini');
        $db = ConnectionFactory::makeConnection();;

        $PublierTouite = new PublierTouite();
        try {
            $PublierTouite->touite($contenu, $db);
            return "Le touite $contenu a été ajouté avec succès.";
        } catch (AuthException $e) {
            return "Le touite $contenu n'a pas pu être ajouté : " . $e->getMessage();
        }

    }
}