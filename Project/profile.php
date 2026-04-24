<?php
require_once 'config/db.php';
requireLogin();

$page_title = 'My Profile';
$user_id    = $_SESSION['user_id'];
$success    = '';
$error      = '';

// Fetch fresh user data
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Task stats
$stats_q = mysqli_query($conn, "SELECT status, COUNT(*) as cnt FROM tasks WHERE user_id = $user_id GROUP BY status");
$stats   = ['todo' => 0, 'inprogress' => 0, 'done' => 0];
while ($r = mysqli_fetch_assoc($stats_q)) $stats[$r['status']] = $r['cnt'];
$total = array_sum($stats);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_profile') {
        $name  = clean($conn, $_POST['name'] ?? '');
        $color = clean($conn, $_POST['avatar_color'] ?? '#6366f1');
        $valid_colors = ['#6366f1','#f59e0b','#10b981','#ef4444','#8b5cf6','#06b6d4','#ec4899','#f97316'];
        if (!in_array($color, $valid_colors)) $color = '#6366f1';

        if (empty($name)) { $error = 'Name cannot be empty.'; }
        else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, avatar_color=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $color, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user_name']  = $name;
                $_SESSION['user_color'] = $color;
                $user['name']  = $name;
                $user['avatar_color'] = $color;
                $success = 'Profile updated successfully!';
            } else { $error = 'Update failed.'; }
        }
    }

    if ($_POST['action'] === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt   = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'si', $hashed, $user_id);
            mysqli_stmt_execute($stmt);
            $success = 'Password changed successfully!';
        }
    }
}

$avatar_colors = ['#6366f1','#f59e0b','#10b981','#ef4444','#8b5cf6','#06b6d4','#ec4899','#f97316'];
include 'includes/header.php';
?>

<!-- NAVBAR -->
<nav class="navbar-taskflow">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebar-toggle" class="btn p-0 d-lg-none" style="color:var(--text-muted);font-size:1.3rem;background:none;border:none;">
                    <i class="bi bi-list"></i>
                </button>
                <a href="index.php" class="navbar-brand">Task<span>Flow</span></a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="theme-toggle-wrap">
                    <span class="theme-icon">🌙</span>
                    <button class="theme-toggle" title="Toggle theme"></button>
                </div>
                <a href="dashboard.php" class="btn-outline-tf" style="padding:0.4rem 1rem;font-size:0.85rem;">
                    <i class="bi bi-kanban"></i> Board
                </a>
                <a href="logout.php" class="btn-outline-tf" style="padding:0.4rem 1rem;font-size:0.85rem;">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="dashboard-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="user-avatar" style="background:<?= $user['avatar_color'] ?>">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div>
                <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="user-role">Free Plan</div>
            </div>
        </div>
        <div class="sidebar-section-label">Workspace</div>
        <a class="sidebar-link" href="dashboard.php"><i class="bi bi-kanban"></i> My Board</a>
        <a class="sidebar-link active" href="profile.php"><i class="bi bi-person-circle"></i> My Profile</a>
        <?php if (!empty($_SESSION['is_admin'])): ?>
        <a class="sidebar-link" href="admin.php"><i class="bi bi-shield-check" style="color:#f59e0b"></i> Admin Panel</a>
        <?php endif; ?>
        <div class="sidebar-divider"></div>
        <a class="sidebar-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="dashboard-topbar">
            <div>
                <h1 class="page-heading">My <span>Profile</span></h1>
                <p style="color:var(--text-muted);font-size:0.9rem;margin:0;">Manage your account settings</p>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="alert-tf success" style="display:block; margin-bottom:1rem;"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert-tf error" style="display:block; margin-bottom:1rem;"><?= $error ?></div>
        <?php endif; ?>

        <div class="profile-wrap">

            <!-- Profile Banner + Avatar -->
            <div class="profile-card" data-aos="fade-up">
                <div class="profile-banner">
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar" style="background:<?= $user['avatar_color'] ?>" id="profile-avatar-preview">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            <div class="profile-avatar-overlay"><i class="bi bi-palette"></i></div>
                        </div>
                    </div>
                </div>
                <div class="profile-body">
                    <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
                    <div class="profile-email"><i class="bi bi-envelope" style="margin-right:5px;"></i><?= htmlspecialchars($user['email']) ?></div>
                    <div class="profile-stats">
                        <div class="profile-stat">
                            <div class="profile-stat-val"><?= $total ?></div>
                            <div class="profile-stat-label">Total Tasks</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-val" style="color:#34d399"><?= $stats['done'] ?></div>
                            <div class="profile-stat-label">Completed</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-val" style="color:#fbbf24"><?= $stats['inprogress'] ?></div>
                            <div class="profile-stat-label">In Progress</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-val" style="color:#94a3b8"><?= $stats['todo'] ?></div>
                            <div class="profile-stat-label">To Do</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-val"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                            <div class="profile-stat-label">Member Since</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile -->
            <div class="profile-card" data-aos="fade-up" data-aos-delay="80">
                <div style="padding:1.75rem;">
                    <div class="section-card-title"><i class="bi bi-pencil" style="margin-right:6px;color:var(--primary-light)"></i>Edit Profile</div>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="avatar_color" id="selected-color" value="<?= $user['avatar_color'] ?>">

                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" name="name" class="form-control-tf" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" class="form-control-tf" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.5;cursor:not-allowed;">
                            </div>
                            <small style="color:var(--text-muted);font-size:0.78rem;">Email cannot be changed.</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Avatar Color</label>
                            <div class="color-picker-row">
                                <?php foreach ($avatar_colors as $color): ?>
                                <div class="color-swatch <?= $color === $user['avatar_color'] ? 'selected' : '' ?>"
                                     style="background:<?= $color ?>"
                                     data-color="<?= $color ?>"
                                     onclick="selectColor('<?= $color ?>')">
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary-tf mt-2">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="profile-card" data-aos="fade-up" data-aos-delay="160">
                <div style="padding:1.75rem;">
                    <div class="section-card-title"><i class="bi bi-lock" style="margin-right:6px;color:var(--primary-light)"></i>Change Password</div>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <div class="input-icon-wrapper">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" name="current_password" class="form-control-tf" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-key input-icon"></i>
                                        <input type="password" name="new_password" class="form-control-tf" placeholder="Min. 6 chars" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm New</label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-key-fill input-icon"></i>
                                        <input type="password" name="confirm_password" class="form-control-tf" placeholder="Repeat password" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary-tf mt-2">
                            <i class="bi bi-shield-lock"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="profile-card" data-aos="fade-up" data-aos-delay="200" style="border-color:rgba(239,68,68,0.25);">
                <div style="padding:1.75rem;">
                    <div class="section-card-title" style="border-color:rgba(239,68,68,0.2);">
                        <i class="bi bi-exclamation-triangle" style="margin-right:6px;color:#f87171"></i>Danger Zone
                    </div>
                    <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:1rem;">Deleting your account will permanently remove all your tasks and data. This cannot be undone.</p>
                    <button class="btn-outline-tf" style="border-color:rgba(239,68,68,0.4);color:#f87171;" onclick="if(confirm('Are you absolutely sure? All data will be deleted.')) window.location='actions/delete_account.php'">
                        <i class="bi bi-trash3"></i> Delete Account
                    </button>
                </div>
            </div>

        </div><!-- /profile-wrap -->
    </main>
</div>

<script>
function selectColor(color) {
    $('#selected-color').val(color);
    $('.color-swatch').removeClass('selected');
    $(`.color-swatch[data-color="${color}"]`).addClass('selected');
    $('#profile-avatar-preview').css('background', color);
}
</script>

<?php include 'includes/footer.php'; ?>
