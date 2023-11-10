<?php
// DisconnectAction.php
namespace admin\touiteur\action;

use iutnc\touiteur\action\Action;

class DisconnectActionBO extends Action
{
    public function execute(): string
    {
        $_SESSION = [];
        session_write_close();

        return "Vous étes deconnecter.  <a href=\"admin.php\">Retour A L\'Accueil</a>";
    }
}
