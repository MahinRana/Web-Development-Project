<?php
require_once 'config/db.php';
redirectIfLoggedIn();

$page_title = 'Login';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'login') {
        $email = clean($conn, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, avatar_color, is_admin FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_color'] = $user['avatar_color'];
                $_SESSION['is_admin']   = $user['is_admin'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">Task<span>Flow</span></div>
        <p class="auth-subtitle">Welcome back! Sign in to your board.</p>

        <?php if ($error): ?>
        <div class="alert-tf error" style="display:block;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control-tf" placeholder="you@example.com" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" class="form-control-tf" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-primary-tf w-100 justify-content-center mt-2">
                Sign In <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <p class="text-center mt-3" style="font-size:0.85rem; color: var(--text-muted);">
            Don't have an account? <a href="register.php" style="color: var(--primary-light);">Sign up free</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>