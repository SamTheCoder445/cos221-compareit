<?php

use LDAP\Result;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
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
<<<<<<< HEAD
                case 'AddReview':
                 $this->addReview($data);
                 break;
                case 'GetAllReviews':
                 $this->getAllReviews($data);
                 break;
                case 'AddToWishlist':
                $this->addToWishlist($data);
                break;
                case 'RemoveFromWishlist':
                $this->removeFromWishlist($data);
                break;case 'GetWishlist':
                $this->getWishlist($data);
                break;
=======
>>>>>>> f95591a17cc98e3fe996c6b6a49d4af947a7e680
                case 'GetDashboardData':
                    $this->getDashboardData();
                    break;
                case 'GetDashboardGraphData':
                    $this->getDashboardGraphData();
                    break;
<<<<<<< HEAD
                case 'AddUserPreferences':
                $this->addUserPreferences($data); 
                break;
                case 'GetUserPreferences':
                $this->getUserPreferences($data);
                break;
=======
>>>>>>> f95591a17cc98e3fe996c6b6a49d4af947a7e680
                default:
                 case 'GetWishlist':
                $this->getWishlist($data);
                break;
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
    $product_id = $data['product_id'] ?? null;
    $brand_id = $data['brand_id'] ?? null;
    $category_id = $data['category_id'] ?? null;
    $search = $data['search'] ?? null;
    $min_price = $data['min_price'] ?? null;
    $max_price = $data['max_price'] ?? null;

    // Pagination
    $limit = isset($data['limit']) ? (int)$data['limit'] : 50;
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

    $params = [];

    // Base SQL with brand and category joins
    $sql = "
        SELECT 
            p.product_id,
            p.title,
            p.description,
            p.thumbnail,
            p.brand_id,
            b.name AS brand,
            p.category_id,
            c.name AS category,
            p.availability_status,
            lp.price AS lowest_price,
            r.name AS retailer_name
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN (
            SELECT pr.product_id, MIN(pr.price) AS price
            FROM prices pr
            GROUP BY pr.product_id
        ) lp ON p.product_id = lp.product_id
        LEFT JOIN prices pr2 ON pr2.product_id = lp.product_id AND pr2.price = lp.price
        LEFT JOIN retailers r ON pr2.retailer_id = r.retailer_id
        WHERE 1 = 1
    ";

    if ($product_id !== null) {
        $sql .= " AND p.product_id = :product_id";
        $params[':product_id'] = $product_id;
    } else {
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

        // Pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
    }

    $stmt = $this->conn->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($product_id !== null) {
        if (count($products) === 0) {
            echo json_encode(["status" => "error", "message" => "Product not found."]);
        } else {
            echo json_encode(["status" => "success", "product" => $products[0]]);
        }
        return;
    }

    // Total count for filtered results
    $countSql = "
        SELECT COUNT(*) FROM products p
        LEFT JOIN (
            SELECT pr.product_id, MIN(pr.price) AS price
            FROM prices pr
            GROUP BY pr.product_id
        ) lp ON p.product_id = lp.product_id
        WHERE 1 = 1
    ";
    $countParams = [];

    if ($brand_id !== null && $brand_id !== 'all') {
        $countSql .= " AND p.brand_id = :brand_id";
        $countParams[':brand_id'] = $brand_id;
    }
    if ($category_id !== null && $category_id !== 'all') {
        $countSql .= " AND p.category_id = :category_id";
        $countParams[':category_id'] = $category_id;
    }
    if (!empty($search)) {
        $countSql .= " AND LOWER(p.title) LIKE :search";
        $countParams[':search'] = '%' . strtolower($search) . '%';
    }
    if (!empty($min_price)) {
        $countSql .= " AND lp.price >= :min_price";
        $countParams[':min_price'] = $min_price;
    }
    if (!empty($max_price)) {
        $countSql .= " AND lp.price <= :max_price";
        $countParams[':max_price'] = $max_price;
    }

    $countStmt = $this->conn->prepare($countSql);
    foreach ($countParams as $key => $val) {
        $countStmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();

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

private function getDashboardData(){
    /*
        "status" -> "success"
        "data": [
            "user"=> [
                "count" => 1200,
                "percentage_increase": 2.3%
            ],
            "products" =>[
                "count" => 1200,
                "percentage_increase": 3.2%
            ]
            "reviews"=> [
                "count" => 1200,
                "percentage_increase": 2.3%
            ],
            "stores" =>[
                "count" => 1200,
                "percentage_increase": 0%
            ]
        ]
    */
    try{
        $userCount = $this->getCount("users");
        $userGrowth = $this->getGrowth("users", "created_at");

        $productsCount = $this->getCount("products");
        $productsGrowth = $this->getGrowth("products", "created_at");

<<<<<<< HEAD
private function addReview($data) {
    if (!isset($data['apikey'], $data['product_id'], $data['review_rating'], $data['comment'])) {
        $this->sendError("Missing required fields", 400);
    }

    $apikey = $data['apikey'];
    $product_id = (int) $data['product_id'];
    $rating = (int) $data['review_rating'];
    $comment = trim($data['comment']);

    if ($rating < 1 || $rating > 5) {
        $this->sendError("Rating must be between 1 and 5", 400);
    }

    // Step 1: Get user_id from API key
    $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE api_key = ?");
    $stmt->execute([$apikey]);
    $user = $stmt->fetch();

    if (!$user) {
        $this->sendError("Invalid API key", 401);
    }

    $user_id = $user['user_id'];

    // Step 2: Check if user is a customer
    $stmt = $this->conn->prepare("SELECT 1 FROM customers WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() === 0) {
        $this->sendError("Only customers can leave reviews", 403);
    }

    // Step 3: Insert the review
    $review_date = date('Y-m-d');

    $stmt = $this->conn->prepare("
        INSERT INTO user_reviews (user_id, product_id, review_rating, comment, review_date) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([$user_id, $product_id, $rating, $comment, $review_date]);

    if ($success) {
        $this->sendSuccess(['message' => 'Review added successfully']);
    } else {
        $this->sendError("Failed to add review", 500);
    }
}



private function getAllReviews($data) {
    if (!isset($data['product_id'])) {
        $this->sendError("Missing product_id", 400);
    }

    $product_id = (int)$data['product_id'];
    

    // Get dummy reviews
    $stmt = $this->conn->prepare("
        SELECT review_rating, comment, reviewer_name, review_date 
        FROM dummy_reviews 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $dummyReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get real user reviews
    $stmt = $this->conn->prepare("
        SELECT ur.review_rating, ur.comment, CONCAT(u.name, ' ', u.surname) AS reviewer_name, ur.review_date 
        FROM user_reviews ur
        JOIN users u ON ur.user_id = u.user_id
        WHERE ur.product_id = ?
    ");
    $stmt->execute([$product_id]);
    $userReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine all reviews
    $allReviews = array_merge($dummyReviews, $userReviews);

    // Calculate average rating
    $totalRating = 0;
    $count = 0;
    foreach ($allReviews as $review) {
        $totalRating += (int)$review['review_rating'];
        $count++;
    }

    $averageRating = $count > 0 ? round($totalRating / $count, 2) : null;

    // Return response
    echo json_encode([
        "status" => "success",
        "reviews" => $allReviews,
        "average_rating" => $averageRating
    ]);
}

private function addToWishlist($data) {
    // 1. Validate required input
    if (!isset($data['apikey'], $data['product_id'])) {
        $this->sendError("Missing 'apikey' or 'product_id'", 400);
    }

    $apikey = $data['apikey'];
    $product_id = (int)$data['product_id'];

    // 2. Validate API key and ensure user is a customer
    $stmt = $this->conn->prepare("
        SELECT u.user_id
        FROM users u
        JOIN customers c ON u.user_id = c.user_id
        WHERE u.api_key = ?
    ");
    $stmt->execute([$apikey]);
    $user = $stmt->fetch();

    if (!$user) {
        $this->sendError("Only logged-in customers can add to wishlist", 403);
    }

    $user_id = $user['user_id'];

    // 3. Validate that product exists 
    $stmt = $this->conn->prepare("SELECT 1 FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        $this->sendError("Product not found", 404);
    }

    // 4. Check if product already in wishlist
    $stmt = $this->conn->prepare("
        SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ?
    ");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->fetch()) {
        $this->sendSuccess(['message' => 'Product already in wishlist']);
    }

    // 5. Insert into wishlist
    $stmt = $this->conn->prepare("
        INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)
    ");
    $stmt->execute([$user_id, $product_id]);

    $this->sendSuccess(['message' => 'Product added to wishlist']);
}
=======
        $retailersCount = $this->getCount("retailers");
        $retailersGrowth = $this->getGrowth("retailers", "created_at");
>>>>>>> f95591a17cc98e3fe996c6b6a49d4af947a7e680

        $reviewCount = $this->getCount("dummy_reviews");
        $reviewsGrowth = $this->getGrowth("dummy_reviews", "review_date");

        $topCategories = $this->getTopCategories();

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => [
                "users"=> [
                    "count" => $userCount,
                    "growth" => $userGrowth
                ],
                "products" =>[
                    "count" => $productsCount,
                    "growth" => $productsGrowth
                ],
                "retailers"=> [
                    "count" => $retailersCount,
                    "growth" => $retailersGrowth
                ],
                "reviews" =>[
                    "count" => $reviewCount,
                    "growth" => $reviewsGrowth
                ],
                "top_categories" => $topCategories
            ]
        ]);
    }catch(Exception $e){
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Interal Server Error: ".$e
        ]);
        exit;
    }
    
}

private function getCount($table){
    $allowedTables = ['users', 'products', 'retailers', 'dummy_reviews'];

    if(!in_array($table, $allowedTables)){
        http_response_code(404);
        echo json_encode([
            "status" => "success",
            "message" => "table not recognised: ".$table
        ]);
        exit;
    }
    $stmt = $this->conn->prepare("SELECT COUNT(*) FROM ".$table);
    $stmt->execute();
    $count = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $count;
}
private function getGrowth($table, $column){
    $allowedTables = ['users', 'products', 'retailers', 'dummy_reviews'];

    if(!in_array($table, $allowedTables)){
        http_response_code(404);
        echo json_encode([
            "status" => "success",
            "message" => "table not recognised: " .$table
        ]);
        exit;
    }
    $sql = "SELECT
            COUNT(CASE WHEN {$column} >= NOW() - INTERVAL 30 DAY THEN 1 END) AS recent,
            COUNT(CASE WHEN {$column} >= NOW() - INTERVAL 60 DAY AND {$column} < NOW() - INTERVAL 30 DAY THEN 1 END) AS previous
            FROM {$table}";

    $stmt = $this->conn->prepare($sql);
    if(!$stmt->execute()){
        $this->sendError("Query execution failed", 500);
    }
    $results = $stmt->fetch(PDO::FETCH_ASSOC);

    $recent = (int)$results['recent'];
    $previous = (int)$results['previous'];
    $growth = $previous > 0 ? (($recent - $previous) / $previous)*100 : 0;
    return $growth;
}
private function getDashboardGraphData(){
    $priceData = $this->getPricesTrend();
    $wishlistData = $this->getWishlistTrend();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => [
            "prices" => [
                "labels" => array_column($priceData, 'date'),
                'average' => array_column($priceData, 'average_price'),
                'data_points' => array_column($priceData, 'data_points')
            ],
            "wishlists" => [
                "labels" => array_column($wishlistData, 'data'),
                'data_points' => array_column($wishlistData, 'data_points')
            ]
        ]
    ]);
}
private function getPricesTrend(){
    $sql = "SELECT 
                DATE(p.created_at) AS date,
                ROUND(AVG(pr.price), 2) AS average_price,
                COUNT(*) AS data_points
            FROM 
                prices pr
            JOIN 
                products p ON pr.product_id = p.product_id
            WHERE 
                p.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            GROUP BY 
                DATE(p.created_at)
            ORDER BY 
                date ASC";

    try{
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }catch(PDOException $e){
        error_log("Database error in getPricesTrend(): ".$e->getMessage());
        $this->sendError("Internal Server Issue", 500);
    }
}
private function getWishlistTrend(){
    $sql = "SELECT
                DATE(created_at) as date,
                COUNT(*) as save_count
            FROM
                wishlists
            WHERE created_at >- DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
            ";
    try{
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }catch(PDOException $e){
        error_log("Database error in getWishlistTrend(): ".$e->getMessage());
        $this->sendError("Internal Server Issue:", 500);
    }
}

private function getTopCategories(){
    $sql = "SELECT 
                p.product_id,
                p.title AS product_name,
                COUNT(*) AS save_count,
                COUNT(DISTINCT w.user_id) AS unique_users
            FROM 
                wishlists w
            JOIN 
                products p ON w.product_id = p.product_id
            WHERE 
                w.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            GROUP BY 
                w.product_id
            ORDER BY 
                save_count DESC
            LIMIT 5;";
    try{
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }catch(PDOException $e){
        error_log("Database error in getWishlistTrend(): ".$e->getMessage());
        $this->sendError("Internal Server Issue:" .$e->getMessage(), 500);
    }
}


private function removeFromWishlist($data) {
    // 1. Validate input
    if (!isset($data['apikey'], $data['product_id'])) {
        $this->sendError("Missing 'apikey' or 'product_id'", 400);
    }

    $apikey = $data['apikey'];
    $product_id = (int)$data['product_id'];

    // 2. Verify customer with API key
    $stmt = $this->conn->prepare("
        SELECT u.user_id
        FROM users u
        JOIN customers c ON u.user_id = c.user_id
        WHERE u.api_key = ?
    ");
    $stmt->execute([$apikey]);
    $user = $stmt->fetch();

    if (!$user) {
        $this->sendError("Only logged-in customers can remove from wishlist", 403);
    }

    $user_id = $user['user_id'];

    // 3. Attempt to delete from wishlist
    $stmt = $this->conn->prepare("
        DELETE FROM wishlists WHERE user_id = ? AND product_id = ?
    ");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->rowCount() > 0) {
        $this->sendSuccess(['message' => 'Product removed from wishlist']);
    } else {
        $this->sendSuccess(['message' => 'Product was not in wishlist']);
    }
}


private function getWishlist($data) {
    // Validate input
    if (!isset($data['apikey'])) {
        $this->sendError("Missing 'apikey'", 400);
    }

    $apikey = $data['apikey'];

    // Validate API key and get user_id (must be a customer)
    $stmt = $this->conn->prepare("
        SELECT u.user_id 
        FROM users u
        JOIN customers c ON u.user_id = c.user_id
        WHERE u.api_key = ?
    ");
    $stmt->execute([$apikey]);
    $user = $stmt->fetch();

    if (!$user) {
        $this->sendError("Invalid API key or not a customer", 403);
    }

    // Fetch wishlist items with title and thumbnail from products
    $stmt = $this->conn->prepare("
        SELECT p.product_id, p.title, p.thumbnail
        FROM wishlists w
        JOIN products p ON w.product_id = p.product_id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user['user_id']]);
    $wishlist = $stmt->fetchAll();

    $this->sendSuccess(['wishlist' => $wishlist]);
}


private function addUserPreferences($data = []) {
    // Define guest account API key
    $GUEST_API_KEY = 'b14561c4fc744210fe86c1eb1ab4a0663640ff12f75e6b7dfca9b4a37eca4756';

    // Validate required fields
    if (empty($data['api_key'])) {
        throw new Exception("API key is required", 400);
    }

    // Block guest API key
    if ($data['api_key'] === $GUEST_API_KEY) {
        throw new Exception("Only logged in customers can save preferences", 403);
    }

    // Verify API key belongs to a customer
    $stmt = $this->conn->prepare("
        SELECT u.user_id 
        FROM users u
        JOIN customers c ON u.user_id = c.user_id
        WHERE u.api_key = ?
    ");
    $stmt->execute([$data['api_key']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("Only logged in customers can save preferences", 403);
    }

    $user_id = $user['user_id'];

    // Validate brand exists if provided
    if (!empty($data['preferred_brand']) && $data['preferred_brand'] !== 'All Brands') {
        $stmt = $this->conn->prepare("SELECT brand_id FROM brands WHERE name = ?");
        $stmt->execute([$data['preferred_brand']]);
        if (!$stmt->fetch()) {
            throw new Exception("Brand '{$data['preferred_brand']}' does not exist", 400);
        }
    }

    // Validate category exists if provided
    if (!empty($data['preferred_category']) && $data['preferred_category'] !== 'All Categories') {
        $stmt = $this->conn->prepare("SELECT category_id FROM categories WHERE name = ?");
        $stmt->execute([$data['preferred_category']]);
        if (!$stmt->fetch()) {
            throw new Exception("Category '{$data['preferred_category']}' does not exist", 400);
        }
    }

    // Validate price range if provided
    $validPriceRanges = ['all', '0-500', '500-1000', '1000-5000', '5000-Infinity'];
    if (!empty($data['preferred_price_range']) && !in_array($data['preferred_price_range'], $validPriceRanges)) {
        throw new Exception("Invalid price range specified", 400);
    }

    // Validate sort_order if provided
    if (isset($data['sort_order'])) {
        $validSortOptions = ['Price: Low to High', 'Price: High to Low'];
        if (!in_array($data['sort_order'], $validSortOptions)) {
            throw new Exception("Invalid sort_order. Must be either 'Price: Low to High' or 'Price: High to Low'", 400);
        }
    }

    // Prepare preferences array
    $preferences = [
        'user_id' => $user_id,
        'preferred_brand' => $data['preferred_brand'] ?? null,
        'preferred_category' => $data['preferred_category'] ?? null,
        'preferred_price_range' => $data['preferred_price_range'] ?? null,
        'sort_order' => $data['sort_order'] ?? null
    ];

    // Check if preferences exist
    $stmt = $this->conn->prepare("SELECT user_id FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    if ($stmt->fetch()) {
        // Update existing preferences
        $query = "UPDATE user_preferences SET ";
        $updates = [];
        $params = [];
        
        foreach (['preferred_brand', 'preferred_category', 'preferred_price_range', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                $params[] = $preferences[$field];
            }
        }
        
        if (!empty($updates)) {
            $query .= implode(', ', $updates) . ", last_updated = CURRENT_TIMESTAMP WHERE user_id = ?";
            $params[] = $user_id;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
        }
    } else {
        // Insert new preferences
        $fields = ['user_id'];
        $placeholders = ['?'];
        $params = [$user_id];
        
        foreach (['preferred_brand', 'preferred_category', 'preferred_price_range', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = $field;
                $placeholders[] = '?';
                $params[] = $preferences[$field];
            }
        }
        
        $query = "INSERT INTO user_preferences (" . implode(', ', $fields) . ", last_updated) 
                 VALUES (" . implode(', ', $placeholders) . ", CURRENT_TIMESTAMP)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
    }

    $this->sendSuccess([
        'message' => 'Preferences saved successfully',
        'user_id' => $user_id,
        'preferences' => $preferences
    ]);
}

private function getUserPreferences($data) {
    // Define guest API key
    $GUEST_API_KEY = 'b14561c4fc744210fe86c1eb1ab4a0663640ff12f75e6b7dfca9b4a37eca4756';

    // Validate required fields
    if (empty($data['api_key'])) {
        throw new Exception("API key is required", 400);
    }

    // Block guest API key
    if ($data['api_key'] === $GUEST_API_KEY) {
        throw new Exception("Guest users cannot have preferences", 403);
    }

    // Verify API key belongs to a customer (not admin)
    $stmt = $this->conn->prepare("
        SELECT u.user_id 
        FROM users u
        LEFT JOIN admins a ON u.user_id = a.user_id
        WHERE u.api_key = ? AND a.user_id IS NULL
    ");
    $stmt->execute([$data['api_key']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("Invalid API key or unauthorized user", 403);
    }

    // Get preferences
    $stmt = $this->conn->prepare("
        SELECT 
            preferred_brand, 
            preferred_category, 
            preferred_price_range, 
            sort_order,
            last_updated
        FROM user_preferences
        WHERE user_id = ?
    ");
    $stmt->execute([$user['user_id']]);
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

    $this->sendSuccess([
        'preferences' => $preferences ?: null,
        'user_id' => $user['user_id']
    ]);
}


//////////////////////////////////////////////////////

private function getDashboardData(){
   
    try{
        $userCount = $this->getCount("users");
        $userGrowth = $this->getGrowth("users", "created_at");

        $productsCount = $this->getCount("products");
        $productsGrowth = $this->getGrowth("products", "created_at");

        $retailersCount = $this->getCount("retailers");
        $retailersGrowth = $this->getGrowth("retailers", "created_at");

        $reviewCount = $this->getCount("dummy_reviews");
        $reviewsGrowth = $this->getGrowth("dummy_reviews", "review_date");

        $topCategories = $this->getTopCategories();

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => [
                "users"=> [
                    "count" => $userCount,
                    "growth" => $userGrowth
                ],
                "products" =>[
                    "count" => $productsCount,
                    "growth" => $productsGrowth
                ],
                "retailers"=> [
                    "count" => $retailersCount,
                    "growth" => $retailersGrowth
                ],
                "reviews" =>[
                    "count" => $reviewCount,
                    "growth" => $reviewsGrowth
                ],
                "top_categories" => $topCategories
            ]
        ]);
    }catch(Exception $e){
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Interal Server Error: ".$e
        ]);
        exit;
    }
    
}

private function getCount($table){
    $allowedTables = ['users', 'products', 'retailers', 'dummy_reviews'];

    if(!in_array($table, $allowedTables)){
        http_response_code(404);
        echo json_encode([
            "status" => "success",
            "message" => "table not recognised: ".$table
        ]);
        exit;
    }
    $stmt = $this->conn->prepare("SELECT COUNT(*) FROM ".$table);
    $stmt->execute();
    $count = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $count;
}
private function getGrowth($table, $column){
    $allowedTables = ['users', 'products', 'retailers', 'dummy_reviews'];

    if(!in_array($table, $allowedTables)){
        http_response_code(404);
        echo json_encode([
            "status" => "success",
            "message" => "table not recognised: " .$table
        ]);
        exit;
    }
    $sql = "SELECT
            COUNT(CASE WHEN {$column} >= NOW() - INTERVAL 30 DAY THEN 1 END) AS recent,
            COUNT(CASE WHEN {$column} >= NOW() - INTERVAL 60 DAY AND {$column} < NOW() - INTERVAL 30 DAY THEN 1 END) AS previous
            FROM {$table}";

    $stmt = $this->conn->prepare($sql);
    if(!$stmt->execute()){
        $this->sendError("Query execution failed", 500);
    }
    $results = $stmt->fetch(PDO::FETCH_ASSOC);

    $recent = (int)$results['recent'];
    $previous = (int)$results['previous'];
    $growth = $previous > 0 ? (($recent - $previous) / $previous)*100 : 0;
    return $growth;
}
private function getDashboardGraphData(){
    $priceData = $this->getPricesTrend();
    $wishlistData = $this->getWishlistTrend();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => [
            "prices" => [
                "labels" => array_column($priceData, 'date'),
                'average' => array_column($priceData, 'average_price'),
                'data_points' => array_column($priceData, 'data_points')
            ],
            "wishlists" => [
                "labels" => array_column($wishlistData, 'data'),
                'data_points' => array_column($wishlistData, 'data_points')
            ]
        ]
    ]);
}
private function getPricesTrend(){
    $sql = "SELECT 
                DATE(p.created_at) AS date,
                ROUND(AVG(pr.price), 2) AS average_price,
                COUNT(*) AS data_points
            FROM 
                prices pr
            JOIN 
                products p ON pr.product_id = p.product_id
            WHERE 
                p.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            GROUP BY 
                DATE(p.created_at)
            ORDER BY 
                date ASC";

    try{
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }catch(PDOException $e){
        error_log("Database error in getPricesTrend(): ".$e->getMessage());
        $this->sendError("Internal Server Issue", 500);
    }
}

private function getWishlistTrend() {
    $sql = "
        WITH RECURSIVE date_series AS (
            SELECT CURDATE() - INTERVAL 29 DAY AS date
            UNION ALL
            SELECT date + INTERVAL 1 DAY
            FROM date_series
            WHERE date + INTERVAL 1 DAY <= CURDATE()
        )
        SELECT
            ds.date,
            COUNT(w.product_id) AS save_count
        FROM
            date_series ds
        LEFT JOIN wishlists w ON DATE(w.created_at) = ds.date
        GROUP BY ds.date
        ORDER BY ds.date ASC
    ";

    try {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    } catch (PDOException $e) {
        error_log("Database error in getWishlistTrend(): " . $e->getMessage());
        $this->sendError("Internal Server Issue", 500);
    }
}


private function getTopCategories(){
    $sql = "SELECT 
                p.product_id,
                p.title AS product_name,
                COUNT(*) AS save_count,
                COUNT(DISTINCT w.user_id) AS unique_users
            FROM 
                wishlists w
            JOIN 
                products p ON w.product_id = p.product_id
            WHERE 
                w.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            GROUP BY 
                w.product_id
            ORDER BY 
                save_count DESC
            LIMIT 5;";
    try{
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }catch(PDOException $e){
        error_log("Database error in getWishlistTrend(): ".$e->getMessage());
        $this->sendError("Internal Server Issue:" .$e->getMessage(), 500);
    }
}



}

 

$api = new API();
$api->processRequest();

