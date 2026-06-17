<?php
require_once 'config.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __('app_name') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
/* ===== NEW NAVBAR & LAYOUT OVERRIDES ===== */
body { display: block; overflow: auto; background: var(--bg); }
.sidebar { display: none !important; }
.main { display: block; padding-top: 80px; min-height: 100vh; overflow: visible; }
.topbar { display: none !important; }

/* ===== الشريط العلوي الجديد ===== */
.app-navbar {
    position: fixed; top: 0; right: 0; left: 0; height: 70px;
    background: var(--card); backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 3%; z-index: 1000; box-shadow: var(--shadow-sm);
    gap: 16px;
}

/* --- قسم اليمين: اللوقو + الـ stats --- */
.nav-right-group {
    display: flex; align-items: center; gap: 16px; flex-shrink: 0;
}

/* اللوقو */
.nav-brand-main { display: flex; align-items: center; gap: 10px; cursor: pointer; text-decoration: none; flex-shrink: 0; }
.nav-brand-main img { width: 40px; height: 40px; border-radius: 12px; box-shadow: var(--shadow-sm); }
.nav-brand-name { font-weight: 800; font-size: 18px; color: var(--text); display: flex; flex-direction: column; line-height: 1.1; }
.nav-brand-name span { font-size: 10px; color: var(--muted); font-weight: 500; }

/* --- الـ stats في الـ navbar --- */
.nav-stats-row {
    display: flex; align-items: center; gap: 8px;
}
.nav-stat-pill {
    display: flex; align-items: center; gap: 6px;
    background: var(--card2); border: 1px solid var(--border);
    border-radius: 10px; padding: 5px 12px;
    cursor: pointer; transition: all 0.2s;
    white-space: nowrap;
}
.nav-stat-pill:hover { border-color: var(--accent); background: var(--accent-glow); transform: translateY(-1px); }
.nav-stat-pill .ns-icon { font-size: 15px; }
.nav-stat-pill .ns-val { font-size: 14px; font-weight: 900; color: var(--text); }
.nav-stat-pill .ns-lbl { font-size: 10px; color: var(--muted); font-weight: 500; }

/* --- قسم الوسط: القائمة --- */
.nav-menu-main { display: flex; align-items: center; gap: 4px; flex: 1; justify-content: center; }
.nav-item {
    padding: 7px 13px; border-radius: 10px; font-weight: 600; font-size: 13.5px;
    color: var(--text2); cursor: pointer; transition: 0.2s;
    display: flex; align-items: center; gap: 6px; user-select: none;
}
.nav-item:hover { background: var(--card2); color: var(--accent); }
.nav-item.active { background: var(--accent-glow); color: var(--accent); font-weight: 700; }

/* --- قسم الأكشن (أيقونات اليسار) --- */
.nav-actions-main { display: flex; align-items: center; gap: 10px; flex-shrink: 0; margin-inline-start: auto; }

/* القوائم المنسدلة */
.nav-dropdown { position: relative; }
.nav-dropdown-menu {
    visibility: hidden; opacity: 0; position: absolute; top: 110%;
    inset-inline-end: 0;
    background: var(--card); border: 1px solid var(--border);
    border-radius: 14px; box-shadow: var(--shadow);
    min-width: 200px; padding: 10px; z-index: 1001;
    transform: translateY(10px); transition: all 0.2s;
}
.nav-dropdown:hover .nav-dropdown-menu { visibility: visible; opacity: 1; transform: translateY(0); }
.nav-dropdown-menu .nav-item { margin-bottom: 2px; justify-content: flex-start; padding: 10px 14px; }

.user-avatar-small {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: white; cursor: pointer; overflow: hidden;
    border: 2px solid transparent; transition: border-color 0.2s;
}
.nav-dropdown:hover .user-avatar-small { border-color: var(--accent); }

/* شريط التنقل السفلي للجوال */
.mobile-bottom-nav { display: none; }

/* ===== صفحة الرئيسية ===== */
.page-title-section { margin-bottom: 24px; padding: 0 4%; }
.page-title-section h2 { font-size: 24px; font-weight: 800; color: var(--text); margin: 0 0 4px; }
.page-title-section p { font-size: 14px; color: var(--muted); margin: 0; }

.content-wrapper { padding: 0 4% 40px; max-width: 1400px; margin: 0 auto; }

/* Stats Row — إخفاء الـ stats من الصفحة (انتقلت للـ navbar) */
.home-stats-row { display: none; }

/* 6-card services grid */
.home-section-label { display: flex; align-items: center; justify-content: space-between; font-size: 16px; font-weight: 800; color: var(--text); margin-bottom: 16px; }
.home-services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px; }
.hs-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 20px; cursor: pointer; transition: all .2s; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
[dir="ltr"] .hs-card { align-items: flex-start; }
.hs-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.09); transform: translateY(-3px); border-color: var(--hc, var(--accent)); }
.hc-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.hc-title { font-size: 14px; font-weight: 800; color: var(--text); }
.hc-desc { font-size: 12px; color: var(--muted); line-height: 1.5; text-align: right; }
[dir="ltr"] .hc-desc { text-align: left; }

/* Bottom 2-col */
.home-bottom-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.home-bottom-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; }
.hbc-header { display: flex; justify-content: space-between; align-items: center; font-size: 15px; font-weight: 800; color: var(--text); margin-bottom: 16px; }
.hbc-link { font-size: 12px; color: var(--accent); cursor: pointer; font-weight: 700; }
.hbc-link:hover { text-decoration: underline; }

.ev-filter-pills { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.ev-pill { font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 20px; border: none; cursor: pointer; background: var(--card2); color: var(--muted); transition: all .2s; font-family: inherit; }
.ev-pill.active { background: var(--accent); color: #fff; }

.home-ev-item { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border); }
.home-ev-item:last-child { border-bottom: none; }
.hev-badge { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; font-size: 14px; font-weight: 900; flex-shrink: 0; line-height: 1.2; }
.hev-info { flex: 1; min-width: 0; }
.hev-title { font-size: 14px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px; }
.hev-loc { font-size: 12px; color: var(--muted); }

.act-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border); }
.act-item:last-child { border-bottom: none; }
.act-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.act-text { flex: 1; font-size: 13px; color: var(--text); line-height: 1.5; font-weight: 500; }
.act-time { font-size: 11px; color: var(--muted); white-space: nowrap; }

/* ===== صفحة الملف الشخصي — إصلاح الخروج من الـ frame ===== */
#page-profile {
    overflow: visible;
}
#page-profile .profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
    align-items: start;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
#page-profile .profile-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
    box-sizing: border-box;
    width: 100%;
    overflow: hidden;
}
#page-profile .profile-card input,
#page-profile .profile-card textarea,
#page-profile .profile-card select {
    width: 100%;
    box-sizing: border-box;
    max-width: 100%;
}
#page-profile .password-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}
@media (max-width: 700px) {
    #page-profile .password-grid { grid-template-columns: 1fr; }
}

/* ===== Responsive ===== */
@media (max-width: 1100px) {
    .nav-stats-row { display: none; } /* إخفاء الـ stats من الـ navbar على الشاشات الصغيرة */
    /* وإعادة عرضها في الصفحة */
    .home-stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 28px; }
    .stat-pill { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 20px 24px; display: flex; flex-direction: column; align-items: flex-end; cursor: pointer; transition: box-shadow .2s, transform .2s; }
    [dir="ltr"] .stat-pill { align-items: flex-start; }
    .stat-pill:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-2px); border-color: var(--accent); }
    .sp-icon { font-size: 26px; margin-bottom: 8px; }
    .sp-val { font-size: 30px; font-weight: 900; color: var(--text); line-height: 1; }
    .sp-lbl { font-size: 12px; color: var(--muted); margin-top: 4px; }
}

@media (max-width: 900px) {
    .nav-menu-main { display: none; }
    .mobile-bottom-nav {
        display: flex; position: fixed; bottom: 0; left: 0; right: 0; height: 65px;
        background: var(--card); border-top: 1px solid var(--border);
        z-index: 1000; padding: 0 10px; justify-content: space-around; align-items: center;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
    }
    .mobile-bottom-nav .nav-item { flex-direction: column; gap: 4px; font-size: 10px; padding: 6px; border-radius: 12px; }
    .mobile-bottom-nav .nav-item span.ni { font-size: 20px; }
    .main { padding-bottom: 80px; }
    .home-services-grid { grid-template-columns: repeat(2,1fr); }
    .home-bottom-row { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .home-stats-row { grid-template-columns: 1fr; }
}
</style>
<script>
  const savedTheme = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', savedTheme);
</script>
</head>
<body>

<nav class="app-navbar">

    <!-- ✅ اليمين: اللوقو + الـ 3 stats --- -->
    <div class="nav-right-group">
        <a class="nav-brand-main" onclick="navigate('home')" href="#">
            <img src="images/Logo.jpg" alt="Logo">
            <div class="nav-brand-name">
                <?= __('app_name') ?>
                <span><?= __('app_sub') ?></span>
            </div>
        </a>

        <!-- الـ 3 stats جنب اللوقو -->
        <div class="nav-stats-row">
            <div class="nav-stat-pill" onclick="navigate('chat')" title="<?= __('chat_msgs') ?>">
                <span class="ns-icon" style="color:#6366f1;">💬</span>
                <span class="ns-val" id="stat-chat">—</span>
                <span class="ns-lbl"><?= __('chat_msgs') ?></span>
            </div>
            <div class="nav-stat-pill" onclick="navigate('lost')" title="<?= __('active_lost') ?>">
                <span class="ns-icon" style="color:#f59e0b;">🔍</span>
                <span class="ns-val" id="stat-lost">—</span>
                <span class="ns-lbl"><?= __('active_lost') ?></span>
            </div>
            <div class="nav-stat-pill" onclick="navigate('events')" title="<?= __('up_events') ?>">
                <span class="ns-icon" style="color:#10b981;">📅</span>
                <span class="ns-val" id="stat-events">—</span>
                <span class="ns-lbl"><?= __('up_events') ?></span>
            </div>
        </div>
    </div>

    <!-- الوسط: القائمة -->
    <div class="nav-menu-main">
        <div class="nav-item active" data-page="home"><?= __('home') ?></div>
        <div class="nav-item" data-page="posts"><?= __('posts') ?></div>
        <div class="nav-item" data-page="resources"><?= __('resources') ?></div>
        <div class="nav-item" data-page="chat">
             <?= __('chat') ?>
            <span id="chat-badge" style="background:var(--danger);color:white;padding:2px 6px;border-radius:10px;font-size:10px;margin-inline-start:4px;">0</span>
        </div>
        <div class="nav-item" data-page="chatbot"><?= __('bot') ?></div>

        <div class="nav-dropdown">
            <div class="nav-item" title="<?= __('services') ?>" style="font-size: 20px; padding: 4px 10px;">☰</div>
            <div class="nav-dropdown-menu">
                <div class="nav-item" data-page="gpa"><?= __('gpa_tracker') ?></div>
                <div class="nav-item" data-page="lost"><?= __('lost') ?></div>
                <div class="nav-item" data-page="map"><?= __('map') ?></div>
                <div class="nav-item" data-page="events"><?= __('events') ?></div>
                <div class="nav-item" data-page="suggestions">💡 <?= __('suggestions') ?></div>
            </div>
        </div>
    </div>

    <!-- الأكشن (اليسار) -->
    <div class="nav-actions-main">
        <div style="position:relative; cursor:pointer;" onclick="toggleNotifs()">
            <span style="font-size:20px; display:inline-block; padding-top:4px;">🔔</span>
            <span id="notif-badge" style="position:absolute; top:-2px; right:-6px; background:var(--danger); color:white; font-size:10px; font-weight:bold; padding:2px 6px; border-radius:10px; display:none;">0</span>

            <div id="notif-dropdown" style="display:none; position:absolute; top:45px; inset-inline-end:-10px; background:var(--card); border:1px solid var(--border); border-radius:14px; width:300px; box-shadow:var(--shadow); z-index:999; padding:16px; max-height:350px; overflow-y:auto; cursor:default;">
                <h4 style="margin:0 0 12px; font-size:15px; border-bottom:1px solid var(--border); padding-bottom:10px; color:var(--text);"><?= __('notifications') ?></h4>
                <div id="notif-list" style="font-size:13px; color:var(--muted); line-height:1.6;"></div>
            </div>
        </div>

        <button class="icon-btn" onclick="toggleTheme()" title="تغيير المظهر">🌓</button>
        <button class="icon-btn" onclick="toggleLang()" style="font-weight:bold;">🌐 <?= $lang === 'ar' ? 'EN' : 'ع' ?></button>

        <div class="nav-dropdown">
            <div class="user-avatar-small" style="background:<?= htmlspecialchars($user['avatar_color']) ?>">
                <?php if (!empty($user['avatar_image'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar_image']) ?>" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <?= mb_substr($user['full_name'], 0, 1) ?>
                <?php endif; ?>
            </div>
            <div class="nav-dropdown-menu">
                <div style="padding: 12px; border-bottom: 1px solid var(--border); margin-bottom: 8px;">
                    <div style="font-weight:800; font-size:15px; color:var(--text); margin-bottom:4px;"><?= htmlspecialchars($user['full_name']) ?></div>
                    <div style="font-size:12px; color:var(--muted);"><?= htmlspecialchars($user['student_id']) ?></div>
                </div>
                <div class="nav-item" data-page="profile">👤 <?= __('edit_profile') ?></div>
                <?php if($user['role'] === 'admin'): ?>
                    <a href="admin.php" class="nav-item" style="text-decoration:none;">🎛 لوحة الإدارة</a>
                <?php endif; ?>
                <div class="nav-item" style="color: var(--danger); margin-top:8px;" onclick="doLogout()">🚪 <?= __('logout') ?></div>
            </div>
        </div>
    </div>
</nav>

<nav class="mobile-bottom-nav">
    <div class="nav-item active" data-page="home"><span class="ni">🏠</span><?= __('home') ?></div>
    <div class="nav-item" data-page="posts"><span class="ni">📰</span><?= __('posts') ?></div>
    <div class="nav-item" data-page="resources"><span class="ni">📚</span><?= __('resources') ?></div>
    <div class="nav-item" data-page="chat"><span class="ni">💬</span><?= __('chat') ?></div>
    <div class="nav-item" data-page="chatbot"><span class="ni">🤖</span><?= __('bot') ?></div>
</nav>

<div class="main">
  <div class="page-title-section">
      <h2 id="page-title"><?= __('home') ?></h2>
      <p id="page-sub"><?= __('hello') ?> <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?> 👋</p>
  </div>

  <div class="content content-wrapper">

    <div class="section active" id="page-home" style="padding:0;">

      <!-- stats تظهر هنا فقط على الشاشات الصغيرة (تختفي على الكبيرة لأنها في الـ navbar) -->
      <div class="home-stats-row">
        <div class="stat-pill" onclick="navigate('chat')">
          <div class="sp-icon" style="color:#6366f1;">💬</div>
          <div class="sp-val" id="stat-chat-page">—</div>
          <div class="sp-lbl"><?= __('chat_msgs') ?></div>
        </div>
        <div class="stat-pill" onclick="navigate('lost')">
          <div class="sp-icon" style="color:#f59e0b;">🔍</div>
          <div class="sp-val" id="stat-lost-page">—</div>
          <div class="sp-lbl"><?= __('active_lost') ?></div>
        </div>
        <div class="stat-pill" onclick="navigate('events')">
          <div class="sp-icon" style="color:#10b981;">📅</div>
          <div class="sp-val" id="stat-events-page">—</div>
          <div class="sp-lbl"><?= __('up_events') ?></div>
        </div>
      </div>

      <div class="home-section-label">
        <span><?= $lang === 'ar' ? 'الخدمات السريعة' : 'Quick Services' ?></span>
        <span style="color:var(--accent);">⠿</span>
      </div>

      <div class="home-services-grid">
        <div class="hs-card" onclick="navigate('resources')" style="--hc:#6366f1;">
          <div class="hc-icon" style="background:#6366f122; color:#6366f1;">📚</div>
          <div class="hc-title"><?= __('resources') ?></div>
          <div class="hc-desc"><?= __('res_desc') ?></div>
        </div>
        <div class="hs-card" onclick="navigate('posts')" style="--hc:#ec4899;">
          <div class="hc-icon" style="background:#ec489922; color:#ec4899;">📰</div>
          <div class="hc-title"><?= __('posts') ?></div>
          <div class="hc-desc"><?= __('posts_desc') ?></div>
        </div>
        <div class="hs-card" onclick="navigate('suggestions')" style="--hc:#f59e0b;">
          <div class="hc-icon" style="background:#f59e0b22; color:#f59e0b;">💡</div>
          <div class="hc-title"><?= __('suggestions') ?></div>
          <div class="hc-desc"><?= __('sugg_desc') ?></div>
        </div>
        <div class="hs-card" onclick="navigate('gpa')" style="--hc:#10b981;">
          <div class="hc-icon" style="background:#10b98122; color:#10b981;">📈</div>
          <div class="hc-title"><?= __('gpa_tracker') ?></div>
          <div class="hc-desc"><?= $lang === 'ar' ? 'احسب معدلك التراكمي بسهولة' : 'Calculate your GPA easily' ?></div>
        </div>
        <div class="hs-card" onclick="navigate('chatbot')" style="--hc:#8b5cf6;">
          <div class="hc-icon" style="background:#8b5cf622; color:#8b5cf6;">🤖</div>
          <div class="hc-title"><?= __('bot') ?></div>
          <div class="hc-desc"><?= __('bot_desc') ?></div>
        </div>
        <div class="hs-card" onclick="navigate('lost')" style="--hc:#0ea5e9;">
          <div class="hc-icon" style="background:#0ea5e922; color:#0ea5e9;">🔍</div>
          <div class="hc-title"><?= __('lost_sys') ?></div>
          <div class="hc-desc"><?= __('lost_desc') ?></div>
        </div>
      </div>

      <div class="home-bottom-row">
        <div class="home-bottom-card">
          <div class="hbc-header">
            <span><?= $lang === 'ar' ? 'الفعاليات القادمة' : 'Upcoming Events' ?></span>
            <a class="hbc-link" onclick="navigate('events')"><?= $lang === 'ar' ? 'عرض الكل' : 'View All' ?></a>
          </div>
          <div class="ev-filter-pills">
            <button class="ev-pill active" onclick="filterHomeEvents('all', this)"><?= $lang === 'ar' ? 'الكل' : 'All' ?></button>
            <button class="ev-pill" onclick="filterHomeEvents('تقني', this)" style="color:#6366f1;"><?= $lang === 'ar' ? 'تقني' : 'Tech' ?></button>
            <button class="ev-pill" onclick="filterHomeEvents('علمي', this)" style="color:#10b981;"><?= $lang === 'ar' ? 'علمي' : 'Academic' ?></button>
          </div>
          <div id="home-events-list" style="display:flex; flex-direction:column; gap:10px; margin-top:4px;"></div>
        </div>

        <div class="home-bottom-card">
          <div class="hbc-header">
            <span><?= $lang === 'ar' ? 'آخر الأنشطة' : 'Recent Activity' ?></span>
          </div>
          <div id="home-activity-list" style="display:flex; flex-direction:column; gap:0;"></div>
        </div>
      </div>
    </div>

    <!-- Resources -->
    <div class="section" id="page-resources" style="padding:0;">
      <div class="section-header" style="margin-bottom:20px;">
        <button class="btn-submit" style="margin:0;" onclick="document.getElementById('res-form').classList.toggle('open')">+ <?= __('add_resource') ?></button>
      </div>
      <div class="lost-form" id="res-form" style="margin-bottom:24px;">
        <div class="form-row">
          <div class="form-field"><label><?= __('res_title') ?></label><input type="text" id="res-title" placeholder="مثال: ملخص شامل لبرمجة الويب"></div>
          <div class="form-field"><label><?= __('course_name') ?></label><input type="text" id="res-course" placeholder="مثال: برمجة ويب"></div>
        </div>
        <div class="form-field" style="margin-bottom:15px;">
          <label><?= __('res_file') ?></label>
          <input type="file" id="res-file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip" style="background:var(--card2); padding:8px;">
        </div>
        <button class="btn-submit" onclick="submitResource()"><?= __('add_resource') ?></button>
        <div id="res-msg" style="margin-top:10px; font-size:13px; color:var(--muted);"></div>
      </div>
      <div id="res-list" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:16px;"></div>
    </div>

    <!-- GPA -->
    <div class="section" id="page-gpa" style="padding:0;">
      <div class="section-header" style="margin-bottom:20px;">
        <h3 id="gpa-total-display" style="color:var(--accent); margin:0; font-size:24px; font-weight:800;"><?= __('your_gpa') ?> 0.00</h3>
      </div>
      <div class="lost-form open" style="display:grid; grid-template-columns: 2fr 1fr 1fr auto; gap:12px; align-items:end; margin-bottom:24px; padding:20px;">
        <div class="form-field"><label><?= __('course_name') ?></label><input type="text" id="gpa-course" placeholder="..."></div>
        <div class="form-field"><label><?= __('credits') ?></label><input type="number" id="gpa-credits" min="1" max="6" value="3"></div>
        <div class="form-field">
            <label><?= __('grade') ?></label>
            <select id="gpa-grade">
                <option value="A+">A+ (5.00)</option><option value="A">A (4.75)</option><option value="B+">B+ (4.50)</option>
                <option value="B">B (4.00)</option><option value="C+">C+ (3.50)</option><option value="C">C (3.00)</option>
                <option value="D+">D+ (2.50)</option><option value="D">D (2.00)</option><option value="F">F (1.00)</option>
            </select>
        </div>
        <button class="btn-submit" onclick="addGPA()" style="margin-top:0; height:44px; white-space:nowrap; padding:0 20px;"><?= __('add_course') ?></button>
      </div>
      <div id="gpa-list" style="display:flex; flex-direction:column; gap:12px;"></div>
    </div>

    <!-- ✅ صفحة الملف الشخصي — مصلحة تماماً -->
    <div class="section" id="page-profile" style="padding:0;">
      <div class="profile-grid">

        <!-- بطاقة التعديل -->
        <div class="profile-card">
          <h4 style="margin-top:0; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:20px; color:var(--accent); display:flex; align-items:center; gap:8px;">✏️ <?= __('edit_profile') ?></h4>
          <form id="profile-form" onsubmit="updateProfile(event)">
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px; background:var(--bg); padding:16px; border-radius:12px; border:1px dashed var(--border); flex-wrap:wrap;">
              <div style="width:65px; height:65px; border-radius:50%; background:<?= htmlspecialchars($user['avatar_color']) ?>; display:flex; align-items:center; justify-content:center; color:white; font-size:24px; font-weight:bold; flex-shrink:0; overflow:hidden;">
                <?php if (!empty($user['avatar_image'])): ?><img src="<?= htmlspecialchars($user['avatar_image']) ?>" style="width:100%;height:100%;object-fit:cover;"><?php else: ?><?= mb_substr($user['full_name'], 0, 1) ?><?php endif; ?>
              </div>
              <div class="form-field" style="flex:1; margin:0; min-width:160px;"><label><?= __('upload_avatar') ?></label><input type="file" id="p-avatar" accept="image/png, image/jpeg, image/jpg, image/webp" style="background:var(--card2); padding:8px; width:100%; box-sizing:border-box;"></div>
            </div>
            <div class="form-field" style="margin-bottom:16px;"><label><?= __('name') ?></label><input type="text" id="p-name" value="<?= htmlspecialchars($user['full_name']) ?>" required></div>
            <div class="form-field" style="margin-bottom:16px;"><label><?= __('bio') ?></label><textarea id="p-bio" rows="3" placeholder="تحدث عن نفسك..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea></div>
            <div class="form-field" style="margin-bottom:24px;"><label><?= __('accent_color') ?></label><input type="color" id="p-color" value="<?= htmlspecialchars($user['avatar_color']) ?>" style="height:44px; width:60px; padding:2px; cursor:pointer; background:var(--card2); border-radius:8px;"></div>
            <button type="submit" class="btn-submit" style="width:100%; height:48px; font-size:16px; border-radius:12px;"><?= __('save_changes') ?></button>
            <div id="profile-msg" style="margin-top:12px; font-size:13px; font-weight:bold; text-align:center;"></div>
          </form>
        </div>

        <!-- بطاقات الجانب -->
        <div style="display:flex; flex-direction:column; gap:24px; min-width:0;">
          <div class="profile-card">
            <h4 style="margin-top:0; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:20px; color:var(--accent); display:flex; align-items:center; gap:8px;">🎓 <?= __('account_info') ?></h4>
            <div class="form-field" style="margin-bottom:16px;"><label><?= __('email') ?> <span style="font-size:10px; color:var(--muted); font-weight:normal;">(<?= __('read_only') ?>)</span></label><input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.6; cursor:not-allowed;"></div>
            <div class="form-field"><label><?= __('sid') ?> <span style="font-size:10px; color:var(--muted); font-weight:normal;">(<?= __('read_only') ?>)</span></label><input type="text" value="<?= htmlspecialchars($user['student_id']) ?>" disabled style="opacity:0.6; cursor:not-allowed;"></div>
          </div>

          <div class="profile-card">
            <h4 style="margin-top:0; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:20px; color:#e53e3e; display:flex; align-items:center; gap:8px;">🔒 <?= __('security') ?></h4>
            <form id="password-form" onsubmit="updatePassword(event)">
              <div class="form-field" style="margin-bottom:16px;"><label><?= __('current_pass') ?></label><input type="password" id="p-curr-pass" required placeholder="••••••••"></div>
              <div class="password-grid">
                  <div class="form-field"><label><?= __('new_pass') ?></label><input type="password" id="p-new-pass" required placeholder="••••••••"></div>
                  <div class="form-field"><label><?= __('pass_conf') ?></label><input type="password" id="p-conf-pass" required placeholder="••••••••"></div>
              </div>
              <button type="submit" class="btn-submit" style="width:100%; height:48px; border-radius:12px; background:var(--card2); color:var(--text); border:1px solid var(--border);"><?= __('save_pass') ?></button>
              <div id="pass-msg" style="margin-top:12px; font-size:13px; font-weight:bold; text-align:center;"></div>
            </form>
          </div>
        </div>

      </div>
    </div>

    <!-- Posts -->
    <div class="section" id="page-posts" style="padding:0;">
      <div class="lost-form open" style="margin-bottom:24px; padding:20px; border-radius:16px;">
        <div class="form-field">
          <textarea id="post-input" placeholder="<?= __('write_post') ?>" style="min-height:90px; margin-bottom:10px; border-radius:12px;"></textarea>
          <label style="font-size:12px; color:var(--muted); margin-bottom:6px;">إرفاق صورة مع المنشور (اختياري)</label>
          <input type="file" id="post-image" accept="image/png, image/jpeg, image/jpg, image/webp" style="background:var(--bg); border: 1px dashed var(--border); padding: 10px; border-radius:10px;">
        </div>
        <button class="btn-submit" onclick="submitPost()" style="margin-top:12px; width:160px;"><?= __('add_post') ?></button>
      </div>
      <div id="posts-list" style="display:flex; flex-direction:column; gap:20px; max-width:750px; margin:0 auto;"></div>
    </div>

    <!-- Suggestions -->
    <div class="section" id="page-suggestions" style="padding:0;">
      <div class="lost-form open" style="margin-bottom:24px; padding:20px; border-radius:16px;">
        <div class="form-field"><textarea id="sugg-input" placeholder="<?= __('write_sugg') ?>" style="min-height:90px; border-radius:12px;"></textarea></div>
        <button class="btn-submit" onclick="submitSuggestion()" style="margin-top:12px; width:160px; background:var(--gold); color:#111;"><?= __('add_sugg') ?></button>
      </div>
      <div id="sugg-list" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;"></div>
    </div>

    <!-- Chat -->
    <div class="section" id="page-chat" style="padding:0; height: calc(100vh - 170px); background:var(--card); border:1px solid var(--border); border-radius:16px;">
      <div class="chat-wrap" style="height:100%;">
        <div class="chat-messages" id="chat-messages"></div>
        <div class="chat-input-bar" style="border-radius: 0 0 16px 16px;">
          <input type="text" id="chat-input" placeholder="<?= __('type_here') ?>" onkeydown="if(event.key==='Enter') sendChat()">
          <button class="send-btn" onclick="sendChat()">➤</button>
        </div>
      </div>
    </div>

    <!-- Chatbot -->
    <div class="section" id="page-chatbot" style="padding:0; height: calc(100vh - 170px); background:var(--card); border:1px solid var(--border); border-radius:16px;">
      <div class="bot-wrap" style="height:100%;">
        <div class="bot-messages" id="bot-messages">
          <div class="bot-msg"><div class="b-icon"></div><div class="b-bubble"><?= $lang === 'ar' ? "أهلاً وسهلاً! 👋 أنا المستشار الذكي لكلية الاتصالات.\nبماذا يمكنني مساعدتك اليوم؟" : "Hello! 👋 I'm the AI assistant for the Telecom College.\nHow can I help you today?" ?></div></div>
        </div>
        <div class="chat-input-bar" style="border-radius: 0 0 16px 16px;">
          <input type="text" id="bot-input" placeholder="<?= __('ask_here') ?>" onkeydown="if(event.key==='Enter') sendBot()">
          <button class="send-btn" onclick="sendBot()">➤</button>
        </div>
      </div>
    </div>

    <!-- Lost & Found -->
    <div class="section" id="page-lost" style="padding:0;">
      <div class="section-header" style="margin-bottom:20px;">
        <button class="btn-add" onclick="toggleLostForm()">+ <?= __('add_report') ?></button>
      </div>
      <div class="lost-form" id="lost-form">
        <div class="form-row"><div class="form-field"><label><?= __('item_name') ?></label><input type="text" id="lf-name"></div><div class="form-field"><label><?= __('loss_loc') ?></label><input type="text" id="lf-loc"></div></div>
        <div class="form-row">
          <div class="form-field"><label><?= __('contact_info') ?></label><input type="text" id="lf-contact"></div>
          <div class="form-field"><label><?= __('status') ?></label><select id="lf-status"><option value="مفقود"><?= __('status_lost') ?></option><option value="وُجد"><?= __('status_found') ?></option></select></div>
        </div>
        <div class="form-field"><label><?= __('desc') ?></label><textarea id="lf-desc"></textarea></div>
        <button class="btn-submit" onclick="submitLost()"><?= __('send_report') ?></button>
      </div>
      <div class="lost-grid" id="lost-list"></div>
    </div>

    <!-- Map -->
    
    <div class="section" id="page-map" style="padding:0; overflow:hidden;">
      <div class="map-section">
        <div class="map-canvas" style="max-width: 900px;">
          <svg viewBox="0 0 430 310" preserveAspectRatio="xMidYMid meet" id="map-svg"
               style="background:var(--card2); display:block; width:100%; height:auto;">

            <defs>
              <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="var(--border)" stroke-width="0.3"/>
              </pattern>
            </defs>
            <rect width="430" height="310" fill="url(#grid)"/>

            <rect x="10" y="10" width="45" height="50" rx="3" fill="var(--accent-glow)" stroke="var(--accent)" stroke-width="1"/>
            <text x="32.5" y="37" fill="var(--accent)" font-size="8" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif">STC</text>

            <rect x="10" y="70" width="45" height="120" rx="2" fill="rgba(255,184,0,0.08)" stroke="var(--gold)" stroke-width="1" stroke-dasharray="4,2"/>
            <text x="32.5" y="132" fill="var(--gold)" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مواقف' : 'Parking' ?></text>

            <rect x="65" y="10" width="30" height="50" rx="2" fill="rgba(255,184,0,0.08)" stroke="var(--gold)" stroke-width="1" stroke-dasharray="4,2"/>
            <text x="80" y="37" fill="var(--gold)" font-size="6" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مواقف' : 'Parking' ?></text>

            <rect x="105" y="10" width="60" height="50" rx="3" fill="none" stroke="var(--border)" stroke-width="1"/>

            <rect x="65" y="70" width="30" height="120" rx="2" fill="rgba(255,184,0,0.08)" stroke="var(--gold)" stroke-width="1" stroke-dasharray="4,2"/>
            <text x="80" y="132" fill="var(--gold)" font-size="6" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مواقف' : 'Parking' ?></text>

            <rect x="105" y="70" width="35" height="30" rx="3" fill="rgba(16,185,129,0.1)" stroke="#10B981" stroke-width="1"/>
            <text x="122.5" y="87" fill="#10B981" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'المسجد' : 'Mosque' ?></text>

            <rect x="105" y="110" width="35" height="50" rx="3" fill="var(--accent-glow)" stroke="var(--accent)" stroke-width="1"/>
            <text x="122.5" y="132" fill="var(--accent)" font-size="6" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif">
              <tspan x="122.5" dy="-4"><?= $lang == 'ar' ? 'مبنى 1' : 'Bldg 1' ?></tspan>
              <tspan x="122.5" dy="12"><?= $lang == 'ar' ? 'العمادة' : 'Deanship' ?></tspan>
            </text>

            <rect x="70" y="222" width="45" height="30" rx="3" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="1"/>
            <text x="93" y="240" fill="#7C3AED" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 18' : 'Bldg 18' ?></text>

            <rect x="175" y="10" width="70" height="50" rx="2" fill="rgba(255,184,0,0.08)" stroke="var(--gold)" stroke-width="1" stroke-dasharray="4,2"/>
            <text x="210" y="37" fill="var(--gold)" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مواقف' : 'Parking' ?></text>

            <rect x="160" y="70" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="175" y="80" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 10' : 'Bldg 10' ?></text>
            <rect x="200" y="70" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="215" y="80" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 9' : 'Bldg 9' ?></text>

            <rect x="160" y="90" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="175" y="100" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 11' : 'Bldg 11' ?></text>
            <rect x="200" y="90" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="215" y="100" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 8' : 'Bldg 8' ?></text>

            <rect x="160" y="115" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="175" y="125" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 12' : 'Bldg 12' ?></text>
            <rect x="200" y="115" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="215" y="125" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 7' : 'Bldg 7' ?></text>

            <rect x="160" y="135" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="175" y="145" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 13' : 'Bldg 13' ?></text>
            <rect x="200" y="135" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="215" y="145" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 6' : 'Bldg 6' ?></text>

            <rect x="160" y="160" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="175" y="170" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 2' : 'Bldg 2' ?></text>
            <rect x="200" y="160" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="215" y="170" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 5' : 'Bldg 5' ?></text>

            <rect x="160" y="180" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="175" y="190" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 3' : 'Bldg 3' ?></text>
            <rect x="200" y="180" width="30" height="15" rx="2" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="0.8"/>
            <text x="215" y="190" fill="#7C3AED" font-size="5" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 4' : 'Bldg 4' ?></text>

            <rect x="160" y="200" width="70" height="20" rx="3" fill="rgba(34,197,94,0.1)" stroke="#22c55e" stroke-width="1"/>
            <text x="195" y="213" fill="#22c55e" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'الصالة الرياضية' : 'Gym' ?></text>

            <rect x="180" y="235" width="40" height="35" rx="3" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="1"/>
            <text x="200" y="255" fill="#7C3AED" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 21' : 'Bldg 21' ?></text>

            <rect x="250" y="80" width="35" height="35" rx="3" fill="rgba(255,184,0,0.1)" stroke="var(--gold)" stroke-width="1"/>
            <text x="267.5" y="100" fill="var(--gold)" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مجلس' : 'Council' ?></text>

            <rect x="300" y="80" width="60" height="40" rx="3" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="1"/>
            <text x="330" y="102" fill="#7C3AED" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 23' : 'Bldg 23' ?></text>

            <rect x="370" y="80" width="45" height="40" rx="3" fill="rgba(34,197,94,0.05)" stroke="#22c55e" stroke-width="1" stroke-dasharray="2,2"/>
            <text x="392.5" y="102" fill="#22c55e" font-size="6" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'ملاعب كبيرة' : 'Courts' ?></text>

            <rect x="370" y="130" width="45" height="40" rx="3" fill="rgba(100,100,100,0.1)" stroke="var(--muted)" stroke-width="1"/>
            <text x="392.5" y="152" fill="var(--muted)" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مغلق' : 'Closed' ?></text>

            <rect x="250" y="130" width="35" height="50" rx="3" fill="rgba(34,197,94,0.1)" stroke="#22c55e" stroke-width="1"/>
            <text x="267.5" y="152" fill="#22c55e" font-size="6" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif">
              <tspan x="267.5" dy="-4"><?= $lang == 'ar' ? 'ملعب' : 'Football' ?></tspan>
              <tspan x="267.5" dy="12"><?= $lang == 'ar' ? 'الكروة' : 'Pitch' ?></tspan>
            </text>

            <rect x="295" y="130" width="45" height="25" rx="3" fill="rgba(34,197,94,0.1)" stroke="#22c55e" stroke-width="1"/>
            <text x="317.5" y="145" fill="#22c55e" font-size="6" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'ملعب الطائرة' : 'Volleyball' ?></text>

            <rect x="295" y="165" width="65" height="45" rx="3" fill="rgba(124,58,237,0.1)" stroke="#7C3AED" stroke-width="1"/>
            <text x="327.5" y="190" fill="#7C3AED" font-size="7" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مبنى 24' : 'Bldg 24' ?></text>

            <rect x="250" y="220" width="165" height="60" rx="3" fill="rgba(255,184,0,0.08)" stroke="var(--gold)" stroke-width="1" stroke-dasharray="4,2"/>
            <text x="332.5" y="252" fill="var(--gold)" font-size="8" text-anchor="middle" font-weight="bold" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'مواقف' : 'Parking' ?></text>

            <text x="215" y="300" fill="var(--muted)" font-size="8" text-anchor="middle" font-family="Tajawal, sans-serif"><?= $lang == 'ar' ? 'خريطة كلية الاتصالات' : 'Telecom College Map' ?></text>
          </svg>
        </div>
      </div>
    </div>

    <!-- Events -->
    <div class="section" id="page-events" style="padding:0;">
      <div class="events-grid" id="events-grid"></div>
    </div>

  </div>
</div>

<script>
// =================== CONFIG & SETUP ===================
const ME = { id: <?= (int)$user['id'] ?>, name: "<?= addslashes($user['full_name']) ?>", color: "<?= addslashes($user['avatar_color']) ?>" };
const LANG = "<?= $lang ?>";

const pageTitles = {
  home:        ["<?= __('home') ?>",        "<?= __('hello') ?> " + ME.name.split(' ')[0] + " 👋"],
  profile:     ["<?= __('profile') ?>",     "إدارة حسابك الشخصي وإعدادات الأمان"],
  posts:       ["<?= __('posts') ?>",       "<?= __('posts_desc') ?>"],
  resources:   ["<?= __('resources') ?>",   "<?= __('res_desc') ?>"],
  suggestions: ["<?= __('suggestions') ?>", "<?= __('sugg_desc') ?>"],
  chat:        ["<?= __('chat') ?>",        "<?= __('chat_desc') ?>"],
  chatbot:     ["<?= __('bot') ?>",         "اسأل الذكاء الاصطناعي الخاص بالكلية"],
  lost:        ["<?= __('lost_sys') ?>",    "أبلغ عن مفقوداتك أو ما وجدته"],
  map:         ["<?= __('map') ?>",         "خريطة مباني ومرافق الكلية"],
  events:      ["<?= __('events') ?>",      "أهم الفعاليات الأكاديمية والتقنية"],
  gpa:         ["<?= __('gpa_tracker') ?>", "تابع وخطط لمعدلك التراكمي"],
};

function toggleTheme() {
    const cur = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', cur);
    localStorage.setItem('theme', cur);
}
function toggleLang() {
    const cur = document.documentElement.lang === 'ar' ? 'en' : 'ar';
    document.cookie = "lang=" + cur + "; path=/; max-age=31536000";
    window.location.reload();
}

function navigate(page) {
    document.querySelectorAll('.nav-item').forEach(el => el.classList.toggle('active', el.dataset.page === page));
    document.querySelectorAll('.section').forEach(el => el.classList.toggle('active', el.id === 'page-' + page));
    const [title, sub] = pageTitles[page] || [page, ''];
    document.getElementById('page-title').textContent = title;
    document.getElementById('page-sub').textContent   = sub;
    if(window.innerWidth <= 900) window.scrollTo({top: 0, behavior: 'smooth'});
    if (page === 'chat')        loadChat();
    if (page === 'lost')        loadLost();
    if (page === 'events')      loadEvents();
    if (page === 'posts')       loadPosts();
    if (page === 'suggestions') loadSuggestions();
    if (page === 'gpa')         loadGPA();
    if (page === 'resources')   loadResources();
}

document.querySelectorAll('.nav-item[data-page]').forEach(el => {
    el.addEventListener('click', () => navigate(el.dataset.page));
});

// =================== RESOURCES ===================
function loadResources() {
    fetch('handler.php?action=res_get').then(r => r.json()).then(data => {
        const list = document.getElementById('res-list'); list.innerHTML = '';
        (data.resources || []).forEach(res => {
            let av = res.avatar_image ? `<img src="${res.avatar_image}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : res.full_name.charAt(0);
            let delBtn = (res.user_id == ME.id) ? `<button class="icon-btn" style="border:none; color:#e53e3e; background:transparent;" onclick="deleteResource(${res.id})">🗑</button>` : '';
            list.innerHTML += `<div class="event-card" style="border-top:3px solid var(--accent); display:flex; flex-direction:column; border-radius:16px;"><div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;"><div><span class="ev-tag" style="background:var(--accent-glow); color:var(--accent); margin-bottom:8px;">📘 ${escHtml(res.course_name)}</span><div style="font-weight:800; font-size:16px; margin-bottom:8px; color:var(--text);">${escHtml(res.title)}</div></div>${delBtn}</div><div style="display:flex; align-items:center; gap:10px; margin-bottom:20px; margin-top:auto;"><div style="width:28px; height:28px; border-radius:50%; background:${res.avatar_color}; display:flex; align-items:center; justify-content:center; color:white; font-size:12px; font-weight:bold;">${av}</div><span style="font-size:13px; color:var(--muted); font-weight:600;">${escHtml(res.full_name)}</span></div><a href="${res.file_path}" target="_blank" style="text-decoration:none;"><button class="btn-submit" style="width:100%; height:42px; display:flex; align-items:center; justify-content:center; gap:8px; background:var(--card2); color:var(--text); border:1px solid var(--border); border-radius:12px;">📥 <?= __('download') ?></button></a></div>`;
        });
    });
}
function submitResource() {
    const title = document.getElementById('res-title').value.trim();
    const course = document.getElementById('res-course').value.trim();
    const file = document.getElementById('res-file').files[0];
    const msgBox = document.getElementById('res-msg');
    if (!title || !course || !file) { msgBox.textContent = "الرجاء ملء جميع الحقول وإرفاق الملف."; msgBox.style.color = "#e53e3e"; return; }
    msgBox.textContent = "جاري الرفع..."; msgBox.style.color = "var(--muted)";
    const fd = new FormData(); fd.append('title', title); fd.append('course_name', course); fd.append('resource_file', file);
    fetch('handler.php?action=res_add', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
        if (data.success) { document.getElementById('res-title').value = ''; document.getElementById('res-course').value = ''; document.getElementById('res-file').value = ''; msgBox.textContent = ""; document.getElementById('res-form').classList.remove('open'); loadResources(); }
        else { msgBox.textContent = data.error || "فشل الرفع."; msgBox.style.color = "#e53e3e"; }
    });
}
function deleteResource(id) {
    if (!confirm('<?= __('confirm_delete') ?>')) return;
    const fd = new FormData(); fd.append('id', id); fetch('handler.php?action=res_delete', { method: 'POST', body: fd }).then(() => loadResources());
}

// =================== POSTS & SUGG ===================
function loadPosts() {
    fetch('handler.php?action=get_posts').then(r => r.json()).then(data => {
        const list = document.getElementById('posts-list'); list.innerHTML = '';
        (data.posts || []).forEach(p => {
            let av = p.user_avatar ? `<img src="${p.user_avatar}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : p.user_name.charAt(0);
            let imageHtml = p.post_image ? `<div style="margin-top:12px; border-radius:12px; overflow:hidden; border:1px solid var(--border);"><img src="${p.post_image}" style="width:100%; max-height:450px; object-fit:cover; display:block;"></div>` : '';
            list.innerHTML += `<div class="lost-item" style="align-items:flex-start; flex-direction:column; gap:12px; padding:20px; border-radius:16px;"><div style="display:flex; gap:14px; width:100%;"><div class="av" style="background:${p.user_color}; width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:16px; flex-shrink:0;">${av}</div><div class="li-info" style="flex:1;"><div class="li-name" style="margin-bottom:4px; font-size:15px;">${escHtml(p.user_name)}</div><div style="font-size:11px; color:var(--muted); margin-bottom:10px;">${p.date}</div>${p.content ? `<div style="font-size:14.5px; line-height:1.6; color:var(--text); white-space:pre-wrap;">${escHtml(p.content)}</div>` : ''}</div></div>${imageHtml}</div>`;
        });
    });
}
function submitPost() {
    const content = document.getElementById('post-input').value.trim(); const imageFile = document.getElementById('post-image').files[0];
    if (!content && !imageFile) return;
    const fd = new FormData(); if (content) fd.append('content', content); if (imageFile) fd.append('post_image', imageFile);
    fetch('handler.php?action=add_post', { method: 'POST', body: fd }).then(r => r.json()).then(data => { if (data.success) { document.getElementById('post-input').value = ''; document.getElementById('post-image').value = ''; loadPosts(); } });
}
function loadSuggestions() {
    fetch('handler.php?action=get_sugg').then(r => r.json()).then(data => {
        const list = document.getElementById('sugg-list'); list.innerHTML = '';
        (data.suggestions || []).forEach(s => {
            let av = s.user_avatar ? `<img src="${s.user_avatar}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : s.user_name.charAt(0);
            list.innerHTML += `<div class="event-card" style="border-top: 3px solid var(--gold); border-radius:16px;"><div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;"><div style="background:${s.user_color}; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:bold; flex-shrink:0;">${av}</div><div><div style="font-size:14px; font-weight:800; color:var(--text);">${escHtml(s.user_name)}</div><div style="font-size:11px; color:var(--muted);">${s.date}</div></div></div><div style="font-size:14px; line-height:1.6; color:var(--text); white-space:pre-wrap;">${escHtml(s.content)}</div></div>`;
        });
    });
}
function submitSuggestion() {
    const content = document.getElementById('sugg-input').value.trim(); if (!content) return;
    const fd = new FormData(); fd.append('content', content);
    fetch('handler.php?action=add_sugg', { method: 'POST', body: fd }).then(r => r.json()).then(data => { if (data.success) { document.getElementById('sugg-input').value = ''; loadSuggestions(); } });
}

// =================== CHAT & BOT ===================
function loadChat() {
    fetch('handler.php?action=chat_get').then(r => r.json()).then(data => {
        const box = document.getElementById('chat-messages'); box.innerHTML = '';
        (data.messages || []).forEach(m => appendChatMsg(m, false));
        const count = (data.messages || []).length;
        document.getElementById('chat-badge').textContent = count;
        // تحديث الـ stats في الـ navbar وفي الصفحة
        setStatVal('stat-chat', count); setStatVal('stat-chat-page', count);
        box.scrollTop = box.scrollHeight;
    });
}
function appendChatMsg(m, scroll = true) {
    const box = document.getElementById('chat-messages'); const div = document.createElement('div'); div.className = 'chat-msg';
    let avatarHtml = m.avatar_image ? `<div class="av" style="background:${m.avatar_color}"><img src="${m.avatar_image}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"></div>` : `<div class="av" style="background:${m.avatar_color}">${m.full_name.charAt(0)}</div>`;
    div.innerHTML = `${avatarHtml}<div class="body"><div class="meta"><span class="uname">${m.full_name}</span><span class="time">${m.time || ''}</span></div><div class="bubble">${escHtml(m.message)}</div></div>`;
    box.appendChild(div); if (scroll) box.scrollTop = box.scrollHeight;
}
function sendChat() {
    const input = document.getElementById('chat-input'); const msg = input.value.trim(); if (!msg) return; input.value = '';
    const fd = new FormData(); fd.append('message', msg);
    fetch('handler.php?action=chat_send', { method: 'POST', body: fd }).then(r => r.json()).then(data => { if (data.success) appendChatMsg(data.message); });
}
function sendBot() {
    const input = document.getElementById('bot-input'); const msg = input.value.trim(); if (!msg) return; input.value = '';
    const box = document.getElementById('bot-messages');
    box.innerHTML += `<div class="user-msg"><div class="u-bubble">${escHtml(msg)}</div></div>`;
    const typingId = 'typing-' + Date.now();
    box.innerHTML += `<div class="bot-msg" id="${typingId}"><div class="b-icon"></div><div class="b-bubble"><span class="typing-dots"><span></span><span></span><span></span></span></div></div>`;
    box.scrollTop = box.scrollHeight;
    const fd = new FormData(); fd.append('message', msg);
    fetch('handler.php?action=bot_ask', { method: 'POST', body: fd })
        .then(r => r.json()).then(data => {
            const typingEl = document.getElementById(typingId); if(typingEl) typingEl.remove();
            box.innerHTML += `<div class="bot-msg"><div class="b-icon"></div><div class="b-bubble">${escHtml(data.reply)}</div></div>`;
            box.scrollTop = box.scrollHeight;
        }).catch(() => { const typingEl = document.getElementById(typingId); if(typingEl) typingEl.remove(); });
}

// =================== LOST & EVENTS ===================
function loadLost() {
    fetch('handler.php?action=lost_get').then(r => r.json()).then(data => {
        const list = document.getElementById('lost-list'); const items = data.items || [];
        const lostCount = items.filter(i => i.status === 'مفقود').length;
        setStatVal('stat-lost', lostCount); setStatVal('stat-lost-page', lostCount);
        list.innerHTML = items.map(i => `<div class="lost-item" style="border-radius:16px; padding:18px;"><div class="li-icon">📦</div><div class="li-info"><div class="li-name">${escHtml(i.item_name)}</div><div class="li-meta">📍 ${escHtml(i.location)} · 👤 ${escHtml(i.full_name)}</div></div><span class="badge-status ${i.status==='وُجد'?'found':'lost'}">${i.status==='وُجد'?'<?= __('status_found') ?>':'<?= __('status_lost') ?>'}</span></div>`).join('');
    });
}
function toggleLostForm() { document.getElementById('lost-form').classList.toggle('open'); }
function submitLost() {
    const fd = new FormData(); fd.append('item_name', document.getElementById('lf-name').value); fd.append('location', document.getElementById('lf-loc').value); fd.append('description', document.getElementById('lf-desc').value); fd.append('contact', document.getElementById('lf-contact').value); fd.append('status', document.getElementById('lf-status').value);
    fetch('handler.php?action=lost_add', { method: 'POST', body: fd }).then(r => r.json()).then(data => { if (data.success) { toggleLostForm(); loadLost(); } });
}

let _allHomeEvents = [];
function loadEvents() {
    fetch('handler.php?action=events_get').then(r => r.json()).then(data => {
        const events = data.events || [];
        setStatVal('stat-events', events.length); setStatVal('stat-events-page', events.length);
        document.getElementById('events-grid').innerHTML = events.map(e => `<div class="event-card" style="border-radius:16px;"><div style="position:absolute;top:0;right:0;left:0;height:4px;background:${e.color}"></div><span class="ev-tag" style="background:${e.color}22;color:${e.color}">🎫 ${escHtml(e.title)}</span><div class="ev-title">${escHtml(e.title)}</div><div class="ev-desc">${escHtml(e.description)}</div><div class="ev-meta">📅 ${escHtml(e.date)}</div></div>`).join('');
        _allHomeEvents = events; renderHomeEvents(events);
    });
}
function renderHomeEvents(events) {
    const list = document.getElementById('home-events-list'); if (!list) return;
    if (!events.length) { list.innerHTML = '<div style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0;">—</div>'; return; }
    list.innerHTML = events.slice(0, 4).map(e => {
        const d = e.date ? e.date.split('-') : ['','','']; const day = d[2] || '';
        return `<div class="home-ev-item" data-cat="${escHtml(e.category||'')}"><div class="hev-badge" style="background:${e.color}22; color:${e.color};"><span style="font-size:18px;font-weight:900;">${escHtml(day)}</span></div><div class="hev-info"><div class="hev-title">${escHtml(e.title)}</div><div class="hev-loc">📍 ${escHtml(e.description || '—')}</div></div></div>`;
    }).join('');
}
function filterHomeEvents(cat, btn) { document.querySelectorAll('.ev-pill').forEach(p => p.classList.remove('active')); btn.classList.add('active'); const filtered = cat === 'all' ? _allHomeEvents : _allHomeEvents.filter(e => (e.category || '') === cat); renderHomeEvents(filtered); }

function loadHomeActivity() {
    const actList = document.getElementById('home-activity-list'); if (!actList) return;
    Promise.all([
        fetch('handler.php?action=chat_get').then(r=>r.json()).catch(()=>({messages:[]})),
        fetch('handler.php?action=lost_get').then(r=>r.json()).catch(()=>({items:[]})),
        fetch('handler.php?action=resources_get').then(r=>r.json()).catch(()=>({resources:[]}))
    ]).then(([chatData, lostData, resData]) => {
        const items = [];
        (chatData.messages||[]).slice(-2).forEach(m => items.push({ dot:'#6366f1', text: `<?= $lang === 'ar' ? 'رسالة جديدة في الشات العام' : 'New message in Public Chat' ?>`, time: m.time||'' }));
        (resData.resources||[]).slice(-1).forEach(r => items.push({ dot:'#10b981', text: `<?= $lang === 'ar' ? 'تم رفع ملخص جديد في الموارد' : 'New resource uploaded' ?>`, time: '' }));
        (lostData.items||[]).filter(i=>i.status==='مفقود').slice(-1).forEach(i => items.push({ dot:'#f59e0b', text: `<?= $lang === 'ar' ? 'بلاغ مفقودات جديد في الحرم' : 'New lost item report' ?>`, time: '' }));
        if (!items.length) { actList.innerHTML = `<div style="color:var(--muted);font-size:13px;padding:20px 0;text-align:center;">—</div>`; return; }
        actList.innerHTML = items.map(a => `<div class="act-item"><div class="act-dot" style="background:${a.dot};"></div><div class="act-text">${escHtml(a.text)}</div><div class="act-time">${escHtml(a.time)}</div></div>`).join('');
    });
}

// =================== PROFILE & PASSWORD ===================
function updateProfile(e) {
    e.preventDefault(); const fd = new FormData();
    fd.append('full_name', document.getElementById('p-name').value); fd.append('bio', document.getElementById('p-bio').value); fd.append('color', document.getElementById('p-color').value);
    const file = document.getElementById('p-avatar').files[0]; if (file) fd.append('avatar', file);
    const msgBox = document.getElementById('profile-msg'); msgBox.textContent = 'جاري الحفظ...'; msgBox.style.color = 'var(--muted)';
    fetch('handler.php?action=update_profile', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
        if (data.success) { msgBox.textContent = '<?= __('update_success') ?>'; msgBox.style.color = '#22c55e'; setTimeout(() => window.location.reload(), 1000); }
        else { msgBox.textContent = 'حدث خطأ أثناء التحديث.'; msgBox.style.color = '#e53e3e'; }
    });
}
function updatePassword(e) {
    e.preventDefault(); const fd = new FormData(); fd.append('curr_pass', document.getElementById('p-curr-pass').value); fd.append('new_pass', document.getElementById('p-new-pass').value); fd.append('conf_pass', document.getElementById('p-conf-pass').value);
    const msgBox = document.getElementById('pass-msg'); msgBox.textContent = 'جاري التحقق...'; msgBox.style.color = 'var(--muted)';
    fetch('handler.php?action=change_password', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
        if (data.success) { msgBox.textContent = 'تم تحديث كلمة المرور بنجاح ✅'; msgBox.style.color = '#22c55e'; document.getElementById('password-form').reset(); }
        else { msgBox.textContent = data.error || 'حدث خطأ غير متوقع'; msgBox.style.color = '#e53e3e'; }
    });
}

// =================== GPA TRACKER ===================
const gradePoints = {'A+':5.0, 'A':4.75, 'B+':4.5, 'B':4.0, 'C+':3.5, 'C':3.0, 'D+':2.5, 'D':2.0, 'F':1.0};
function loadGPA() {
    fetch('handler.php?action=gpa_get').then(r => r.json()).then(data => {
        const list = document.getElementById('gpa-list'); list.innerHTML = ''; let totalP = 0, totalC = 0;
        (data.courses || []).forEach(c => {
            totalP += gradePoints[c.grade] * c.credits; totalC += parseInt(c.credits);
            list.innerHTML += `<div class="lost-item" style="display:flex; justify-content:space-between; padding:16px 24px; border-radius:12px;"><div><strong style="font-size:15px;">${escHtml(c.course_name)}</strong> <span style="color:var(--muted); font-size:12px; margin:0 12px; font-weight:600;">${c.credits} <?= __('credits') ?></span></div><div style="display:flex; align-items:center; gap:20px;"><strong style="color:var(--accent); font-size:16px;">${c.grade}</strong><button class="icon-btn" style="border:none; color:red; height:auto; padding:0; background:transparent;" onclick="deleteGPA(${c.id})">🗑</button></div></div>`;
        });
        const finalGPA = totalC > 0 ? (totalP / totalC).toFixed(2) : '0.00';
        document.getElementById('gpa-total-display').innerHTML = `<?= __('your_gpa') ?> ${finalGPA} <span style="font-size:14px;color:var(--muted);">/ 5.00</span>`;
    });
}
function addGPA() { const fd = new FormData(); fd.append('course_name', document.getElementById('gpa-course').value); fd.append('credits', document.getElementById('gpa-credits').value); fd.append('grade', document.getElementById('gpa-grade').value); fetch('handler.php?action=gpa_add', { method: 'POST', body: fd }).then(r => r.json()).then(d => { if(d.success) { document.getElementById('gpa-course').value=''; loadGPA(); } }); }
function deleteGPA(id) { const fd = new FormData(); fd.append('id', id); fetch('handler.php?action=gpa_delete', { method: 'POST', body: fd }).then(() => loadGPA()); }

// =================== NOTIFICATIONS ===================
function loadNotifs() {
    fetch('handler.php?action=notif_get').then(r => r.json()).then(data => {
        const badge = document.getElementById('notif-badge');
        if (data.unread > 0) { badge.textContent = data.unread; badge.style.display = 'inline-block'; } else { badge.style.display = 'none'; }
        const list = document.getElementById('notif-list'); list.innerHTML = '';
        if (data.notifications && data.notifications.length > 0) {
            data.notifications.forEach(n => {
                let bg = n.is_read == 0 ? 'var(--accent-glow)' : 'transparent';
                list.innerHTML += `<div style="padding:12px; border-bottom:1px solid var(--border); background:${bg}; border-radius:10px; margin-bottom:8px;"><strong style="color:var(--text); display:block; margin-bottom:4px; font-size:14px;">${n.title}</strong><span style="color:var(--muted);">${n.message}</span></div>`;
            });
        } else { list.innerHTML = '<?= __('no_notif') ?>'; }
    });
}
function toggleNotifs() {
    const drop = document.getElementById('notif-dropdown'); drop.style.display = drop.style.display === 'none' ? 'block' : 'none';
    if (drop.style.display === 'block') { fetch('handler.php?action=notif_read').then(() => setTimeout(loadNotifs, 1000)); }
}

// =================== HELPERS ===================
function setStatVal(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }
function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function doLogout() { fetch('handler.php?action=logout').then(() => window.location.href = 'login.php'); }

// Init
loadChat(); loadLost(); loadEvents(); loadNotifs(); loadHomeActivity();
setInterval(loadNotifs, 10000);
</script>
</body>
</html>