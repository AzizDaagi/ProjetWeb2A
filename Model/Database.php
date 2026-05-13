<?php

namespace App\Model;

use mysqli;
use RuntimeException;

class Database
{
    public static function connection(): mysqli
    {
        static $connection = null;

        if ($connection instanceof mysqli) {
            return $connection;
        }

        $config = require dirname(__DIR__) . '/Controller/config/database.php';

        $connection = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['dbname']
        );

        if ($connection->connect_error) {
            throw new RuntimeException('Database connection failed: ' . $connection->connect_error);
        }

        $connection->set_charset($config['charset'] ?? 'utf8');

        return $connection;
    }
}
