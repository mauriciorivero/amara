<?php

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        $this->loadEnv();

        $host = $this->getEnv('DB_HOST');
        $db_name = $this->getEnv('DB_NAME');
        $username = $this->getEnv('DB_USERNAME');
        $password = $this->getEnv('DB_PASSWORD');
        $charset = $this->getEnv('DB_CHARSET', 'utf8mb4');

        try {
            $dsn = "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=" . $charset;
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error de conexión a BD: " . $e->getMessage());
        }
    }

    // Método para obtener la instancia única
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Método para obtener la conexión PDO
    public function getConnection()
    {
        return $this->conn;
    }

    // Helper simple para leer el archivo .env (o env.php)
    private function loadEnv()
    {
        $envFile = __DIR__ . '/env.php';
        if (!file_exists($envFile)) {
            throw new Exception("Archivo de configuración no encontrado: " . $envFile);
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0)
                continue;

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Guardar en variables de entorno o $_ENV
            $_ENV[$name] = $value;
        }
    }

    private function getEnv($key, $default = null)
    {
        return isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }
}
