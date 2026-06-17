<?php
require_once 'config.php';
requireAdmin(); // يمنع الطلاب من الدخول
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __('admin_panel') ?> - <?= __('app_name') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
</script>
<style>
  /* ===== Admin Layout ===== */
  .admin-wrap { display:flex; min-height:100vh; }

  .admin-sidebar {
    width: 260px; flex-shrink:0;
    background: var(--card-bg);
    border-inline-end: 1px solid var(--border);
    display: flex; flex-direction: column;
    padding: 24px 0;
  }
  .admin-logo {
    padding: 0 20px 24px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 16px;
  }
  .admin-logo h2 { margin:0; font-size:20px; color:var(--accent); }
  .admin-logo p  { margin:4px 0 0; font-size:12px; color:var(--muted); }

  .admin-nav a {
    display:flex; align-items:center; gap:12px;
    padding:12px 20px; color:var(--text);
    text-decoration:none; font-size:14px;
    border-inline-start: 3px solid transparent;
    transition:.2s;
  }
  .admin-nav a:hover, .admin-nav a.active {
    background: rgba(var(--accent-rgb),.08);
    border-inline-start-color: var(--accent);
    color: var(--accent);
  }
  .admin-nav .nav-icon { font-size:18px; }

  .admin-bottom { margin-top:auto; padding:16px 20px; }
  .admin-bottom a {
    display:flex; align-items:center; gap:10px;
    color:var(--muted); font-size:13px; text-decoration:none;
  }
  .admin-bottom a:hover { color:var(--danger); }

  /* ===== Main Content ===== */
  .admin-main { flex:1; padding:32px; overflow-y:auto; }

  .admin-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:28px;
  }
  .admin-header h1 { margin:0; font-size:24px; }
  .admin-header .user-badge {
    display:flex; align-items:center; gap:10px;
  }
  .admin-avatar {
    width:38px; height:38px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:16px; color:#fff;
  }

  /* ===== Stats Cards ===== */
  .stats-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:16px; margin-bottom:32px;
  }
  .stat-card {
    background:var(--card-bg); border:1px solid var(--border);
    border-radius:16px; padding:20px;
    display:flex; flex-direction:column; gap:8px;
  }
  .stat-icon { font-size:28px; }
  .stat-value { font-size:32px; font-weight:900; color:var(--accent); }
  .stat-label { font-size:13px; color:var(--muted); }

  /* ===== Section ===== */
  .admin-section { display:none; }
  .admin-section.active { display:block; }

  .section-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:20px;
  }
  .section-header h2 { margin:0; font-size:20px; }

  /* ===== Tables ===== */
  .admin-table {
    width:100%; border-collapse:collapse;
    background:var(--card-bg); border-radius:16px; overflow:hidden;
    border:1px solid var(--border);
  }
  .admin-table th {
    background:rgba(var(--accent-rgb),.1);
    padding:12px 16px; font-size:13px; color:var(--muted);
    text-align:inherit; font-weight:600;
  }
  .admin-table td {
    padding:12px 16px; font-size:14px;
    border-top:1px solid var(--border);
  }
  .admin-table tr:hover td { background:rgba(255,255,255,.02); }

  .badge {
    display:inline-block; padding:3px 10px; border-radius:20px;
    font-size:12px; font-weight:600;
  }
  .badge-lost   { background:rgba(239,68,68,.15); color:#ef4444; }
  .badge-found  { background:rgba(16,185,129,.15); color:#10b981; }
  .badge-admin  { background:rgba(255,107,43,.15); color:#FF6B2B; }
  .badge-student{ background:rgba(124,58,237,.15); color:#7C3AED; }

  /* ===== Forms ===== */
  .admin-form-card {
    background:var(--card-bg); border:1px solid var(--border);
    border-radius:16px; padding:24px; margin-bottom:24px;
  }
  .admin-form-card h3 { margin:0 0 20px; font-size:16px; }
  .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
  .form-group label { font-size:13px; color:var(--muted); }
  .form-group input, .form-group textarea, .form-group select {
    background:var(--input-bg); border:1px solid var(--border);
    border-radius:10px; padding:10px 14px; color:var(--text);
    font-family:inherit; font-size:14px;
  }
  .form-group input[type="color"] { height:44px; padding:4px 8px; cursor:pointer; }
  .form-group textarea { resize:vertical; min-height:80px; }

  .btn-primary {
    background:var(--accent); color:#fff;
    border:none; padding:11px 24px; border-radius:10px;
    font-family:inherit; font-size:14px; font-weight:600;
    cursor:pointer; transition:.2s;
  }
  .btn-primary:hover { opacity:.9; }
  .btn-danger {
    background:rgba(239,68,68,.1); color:#ef4444;
    border:1px solid rgba(239,68,68,.2); padding:6px 14px;
    border-radius:8px; font-family:inherit; font-size:13px;
    cursor:pointer; transition:.2s;
  }
  .btn-danger:hover { background:rgba(239,68,68,.2); }

  /* ===== Mobile ===== */
  @media(max-width:768px){
    .admin-wrap { flex-direction:column; }
    .admin-sidebar { width:100%; flex-direction:row; overflow-x:auto; padding:10px 0; }
    .admin-logo { display:none; }
    .admin-nav { display:flex; flex-direction:row; }
    .admin-nav a { padding:10px 14px; border-inline-start:none; border-bottom:3px solid transparent; white-space:nowrap; }
    .admin-nav a:hover, .admin-nav a.active { border-inline-start-color:transparent; border-bottom-color:var(--accent); }
    .admin-bottom { display:none; }
    .admin-main { padding:16px; }
    .form-row { grid-template-columns:1fr; }
  }
</style>
</head>
<body>
<div class="grid-bg"></div>
<div class="admin-wrap">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-logo">
      <h2>🎛 <?= __('admin_panel') ?></h2>
      <p><?= __('app_name') ?> · <?= __('college') ?></p>
    </div>
    <nav class="admin-nav">
      <a href="#" class="active" onclick="showSection('dashboard',this)">
        <span class="nav-icon">📊</span> لوحة التحكم
      </a>
      <a href="#" onclick="showSection('events',this)">
        <span class="nav-icon">📅</span> <?= __('admin_events') ?>
      </a>
      <a href="#" onclick="showSection('lost',this)">
        <span class="nav-icon">🔍</span> <?= __('admin_lost') ?>
      </a>
      <a href="#" onclick="showSection('users',this)">
        <span class="nav-icon">👥</span> <?= __('admin_users') ?>
      </a>
    </nav>
    <div class="admin-bottom">
      <a href="logout.php">
        <span>🚪</span> <?= __('logout') ?>
      </a>
    </div>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <div class="admin-header">
      <h1 id="section-title">لوحة التحكم</h1>
      <div class="user-badge">
        <span style="font-size:13px;color:var(--muted)">مدير النظام</span>
        <div class="admin-avatar" style="background:<?= htmlspecialchars($user['avatar_color']) ?>">
          <?= mb_substr($user['full_name'], 0, 1) ?>
        </div>
      </div>
    </div>

    <!-- ===== Dashboard ===== -->
    <section id="sec-dashboard" class="admin-section active">
      <div class="stats-grid" id="stats-grid">
        <div class="stat-card">
          <span class="stat-icon">👥</span>
          <span class="stat-value" id="stat-users">—</span>
          <span class="stat-label"><?= __('total_users') ?></span>
        </div>
        <div class="stat-card">
          <span class="stat-icon">🔍</span>
          <span class="stat-value" id="stat-lost">—</span>
          <span class="stat-label"><?= __('total_lost') ?></span>
        </div>
        <div class="stat-card">
          <span class="stat-icon">📅</span>
          <span class="stat-value" id="stat-events">—</span>
          <span class="stat-label"><?= __('total_events') ?></span>
        </div>
        <div class="stat-card">
          <span class="stat-icon">📝</span>
          <span class="stat-value" id="stat-posts">—</span>
          <span class="stat-label"><?= __('total_posts') ?></span>
        </div>
      </div>

      <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:20px;">
        <h3 style="margin:0 0 12px;">🚀 روابط سريعة</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <button class="btn-primary" onclick="showSection('events',document.querySelector('[onclick*=events]'))">+ إضافة فعالية</button>
          <button class="btn-primary" style="background:#10B981" onclick="showSection('lost',document.querySelector('[onclick*=lost]'))">عرض المفقودات</button>
          <button class="btn-primary" style="background:#7C3AED" onclick="showSection('users',document.querySelector('[onclick*=users]'))">إدارة المستخدمين</button>
          <a href="index.php" style="text-decoration:none;">
            <button class="btn-primary" style="background:#0072FF">العودة للموقع →</button>
          </a>
        </div>
      </div>
    </section>

    <!-- ===== Events ===== -->
    <section id="sec-events" class="admin-section">
      <!-- نموذج إضافة فعالية -->
      <div class="admin-form-card">
        <h3>➕ <?= __('add_event') ?></h3>
        <div class="form-row">
          <div class="form-group">
            <label><?= __('event_title') ?></label>
            <input type="text" id="ev-title" placeholder="عنوان الفعالية">
          </div>
          <div class="form-group">
            <label><?= __('event_date') ?></label>
            <input type="date" id="ev-date">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label><?= __('event_desc') ?></label>
            <textarea id="ev-desc" rows="2" placeholder="وصف الفعالية..."></textarea>
          </div>
          <div class="form-group">
            <label><?= __('event_color') ?></label>
            <input type="color" id="ev-color" value="#7C3AED">
          </div>
        </div>
        <button class="btn-primary" onclick="addEvent()"><?= __('save_event') ?></button>
      </div>

      <!-- جدول الفعاليات -->
      <div class="section-header">
        <h2>📅 <?= __('admin_events') ?></h2>
      </div>
      <table class="admin-table" id="events-table">
        <thead>
          <tr>
            <th>#</th>
            <th>العنوان</th>
            <th>التاريخ</th>
            <th>الوصف</th>
            <th>اللون</th>
            <th><?= __('delete') ?></th>
          </tr>
        </thead>
        <tbody id="events-tbody">
          <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px;">جاري التحميل...</td></tr>
        </tbody>
      </table>
    </section>

    <!-- ===== Lost Items ===== -->
    <section id="sec-lost" class="admin-section">
      <div class="section-header">
        <h2>🔍 <?= __('admin_lost') ?></h2>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>الغرض</th>
            <th>المكان</th>
            <th>الحالة</th>
            <th>صاحب البلاغ</th>
            <th>التاريخ</th>
            <th><?= __('delete') ?></th>
          </tr>
        </thead>
        <tbody id="lost-tbody">
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px;">جاري التحميل...</td></tr>
        </tbody>
      </table>
    </section>

    <!-- ===== Users ===== -->
    <section id="sec-users" class="admin-section">
      <div class="section-header">
        <h2>👥 <?= __('admin_users') ?></h2>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>الرقم الجامعي</th>
            <th>البريد</th>
            <th>الدور</th>
            <th>تاريخ التسجيل</th>
            <th><?= __('delete') ?></th>
          </tr>
        </thead>
        <tbody id="users-tbody">
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px;">جاري التحميل...</td></tr>
        </tbody>
      </table>
    </section>

  </main>
</div>

<script>
const API = 'handler.php';

// ===== Navigation =====
function showSection(name, el) {
  document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.admin-nav a').forEach(a => a.classList.remove('active'));
  document.getElementById('sec-' + name).classList.add('active');
  if (el) el.classList.add('active');

  const titles = {dashboard:'لوحة التحكم', events:'إدارة الفعاليات', lost:'إدارة المفقودات', users:'إدارة المستخدمين'};
  document.getElementById('section-title').textContent = titles[name] || name;

  if (name === 'dashboard') loadStats();
  if (name === 'events')    loadEvents();
  if (name === 'lost')      loadLost();
  if (name === 'users')     loadUsers();
}

// ===== Stats =====
async function loadStats() {
  const r = await fetch(API + '?action=admin_stats');
  const d = await r.json();
  document.getElementById('stat-users').textContent  = d.users  ?? '—';
  document.getElementById('stat-lost').textContent   = d.lost   ?? '—';
  document.getElementById('stat-events').textContent = d.events ?? '—';
  document.getElementById('stat-posts').textContent  = d.posts  ?? '—';
}

// ===== Events =====
async function loadEvents() {
  const r = await fetch(API + '?action=events_get');
  const d = await r.json();
  const tbody = document.getElementById('events-tbody');
  if (!d.events?.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px;">لا توجد فعاليات</td></tr>'; return; }
  tbody.innerHTML = d.events.map(e => `
    <tr>
      <td>${e.id}</td>
      <td><strong>${e.title}</strong></td>
      <td>${e.event_date}</td>
      <td>${e.description || '—'}</td>
      <td><span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:${e.color};vertical-align:middle;"></span></td>
      <td><button class="btn-danger" onclick="deleteEvent(${e.id})">🗑 حذف</button></td>
    </tr>
  `).join('');
}

async function addEvent() {
  const title = document.getElementById('ev-title').value.trim();
  const date  = document.getElementById('ev-date').value;
  const desc  = document.getElementById('ev-desc').value.trim();
  const color = document.getElementById('ev-color').value;
  if (!title || !date) { alert('يرجى إدخال العنوان والتاريخ'); return; }

  const fd = new FormData();
  fd.append('title', title); fd.append('event_date', date);
  fd.append('description', desc); fd.append('color', color);

  const r = await fetch(API + '?action=events_add', {method:'POST', body:fd});
  const d = await r.json();
  if (d.success) {
    document.getElementById('ev-title').value = '';
    document.getElementById('ev-date').value  = '';
    document.getElementById('ev-desc').value  = '';
    loadEvents();
  }
}

async function deleteEvent(id) {
  if (!confirm('<?= __('confirm_delete') ?>')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch(API + '?action=events_delete', {method:'POST', body:fd});
  loadEvents();
}

// ===== Lost =====
async function loadLost() {
  const r = await fetch(API + '?action=lost_get');
  const d = await r.json();
  const tbody = document.getElementById('lost-tbody');
  if (!d.items?.length) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px;">لا توجد بلاغات</td></tr>'; return; }
  tbody.innerHTML = d.items.map(i => `
    <tr>
      <td>${i.id}</td>
      <td><strong>${i.item_name}</strong></td>
      <td>${i.location}</td>
      <td><span class="badge ${i.status === 'مفقود' ? 'badge-lost' : 'badge-found'}">${i.status}</span></td>
      <td>${i.full_name}</td>
      <td>${(i.created_at || i.date || '').substring(0,10)}</td>
      <td><button class="btn-danger" onclick="deleteLost(${i.id})">🗑 حذف</button></td>
    </tr>
  `).join('');
}

async function deleteLost(id) {
  if (!confirm('<?= __('confirm_delete') ?>')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch(API + '?action=lost_delete', {method:'POST', body:fd});
  loadLost();
}

// ===== Users =====
async function loadUsers() {
  const r = await fetch(API + '?action=admin_users');
  const d = await r.json();
  const tbody = document.getElementById('users-tbody');
  if (!d.users?.length) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px;">لا يوجد مستخدمون</td></tr>'; return; }
  tbody.innerHTML = d.users.map(u => `
    <tr>
      <td>${u.id}</td>
      <td>
        <div style="display:flex;align-items:center;gap:8px;">
          <div style="width:30px;height:30px;border-radius:50%;background:${u.avatar_color};display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;">
            ${u.full_name.charAt(0)}
          </div>
          ${u.full_name}
        </div>
      </td>
      <td>${u.student_id}</td>
      <td>${u.email}</td>
      <td><span class="badge ${u.role === 'admin' ? 'badge-admin' : 'badge-student'}">${u.role === 'admin' ? 'أدمن' : 'طالب'}</span></td>
      <td>${(u.created_at || '').substring(0,10)}</td>
      <td>${u.role !== 'admin' ? `<button class="btn-danger" onclick="deleteUser(${u.id})">🗑 حذف</button>` : '—'}</td>
    </tr>
  `).join('');
}

async function deleteUser(id) {
  if (!confirm('<?= __('confirm_delete') ?>')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch(API + '?action=admin_delete_user', {method:'POST', body:fd});
  loadUsers();
}

// Load dashboard on start
loadStats();
</script>
</body>
</html>