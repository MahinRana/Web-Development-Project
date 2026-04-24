<?php
$page_title = 'Manage Smarter';
include 'includes/header.php';
?>

<!-- NAVBAR -->
<nav class="navbar-taskflow">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <a href="index.php" class="navbar-brand">Task<span>Flow</span></a>
            <div class="d-flex align-items-center gap-3">
                <a href="#features" class="nav-link d-none d-md-block">Features</a>
                <a href="login.php" class="btn-outline-tf" style="padding: 0.5rem 1.25rem; font-size: 0.9rem;">Log in</a>
                <a href="register.php" class="btn-primary-tf" style="padding: 0.5rem 1.25rem; font-size: 0.9rem;">Get started</a>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero-section">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>
    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Left -->
            <div class="col-lg-6">
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    Drag. Drop. Done.
                </div>
                <h1 class="hero-title">
                    Where great<br>
                    work gets<br>
                    <span class="highlight">organized.</span>
                </h1>
                <p class="hero-subtitle">
                    TaskFlow is an animated Kanban board that makes managing your tasks feel effortless. Drag tasks, set priorities, hit deadlines.
                </p>
                <div class="hero-actions">
                    <a href="register.php" class="btn-primary-tf">
                        Start for free <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#features" class="btn-outline-tf">
                        <i class="bi bi-play-circle"></i> See how it works
                    </a>
                </div>
            </div>

            <!-- Right: Mockup -->
            <div class="col-lg-6">
                <div class="hero-mockup">
                    <div class="mockup-card">
                        <!-- Mini Kanban preview -->
                        <div class="mockup-header">
                            <div class="mockup-dot" style="background:#ef4444"></div>
                            <div class="mockup-dot" style="background:#f59e0b"></div>
                            <div class="mockup-dot" style="background:#10b981"></div>
                            <span style="margin-left:8px; font-size:0.78rem; color:#64748b; font-family: var(--font-display);">TaskFlow Board</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="mockup-col-title">📋 To Do</div>
                                <div class="mockup-task">
                                    <div style="font-size:0.8rem; font-weight:600; margin-bottom:4px;">Design wireframes</div>
                                    <span class="priority-badge priority-high">High</span>
                                </div>
                                <div class="mockup-task">
                                    <div style="font-size:0.8rem; font-weight:600; margin-bottom:4px;">Write docs</div>
                                    <span class="priority-badge priority-low">Low</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mockup-col-title">⚡ In Progress</div>
                                <div class="mockup-task" style="border-color: rgba(99,102,241,0.4);">
                                    <div style="font-size:0.8rem; font-weight:600; margin-bottom:4px;">Build API</div>
                                    <span class="priority-badge priority-medium">Medium</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mockup-col-title">✅ Done</div>
                                <div class="mockup-task" style="opacity:0.6;">
                                    <div style="font-size:0.8rem; font-weight:600; margin-bottom:4px; text-decoration:line-through;">Setup DB</div>
                                    <span class="priority-badge priority-low">Low</span>
                                </div>
                                <div class="mockup-task" style="opacity:0.6;">
                                    <div style="font-size:0.8rem; font-weight:600; margin-bottom:4px; text-decoration:line-through;">Auth system</div>
                                    <span class="priority-badge priority-medium">Medium</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="0">
                <div class="stat-number counter" data-target="1240">0</div>
                <div class="stat-label">Tasks Completed</div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-number counter" data-target="320">0</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-number counter" data-target="98">0</div>
                <div class="stat-label">% Uptime</div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-number counter" data-target="3">0</div>
                <div class="stat-label">Kanban Columns</div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features-section" id="features">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-label">Why TaskFlow</div>
            <h2 class="section-title">Everything you need<br>to stay productive</h2>
            <p class="text-muted" style="max-width:450px; margin: 0 auto;">Simple enough for day one, powerful enough for complex projects.</p>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['icon' => '🎯', 'title' => 'Drag & Drop Board', 'desc' => 'Effortlessly move tasks between columns with smooth jQuery UI drag and drop. Changes are saved instantly via AJAX.'],
                ['icon' => '⚡', 'title' => 'Priority Levels', 'desc' => 'Mark tasks as Low, Medium, or High priority with color-coded visual indicators so nothing gets missed.'],
                ['icon' => '📅', 'title' => 'Due Date Tracking', 'desc' => 'Set deadlines and get visual warnings when tasks are overdue so you always stay on schedule.'],
                ['icon' => '🔒', 'title' => 'Secure Auth', 'desc' => 'Your tasks are private. Secure login and registration with PHP sessions and hashed passwords.'],
                ['icon' => '✨', 'title' => 'Smooth Animations', 'desc' => 'Every interaction is animated — from scroll reveals to card hover effects and toast notifications.'],
                ['icon' => '📱', 'title' => 'Responsive Design', 'desc' => 'Fully responsive with Bootstrap 5. Works beautifully on desktop, tablet, and mobile.'],
            ];
            foreach ($features as $i => $f): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <div class="feature-card">
                    <div class="feature-icon"><?= $f['icon'] ?></div>
                    <h5><?= $f['title'] ?></h5>
                    <p><?= $f['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section" data-aos="fade-up">
    <div class="container position-relative" style="z-index:1;">
        <h2 class="section-title mb-3">Ready to get organized?</h2>
        <p class="text-muted mb-4" style="max-width:400px; margin: 0 auto 2rem;">Join TaskFlow today and take control of your productivity.</p>
        <a href="register.php" class="btn-primary-tf" style="font-size: 1rem; padding: 1rem 2.5rem;">
            Create free account <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</section>

<!-- FOOTER -->
<footer style="border-top: 1px solid var(--dark-border); padding: 2rem 0; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
    <div class="container">
        <span style="font-family: var(--font-display); font-weight: 700; color: var(--text-primary);">Task<span style="color: var(--primary)">Flow</span></span>
        &nbsp;&mdash;&nbsp; Built with PHP, MySQL, Bootstrap & jQuery
    </div>
</footer>

<?php include 'includes/footer.php'; ?>
