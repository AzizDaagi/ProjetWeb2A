<?php
class config
{
    private static $pdo = null;
    private static $envLoaded = false;

    public static function loadEnv()
    {
        if (self::$envLoaded) {
            return;
        }
        
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!getenv($key)) {
                        putenv("$key=$value");
                    }
                }
            }
        }
        
        self::$envLoaded = true;
    }

    public static function getConnexion()
    {
        self::loadEnv();
        
        if (!isset(self::$pdo)) {

            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "smart_nutrition";

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );

            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
?>