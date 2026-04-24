<?php
require_once 'config/db.php';
requireLogin();

$page_title = 'Dashboard';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_color = $_SESSION['user_color'];

// Fetch all tasks for this user
$tasks = ['todo' => [], 'inprogress' => [], 'done' => []];
$result = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id = $user_id ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $tasks[$row['status']][] = $row;
}
$total = count($tasks['todo']) + count($tasks['inprogress']) + count($tasks['done']);

function renderTask($task) {
    $today = date('Y-m-d');
    $due = $task['due_date'];
    $overdue = ($due && $due < $today && $task['status'] !== 'done');
    $dueText = $due ? date('M d', strtotime($due)) : '';
    ?>
    <div class="task-card" data-task-id="<?= $task['id'] ?>" data-priority="<?= $task['priority'] ?>">
        <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
        <?php if ($task['description']): ?>
        <div class="task-desc"><?= htmlspecialchars($task['description']) ?></div>
        <?php endif; ?>
        <div class="task-meta">
            <div class="d-flex align-items-center gap-2">
                <span class="priority-badge priority-<?= $task['priority'] ?>"><?= ucfirst($task['priority']) ?></span>
                <?php if ($dueText): ?>
                <span class="task-due <?= $overdue ? 'overdue' : '' ?>">
                    <i class="bi bi-calendar3"></i> <?= $dueText ?><?= $overdue ? ' ⚠' : '' ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="task-actions">
                <button class="task-btn task-btn-edit" onclick="openEditModal(<?= $task['id'] ?>)" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="task-btn task-btn-delete" onclick="deleteTask(<?= $task['id'] ?>)" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php
}

include 'includes/header.php';
?>

<!-- NAVBAR (Dashboard) -->
<nav class="navbar-taskflow">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebar-toggle" class="btn p-0 d-lg-none" style="color:var(--text-muted); font-size:1.3rem; background:none; border:none;">
                    <i class="bi bi-list"></i>
                </button>
                <a href="index.php" class="navbar-brand">Task<span>Flow</span></a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span style="font-size:0.85rem; color:var(--text-muted);" class="d-none d-md-inline">Hey, <strong style="color:var(--text-primary);"><?= htmlspecialchars(explode(' ', $user_name)[0]) ?></strong> 👋</span>
                <div class="theme-toggle-wrap">
                    <span class="theme-icon">🌙</span>
                    <button class="theme-toggle" title="Toggle theme"></button>
                </div>
                <a href="profile.php" class="btn-outline-tf" style="padding:0.4rem 1rem; font-size:0.85rem;" title="Profile">
                    <i class="bi bi-person"></i>
                </a>
                <a href="logout.php" class="btn-outline-tf" style="padding:0.4rem 1rem; font-size:0.85rem;" title="Logout">
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
            <div class="user-avatar" style="background: <?= $user_color ?>">
                <?= strtoupper(substr($user_name, 0, 1)) ?>
            </div>
            <div>
                <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                <div class="user-role">Free Plan</div>
            </div>
        </div>

        <div class="sidebar-section-label">Workspace</div>
        <a class="sidebar-link active" href="dashboard.php">
            <i class="bi bi-kanban"></i> My Board
        </a>

        <div class="sidebar-section-label mt-3">Tasks</div>
        <button class="sidebar-link" onclick="openAddModal('todo')">
            <i class="bi bi-plus-circle"></i> Add New Task
        </button>
        <button class="sidebar-link" onclick="filterTasks('high')">
            <i class="bi bi-exclamation-circle" style="color:#f87171"></i> High Priority
        </button>
        <button class="sidebar-link" onclick="filterTasks('all')">
            <i class="bi bi-grid-3x3-gap"></i> All Tasks
        </button>

        <div class="sidebar-section-label mt-3">Account</div>
        <a class="sidebar-link" href="profile.php">
            <i class="bi bi-person-circle"></i> My Profile
        </a>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
        <a class="sidebar-link" href="admin.php">
            <i class="bi bi-shield-check" style="color:#f59e0b"></i> Admin Panel
        </a>
        <?php endif; ?>
        <div class="sidebar-divider"></div>
        <a class="sidebar-link" href="logout.php">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- Top bar -->
        <div class="dashboard-topbar">
            <div>
                <h1 class="page-heading">My <span>Board</span></h1>
                <p style="color:var(--text-muted); font-size:0.9rem; margin:0;"><?= date('l, F j, Y') ?></p>
            </div>
            <button class="btn-primary-tf" onclick="openAddModal('todo')">
                <i class="bi bi-plus-lg"></i> New Task
            </button>
        </div>

        <!-- STAT CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="0">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(99,102,241,0.15); color:#818cf8;">
                        <i class="bi bi-list-task"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="stat-total"><?= $total ?></div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(100,116,139,0.15); color:#94a3b8;">
                        <i class="bi bi-circle"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="stat-todo"><?= count($tasks['todo']) ?></div>
                        <div class="stat-label">To Do</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="160">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(245,158,11,0.15); color:#fbbf24;">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="stat-inprogress"><?= count($tasks['inprogress']) ?></div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="240">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(16,185,129,0.15); color:#34d399;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="stat-done"><?= count($tasks['done']) ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEARCH & FILTER -->
        <div class="search-filter-row" data-aos="fade-up">
            <div class="search-bar-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="task-search" placeholder="Search tasks...">
            </div>
            <div class="filter-chips">
                <span class="filter-chip active" data-filter="all">All</span>
                <span class="filter-chip" data-filter="high">🔴 High</span>
                <span class="filter-chip" data-filter="medium">🟡 Medium</span>
                <span class="filter-chip" data-filter="low">🟢 Low</span>
            </div>
        </div>

        <!-- KANBAN BOARD -->
        <div class="kanban-board" data-aos="fade-up" data-aos-delay="100">

            <!-- TO DO -->
            <div class="kanban-col" id="col-todo">
                <div class="kanban-col-header">
                    <div class="col-title">
                        <span class="col-dot" style="background:#64748b"></span> To Do
                    </div>
                    <span class="col-count"><?= count($tasks['todo']) ?></span>
                </div>
                <div class="kanban-tasks" id="tasks-todo" data-status="todo">
                    <?php foreach ($tasks['todo'] as $task) renderTask($task); ?>
                </div>
                <p class="no-tasks-placeholder"><i class="bi bi-inbox"></i>No tasks match your filter</p>
                <div style="padding: 0 1rem 1rem;">
                    <button class="btn-add-task" onclick="openAddModal('todo')">
                        <i class="bi bi-plus"></i> Add task
                    </button>
                </div>
            </div>

            <!-- IN PROGRESS -->
            <div class="kanban-col" id="col-inprogress">
                <div class="kanban-col-header">
                    <div class="col-title">
                        <span class="col-dot" style="background:#f59e0b"></span> In Progress
                    </div>
                    <span class="col-count"><?= count($tasks['inprogress']) ?></span>
                </div>
                <div class="kanban-tasks" id="tasks-inprogress" data-status="inprogress">
                    <?php foreach ($tasks['inprogress'] as $task) renderTask($task); ?>
                </div>
                <p class="no-tasks-placeholder"><i class="bi bi-inbox"></i>No tasks match your filter</p>
                <div style="padding: 0 1rem 1rem;">
                    <button class="btn-add-task" onclick="openAddModal('inprogress')">
                        <i class="bi bi-plus"></i> Add task
                    </button>
                </div>
            </div>

            <!-- DONE -->
            <div class="kanban-col" id="col-done">
                <div class="kanban-col-header">
                    <div class="col-title">
                        <span class="col-dot" style="background:#10b981"></span> Done
                    </div>
                    <span class="col-count"><?= count($tasks['done']) ?></span>
                </div>
                <div class="kanban-tasks" id="tasks-done" data-status="done">
                    <?php foreach ($tasks['done'] as $task) renderTask($task); ?>
                </div>
                <p class="no-tasks-placeholder"><i class="bi bi-inbox"></i>No tasks match your filter</p>
                <div style="padding: 0 1rem 1rem;">
                    <button class="btn-add-task" onclick="openAddModal('done')">
                        <i class="bi bi-plus"></i> Add task
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ADD/EDIT TASK MODAL -->
<div class="modal fade modal-tf" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="task-form">
                <div class="modal-body p-4">
                    <input type="hidden" name="task_id" id="task-id">
                    <input type="hidden" name="status" id="task-status" value="todo">

                    <div class="form-group">
                        <label class="form-label">Task Title *</label>
                        <input type="text" name="title" id="task-title" class="form-control-tf" placeholder="What needs to be done?" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="task-description" class="form-control-tf" rows="3" placeholder="Add details..." style="resize:vertical;"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Priority</label>
                                <select name="priority" id="task-priority" class="form-control-tf">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium" selected>🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" id="task-due" class="form-control-tf">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-tf" data-bs-dismiss="modal" style="padding:0.5rem 1.25rem;">Cancel</button>
                    <button type="submit" class="btn-primary-tf" id="save-task-btn" style="padding:0.5rem 1.5rem;">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Filter tasks by priority
function filterTasks(priority) {
    if (priority === 'all') {
        $('.task-card').fadeIn(200);
        showToast('Showing all tasks', 'info');
    } else {
        $('.task-card').each(function() {
            if ($(this).data('priority') === priority) {
                $(this).fadeIn(200);
            } else {
                $(this).fadeOut(200);
            }
        });
        showToast('Filtered: ' + priority + ' priority', 'info');
    }
}
</script>

<?php include 'includes/footer.php'; ?>