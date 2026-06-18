<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../includes/db_config.php';

try {
    $stmt = $conn->query("SELECT id, shop_name, owner_name, email, shop_description, address, store_type, contact_number, qr_code_token, logo_path, theme_color, theme_bg, font_style, created_at FROM vendors ORDER BY id DESC");
    $stores = $stmt->fetchAll();

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $http_host = $_SERVER['HTTP_HOST'];
    $base_dir = dirname(dirname($_SERVER['PHP_SELF']));
    $base_dir = str_replace('\\', '/', $base_dir);
    if ($base_dir === '/') {
        $base_dir = '';
    }
    $base_url = "$protocol://$http_host$base_dir/";

    foreach ($stores as &$store) {
        if ($store['logo_path']) {
            $store['logo_url'] = $base_url . $store['logo_path'];
        } else {
            $store['logo_url'] = null;
        }
    }

    echo json_encode([
        "status" => true,
        "stores" => $stores
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>