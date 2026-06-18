<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../includes/db_config.php';

$id = $_GET['id'] ?? '';
$code = $_GET['code'] ?? '';

if (empty($id) && empty($code)) {
    echo json_encode([
        "status" => false,
        "message" => "Store ID or QR Code Token Required. Example: store.php?id=1 or store.php?code=token_bakery"
    ]);
    exit;
}

try {
    if (!empty($code)) {
        $stmt = $conn->prepare("SELECT id, shop_name, owner_name, email, shop_description, address, store_type, contact_number, qr_code_token, logo_path, theme_color, theme_bg, font_style, created_at FROM vendors WHERE qr_code_token = ?");
        $stmt->execute([$code]);
    } else {
        $stmt = $conn->prepare("SELECT id, shop_name, owner_name, email, shop_description, address, store_type, contact_number, qr_code_token, logo_path, theme_color, theme_bg, font_style, created_at FROM vendors WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    $store = $stmt->fetch();

    if ($store) {
        // Fetch products for this store including specifications
        $itemsStmt = $conn->prepare("SELECT id, name, description, price, image_path, availability, weight_qty, product_type, shelf_life, grade, price_unit, created_at FROM items WHERE vendor_id = ? ORDER BY id DESC");
        $itemsStmt->execute([$store['id']]);
        $products = $itemsStmt->fetchAll();

        // Format logo path to full URL if present
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $http_host = $_SERVER['HTTP_HOST'];
        $base_dir = dirname(dirname($_SERVER['PHP_SELF']));
        $base_dir = str_replace('\\', '/', $base_dir);
        if ($base_dir === '/') {
            $base_dir = '';
        }
        $base_url = "$protocol://$http_host$base_dir/";

        if ($store['logo_path']) {
            $store['logo_url'] = $base_url . $store['logo_path'];
        } else {
            $store['logo_url'] = null;
        }

        // Format product image paths to full URLs
        foreach ($products as &$prod) {
            if ($prod['image_path']) {
                $prod['image_url'] = $base_url . $prod['image_path'];
            } else {
                $prod['image_url'] = $base_url . 'assets/images/placeholder_product.svg';
            }
        }

        echo json_encode([
            "status" => true,
            "store" => $store,
            "products" => $products
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Store Not Found"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>