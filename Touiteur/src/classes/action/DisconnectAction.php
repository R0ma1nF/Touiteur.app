<?php
namespace iutnc\touiteur\action;

class DisconnectAction extends Action
{
    public function execute(): string
    {
        session_destroy();
        header('Location: index.php');
        exit();
    }
}
