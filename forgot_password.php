<?php
require_once 'includes/db_config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['vendor_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';
$step = 1; // 1: Verify Account, 2: Reset Password

// Step 1: Verify Email and Contact Number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_verify'])) {
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if (empty($email) || empty($contact)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Check if vendor exists with this email and contact number
            $stmt = $conn->prepare("SELECT id FROM vendors WHERE email = ? AND contact_number = ?");
            $stmt->execute([$email, $contact]);
            $vendor = $stmt->fetch();

            if ($vendor) {
                $_SESSION['reset_vendor_id'] = $vendor['id'];
                $step = 2;
            } else {
                $error = 'No matching account found with that email and contact number.';
            }
        } catch (PDOException $e) {
            $error = 'Error checking account: ' . $e->getMessage();
        }
    }
}

// Step 2: Save New Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_reset'])) {
    if (!isset($_SESSION['reset_vendor_id'])) {
        header("Location: forgot_password.php");
        exit();
    }

    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (empty($new_pass) || empty($confirm_pass)) {
        $error = 'Please fill in all fields.';
        $step = 2;
    } elseif ($new_pass !== $confirm_pass) {
        $error = 'Passwords do not match.';
        $step = 2;
    } elseif (strlen($new_pass) < 6) {
        $error = 'Password must be at least 6 characters long.';
        $step = 2;
    } else {
        try {
            $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE vendors SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['reset_vendor_id']]);
            
            // Clean up session variable
            unset($_SESSION['reset_vendor_id']);
            
            $success = 'Password reset successfully! You can now log in with your new password.';
            $step = 3; // Success state
        } catch (PDOException $e) {
            $error = 'Failed to reset password: ' . $e->getMessage();
            $step = 2;
        }
    }
}

// If they are on step 2 but session is missing (e.g. page refreshed)
if ($step === 2 && !isset($_SESSION['reset_vendor_id'])) {
    $step = 1;
}

$page_title = "Forgot Password";
require_once 'includes/header.php';
?>

<div class="container auth-page">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="assets/images/logo.png" alt="LocalMart Logo" style="height: 60px; width: auto; object-fit: contain; border-radius: 4px;">
        </div>

        <div class="auth-header">
            <h2>Reset Password</h2>
            <?php if ($step === 1): ?>
                <p>Verify your vendor account details to reset your password.</p>
            <?php elseif ($step === 2): ?>
                <p>Create a secure new password for your account.</p>
            <?php else: ?>
                <p>Your password has been updated.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <span>⚠️</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; background-color: rgba(22, 163, 74, 0.08); color: var(--primary-green); border-color: rgba(22, 163, 74, 0.2);">
                <span>✅</span>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <!-- STEP 1: VERIFY -->
            <form action="forgot_password.php" method="POST">
                <input type="hidden" name="action_verify" value="1">
                
                <div class="form-group">
                    <label class="form-label" for="email">Registered Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="owner@store.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="contact">Registered Contact Number</label>
                    <input type="text" id="contact" name="contact" class="form-control" placeholder="e.g. 9876543210" required value="<?php echo isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px; padding: 14px;">Verify Account</button>
            </form>
        
        <?php elseif ($step === 2): ?>
            <!-- STEP 2: RESET -->
            <form action="forgot_password.php" method="POST">
                <input type="hidden" name="action_reset" value="1">

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="At least 6 characters" required style="padding-right: 45px; width: 100%;">
                        <button type="button" id="toggle-new-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; padding: 4px;" title="Toggle Password Visibility">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px; padding: 14px;">Update Password</button>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', () => {
                const passwordInput = document.getElementById('new_password');
                const toggleBtn = document.getElementById('toggle-new-password');
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

        <?php else: ?>
            <!-- STEP 3: SUCCESS -->
            <a href="login.php" class="btn btn-primary" style="width: 100%; display: block; text-align: center; margin-top: 15px; padding: 14px; text-decoration: none;">Proceed to Login</a>
        <?php endif; ?>

        <div class="form-footer" style="margin-top: 25px;">
            Remember your password? <a href="login.php">Back to Login</a>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
