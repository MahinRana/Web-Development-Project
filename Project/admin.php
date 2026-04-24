<?php
require_once 'config/db.php';
requireLogin();

// Admin only
if (empty($_SESSION['is_admin'])) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Admin Panel';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['delete_user'])) {
        $uid = intval($_POST['delete_user']);
        if ($uid !== $_SESSION['user_id']) { // Can't delete yourself
            mysqli_query($conn, "DELETE FROM tasks WHERE user_id = $uid");
            mysqli_query($conn, "DELETE FROM users WHERE id = $uid");
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete yourself']);
        }
        exit();
    }

    if (isset($_POST['toggle_admin'])) {
        $uid      = intval($_POST['toggle_admin']);
        $new_val  = intval($_POST['new_val']);
        mysqli_query($conn, "UPDATE users SET is_admin=$new_val WHERE id=$uid");
        echo json_encode(['status' => 'success']);
        exit();
    }

    if (isset($_POST['delete_task'])) {
        $tid = intval($_POST['delete_task']);
        mysqli_query($conn, "DELETE FROM tasks WHERE id = $tid");
        echo json_encode(['status' => 'success']);
        exit();
    }
}

// Fetch all users with task counts
$users_q = mysqli_query($conn, "
    SELECT u.*, 
        COUNT(t.id) as task_count,
        SUM(t.status='done') as done_count,
        SUM(t.status='inprogress') as inprogress_count,
        SUM(t.status='todo') as todo_count
    FROM users u
    LEFT JOIN tasks t ON t.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = [];
while ($r = mysqli_fetch_assoc($users_q)) $users[] = $r;

// Fetch all tasks with user info
$tasks_q = mysqli_query($conn, "
    SELECT t.*, u.name as user_name, u.avatar_color
    FROM tasks t
    JOIN users u ON u.id = t.user_id
    ORDER BY t.created_at DESC
    LIMIT 100
");
$all_tasks = [];
while ($r = mysqli_fetch_assoc($tasks_q)) $all_tasks[] = $r;

// Site-wide stats
$total_users = count($users);
$total_tasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks"))['c'];
$done_tasks  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE status='done'"))['c'];
$high_tasks  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE priority='high'"))['c'];

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
                <a href="index.php" class="navbar-brand">Task<span>Flow</span>
                    <span class="admin-badge">Admin</span>
                </a>
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
            <div class="user-avatar" style="background:<?= $_SESSION['user_color'] ?>">
                <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
            </div>
            <div>
                <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <div class="sidebar-section-label">Workspace</div>
        <a class="sidebar-link" href="dashboard.php"><i class="bi bi-kanban"></i> My Board</a>
        <a class="sidebar-link" href="profile.php"><i class="bi bi-person-circle"></i> My Profile</a>
        <a class="sidebar-link active" href="admin.php"><i class="bi bi-shield-check" style="color:#f59e0b"></i> Admin Panel</a>
        <div class="sidebar-divider"></div>
        <a class="sidebar-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="dashboard-topbar">
            <div>
                <h1 class="page-heading">Admin <span>Panel</span></h1>
                <p style="color:var(--text-muted);font-size:0.9rem;margin:0;">Full system overview & management</p>
            </div>
        </div>

        <!-- SITE STATS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="0">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(99,102,241,0.15);color:#818cf8;"><i class="bi bi-people"></i></div>
                    <div><div class="stat-value"><?= $total_users ?></div><div class="stat-label">Total Users</div></div>
                </div>
            </div>
            <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(245,158,11,0.15);color:#fbbf24;"><i class="bi bi-list-task"></i></div>
                    <div><div class="stat-value"><?= $total_tasks ?></div><div class="stat-label">Total Tasks</div></div>
                </div>
            </div>
            <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="160">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(16,185,129,0.15);color:#34d399;"><i class="bi bi-check-circle"></i></div>
                    <div><div class="stat-value"><?= $done_tasks ?></div><div class="stat-label">Completed</div></div>
                </div>
            </div>
            <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="240">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(239,68,68,0.15);color:#f87171;"><i class="bi bi-exclamation-circle"></i></div>
                    <div><div class="stat-value"><?= $high_tasks ?></div><div class="stat-label">High Priority</div></div>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <div class="admin-tabs" data-aos="fade-up">
            <button class="admin-tab-btn active" onclick="showAdminTab('users', this)">
                <i class="bi bi-people"></i> Users (<?= $total_users ?>)
            </button>
            <button class="admin-tab-btn" onclick="showAdminTab('tasks', this)">
                <i class="bi bi-list-task"></i> All Tasks (<?= $total_tasks ?>)
            </button>
        </div>

        <!-- USERS TABLE -->
        <div id="admin-tab-users" data-aos="fade-up">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Tasks</th>
                            <th>Progress</th>
                            <th>Joined</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr id="user-row-<?= $u['id'] ?>">
                        <td>
                            <div class="admin-user-cell">
                                <div class="admin-mini-avatar" style="background:<?= $u['avatar_color'] ?>">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($u['name']) ?>
                                    <?php if ($u['is_admin']): ?>
                                    <span class="admin-badge">Admin</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </td>
                        <td style="color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['task_count'] ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <span class="task-pill pill-todo"><?= (int)$u['todo_count'] ?> todo</span>
                                <span class="task-pill pill-inprogress"><?= (int)$u['inprogress_count'] ?> active</span>
                                <span class="task-pill pill-done"><?= (int)$u['done_count'] ?> done</span>
                            </div>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.82rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <button class="task-btn" onclick="toggleAdmin(<?= $u['id'] ?>, <?= $u['is_admin'] ?>)"
                                style="background:rgba(245,158,11,0.1);color:#fbbf24;width:auto;padding:3px 10px;font-size:0.75rem;"
                                title="<?= $u['is_admin'] ? 'Remove admin' : 'Make admin' ?>">
                                <i class="bi bi-shield<?= $u['is_admin'] ? '-fill' : '' ?>"></i>
                                <?= $u['is_admin'] ? 'Admin' : 'User' ?>
                            </button>
                            <?php else: ?>
                            <span style="font-size:0.8rem;color:var(--text-muted);">You</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <button class="task-btn task-btn-delete" onclick="deleteUser(<?= $u['id'] ?>)" title="Delete user">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TASKS TABLE -->
        <div id="admin-tab-tasks" style="display:none;" data-aos="fade-up">
            <!-- Search for admin tasks -->
            <div class="search-bar-wrap mb-3" style="max-width:380px;">
                <i class="bi bi-search"></i>
                <input type="text" id="admin-task-search" placeholder="Search tasks...">
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table" id="admin-tasks-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>User</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($all_tasks as $t): ?>
                    <tr id="task-row-<?= $t['id'] ?>" class="admin-task-row">
                        <td>
                            <div style="font-weight:600;font-size:0.88rem;"><?= htmlspecialchars($t['title']) ?></div>
                            <?php if ($t['description']): ?>
                            <div style="font-size:0.77rem;color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars(substr($t['description'],0,60)) ?>...</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="admin-user-cell">
                                <div class="admin-mini-avatar" style="background:<?= $t['avatar_color'] ?>;width:24px;height:24px;font-size:0.65rem;border-radius:6px;">
                                    <?= strtoupper(substr($t['user_name'],0,1)) ?>
                                </div>
                                <span style="font-size:0.83rem;"><?= htmlspecialchars($t['user_name']) ?></span>
                            </div>
                        </td>
                        <td><span class="priority-badge priority-<?= $t['priority'] ?>"><?= ucfirst($t['priority']) ?></span></td>
                        <td>
                            <?php
                            $pill_map = ['todo'=>'pill-todo','inprogress'=>'pill-inprogress','done'=>'pill-done'];
                            $label_map = ['todo'=>'To Do','inprogress'=>'In Progress','done'=>'Done'];
                            ?>
                            <span class="task-pill <?= $pill_map[$t['status']] ?>"><?= $label_map[$t['status']] ?></span>
                        </td>
                        <td style="font-size:0.82rem;color:var(--text-muted);">
                            <?= $t['due_date'] ? date('M j, Y', strtotime($t['due_date'])) : '—' ?>
                        </td>
                        <td style="font-size:0.82rem;color:var(--text-muted);"><?= date('M j', strtotime($t['created_at'])) ?></td>
                        <td>
                            <button class="task-btn task-btn-delete" onclick="adminDeleteTask(<?= $t['id'] ?>)" title="Delete task">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script>
function showAdminTab(tab, btn) {
    $('#admin-tab-users, #admin-tab-tasks').hide();
    $('#admin-tab-' + tab).fadeIn(250);
    $('.admin-tab-btn').removeClass('active');
    $(btn).addClass('active');
}

function deleteUser(uid) {
    if (!confirm('Delete this user and ALL their tasks? This cannot be undone.')) return;
    $.post('admin.php', { delete_user: uid }, function(res) {
        if (res.status === 'success') {
            $('#user-row-' + uid).fadeOut(300, function() { $(this).remove(); });
            showToast('User deleted', 'info');
        } else { showToast(res.message, 'error'); }
    }, 'json');
}

function toggleAdmin(uid, current) {
    const newVal = current ? 0 : 1;
    const label  = newVal ? 'make admin' : 'remove admin rights';
    if (!confirm(`Are you sure you want to ${label} for this user?`)) return;
    $.post('admin.php', { toggle_admin: uid, new_val: newVal }, function(res) {
        if (res.status === 'success') {
            showToast('Role updated! Refresh to see changes.', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    }, 'json');
}

function adminDeleteTask(tid) {
    if (!confirm('Delete this task permanently?')) return;
    $.post('admin.php', { delete_task: tid }, function(res) {
        if (res.status === 'success') {
            $('#task-row-' + tid).fadeOut(300, function() { $(this).remove(); });
            showToast('Task deleted', 'info');
        }
    }, 'json');
}

// Admin task search
$('#admin-task-search').on('input', function() {
    const q = $(this).val().toLowerCase();
    $('.admin-task-row').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.includes(q));
    });
});
</script>

<?php include 'includes/footer.php'; ?>
