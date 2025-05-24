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
                case 'GetAllProducts':
                     $this->getAllProducts($data);
                    break;
                case 'GetAllBrands':
                     $this->getAllBrands();
                     break;
                case 'GetAllCategories':
                    $this->getAllCategories();
                    break;
                case 'GetProductImages':
                    $this->getProductImages($data);
                    break;
                case 'GetAllPrices':
                 $this->getAllPrices($data);
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



private function getAllProducts($data) {
    // Validate API key
    if (!isset($data['apikey'])) {
        echo json_encode(["error" => "Missing API key."]);
        return;
    }

    $apikey = $data['apikey'];

    // Validate API key exists
    $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE api_key = ?");
    $stmt->execute([$apikey]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(["error" => "Invalid API key."]);
        return;
    }

    // Optional filters
    $brand_id = $data['brand_id'] ?? null;
    $category_id = $data['category_id'] ?? null;
    $search = $data['search'] ?? null;
    $min_price = $data['min_price'] ?? null;
    $max_price = $data['max_price'] ?? null;

    // Pagination defaults
    $limit = isset($data['limit']) ? (int)$data['limit'] : 50;
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

    $params = [];

    // Base SQL
    $sql = "
        SELECT 
            p.product_id,
            p.title,
            p.description,
            p.thumbnail,
            p.brand_id,
            p.category_id,
            p.availability_status,
            lp.price AS lowest_price,
            r.name AS retailer_name
        FROM products p
        LEFT JOIN (
            SELECT pr.product_id, MIN(pr.price) AS price
            FROM prices pr
            GROUP BY pr.product_id
        ) lp ON p.product_id = lp.product_id
        LEFT JOIN prices pr2 ON pr2.product_id = lp.product_id AND pr2.price = lp.price
        LEFT JOIN retailers r ON pr2.retailer_id = r.retailer_id
        WHERE 1 = 1
    ";

    // Filters
    if ($brand_id !== null && $brand_id !== 'all') {
        $sql .= " AND p.brand_id = :brand_id";
        $params[':brand_id'] = $brand_id;
    }

    if ($category_id !== null && $category_id !== 'all') {
        $sql .= " AND p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }

    if (!empty($search)) {
        $sql .= " AND LOWER(p.title) LIKE :search";
        $params[':search'] = '%' . strtolower($search) . '%';
    }

    if (!empty($min_price)) {
        $sql .= " AND lp.price >= :min_price";
        $params[':min_price'] = $min_price;
    }

    if (!empty($max_price)) {
        $sql .= " AND lp.price <= :max_price";
        $params[':max_price'] = $max_price;
    }

    // Count total matching records
    $countSql = "
        SELECT COUNT(*) FROM products p
        LEFT JOIN (
            SELECT pr.product_id, MIN(pr.price) AS price
            FROM prices pr
            GROUP BY pr.product_id
        ) lp ON p.product_id = lp.product_id
        WHERE 1 = 1
    ";
    $countParams = $params;

    if ($brand_id !== null && $brand_id !== 'all') {
        $countSql .= " AND p.brand_id = :brand_id";
    }
    if ($category_id !== null && $category_id !== 'all') {
        $countSql .= " AND p.category_id = :category_id";
    }
    if (!empty($search)) {
        $countSql .= " AND LOWER(p.title) LIKE :search";
    }
    if (!empty($min_price)) {
        $countSql .= " AND lp.price >= :min_price";
    }
    if (!empty($max_price)) {
        $countSql .= " AND lp.price <= :max_price";
    }

    $countStmt = $this->conn->prepare($countSql);
    foreach ($countParams as $key => $val) {
        $countStmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();

    // Add pagination to query
    $sql .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $stmt = $this->conn->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $products,
        "total" => $total
    ]);
}





private function getAllBrands() {
    try {
        $stmt = $this->conn->query("SELECT brand_id, name FROM brands ORDER BY name");
        $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($brands);
    } catch (PDOException $e) {
        $this->sendError("Failed to fetch brands", 500);
    }
}



private function getAllCategories() {
    try {
        $stmt = $this->conn->query("SELECT category_id, name FROM categories ORDER BY name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendSuccess($categories);
    } catch (PDOException $e) {
        $this->sendError("Failed to fetch categories", 500);
    }
}



private function getAllPrices($data) {
   

    // Validate input
    if (!isset($data['apikey'], $data['product_id'])) {
        echo json_encode(["error" => "Missing 'apikey' or 'product_id'."]);
        exit;
    }

    $apikey = $data['apikey'];
    $product_id = intval($data['product_id']);

    // Validate API key
    $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE api_key = ?");
    $stmt->execute([$apikey]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(["error" => "Invalid API key."]);
        exit;
    }

    // Fetch all prices with retailer info
    $sql = "
        SELECT 
            p.product_id,
            r.name AS retailer_name,
            p.price,
            r.website
        FROM 
            prices p
        JOIN 
            retailers r ON p.retailer_id = r.retailer_id
        WHERE 
            p.product_id = ?
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$product_id]);
    $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($prices)) {
        echo json_encode([
            "product_id" => $product_id,
            "prices" => [],
            "lowest_price" => null
        ]);
        exit;
    }

    // Determine the lowest price
    $lowest = $prices[0];
    foreach ($prices as $entry) {
        if ($entry['price'] < $lowest['price']) {
            $lowest = $entry;
        }
    }

    // Final response
    echo json_encode([
        "product_id" => $product_id,
        "prices" => $prices,
        "lowest_price" => $lowest
    ]);
}



private function getProductImages($data) {
    if (!isset($data['product_id'])) {
        echo json_encode(["error" => "Missing product_id."]);
        return;
    }

    $product_id = $data['product_id'];

    // Get thumbnail
    $stmt = $this->conn->prepare("SELECT thumbnail FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $thumbnail = $stmt->fetchColumn();

    // Get other images
    $stmt = $this->conn->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Put thumbnail first
    if ($thumbnail) {
        array_unshift($images, $thumbnail);
    }

    echo json_encode([
        "status" => "success",
        "images" => $images
    ]);
}

























}

 


// Run the API
$api = new API();
$api->processRequest();

