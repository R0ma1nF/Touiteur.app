<?php
namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory as ConnectionFactory;

/**
 * Classe abstraite représentant une action générique.
 */
abstract class Action
{
    /**
     * @var string|null $http_method Le type de méthode HTTP utilisé (GET, POST, etc.).
     */
    protected ?string $http_method = null;

    /**
     * @var string|null $hostname Le nom d'hôte du serveur.
     */
    protected ?string $hostname = null;

    /**
     * @var string|null $script_name Le chemin du script en cours d'exécution.
     */
    protected ?string $script_name = null;

    /**
     * Méthode invoquée lors de l'exécution de l'action.
     *
     * @return string Le résultat de l'exécution de l'action.
     */
    public function __invoke(): string
    {
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
        return $this->execute();
    }

    /**
     * Méthode abstraite à implémenter par les classes filles pour définir le comportement de l'action.
     *
     * @return string Le résultat de l'exécution de l'action.
     */
    abstract public function execute(): string;

    /**
     * Méthode pour gérer les requêtes de type GET.
     *
     * @return string Le résultat de la gestion de la requête GET.
     */
    public function handleGetRequest(): string
    {
        return '';
    }

    /**
     * Méthode pour gérer les requêtes de type POST.
     *
     * @return string Le résultat de la gestion de la requête POST.
     */
    public function handlePostRequest(): string
    {
        return '';
    }
}
