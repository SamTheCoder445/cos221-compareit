<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';



class API {
    private $conn;
    
    public function __construct() {
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
            $this->sendError("Database connection failed", 500);
            exit;
        }
    }

    public function processRequest() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Only POST method is allowed', 405);
            }

            $jsonInput = file_get_contents('php://input');
            $data = json_decode($jsonInput, true);

            if (empty($data)) {
                throw new Exception('Invalid or empty JSON input', 400);
            }

            switch ($data['type'] ?? '') {
                case 'Register':
                    $this->handleRegistration($data);
                    break;
                case 'Login':
                    $this->handleLogin($data);
                    break;
                default:
                    throw new Exception('Invalid request type', 400);
            }
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    private function handleRegistration($data) {
        // Validation
        $required = ['name', 'surname', 'email', 'password', 'user_type'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required", 400);
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format', 400);
        }

        if (strlen($data['password']) < 8) {
            throw new Exception('Password must be at least 8 characters', 400);
        }

        $validTypes = ['Admin', 'Customer'];
        if (!in_array($data['user_type'], $validTypes)) {
            throw new Exception('Invalid user type', 400);
        }

        // Check if email exists
        $stmt = $this->conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered', 409);
        }

   

$salt = bin2hex(random_bytes(8));
$hashedPassword = hash('sha256', $data['password'] . $salt) . ':' . $salt;

        // Generate API key
        $apiKey = bin2hex(random_bytes(32));

        // Start transaction
        $this->conn->beginTransaction();

        try {
            // Insert into users table
            $stmt = $this->conn->prepare("
                INSERT INTO users (name, surname, email, password, api_key, user_type) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['surname'],
                $data['email'],
                $hashedPassword,
                $apiKey,
                $data['user_type']
            ]);
            
            $userId = $this->conn->lastInsertId();
            
            // Insert into appropriate role table
            if ($data['user_type'] === 'Admin') {
                $stmt = $this->conn->prepare("INSERT INTO admins (user_id) VALUES (?)");
            } else {
                $stmt = $this->conn->prepare("INSERT INTO customers (user_id) VALUES (?)");
            }
            $stmt->execute([$userId]);
            
            $this->conn->commit();
            
            $this->sendSuccess([
                'message' => 'Registration successful',
                'api_key' => $apiKey,
                'user_id' => $userId
            ]);
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new Exception('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    private function sendSuccess($data = [], $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
        exit;
    }

    private function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit;
    }



    private function handleLogin($data) {
    // Validate input
    $required = ['email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required", 400);
        }
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format', 400);
    }

    try {
        // Get user data including password hash and API key
        $stmt = $this->conn->prepare("
            SELECT user_id, name, password, api_key, user_type 
            FROM users 
            WHERE email = ?
        ");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception('Invalid email or password', 401);
        }

        // Verify password (stored as hash:salt)
        $passwordParts = explode(':', $user['password']);
        if (count($passwordParts) !== 2) {
            throw new Exception('Invalid password format in database', 500);
        }

        list($storedHash, $salt) = $passwordParts;
        $computedHash = hash('sha256', $data['password'] . $salt);

        if (!hash_equals($storedHash, $computedHash)) {
            throw new Exception('Invalid email or password', 401);
        }

        // Login successful - return user data
        $this->sendSuccess([
            'message' => 'Login successful',
            'api_key' => $user['api_key'],
            'user_id' => $user['user_id'],
            'name' => $user['name'],
            'user_type' => $user['user_type']
        ]);

    } catch (PDOException $e) {
        throw new Exception('Database error during login', 500);
    }
}
}

 


// Run the API
$api = new API();
$api->processRequest();

