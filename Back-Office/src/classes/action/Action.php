<?php
namespace iutnc\BackOffice\action;

use iutnc\BackOffice\db\ConnectionFactory as ConnectionFactory;
abstract class Action
{

    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    public function __invoke(): string
    {
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
        return $this->execute();
    }

    abstract public function execute(): string;

    public function handleGetRequest(): string
    {
        return '';
    }

    public function handlePostRequest(): string
    {
        return '';
    }
}
