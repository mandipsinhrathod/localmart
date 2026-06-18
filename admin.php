<?php
require_once 'includes/db_config.php';

// Fetch developer analytics
try {
    // 1. Total Registered Stores
    $stmtVendors = $conn->query("SELECT COUNT(*) FROM vendors");
    $totalVendors = $stmtVendors->fetchColumn();

    // 2. Total Products Listed
    $stmtItems = $conn->query("SELECT COUNT(*) FROM items");
    $totalItems = $stmtItems->fetchColumn();

    // 3. Full List of Registered Stores with Item Counts
    $stmtStoreList = $conn->query("
        SELECT 
            v.id, 
            v.shop_name, 
            v.email, 
            v.qr_code_token, 
            v.created_at, 
            COUNT(i.id) AS product_count 
        FROM vendors v 
        LEFT JOIN items i ON v.id = i.vendor_id 
        GROUP BY v.id 
        ORDER BY v.created_at DESC
    ");
    $registeredStores = $stmtStoreList->fetchAll();

} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

$page_title = "Developer Dashboard";
require_once 'includes/header.php';
?>

<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Dashboard Header -->
    <div class="dashboard-panel-header" style="margin-bottom: 35px;">
        <div>
            <span style="font-weight: 600; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem;">Platform Operations</span>
            <h1 style="font-family: var(--font-heading); margin-top: 5px;">Developer Admin Panel</h1>
        </div>
        <div>
            <a href="index.php" class="btn btn-secondary">Back to Homepage</a>
        </div>
    </div>

    <!-- Platform Stats Cards -->
    <div class="admin-stats-row">
        <!-- Card 1: Registered Stores -->
        <div class="stat-card">
            <div class="stat-icon">🏪</div>
            <div>
                <div class="stat-number"><?php echo $totalVendors; ?></div>
                <div class="stat-label">Registered Stores</div>
            </div>
        </div>

        <!-- Card 2: Cataloged Products -->
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div>
                <div class="stat-number"><?php echo $totalItems; ?></div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>

        <!-- Card 3: Platform Ratio -->
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div>
                <div class="stat-number">
                    <?php 
                    echo $totalVendors > 0 ? number_format($totalItems / $totalVendors, 1) : '0';
                    ?>
                </div>
                <div class="stat-label">Avg. Items / Store</div>
            </div>
        </div>
    </div>

    <!-- Registered Stores Master Table -->
    <div class="items-list-container">
        <h3 style="margin-bottom: 20px; font-family: var(--font-heading);">Global Registered Stores</h3>

        <?php if (empty($registeredStores)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏪</div>
                <h3>No Stores Registered</h3>
                <p>There are currently no stores registered on the LocalMart platform. Share your vendor signup page to bring local businesses online.</p>
                <a href="register.php" class="btn btn-primary" style="margin-top: 15px;">Register A Test Shop</a>
            </div>
        <?php else: ?>
            <div class="custom-table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Store ID</th>
                            <th>Store Name</th>
                            <th>Owner Email</th>
                            <th>QR Token</th>
                            <th>Date Joined</th>
                            <th>Total Products</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registeredStores as $store): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--text-muted);">#<?php echo $store['id']; ?></td>
                                <td class="item-name-cell"><?php echo htmlspecialchars($store['shop_name']); ?></td>
                                <td><?php echo htmlspecialchars($store['email']); ?></td>
                                <td><code><?php echo htmlspecialchars($store['qr_code_token']); ?></code></td>
                                <td style="font-size: 0.9rem; color: var(--text-muted);">
                                    <?php echo date('M d, Y', strtotime($store['created_at'])); ?>
                                </td>
                                <td style="font-weight: 600; text-align: center;">
                                    <?php echo $store['product_count']; ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="store.php?code=<?php echo urlencode($store['qr_code_token']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 6px 12px; font-size: 0.8rem; border-color: var(--accent-gold); color: var(--accent-gold);">
                                        Visit Store
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
