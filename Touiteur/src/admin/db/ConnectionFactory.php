<?php

namespace admin\touiteur\db;

use PDO;

class ConnectionFactory
{
    // Configuration de la base de données
    private static $config = [];

    // Instance de la connexion PDO
    private static $instance;

    /**
     * Configure les paramètres de la base de données à partir d'un fichier.
     *
     * @param string $file Chemin vers le fichier de configuration.
     */
    public static function setConfig($file)
    {
        self::$config = parse_ini_file($file);
    }

    /**
     * Établit une connexion à la base de données en utilisant les paramètres configurés.
     *
     * @return PDO Instance de la connexion PDO.
     * @throws \Exception Si la base de données n'est pas configurée, appeler setConfig() avant de faire une connexion.
     */
    public static function makeConnection()
    {
        // Vérifie si la configuration de la base de données est définie
        if (empty(self::$config)) {
            throw new \Exception("La base de données n'est pas configurée. Appeler setConfig() avant de faire une connexion.");
        }

        // Crée une nouvelle instance de connexion PDO si elle n'existe pas encore
        if (self::$instance === null) {
            $dsn = self::buildDsn();
            self::$instance = new \PDO($dsn, self::$config['username'], self::$config['password'], array(PDO::ATTR_PERSISTENT => true));
        }

        // Retourne l'instance de la connexion PDO
        return self::$instance;
    }

    /**
     * Construit la chaîne DSN (Data Source Name) pour PDO en utilisant les paramètres configurés.
     *
     * @return string Chaîne DSN pour la connexion PDO.
     */
    private static function buildDsn()
    {
        $dsn = "mysql:host=" . self::$config['host'];
        $dsn .= ";dbname=" . self::$config['database'];
        $dsn .= ";charset=" . self::$config['charset'];

        // Retourne la chaîne DSN construite
        return $dsn;
    }
}
