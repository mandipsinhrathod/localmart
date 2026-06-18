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

$phone = trim($_POST['phone'] ?? '');
$passwordInput = $_POST['password'] ?? '';

if (empty($phone) || empty($passwordInput)) {
    echo json_encode(["status" => "error", "message" => "Missing phone or password"]);
    exit;
}

try {
    // Fetch user by phone
    $stmt = $conn->prepare("SELECT id, name, phone, email, password FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify password
        if (password_verify($passwordInput, $user['password'])) {
            // Remove password hash from the JSON response for security
            unset($user['password']);
            
            // Format ID as integer
            $user['id'] = (int)$user['id'];
            
            echo json_encode([
                "status" => "success", 
                "message" => "Login successful",
                "user" => $user
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
