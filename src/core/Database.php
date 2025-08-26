<?php
class Database {
    // Usamos el patrón Singleton para evitar múltiples conexiones
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Los detalles de la conexión se cargan desde el archivo de configuración
        require_once __DIR__ . '/../../config/database.php';

        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);

            // Configurar PDO para que lance excepciones en caso de error
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // En un entorno de producción, esto debería registrarse en un archivo de log
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene la instancia única de la conexión a la base de datos.
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Retorna el objeto de conexión PDO.
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    // Prevenir la clonación de la instancia (parte del patrón Singleton)
    private function __clone() {}

    // Prevenir la deserialización de la instancia (parte del patrón Singleton)
    public function __wakeup() {}
}
?>
