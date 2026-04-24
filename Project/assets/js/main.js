/* ============================================
   TaskFlow — Main JavaScript (Fixed)
   ============================================ */

$(document).ready(function () {

    // ── THEME: init on DOM ready ──
    initTheme();
    $(document).on('click', '.theme-toggle', toggleTheme);

    // ── CURSOR GLOW ──
    if (!('ontouchstart' in window)) {
        const glow = $('<div class="cursor-glow"></div>').appendTo('body');
        $(document).mousemove(function (e) {
            glow.css({ left: e.clientX, top: e.clientY });
        });
    }

    // ── NAVBAR SCROLL ──
    $(window).scroll(function () {
        $('.navbar-taskflow').toggleClass('scrolled', $(this).scrollTop() > 50);
    });

    // ── SMOOTH SCROLL ──
    $('a[href^="#"]').on('click', function (e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({ scrollTop: target.offset().top - 80 }, 700);
        }
    });

    // ── MOBILE SIDEBAR ──
    $('#sidebar-toggle').on('click', function () {
        $('.sidebar').toggleClass('open');
    });

    // ── KANBAN DRAG & DROP ──
    if ($('.kanban-tasks').length) {
        initKanban();
    }

    // ── COUNTER ANIMATION ──
    animateCounters();

    // ── SEARCH ──
    $(document).on('input', '#task-search', function () {
        searchQuery = $(this).val().toLowerCase().trim();
        applyFilters();
    });

    // ── FILTER CHIPS ──
    $(document).on('click', '.filter-chip', function () {
        activeFilter = $(this).data('filter');
        $('.filter-chip').removeClass('active');
        $(this).addClass('active');
        applyFilters();
    });

    // ── SAVE TASK (Add/Edit) ──
    $(document).on('submit', '#task-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const btn   = $('#save-task-btn');
        const taskId = $('#task-id').val();
        const url    = taskId ? 'actions/update_task.php' : 'actions/add_task.php';

        btn.html('<span class="spinner-tf"></span> Saving...').prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: $form.serialize(),
            success: function (raw) {
                btn.html('Save Task').prop('disabled', false);
                let res;
                try { res = typeof raw === 'object' ? raw : JSON.parse(raw); }
                catch(e) {
                    // PHP returned an error page — show it
                    showToast('Server error. Check PHP logs.', 'error');
                    console.error('Raw server response:', raw);
                    return;
                }
                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('taskModal')).hide();
                    showToast(taskId ? 'Task updated!' : 'Task added!', 'success');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast(res.message || 'Error saving task', 'error');
                }
            },
            error: function (xhr) {
                btn.html('Save Task').prop('disabled', false);
                showToast('Connection error (' + xhr.status + '). Is PHP running?', 'error');
                console.error('XHR error:', xhr.responseText);
            }
        });
    });
});

/* ══════════════════════════════
   DARK / LIGHT MODE
══════════════════════════════ */
function initTheme() {
    const saved = localStorage.getItem('tf-theme') || 'dark';
    if (saved === 'light') {
        document.documentElement.classList.add('light-mode');
    } else {
        document.documentElement.classList.remove('light-mode');
    }
    updateThemeIcon(saved);
}

function toggleTheme() {
    const isLight = document.documentElement.classList.toggle('light-mode');
    localStorage.setItem('tf-theme', isLight ? 'light' : 'dark');
    updateThemeIcon(isLight ? 'light' : 'dark');
}

function updateThemeIcon(mode) {
    $('.theme-icon').text(mode === 'light' ? '☀️' : '🌙');
}

/* ══════════════════════════════
   SEARCH & FILTER
══════════════════════════════ */
let activeFilter = 'all';
let searchQuery  = '';

function applyFilters() {
    $('.task-card').each(function () {
        const $card    = $(this);
        const title    = $card.find('.task-title').text().toLowerCase();
        const desc     = $card.find('.task-desc').text().toLowerCase();
        const priority = $card.data('priority');
        const matchSearch = searchQuery === '' || title.includes(searchQuery) || desc.includes(searchQuery);
        const matchFilter = activeFilter === 'all' || priority === activeFilter;

        if (matchSearch && matchFilter) {
            $card.removeClass('search-hidden').stop(true).fadeIn(200);
            if (searchQuery !== '') highlightText($card.find('.task-title'), searchQuery);
            else $card.find('.task-title').html($card.find('.task-title').text());
        } else {
            $card.addClass('search-hidden').stop(true).fadeOut(150);
        }
    });

    updateColumnCounts();
    $('.kanban-tasks').each(function () {
        const visible = $(this).find('.task-card:not(.search-hidden)').length;
        $(this).next('.no-tasks-placeholder').toggle(visible === 0);
    });
}

function highlightText($el, query) {
    const text  = $el.text();
    const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    $el.html(text.replace(regex, '<span class="highlight-match">$1</span>'));
}

/* ══════════════════════════════
   TOAST NOTIFICATIONS
══════════════════════════════ */
function showToast(message, type) {
    type = type || 'info';
    const icons  = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };
    const colors = { success: '#34d399', error: '#f87171', info: '#818cf8' };

    if (!$('.toast-container').length) $('<div class="toast-container"></div>').appendTo('body');

    const toast = $('<div class="toast-tf toast-' + type + '"><i class="bi ' + icons[type] + ' toast-icon" style="color:' + colors[type] + '"></i><span>' + message + '</span></div>');
    $('.toast-container').append(toast);
    setTimeout(function () { toast.addClass('hiding'); setTimeout(function() { toast.remove(); }, 300); }, 3500);
}

/* ══════════════════════════════
   KANBAN
══════════════════════════════ */
function initKanban() {
    $('.kanban-tasks').sortable({
        connectWith: '.kanban-tasks',
        placeholder: 'task-card ui-sortable-placeholder',
        tolerance: 'pointer',
        revert: 150,
        start: function (e, ui) { ui.item.css('opacity', 0.85); },
        stop:  function (e, ui) { ui.item.css('opacity', 1); },
        update: function (e, ui) {
            if (this === ui.item.parent()[0]) {
                updateTaskStatus(ui.item.data('task-id'), $(this).data('status'));
            }
        }
    }).disableSelection();
}

function updateTaskStatus(taskId, status) {
    $.post('actions/update_status.php', { task_id: taskId, status: status }, function (raw) {
        let res;
        try { res = typeof raw === 'object' ? raw : JSON.parse(raw); } catch(e) { return; }
        if (res.status === 'success') {
            showToast('Task moved!', 'success');
            updateColumnCounts();
        } else {
            showToast(res.message || 'Move failed', 'error');
        }
    });
}

function updateColumnCounts() {
    $('.kanban-tasks').each(function () {
        const count = $(this).find('.task-card:not(.search-hidden)').length;
        $(this).closest('.kanban-col').find('.col-count').text(count);
    });
    $('#stat-todo').text($('#tasks-todo .task-card').length);
    $('#stat-inprogress').text($('#tasks-inprogress .task-card').length);
    $('#stat-done').text($('#tasks-done .task-card').length);
    $('#stat-total').text($('.task-card').length);
}

/* ══════════════════════════════
   MODALS
══════════════════════════════ */
function openAddModal(status) {
    $('#task-id').val('');
    $('#task-title').val('');
    $('#task-description').val('');
    $('#task-priority').val('medium');
    $('#task-status').val(status || 'todo');
    $('#task-due').val('');
    $('#taskModalLabel').text('Add New Task');
    new bootstrap.Modal(document.getElementById('taskModal')).show();
}

function openEditModal(taskId) {
    $.get('actions/get_task.php', { task_id: taskId }, function (raw) {
        let task;
        try { task = typeof raw === 'object' ? raw : JSON.parse(raw); } catch(e) { return; }
        if (!task) return;
        $('#task-id').val(task.id);
        $('#task-title').val(task.title);
        $('#task-description').val(task.description);
        $('#task-priority').val(task.priority);
        $('#task-status').val(task.status);
        $('#task-due').val(task.due_date);
        $('#taskModalLabel').text('Edit Task');
        new bootstrap.Modal(document.getElementById('taskModal')).show();
    });
}

function deleteTask(taskId) {
    if (!confirm('Delete this task?')) return;
    $.post('actions/delete_task.php', { task_id: taskId }, function (raw) {
        let res;
        try { res = typeof raw === 'object' ? raw : JSON.parse(raw); } catch(e) { return; }
        if (res.status === 'success') {
            $('[data-task-id="' + taskId + '"]').fadeOut(300, function () { $(this).remove(); updateColumnCounts(); });
            showToast('Task deleted', 'info');
        } else { showToast(res.message, 'error'); }
    });
}

/* ══════════════════════════════
   MISC
══════════════════════════════ */
function animateCounters() {
    $('.counter').each(function () {
        const $el = $(this), target = parseInt($el.data('target'));
        $({ count: 0 }).animate({ count: target }, {
            duration: 1500, easing: 'swing',
            step: function () { $el.text(Math.ceil(this.count).toLocaleString()); },
            complete: function () { $el.text(target.toLocaleString()); }
        });
    });
}