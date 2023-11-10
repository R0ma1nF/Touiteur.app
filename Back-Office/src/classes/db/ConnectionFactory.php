<?php

namespace iutnc\BackOffice\db;

use PDO;

class ConnectionFactory
{
    private static $config = [];
    private static $instance;

    public static function setConfig($file)
    {
        self::$config = parse_ini_file($file);
    }

    public static function makeConnection()
    {
        if (empty(self::$config)) {
            throw new \Exception("la base de donnée n'est pas configuré. Appeler setConfig() avant de faire une connection.");
        }

        if (self::$instance === null) {
            $dsn = self::buildDsn();
            self::$instance = new \PDO($dsn, self::$config['username'], self::$config['password'], array(PDO::ATTR_PERSISTENT => true));

        }

        return self::$instance;
    }

    private static function buildDsn()
    {
        $dsn = "mysql:host=" . self::$config['host'];
        $dsn .= ";dbname=" . self::$config['database'];
        $dsn .= ";charset=" . self::$config['charset'];
        return $dsn;
    }
}
