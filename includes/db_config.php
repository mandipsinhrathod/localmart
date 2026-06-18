<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ================================
// LOCALMART DATABASE CONFIGURATION
// AUTO CREATES DATABASE & TABLES
// ================================

$host = getenv('DB_HOST') ?: "localhost";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";
$dbname = getenv('DB_NAME') ?: "localmart";

try {
    // Connect to MySQL Server (without dbname to create it if missing)
    $conn = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create Database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Re-connect with dbname specified
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // =================================
    // CREATE VENDORS TABLE
    // =================================
    $vendorsTable = "
    CREATE TABLE IF NOT EXISTS vendors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shop_name VARCHAR(100) NOT NULL,
        owner_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        shop_description TEXT,
        address TEXT NOT NULL,
        store_type VARCHAR(100) NOT NULL,
        contact_number VARCHAR(20) NOT NULL,
        qr_code_token VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->exec($vendorsTable);

    // =================================
    // CREATE ITEMS TABLE
    // =================================
    $itemsTable = "
    CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->exec($itemsTable);

    // =================================
    // CREATE CUSTOMERS TABLE
    // =================================
    $customersTable = "
    CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->exec($customersTable);

    // =================================
    // UPDATE VENDORS TABLE WITH BRANDING COLUMNS
    // =================================
    $columnsToAdd = [
        'logo_path' => "VARCHAR(255) DEFAULT NULL",
        'theme_color' => "VARCHAR(7) DEFAULT '#0284C7'",
        'theme_bg' => "VARCHAR(20) DEFAULT 'cozy'",
        'font_style' => "VARCHAR(50) DEFAULT 'outfit'"
    ];

    foreach ($columnsToAdd as $colName => $colType) {
        try {
            $conn->query("SELECT `$colName` FROM vendors LIMIT 1");
        } catch (PDOException $e) {
            $conn->exec("ALTER TABLE vendors ADD COLUMN `$colName` $colType");
        }
    }

    // =================================
    // UPDATE ITEMS TABLE WITH PRODUCT SPECIFICATION COLUMNS
    // =================================
    $itemColumnsToAdd = [
        'availability' => "VARCHAR(50) DEFAULT 'In Stock'",
        'weight_qty' => "VARCHAR(50) DEFAULT NULL",
        'product_type' => "VARCHAR(50) DEFAULT NULL",
        'shelf_life' => "VARCHAR(50) DEFAULT NULL",
        'grade' => "VARCHAR(50) DEFAULT 'No Grade'",
        'price_unit' => "VARCHAR(50) DEFAULT 'kg'"
    ];

    foreach ($itemColumnsToAdd as $colName => $colType) {
        try {
            $conn->query("SELECT `$colName` FROM items LIMIT 1");
        } catch (PDOException $e) {
            $conn->exec("ALTER TABLE items ADD COLUMN `$colName` $colType");
        }
    }


} catch (PDOException $e) {
    die("Database Connection / Setup Failed: " . $e->getMessage());
}

// Optional Timezone
date_default_timezone_set('Asia/Kolkata');
?>