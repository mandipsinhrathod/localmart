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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>
                    <a href="forgot_password.php" style="font-size: 0.85rem; color: var(--accent-gold); font-weight: 500; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-gold-hover)'" onmouseout="this.style.color='var(--accent-gold)'">Forgot Password?</a>
                </div>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Your account password" required style="padding-right: 45px; width: 100%;">
                    <button type="button" id="toggle-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; padding: 4px; transition: color 0.2s;" title="Toggle Password Visibility">
                        <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('toggle-password');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            if (isPassword) {
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
