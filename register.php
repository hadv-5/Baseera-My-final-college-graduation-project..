<?php
// ======[ Controller Init ]======
require_once 'config.php';
if (currentUser()) { header('Location: ' . (isAdmin() ? 'admin.php' : 'index.php')); exit; }

$error = ''; $success = '';

// ======[ Registration Processing ]======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name']  ?? '');
    $sid   = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email']      ?? '');
    $pass  = $_POST['password']  ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (!$name || !$sid || !$email || !$pass) {
        $error = __('fill_all');
    } elseif (!preg_match('/@tvtc\.edu\.sa$/i', $email)) {
        $error = __('err_domain');
    } elseif ($pass !== $pass2) {
        $error = __('err_pass_match');
    } elseif (strlen($pass) < 6) {
        $error = __('err_pass_len');
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1');
        $stmt->execute([$email, $sid]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmtE = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmtE->execute([$email]);
            $error = $stmtE->fetch() ? __('err_email_exist') : __('err_sid_exist');
        } else {
            $colors = ['#2563EB','#7C3AED','#10B981','#0072FF','#EC4899','#F59E0B'];
            $color  = $colors[array_rand($colors)];
            $hash   = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = db()->prepare('INSERT INTO users (full_name, student_id, email, password, avatar_color, role) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$name, $sid, $email, $hash, $color, 'student']);
            $success = __('succ_reg');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __('create_acc') ?> - <?= __('app_name') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
  // ======[ Theme Preload ]======
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
</script>
<style>
/* ======[ Navigation & Header ]====== */
.topnav {
  position: fixed; inset-inline: 0; top: 0; z-index: 100;
  background: rgba(255,255,255,.85); backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border); height: 56px;
  display: flex; align-items: center; padding: 0 28px;
  justify-content: space-between;
}
[data-theme="dark"] .topnav { background: rgba(13,17,23,.85); }
.nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.nav-logo { width: 34px; height: 34px; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); }
.nav-logo img { width: 100%; height: 100%; object-fit: cover; }
.nav-brand-name { font-size: 17px; font-weight: 700; color: var(--text); }

.nav-links { display: flex; align-items: center; gap: 4px; list-style: none; flex: 1; justify-content: center; }
.nav-links a { text-decoration: none; font-size: 13px; font-weight: 500; color: var(--text2); padding: 6px 12px; border-radius: 6px; transition: .15s; }
.nav-links a:hover { background: var(--card2); color: var(--accent); }

.nav-actions { display: flex; align-items: center; gap: 8px; }
.btn-nav-ghost { font-size: 13px; font-weight: 600; padding: 7px 18px; border-radius: 8px; border: 1.5px solid var(--border); color: var(--text2); text-decoration: none; transition: .18s; }
.btn-nav-ghost:hover { border-color: var(--accent); color: var(--accent); }

/* ======[ Integration Buttons ]====== */
.btn-ms-reg {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  width: 100%; background: var(--card); border: 1px solid var(--border);
  border-radius: 10px; padding: 12px; font-size: 14px; font-weight: 700;
  color: var(--text); text-decoration: none; margin-top: 15px; transition: .2s;
}
.btn-ms-reg:hover { border-color: #0078d4; color: #0078d4; box-shadow: 0 4px 12px rgba(0,120,212,.15); transform: translateY(-1px); }
.ms-icon { width: 18px; height: 18px; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 23 23'%3E%3Cpath fill='%23f35325' d='M0 0h11v11H0z'/%3E%3Cpath fill='%2381bc06' d='M12 0h11v11H12z'/%3E%3Cpath fill='%2305a6f0' d='M0 12h11v11H0z'/%3E%3Cpath fill='%23ffba08' d='M12 12h11v11H12z'/%3E%3C/svg%3E") center/contain no-repeat; }

/* ======[ Illustration Options ]====== */
.reg-illustration {
  margin-top: auto; width: 100%; max-width: 310px;
  align-self: center; animation: floatImage 5s ease-in-out infinite;
  padding-top: 20px;
}
.reg-illustration img { width: 100%; height: auto; display: block; object-fit: contain; }

@keyframes floatImage {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}

/* ======[ Custom Layout ]====== */
.auth-page-layout { padding-top: 60px; display: grid; grid-template-columns: 1fr 1.1fr; max-width: 1100px; width: 92%; margin: 40px auto; background: var(--card); border: 1px solid var(--border); border-radius: 24px; box-shadow: var(--shadow); overflow: hidden; }
.auth-hero { padding: 40px; background: linear-gradient(145deg, var(--card2), var(--card)); border-inline-end: 1px solid var(--border); display: flex; flex-direction: column; justify-content: flex-start; }
.auth-hero-logo img { width: 70px; border-radius: 16px; margin-bottom: 20px; }
.auth-hero h1 { font-size: 28px; font-weight: 800; margin-bottom: 10px; }
.auth-hero p { font-size: 14px; color: var(--text2); margin-bottom: 24px; }
.auth-hero-features { display: flex; flex-direction: column; gap: 14px; }
.auth-hero-feature { display: flex; align-items: center; gap: 12px; }
.feat-icon { width: 34px; height: 34px; border-radius: 8px; background: var(--accent-glow); display: flex; align-items: center; justify-content: center; font-size: 15px; }
.feat-text { font-size: 13px; font-weight: 600; }

.auth-form-side { padding: 40px; display: flex; flex-direction: column; justify-content: center; }
.college-badge { display: inline-block; background: var(--accent-glow); color: var(--accent); font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-bottom: 16px; align-self: flex-start; }
.auth-form-side h2 { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
.auth-form-side p.sub { font-size: 13px; color: var(--muted); margin-bottom: 24px; }

/* ======[ Form Fields ]====== */
.auth-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; width: 100%; }
.auth-field label { font-size: 12.5px; font-weight: 700; color: var(--text2); }
.auth-field input { background: var(--card2); border: 1px solid var(--border); border-radius: 10px; padding: 11px 14px; color: var(--text); font-size: 13.5px; outline: none; transition: .2s; }
.auth-field input:focus { border-color: var(--accent); background: var(--card); }
.row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; width: 100%; }

.btn-auth { width: 100%; background: var(--accent); color: #fff; border: none; border-radius: 10px; padding: 13px; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 10px; transition: .2s; }
.btn-auth:hover { opacity: 0.95; transform: translateY(-1px); }
.error-box { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: var(--danger); padding: 12px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }
.success-box { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: var(--green); padding: 12px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }
.divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; color: var(--muted); font-size: 12px; font-weight: 600; width: 100%; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
.auth-link { text-align: center; font-size: 13px; color: var(--text2); }
.auth-link a { color: var(--accent); font-weight: 700; text-decoration: none; }

@media (max-width: 850px) { .auth-page-layout { grid-template-columns: 1fr; } .auth-hero { display: none; } .nav-links { display: none; } }
</style>
</head>
<body class="auth-page">
<div class="grid-bg"></div>

<nav class="topnav">
  <a class="nav-brand" href="index.php">
    <div class="nav-logo"><img src="images/Logo.jpg" alt="Logo"></div>
    <span class="nav-brand-name"><?= __('app_name') ?></span>
  </a>

  <div class="nav-actions">
    <button class="icon-btn" onclick="toggleTheme()" style="height:34px; border-radius:8px; border:1px solid var(--border); background:var(--card2); color:var(--text);">🌓</button>
    <button class="icon-btn" onclick="toggleLang()" style="height:34px; border-radius:8px; border:1px solid var(--border); background:var(--card2); color:var(--text); font-weight:bold; font-size:12px; padding:0 8px;">🌐 <?= $lang === 'ar' ? 'EN' : 'ع' ?></button>
    <a href="login.php" class="btn-nav-ghost"><?= __('login_link') ?></a>
  </div>
</nav>

<div class="auth-page-layout" style="position:relative; z-index:10;">
  <div class="auth-hero">
    <div class="auth-hero-logo"><img src="images/Logo.jpg" alt="Logo"></div>
    <h1><?= __('college') ?></h1>
    <p><?= $lang === 'ar' ? 'انضم إلى مجتمع الطلاب واستفد من جميع خدمات بوابة بصيرة' : 'Join the student community and benefit from all Baseera portal services' ?></p>

    <div class="auth-hero-features">
      <div class="auth-hero-feature"><div class="feat-icon">🔒</div><div class="feat-text"><?= $lang === 'ar' ? 'حساب آمن ومحمي بالكامل' : 'Fully secure and protected account' ?></div></div>
      <div class="auth-hero-feature"><div class="feat-icon">⚡</div><div class="feat-text"><?= $lang === 'ar' ? 'وصول فوري لجميع الخدمات' : 'Instant access to all services' ?></div></div>
    </div>

    <div class="reg-illustration">
      <img src="images/undraw_in-the-office_e7pg (1).svg" alt="User Office Register Vector">
    </div>
  </div>

  <div class="auth-form-side">
    <div class="college-badge">✨ <?= __('create_acc') ?></div>
    <h2><?= __('create_acc') ?></h2>
    <p class="sub"><?= $lang === 'ar' ? 'أنشئ حسابك للوصول إلى بوابة بصيرة' : 'Create your account to access Baseera portal' ?></p>

    <?php if ($error): ?><div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success-box">✅ <?= htmlspecialchars($success) ?> <a href="login.php" style="color:var(--green); font-weight:700; text-decoration:underline;"><?= __('login_link') ?></a></div><?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" style="width:100%;">
      <div class="auth-field"><label><?= __('name') ?></label><input type="text" name="full_name" required placeholder="<?= $lang === 'ar' ? 'أحمد محمد ' : 'Ahmed Mohammed' ?>"></div>
      <div class="row2">
        <div class="auth-field"><label><?= __('sid') ?></label><input type="text" name="student_id" required placeholder="2024XXXX"></div>
        <div class="auth-field"><label><?= __('email') ?></label><input type="email" name="email" required placeholder="student@tvtc.edu.sa"></div>
      </div>
      <div class="row2">
        <div class="auth-field"><label><?= __('pass') ?></label><input type="password" name="password" required placeholder="••••••••"></div>
        <div class="auth-field"><label><?= __('pass_conf') ?></label><input type="password" name="password2" required placeholder="••••••••"></div>
      </div>
      <button type="submit" class="btn-auth"><?= __('create_btn') ?></button>
    </form>

    <div class="divider"><span><?= $lang === 'ar' ? 'أو عبر الحساب الجامعي' : 'Or via University Account' ?></span></div>
    <a href="oauth.php?action=login" class="btn-ms-reg"><span class="ms-icon"></span><?= __('sign_ms') ?></a>
    <?php endif; ?>

    <div class="divider" style="margin-top:15px;"><span><?= $lang === 'ar' ? 'لديك حساب؟' : 'Have an account?' ?></span></div>
    <div class="auth-link"><?= __('have_acc') ?> <a href="login.php"><?= __('login_link') ?></a></div>
  </div>
</div>

<script>
// ======[ Layout Scripting ]======
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', current);
    localStorage.setItem('theme', current);
}
function toggleLang() {
    const currentLang = document.documentElement.lang === 'ar' ? 'en' : 'ar';
    document.cookie = "lang=" + currentLang + "; path=/; max-age=31536000";
    window.location.reload();
}
</script>
</body>
</html>
<?php
// =======[ Team Collage 2026 ]=====
?>