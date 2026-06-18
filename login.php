<?php
require_once 'includes/db_config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['vendor_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, shop_name, password, qr_code_token FROM vendors WHERE email = ?");
            $stmt->execute([$email]);
            $vendor = $stmt->fetch();

            if ($vendor && password_verify($password, $vendor['password'])) {
                // Set session
                $_SESSION['vendor_id'] = $vendor['id'];
                $_SESSION['vendor_name'] = $vendor['shop_name'];
                $_SESSION['vendor_token'] = $vendor['qr_code_token'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid email address or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed: ' . $e->getMessage();
        }
    }
}

$page_title = "Merchant Login";
require_once 'includes/header.php';
?>

<div class="container auth-page">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="assets/images/logo.png" alt="LocalMart Logo" style="height: 60px; width: auto; object-fit: contain; border-radius: 4px;">
        </div>
        <div class="auth-header">
            <h2>Merchant Portal</h2>
            <p>Access your vendor admin panel to manage items and retrieve your store QR code.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="owner@store.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Your account password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 14px;">Sign In to Dashboard</button>
        </form>

        <div class="form-footer">
            New vendor? <a href="register.php">Register your store here</a>
            <div style="margin-top: 15px; border-top: 1px solid var(--border-color-light); padding-top: 15px; font-size: 0.85rem;">
                Are you a platform admin? <a href="admin.php" style="color: var(--text-muted); font-weight: 500; text-decoration: underline;">Developer Login</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
