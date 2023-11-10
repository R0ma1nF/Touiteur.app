<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\action\Action;

class DisconnectAction extends Action
{
    /**
     * methode appelée lors de l'execution de l'action
     * @return string Le résultat de l'exécution de l'action.
     */
    public function execute(): string
    {
        $_SESSION = [];
        session_write_close();

        return "Vous étes deconnecter  <a href=\"index.php\">Retour A L\'Accueil</a>";
    }
}
