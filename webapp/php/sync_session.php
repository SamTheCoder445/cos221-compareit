<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['api_key'])) {
        // Validate API key against database
        /*$stmt = $conn->prepare("SELECT user_id FROM users WHERE api_key = ?");
        $stmt->execute([$data['api_key']]);
        
        if ($user = $stmt->fetch()) {
            $_SESSION['api_key'] = $data['api_key'];
            $_SESSION['user_id'] = $user['user_id'];
            echo json_encode(['status' => 'success']);
            exit;
        }*/
        $user = $db->validateAPIKey($data['api_key']);
        if($user !== null){
            $_SESSION['api_key'] = $data['api_key'];
            $_SESSION['user_id'] = $user['user_id'];
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid API key']);