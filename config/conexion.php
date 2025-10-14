<?php
/**
 * Configuración de conexión a la base de datos
 * Base de datos: sleppystore
 * Compatible con XAMPP MySQL
 */

// Definir BASE_URL si no está definido
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = '/fashion-master/';
    define('BASE_URL', $protocol . '://' . $host . $path);
}

class Database {
    // Configuración de la base de datos
    private $host = 'localhost';
    private $db_name = 'sleppystore';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    public $pdo;
    
    /**
     * Obtener conexión PDO
     */
    public function getConnection() {
        $this->pdo = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        
        return $this->pdo;
    }
    
    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->pdo = null;
    }
    
    /**
     * Verificar conexión
     */
    public function testConnection() {
        try {
            $connection = $this->getConnection();
            if($connection) {
                echo "✅ Conexión exitosa a la base de datos 'sleppystore'";
                return true;
            }
        } catch(Exception $e) {
            echo "❌ Error en la conexión: " . $e->getMessage();
            return false;
        }
    }
}

/**
 * Función helper para obtener conexión rápidamente
 */
function getDB() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Función para ejecutar consultas SELECT de forma sencilla
 */
function executeQuery($query, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        echo "Error en consulta: " . $e->getMessage();
        return false;
    }
}

/**
 * Función para ejecutar INSERT, UPDATE, DELETE
 */
function executeNonQuery($query, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($query);
        return $stmt->execute($params);
    } catch(PDOException $e) {
        echo "Error en operación: " . $e->getMessage();
        return false;
    }
}

/**
 * Función para obtener el último ID insertado
 */
function getLastInsertId() {
    $db = getDB();
    return $db->lastInsertId();
}

// Crear una instancia global de conexión para compatibilidad
$database = new Database();
$conn = $database->getConnection();

// Verificar que la conexión sea exitosa
if (!$conn) {
    die('Error: No se pudo establecer conexión a la base de datos');
}
?>