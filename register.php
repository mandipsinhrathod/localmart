<?php
require_once 'includes/db_config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['vendor_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_name = trim($_POST['shop_name'] ?? '');
    $owner_name = trim($_POST['owner_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $store_type = trim($_POST['store_type'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $shop_description = trim($_POST['shop_description'] ?? '');

    if (empty($shop_name) || empty($owner_name) || empty($email) || empty($password) || empty($address) || empty($store_type) || empty($contact_number)) {
        $error = 'Please fill out all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            // Check if email already registered
            $checkStmt = $conn->prepare("SELECT id FROM vendors WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = 'A shop with this email address is already registered.';
            } else {
                // Handle Logo Upload
                $logo_path = null;
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $logoTmpPath = $_FILES['logo']['tmp_name'];
                    $logoName = $_FILES['logo']['name'];
                    $logoExtension = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));

                    $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
                    if (in_array($logoExtension, $allowed)) {
                        $uploadDir = 'assets/images/uploads/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $newLogoName = 'logo_reg_' . time() . '_' . rand(1000, 9999) . '.' . $logoExtension;
                        $destPath = $uploadDir . $newLogoName;
                        if (move_uploaded_file($logoTmpPath, $destPath)) {
                            $logo_path = $destPath;
                        }
                    } else {
                        $error = 'Invalid logo format. Allowed: JPG, PNG, WEBP, SVG.';
                    }
                }

                if (empty($error)) {
                    // Generate a unique token for the QR code URL
                    $token = 'shop_' . bin2hex(random_bytes(6));
                    
                    // Securely hash the password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Insert vendor including logo_path
                    $insertStmt = $conn->prepare("INSERT INTO vendors (shop_name, owner_name, email, password, shop_description, address, store_type, contact_number, qr_code_token, logo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $insertStmt->execute([$shop_name, $owner_name, $email, $hashedPassword, $shop_description, $address, $store_type, $contact_number, $token, $logo_path]);
                    
                    $vendorId = $conn->lastInsertId();

                    // Set session variables to log the vendor in
                    $_SESSION['vendor_id'] = $vendorId;
                    $_SESSION['vendor_name'] = $shop_name;
                    $_SESSION['vendor_token'] = $token;

                    // Redirect to dashboard with success flag
                    header("Location: dashboard.php?registered=1");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$page_title = "Register Shop";
require_once 'includes/header.php';
?>

<!-- Custom CSS for registration enhancement -->
<style>
    .registration-wrapper {
        background: radial-gradient(circle at 10% 20%, rgba(2, 132, 199, 0.05) 0%, transparent 60%),
                    radial-gradient(circle at 90% 80%, rgba(184, 144, 71, 0.05) 0%, transparent 60%);
        min-height: calc(100vh - 100px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px 15px;
    }

    .glass-auth-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(90, 75, 50, 0.08);
        width: 100%;
        max-width: 720px;
        padding: 50px;
        transition: var(--transition-smooth);
    }

    .input-group-animated {
        position: relative;
        margin-bottom: 2px;
    }

    .input-group-animated input,
    .input-group-animated select,
    .input-group-animated textarea {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: #ffffff;
        padding: 14px 16px;
        transition: var(--transition-smooth);
        font-family: var(--font-body);
        font-size: 0.95rem;
    }

    .input-group-animated input:focus,
    .input-group-animated select:focus,
    .input-group-animated textarea:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15);
        outline: none;
    }

    /* Drag and Drop Zone styling */
    .logo-dropzone {
        border: 2px dashed var(--border-color);
        border-radius: 16px;
        background: rgba(250, 247, 242, 0.4);
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        position: relative;
        transition: var(--transition-smooth);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .logo-dropzone.dragover {
        border-color: var(--primary-blue);
        background: rgba(2, 132, 199, 0.06);
    }

    .logo-preview-container {
        display: none;
        position: relative;
        width: 100px;
        height: 100px;
        margin-bottom: 15px;
    }

    .logo-preview-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--accent-gold);
        box-shadow: var(--shadow-md);
    }

    .remove-logo-btn {
        position: absolute;
        top: -6px;
        right: -6px;
        background: var(--danger);
        color: #ffffff;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 0.75rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* Password Strength Meter */
    .strength-meter-container {
        margin-top: 8px;
        margin-bottom: 2px;
    }

    .strength-meter-bar {
        height: 5px;
        background-color: #E2E8F0;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .strength-meter-fill {
        height: 100%;
        width: 0;
        transition: width 0.3s ease-in-out, background-color 0.3s ease-in-out;
    }

    .strength-text {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .strength-text.weak { color: var(--danger); }
    .strength-text.medium { color: var(--accent-gold); }
    .strength-text.strong { color: var(--primary-green); }

    .glow-btn {
        background: linear-gradient(135deg, var(--primary-blue), #0270a8);
        color: #ffffff;
        padding: 16px;
        font-size: 1.05rem;
        font-weight: 700;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(2, 132, 199, 0.25);
        border: none;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .glow-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(2, 132, 199, 0.4);
    }

    .glow-btn:active {
        transform: translateY(1px);
    }

    /* Floating layout for icons */
    .form-group-with-icon {
        position: relative;
    }
    
    .form-group-with-icon input {
        padding-left: 45px !important;
    }

    .form-field-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: var(--text-muted);
        opacity: 0.7;
        pointer-events: none;
    }
</style>

<div class="registration-wrapper">
    <div class="glass-auth-card">
        <!-- Logo and Header -->
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="assets/images/logo.png" alt="LocalMart Logo" style="height: 70px; width: auto; object-fit: contain; border-radius: 4px;">
        </div>
        <div class="auth-header" style="text-align: center; margin-bottom: 40px;">
            <h2 style="font-family: var(--font-heading); font-size: 2.3rem; margin-bottom: 8px; font-weight: 700; color: var(--text-main);">Merchant Registration</h2>
            <p style="color: var(--text-muted); font-size: 1.05rem;">Establish your digital store, configure branding styles, and publish items today.</p>
        </div>

        <!-- Error Panel -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom: 30px; border-radius: 12px; box-shadow: var(--shadow-sm);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                    <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                </svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Registration -->
        <form action="register.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Grid for Shop Name and Owner Name -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Shop Name -->
                <div class="form-group input-group-animated">
                    <label class="form-label" for="shop_name">Store Name *</label>
                    <div class="form-group-with-icon">
                        <svg class="form-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" />
                        </svg>
                        <input type="text" id="shop_name" name="shop_name" class="form-control" placeholder="Greenland Grocery Store" required value="<?php echo isset($_POST['shop_name']) ? htmlspecialchars($_POST['shop_name']) : ''; ?>" style="width: 100%;">
                    </div>
                </div>

                <!-- Owner Name -->
                <div class="form-group input-group-animated">
                    <label class="form-label" for="owner_name">Owner Name *</label>
                    <div class="form-group-with-icon">
                        <svg class="form-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <input type="text" id="owner_name" name="owner_name" class="form-control" placeholder="Rakesh Sharma" required value="<?php echo isset($_POST['owner_name']) ? htmlspecialchars($_POST['owner_name']) : ''; ?>" style="width: 100%;">
                    </div>
                </div>
            </div>

            <!-- Grid for Email and Contact Number -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Email -->
                <div class="form-group input-group-animated">
                    <label class="form-label" for="email">Mail ID *</label>
                    <div class="form-group-with-icon">
                        <svg class="form-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.62a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <input type="email" id="email" name="email" class="form-control" placeholder="owner@store.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" style="width: 100%;">
                    </div>
                </div>

                <!-- Contact Number -->
                <div class="form-group input-group-animated">
                    <label class="form-label" for="contact_number">Contact Number *</label>
                    <div class="form-group-with-icon">
                        <svg class="form-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372a2.25 2.25 0 00-1.666-2.185l-4.868-1.21a2.25 2.25 0 00-2.53 1.25l-.83 1.66c-2.924-1.656-5.354-4.084-7.008-7.008l1.66-.83a2.25 2.25 0 001.25-2.53l-1.21-4.868a2.25 2.25 0 00-2.185-1.666H3.75A2.25 2.25 0 001.5 3.75v3a2.25 2.25 0 00.75 1.5z" />
                        </svg>
                        <input type="text" id="contact_number" name="contact_number" class="form-control" minlength="10" maxlength="10" placeholder="e.g. 9876543210" required value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>" style="width: 100%;">
                    </div>
                </div>
            </div>

            <!-- Grid for Password and Store Type Dropdown -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Password -->
                <div class="form-group input-group-animated">
                    <label class="form-label" for="password">Password *</label>
                    <div class="form-group-with-icon" style="margin-bottom: 0;">
                        <svg class="form-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required style="width: 100%;">
                    </div>
                    
                    <!-- Password Strength Meter -->
                    <div class="strength-meter-container">
                        <div class="strength-meter-bar">
                            <div class="strength-meter-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-text" id="strengthText">Enter your password</span>
                    </div>
                </div>

                <!-- Store Type Dropdown -->
                <div class="form-group input-group-animated">
                    <label class="form-label" for="store_type">Category of Store *</label>
                    <select id="store_type" name="store_type" class="form-control" required style="width: 100%; height: 50px; background: #ffffff;">
                        <option value="" disabled selected>Select category...</option>
                        <option value="Grocery" <?php echo (isset($_POST['store_type']) && $_POST['store_type'] === 'Grocery') ? 'selected' : ''; ?>>🛒 Grocery</option>
                        <option value="Bakery" <?php echo (isset($_POST['store_type']) && $_POST['store_type'] === 'Bakery') ? 'selected' : ''; ?>>🍰 Bakery</option>
                        <option value="Cafe" <?php echo (isset($_POST['store_type']) && $_POST['store_type'] === 'Cafe') ? 'selected' : ''; ?>>☕ Cafe &amp; Restaurant</option>
                        <option value="Pharmacy" <?php echo (isset($_POST['store_type']) && $_POST['store_type'] === 'Pharmacy') ? 'selected' : ''; ?>>💊 Pharmacy</option>
                        <option value="Boutique" <?php echo (isset($_POST['store_type']) && $_POST['store_type'] === 'Boutique') ? 'selected' : ''; ?>>👕 Boutique &amp; Clothing</option>
                        <option value="Electronics" <?php echo (isset($_POST['store_type']) && $_POST['store_type'] === 'Electronics') ? 'selected' : ''; ?>>🔌 Electronics &amp; Mobiles</option>
                        <option value="Other" <?php echo (isset($_POST['store_type']) && $_POST['store_type'] === 'Other') ? 'selected' : ''; ?>>✨ Other Services</option>
                    </select>
                </div>
            </div>

            <!-- Shop Logo Upload Drag-n-Drop Area -->
            <div class="form-group input-group-animated">
                <label class="form-label" style="margin-bottom: 8px;">Store Logo (Optional)</label>
                <div class="logo-dropzone" id="logoDropzone">
                    <!-- Image Preview element -->
                    <div class="logo-preview-container" id="logoPreviewContainer">
                        <img src="" alt="Logo Preview" class="logo-preview-circle" id="logoPreview">
                        <div class="remove-logo-btn" id="removeLogoBtn" title="Remove image">×</div>
                    </div>
                    
                    <div id="dropzoneText">
                        <div style="font-size: 2.5rem; margin-bottom: 12px; transition: transform 0.3s;" id="dropzoneIcon">📤</div>
                        <p style="font-weight: 600; font-size: 0.95rem; margin-bottom: 6px; color: var(--text-main);">
                            Drag and drop your logo here or <span style="color: var(--primary-blue); text-decoration: underline;">browse</span>
                        </p>
                        <p style="color: var(--text-muted); font-size: 0.8rem;">Supports: JPG, JPEG, PNG, WEBP, SVG (Max 2MB)</p>
                    </div>
                    <input type="file" id="logo" name="logo" accept="image/*" style="display: none;">
                </div>
            </div>

            <!-- Address -->
            <div class="form-group input-group-animated">
                <label class="form-label" for="address">Store Address *</label>
                <textarea id="address" name="address" class="form-control" placeholder="Enter physical street address, city, and zip code" required style="width: 100%; min-height: 80px; resize: vertical;"></textarea>
            </div>

            <!-- Shop Description -->
            <div class="form-group input-group-animated">
                <label class="form-label" for="shop_description">Store Description</label>
                <textarea id="shop_description" name="shop_description" class="form-control" placeholder="Tell customers about your store, specialty items, opening hours, etc..." style="width: 100%; min-height: 80px; resize: vertical;"></textarea>
            </div>

            <button type="submit" class="glow-btn">Create Merchant Account</button>
        </form>

        <div class="form-footer" style="text-align: center; margin-top: 35px; color: var(--text-muted); font-size: 0.95rem;">
            Already have a store registered? <a href="login.php" style="color: var(--primary-blue); font-weight: 700; text-decoration: underline;">Log in here</a>
        </div>
    </div>
</div>

<script>
    // -------------------------------------------------------------
    // LOGO DRAG-AND-DROP AND REAL-TIME PREVIEW
    // -------------------------------------------------------------
    const dropzone = document.getElementById('logoDropzone');
    const fileInput = document.getElementById('logo');
    const previewContainer = document.getElementById('logoPreviewContainer');
    const previewImage = document.getElementById('logoPreview');
    const removeBtn = document.getElementById('removeLogoBtn');
    const dropzoneText = document.getElementById('dropzoneText');
    const dropzoneIcon = document.getElementById('dropzoneIcon');

    // Click dropzone to trigger input
    dropzone.addEventListener('click', (e) => {
        // Prevent trigger if clicking on the remove button
        if (e.target !== removeBtn) {
            fileInput.click();
        }
    });

    // File selected via dialog
    fileInput.addEventListener('change', () => {
        handleFiles(fileInput.files);
    });

    // Drag-over styling
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
        dropzoneIcon.style.transform = 'scale(1.2) translateY(-5px)';
    });

    // Drag-leave styling
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
        dropzoneIcon.style.transform = 'none';
    });

    // Drop handler
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        dropzoneIcon.style.transform = 'none';
        
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length) {
            fileInput.files = files; // Sync files to input
            handleFiles(files);
        }
    });

    function handleFiles(files) {
        if (files.length === 0) return;
        const file = files[0];
        
        // Validate type
        if (!file.type.match('image.*')) {
            alert('Please select an image file (JPG, PNG, WEBP, SVG).');
            return;
        }

        // Validate size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds the 2MB limit.');
            return;
        }

        // Show Image Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
            dropzoneText.style.display = 'none';
            dropzone.style.padding = '15px';
        }
        reader.readAsDataURL(file);
    }

    // Remove file handler
    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Avoid triggering file input dialog
        fileInput.value = ''; // Reset input
        previewContainer.style.display = 'none';
        previewImage.src = '';
        dropzoneText.style.display = 'block';
        dropzone.style.padding = '30px 20px';
    });

    // -------------------------------------------------------------
    // DYNAMIC PASSWORD STRENGTH METER
    // -------------------------------------------------------------
    const passwordInput = document.getElementById('password');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    passwordInput.addEventListener('input', () => {
        const val = passwordInput.value;
        let score = 0;

        if (val.length === 0) {
            strengthFill.style.width = '0%';
            strengthText.textContent = 'Enter your password';
            strengthText.className = 'strength-text';
            return;
        }

        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        let percentage = (score / 5) * 100;
        strengthFill.style.width = percentage + '%';

        if (score <= 2) {
            strengthFill.style.backgroundColor = '#DC2626'; // Red
            strengthText.textContent = 'Weak Password ⚠️';
            strengthText.className = 'strength-text weak';
        } else if (score <= 4) {
            strengthFill.style.backgroundColor = '#B89047'; // Gold/Yellow
            strengthText.textContent = 'Moderate Password 👍';
            strengthText.className = 'strength-text medium';
        } else {
            strengthFill.style.backgroundColor = '#16A34A'; // Green
            strengthText.textContent = 'Strong Password! Perfect ✨';
            strengthText.className = 'strength-text strong';
        }
    });
</script>

<?php
require_once 'includes/footer.php';
?>
