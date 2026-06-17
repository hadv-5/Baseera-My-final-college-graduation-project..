<?php
// ======[ Controller Init ]======
require_once 'config.php';

if (currentUser()) {
    header('Location: ' . (isAdmin() ? 'admin.php' : 'index.php'));
    exit;
}

$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);

// ======[ Access Control Catch ]======
if (isset($_GET['auth_req'])) {
    $error = $lang === 'ar' ? 'يرجى تسجيل الدخول أولاً للوصول إلى هذه الخدمة المحمية.' : 'Please login first to access this secure service.';
}

// ======[ Authentication Logic ]======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email && $password) {
        if (!preg_match('/@tvtc\.edu\.sa$/i', $email) && $email !== 'admin@college.edu') {
            $error = __('err_domain');
        } else {
            $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id'           => $user['id'],
                    'full_name'    => $user['full_name'],
                    'student_id'   => $user['student_id'],
                    'email'        => $user['email'],
                    'avatar_color' => $user['avatar_color'],
                    'avatar_image' => $user['avatar_image'],
                    'role'         => $user['role'],
                ];
                header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'index.php'));
                exit;
            }
            $error = __('err_login');
        }
    } else {
        $error = __('fill_all');
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __('login_link') ?> — <?= __('app_name') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script>
  // ======[ Theme Preload ]======
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
</script>
<style>
/* ======[ Reset & Theme Setup ]====== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --blue-main:   #1d4ed8; 
  --blue-mid:    #2563eb; 
  --blue-light:  #3b82f6; 
  --blue-pale:   #dbeafe;
  --navy:        #0f172a; 
  --slate:       #475569; 
  --muted:       #94a3b8; 
  --border:      #e2e8f0;
  --white:       #ffffff; 
  --bg:          #f0f4f8; 
  --input-bg:    #f8fafc; 
  --error:       #ef4444;
  --nav-bg:      rgba(255,255,255,.85);
  --right-bg:    #e8eef7;
  --font-ar:     'Tajawal', sans-serif; 
  --font-en:     'Plus Jakarta Sans', sans-serif;
  --radius:      12px; 
  --shadow-sm:   0 1px 3px rgba(0,0,0,.08); 
  --shadow-md:   0 4px 20px rgba(0,0,0,.08);
}

[data-theme="dark"] {
  --blue-main:   #2563eb; 
  --blue-mid:    #3b82f6; 
  --blue-light:  #60a5fa; 
  --blue-pale:   rgba(37,99,235,.15);
  --navy:        #e6edf3; 
  --slate:       #8b949e; 
  --muted:       #656d76; 
  --border:      #30363d;
  --white:       #161b22; 
  --bg:          #0d1117; 
  --input-bg:    #21262d; 
  --error:       #f87171;
  --nav-bg:      rgba(13,17,23,.85);
  --right-bg:    #0b1329;
  --shadow-md:   0 4px 20px rgba(0,0,0,.4);
}

body { 
  font-family: <?= $lang === 'ar' ? "var(--font-ar)" : "var(--font-en)" ?>; 
  background: var(--bg); color: var(--navy);
  min-height: 100vh; display: flex; flex-direction: column; 
  overflow: hidden; transition: background .3s, color .3s;
}

/* ======[ Top Navbar ]====== */
.topnav { 
  position: fixed; inset-inline: 0; top: 0; z-index: 100; 
  background: var(--nav-bg); backdrop-filter: blur(12px); 
  border-bottom: 1px solid var(--border); height: 56px; 
  display: flex; align-items: center; padding: 0 28px; 
  justify-content: space-between; transition: background .3s, border-color .3s;
}
.nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.nav-logo { width: 34px; height: 34px; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); }
.nav-logo img { width: 100%; height: 100%; object-fit: cover; }
.nav-brand-name { font-size: 17px; font-weight: 700; color: var(--navy); }

.nav-links { display: flex; align-items: center; gap: 4px; list-style: none; flex: 1; justify-content: center; }
.nav-links a { text-decoration: none; font-size: 13px; font-weight: 500; color: var(--slate); padding: 6px 12px; border-radius: 6px; transition: .15s; }
.nav-links a:hover { background: var(--blue-pale); color: var(--blue-main); }

.nav-actions { display: flex; align-items: center; gap: 8px; }
.icon-btn { background: var(--input-bg); border: 1px solid var(--border); color: var(--navy); font-size: 14px; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: .18s; }
.icon-btn:hover { border-color: var(--blue-light); color: var(--blue-main); }
.btn-nav-fill { font-size: 13px; font-weight: 600; padding: 7px 18px; border-radius: 8px; border: none; background: var(--blue-main); color: #ffffff; text-decoration: none; transition: .18s; }
.btn-nav-fill:hover { background: var(--blue-mid); }
.lang-btn { background: var(--input-bg); border: 1px solid var(--border); color: var(--slate); font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: .18s; }

/* ======[ Split Screen Layout ]====== */
.page-body { flex: 1; display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; padding-top: 56px; }
.left-panel { display: flex; align-items: center; justify-content: center; padding: 60px 48px; background: var(--white); position: relative; z-index: 1; transition: background .3s; }
.left-inner { width: 100%; max-width: 380px; }
.form-title { font-size: 30px; font-weight: 700; color: var(--navy); margin-bottom: 8px; }
.form-subtitle { font-size: 14px; color: var(--muted); margin-bottom: 32px; }

/* ======[ Form Styles ]====== */
.error-box { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: var(--error); padding: 11px 16px; border-radius: var(--radius); font-size: 13.5px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
.field-group { margin-bottom: 18px; }
.field-label { display: block; font-size: 13px; font-weight: 600; color: var(--navy); margin-bottom: 7px; }
.field-wrap { position: relative; }
.field-input { width: 100%; background: var(--input-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px 14px; font-size: 14px; color: var(--navy); outline: none; transition: .18s; }
.field-input:focus { border-color: var(--blue-light); box-shadow: 0 0 0 3px var(--blue-pale); background: var(--white); }
.eye-btn { position: absolute; inset-inline-end: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); font-size: 16px; padding: 2px 4px; }
.forgot-link { display: block; text-align: end; font-size: 12px; color: var(--blue-main); text-decoration: none; margin-top: 6px; font-weight: 500; }

.btn-submit { width: 100%; background: var(--blue-main); color: #ffffff; border: none; border-radius: var(--radius); padding: 13px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 24px; transition: .18s; }
.btn-submit:hover { background: var(--blue-mid); box-shadow: 0 6px 20px rgba(29,78,216,.30); }
.divider { display: flex; align-items: center; gap: 12px; margin: 22px 0; color: var(--muted); font-size: 12px; font-weight: 500; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* ======[ Microsoft Buttons ]====== */
.btn-microsoft { width: 100%; background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px; font-size: 14px; font-weight: 600; color: var(--navy); display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; transition: .18s; }
.btn-microsoft:hover { border-color: #0078d4; color: #0078d4; box-shadow: 0 2px 12px rgba(0,120,212,.15); }
.ms-icon { width: 18px; height: 18px; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 23 23'%3E%3Cpath fill='%23f35325' d='M0 0h11v11H0z'/%3E%3Cpath fill='%2381bc06' d='M12 0h11v11H12z'/%3E%3Cpath fill='%2305a6f0' d='M0 12h11v11H0z'/%3E%3Cpath fill='%23ffba08' d='M12 12h11v11H12z'/%3E%3C/svg%3E") center/contain no-repeat; }

.bottom-link { text-align: center; margin-top: 24px; font-size: 13px; color: var(--muted); }
.bottom-link a { color: var(--blue-main); font-weight: 600; text-decoration: none; }
.demo-hint { background: var(--blue-pale); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: var(--blue-main); margin-top: 16px; text-align: center; line-height: 1.7; border: 1px solid rgba(29,78,216,.1); }
.demo-hint span { font-weight: 700; }

/* ======[ Right Panel & Illustration ]====== */
.right-panel { position: relative; background: var(--right-bg); overflow: hidden; display: flex; align-items: center; justify-content: center; transition: background .3s; }
.right-panel::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 80% 60% at 70% 30%, rgba(59,130,246,.15) 0%, transparent 70%), radial-gradient(ellipse 60% 80% at 20% 80%, rgba(29,78,216,.10) 0%, transparent 65%); }
.geo-block { position: absolute; inset-inline-end: 0; top: 0; bottom: 0; width: 52%; background: linear-gradient(160deg, #2563eb 0%, #1d4ed8 60%, #1e3a8a 100%); clip-path: polygon(18% 0%, 100% 0%, 100% 100%, 0% 100%); z-index: 1; opacity: 0.4; }
[dir="rtl"] .geo-block { inset-inline-end: auto; inset-inline-start: 0; clip-path: polygon(0% 0%, 82% 0%, 100% 100%, 0% 100%); }
.dot-grid { position: absolute; inset: 0; z-index: 0; background-image: radial-gradient(circle, rgba(29,78,216,.15) 1px, transparent 1px); background-size: 28px 28px; }

.hero-illustration { position: absolute; inset-inline-end: 8%; bottom: 8%; width: 48%; max-width: 460px; z-index: 2; pointer-events: none; animation: floatImage 5s ease-in-out infinite; }
.hero-illustration img { width: 100%; height: auto; display: block; object-fit: contain; }

@keyframes floatImage {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.right-content { position: relative; z-index: 3; display: flex; flex-direction: column; align-items: flex-start; padding: 0 10% 0 8%; gap: 20px; max-width: 420px; margin-bottom: 250px; }
[dir="rtl"] .right-content { align-items: flex-end; }
.right-tagline { font-size: 34px; font-weight: 800; color: #ffffff; line-height: 1.25; }
[dir="rtl"] .right-tagline { text-align: right; }
.right-tagline span { color: var(--blue-light); }
.right-sub { font-size: 15px; color: var(--muted); line-height: 1.65; max-width: 300px; }
[dir="rtl"] .right-sub { text-align: right; }

.feature-chips { display: flex; flex-wrap: wrap; gap: 8px; }
[dir="rtl"] .feature-chips { justify-content: flex-end; }
.chip { display: flex; align-items: center; gap: 7px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 30px; padding: 6px 12px; font-size: 12px; font-weight: 600; color: #ffffff; backdrop-filter: blur(4px); }

/* ======[ Animations & Media ]====== */
.left-inner { animation: slideInLeft .5s cubic-bezier(.22,.68,0,1.2) both; }
@keyframes slideInLeft { from { opacity: 0; transform: translateX(<?= $lang === 'ar' ? '20px' : '-20px' ?>); } to { opacity: 1; transform: translateX(0); } }
.right-content { animation: slideInRight .55s .1s cubic-bezier(.22,.68,0,1.2) both; }
@keyframes slideInRight { from { opacity: 0; transform: translateX(<?= $lang === 'ar' ? '-20px' : '20px' ?>); } to { opacity: 1; transform: translateX(0); } }

@media (max-width: 850px) { .page-body { grid-template-columns: 1fr; } .right-panel { display: none; } .left-panel { padding: 40px 24px; } .nav-links { display: none; } }
</style>
</head>
<body>

<nav class="topnav">
  <a class="nav-brand" href="index.php">
    <div class="nav-logo"><img src="images/Logo.jpg" alt="Logo"></div>
    <span class="nav-brand-name"><?= __('app_name') ?></span>
  </a>

  <div class="nav-actions">
    <button class="icon-btn" onclick="toggleTheme()" title="<?= $lang === 'ar' ? 'تغيير المظهر' : 'Toggle Theme' ?>">🌓</button>
    <button class="lang-btn" onclick="toggleLang()">🌐 <?= $lang === 'ar' ? 'EN' : 'ع' ?></button>
    <a href="register.php" class="btn-nav-fill"><?= __('create_acc') ?></a>
  </div>
</nav>

<div class="page-body">
  <div class="left-panel">
    <div class="left-inner">

      <h1 class="form-title"><?= __('welcome') ?></h1>
      <p class="form-subtitle"><?= __('login_sub') ?></p>

      <div id="dynamic-alert" class="error-box" style="<?= $error ? 'display:flex;' : 'display:none;' ?>">
          ⚠️ <span id="alert-text"><?= htmlspecialchars($error) ?></span>
      </div>

      <form method="POST">
        <div class="field-group">
          <label class="field-label" for="email"><?= __('email') ?></label>
          <div class="field-wrap">
            <input class="field-input" type="email" id="email" name="email" placeholder="student@tvtc.edu.sa" required autocomplete="email">
          </div>
        </div>

        <div class="field-group">
          <label class="field-label" for="password"><?= __('pass') ?></label>
          <div class="field-wrap">
            <input class="field-input" type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="eye-btn" onclick="togglePass()" title="Show/Hide">👁</button>
          </div>
          <a href="#" class="forgot-link"><?= $lang === 'ar' ? 'نسيت كلمة المرور؟' : 'Forgot password?' ?></a>
        </div>

        <button type="submit" class="btn-submit"><?= __('login_btn') ?></button>
      </form>

      <div class="divider"><?= $lang === 'ar' ? 'أو' : 'or' ?></div>

      <a href="oauth.php?action=login" class="btn-microsoft">
        <span class="ms-icon"></span>
        <?= __('sign_ms') ?>
      </a>

      <div class="demo-hint">
        <?= __('demo_hint') ?> <?= $lang === 'ar' ? 'طالب' : 'Student' ?>: <span>ahmed@tvtc.edu.sa</span> | <?= __('pass') ?>: <span>admin123</span>
      </div>

      <div class="bottom-link">
        <?= __('no_acc') ?> <a href="register.php"><?= __('create_acc') ?></a>
      </div>

    </div>
  </div>

  <div class="right-panel">
    <div class="dot-grid"></div>
    <div class="geo-block"></div>
    
    <div class="hero-illustration">
      <img src="images/Privacy policy-rafiki.svg" alt="Secure Account Access">
    </div>

    <div class="right-content">
      <?php if ($lang === 'ar'): ?>
        <h2 class="right-tagline">بوابتك الذكية<br>إلى <span>عالم الكلية</span></h2>
        <p class="right-sub">منصة متكاملة تجمع الشات، الفعاليات، المفقودات، والمستشار الذكي.</p>
      <?php else: ?>
        <h2 class="right-tagline">Your Smart Gateway<br>to <span>Campus Life</span></h2>
        <p class="right-sub">An all-in-one platform for campus connectivity and smart AI tools.</p>
      <?php endif; ?>
      <div class="feature-chips">
        <div class="chip">💬 <?= $lang === 'ar' ? 'الشات العام' : 'Public Chat' ?></div>
        <div class="chip">🤖 <?= $lang === 'ar' ? 'مستشار ذكي' : 'AI Advisor' ?></div>
        <div class="chip">📅 <?= $lang === 'ar' ? 'فعاليات' : 'Events' ?></div>
        <div class="chip">🔍 <?= $lang === 'ar' ? 'مفقودات' : 'Lost & Found' ?></div>
      </div>
    </div>
  </div>
</div>

<script>
// ======[ JS Functions ]======
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    localStorage.setItem('theme', currentTheme);
}

function triggerSecureAlert() {
    const alertBox  = document.getElementById('dynamic-alert');
    const alertText = document.getElementById('alert-text');
    const emailF    = document.getElementById('email');
    alertText.textContent = <?= $lang === 'ar' ? "'يرجى تسجيل الدخول أولاً للوصول إلى هذه الخدمة المحمية.'" : "'Please login first to access this secure service.'" ?>;
    alertBox.style.display = 'flex';
    alertBox.animate([{ transform: 'scale(0.98)' }, { transform: 'scale(1)' }], { duration: 200 });
    emailF.focus();
}

function togglePass() {
  const p = document.getElementById('password');
  p.type = p.type === 'password' ? 'text' : 'password';
}

function toggleLang() {
  const cur = document.documentElement.lang === 'ar' ? 'en' : 'ar';
  document.cookie = "lang=" + cur + "; path=/; max-age=31536000";
  window.location.reload();
}
</script>
</body>
</html>
<?php
// =======[ Team Collage 2026 ]=====
?>