<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../includes/db_config.php';

// Accept vendor_id, store_id, or shop_id for flexible client integration
$vendor_id = $_GET['vendor_id'] ?? $_GET['store_id'] ?? $_GET['shop_id'] ?? '';

try {
    if (!empty($vendor_id)) {
        $stmt = $conn->prepare("SELECT id, vendor_id, name, description, price, image_path, availability, weight_qty, product_type, shelf_life, grade, price_unit, created_at FROM items WHERE vendor_id = ? ORDER BY id DESC");
        $stmt->execute([$vendor_id]);
    } else {
        $stmt = $conn->query("SELECT id, vendor_id, name, description, price, image_path, availability, weight_qty, product_type, shelf_life, grade, price_unit, created_at FROM items ORDER BY id DESC");
    }
    $products = $stmt->fetchAll();

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $http_host = $_SERVER['HTTP_HOST'];
    $base_dir = dirname(dirname($_SERVER['PHP_SELF']));
    $base_dir = str_replace('\\', '/', $base_dir);
    if ($base_dir === '/') {
        $base_dir = '';
    }
    $base_url = "$protocol://$http_host$base_dir/";

    foreach ($products as &$prod) {
        if ($prod['image_path']) {
            $prod['image_url'] = $base_url . $prod['image_path'];
        } else {
            $prod['image_url'] = $base_url . 'assets/images/placeholder_product.svg';
        }
    }

    echo json_encode([
        "status" => true,
        "products" => $products
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
