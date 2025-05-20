<?php
header("Content-Type: application/json");

// Load DB config
require_once("config.php");

// Database Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Helper Class
class Utils {
    public static function validateEmail($email) {
        return preg_match('/^[\w\.-]+@[\w\.-]+\.\w{2,4}$/', $email);
    }

    public static function validatePassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{9,}$/', $password);
    }

    public static function hashPassword($password) {
        return hash("sha256", $password);
    }

    public static function generateApiKey($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    public static function timestamp() {
        return round(microtime(true) * 1000);
    }
}

// API Class
class API {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function handleRequest() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["type"])) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Missing 'type' field."
            ]);
            return;
        }

        switch ($data["type"]) {
            case "Register":
                $this->register($data);
                break;

            case "GetAllProducts":
                $this->getAllProducts();
                break;

            default:
                http_response_code(400);
                echo json_encode([
                    "status" => "error",
                    "message" => "Unknown request type."
                ]);
                break;
        }
    }

    private function register($data) {
        $required = ["name", "surname", "email", "password", "user_type"];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode([
                    "status" => "error",
                    "message" => "Missing or empty field: $field"
                ]);
                return;
            }
        }

        $name = $this->conn->real_escape_string($data["name"]);
        $surname = $this->conn->real_escape_string($data["surname"]);
        $email = $this->conn->real_escape_string($data["email"]);
        $password = $data["password"];
        $userType = $this->conn->real_escape_string($data["user_type"]);

        if (!Utils::validateEmail($email)) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid email format."
            ]);
            return;
        }

        if (!Utils::validatePassword($password)) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Password must be alteast 8 characters and include upper, lower, digit, and special character."
            ]);
            return;
        }

        // Check if user exists
        $checkQuery = "SELECT email FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($checkQuery);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "SQL preparation failed: " . $this->conn->error
            ]);
            return;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            http_response_code(409); 
            echo json_encode([
                "status" => "error",
                "message" => "User with this email already exists."
            ]);
            return;
        }

        // Hash password and generate API key
        $hashedPassword = Utils::hashPassword($password);
        $apiKey = Utils::generateApiKey();

        // Insert new user
        $insertQuery = "INSERT INTO users (name, surname, email, password, user_type, api_key) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($insertQuery);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "SQL preparation failed: " . $this->conn->error
            ]);
            return;
        }

        $stmt->bind_param("ssssss", $name, $surname, $email, $hashedPassword, $userType, $apiKey);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "User registered successfully.",
                "timestamp" => Utils::timestamp(),
                "data" => [
                    "apikey" => $apiKey
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to register user: " . $stmt->error
            ]);
        }
    }

private function getAllProducts() {

$sql = "SELECT * FROM product";
$result = $conn->query($sql);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
echo "<products>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<product>";
        echo "<id>" . $row['id'] . "</id>";
        echo "<title>" . htmlspecialchars($row['title']) . "</title>";
        echo "<description>" . htmlspecialchars($row['description']) . "</description>";
        echo "<brand>" . $row['brand'] . "</brand>";
        echo "<category>" . $row['category'] . "</category>";
        echo "<thumbnail>" . htmlspecialchars($row['thumbnail']) . "</thumbnail>";
        echo "<rating>" . $row['rating'] . "</rating>";
        echo "</product>";
    }
}

echo "</products>";

$conn->close();

 }
}

// Handle the request
$api = new API($conn);
$api->handleRequest();