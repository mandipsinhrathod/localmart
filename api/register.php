<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../includes/db_config.php';

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$passwordInput = $_POST['password'] ?? '';

if (empty($name) || empty($phone) || empty($passwordInput)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

try {
    // Check if user already exists (using prepared statements to prevent SQL Injection)
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $checkStmt->execute([$phone]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Phone number is already registered"]);
        exit;
    }

    // Hash password securely (using BCRYPT as requested)
    $hashedPassword = password_hash($passwordInput, PASSWORD_BCRYPT);

    // Insert into database
    $insertStmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
    $success = $insertStmt->execute([$name, $phone, $email ?: null, $hashedPassword]);

    if ($success) {
        $userId = $conn->lastInsertId();
        echo json_encode([
            "status" => "success", 
            "message" => "User registered successfully",
            "user" => [
                "id" => (int)$userId,
                "name" => $name,
                "phone" => $phone,
                "email" => $email ?: null
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to register user"]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
