<?php
// DisconnectAction.php
namespace iutnc\touiteur\action;

class DisconnectAction
{
public function __invoke(): string
{
    $_SESSION = [];
    session_write_close();

return "You have been logged out. <a href=\"index.php\">Return to Home</a>";
}
}
