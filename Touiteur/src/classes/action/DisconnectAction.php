<?php
// DisconnectAction.php
namespace iutnc\touiteur\action;

use iutnc\touiteur\action\Action;

class DisconnectAction extends Action
{
    public function execute(): string
    {
        $_SESSION = [];
        session_write_close();

        return "Vous étes deconnecter  <a href=\"index.php\">Retour A L\'Accueil</a>";
    }
}
