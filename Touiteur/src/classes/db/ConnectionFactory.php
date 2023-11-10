<?php

namespace iutnc\touiteur\db;

use PDO;

/**
 * Classe ConnectionFactory
 * Gère la création et la gestion de la connexion à la base de données.
 */
class ConnectionFactory
{
    /** @var array Les paramètres de configuration de la base de données. */
    private static $config = [];

    /** @var PDO|null L'instance PDO de la connexion à la base de données. */
    private static $instance;

    /**
     * Définit la configuration de la base de données à partir d'un fichier INI.
     *
     * @param string $file Chemin vers le fichier de configuration INI.
     */
    public static function setConfig($file)
    {
        self::$config = parse_ini_file($file);
    }

    /**
     * Crée et retourne une instance de connexion à la base de données.
     *
     * @return PDO L'instance de la connexion PDO.
     * @throws \Exception Si la base de données n'est pas configurée. Appeler setConfig() avant de faire une connexion.
     */
    public static function makeConnection()
    {
        if (empty(self::$config)) {
            throw new \Exception("La base de données n'est pas configurée. Appeler setConfig() avant de faire une connexion.");
        }

        if (self::$instance === null) {
            $dsn = self::buildDsn();
            self::$instance = new \PDO($dsn, self::$config['username'], self::$config['password'], array(PDO::ATTR_PERSISTENT => true));
        }

        return self::$instance;
    }

    /**
     * Construit et retourne la chaîne de connexion (DSN) à partir des paramètres de configuration.
     *
     * @return string La chaîne DSN pour la connexion à la base de données.
     */
    private static function buildDsn()
    {
        $dsn = "mysql:host=" . self::$config['host'];
        $dsn .= ";dbname=" . self::$config['database'];
        $dsn .= ";charset=" . self::$config['charset'];
        return $dsn;
    }
}
