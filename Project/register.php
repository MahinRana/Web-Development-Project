<?php
require_once 'config/db.php';
redirectIfLoggedIn();

$page_title = 'Create Account';
$error = '';
$colors = ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($conn, $_POST['name'] ?? '');
    $email = clean($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email exists
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = 'Email already registered. Please log in.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $color = $colors[array_rand($colors)];
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, avatar_color) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hashed, $color);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_color'] = $color;
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">Task<span>Flow</span></div>
        <p class="auth-subtitle">Create your account and start organizing.</p>

        <?php if ($error): ?>
        <div class="alert-tf error" style="display:block;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" name="name" class="form-control-tf" placeholder="Your name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control-tf" placeholder="you@example.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" class="form-control-tf" placeholder="Min. 6 characters" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input type="password" name="confirm_password" class="form-control-tf" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" class="btn-primary-tf w-100 justify-content-center mt-2">
                Create Account <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <p class="text-center mt-3" style="font-size:0.85rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="color: var(--primary-light);">Sign in</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
