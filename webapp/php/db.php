<?php
require_once __DIR__. "/config.php";

class Database{
    private $conn;
    private static $instance = null;

    public function __construct(){
        try {
            $this->conn = new PDO(
                "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME,
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => "Internal Server Error"
            ]);
            exit;
        }
    }

    public function getConnection(){
        return $this->conn;
    }
    public static function getInstance(){
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function validateAPIKey($key){
        if (isset($data['api_key'])) {
            // Validate API key against database
            $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE api_key = ?");
            $stmt->execute([$data['api_key']]);
            
            if ($user = $stmt->fetch()) {
                return $user;
            }
        }
        return null;
    }
}

$db = Database::getInstance();
?>