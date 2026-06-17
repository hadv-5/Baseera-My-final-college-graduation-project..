<?php
// ============================================================================
// Public Map View — Baseera (Accessible to Everyone)
// ============================================================================
require_once 'config.php';
$user = currentUser(); // للتحقق ما إذا كان الزائر مسجلاً لتغيير شكل القائمة
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __('map') ?> — <?= __('app_name') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ── استيراد المتغيرات الأساسية ── */
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
  --font-ar:     'Tajawal', sans-serif;
  --font-en:     'Plus Jakarta Sans', sans-serif;
  --radius:      12px;
  --shadow-sm:   0 1px 3px rgba(0,0,0,.08);
  --shadow-md:   0 4px 20px rgba(0,0,0,.08);
}
body {
  font-family: <?= $lang === 'ar' ? "var(--font-ar)" : "var(--font-en)" ?>;
  background: var(--bg); color: var(--navy);
  min-height: 100vh; display: flex; flex-direction: column;
}

/* ── شريط التنقل العلوي ── */
.topnav {
  position: fixed; inset-inline: 0; top: 0; z-index: 100;
  background: rgba(255,255,255,.85); backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border); height: 56px;
  display: flex; align-items: center; padding: 0 28px;
  justify-content: space-between;
}
.nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.nav-logo { width: 34px; height: 34px; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); }
.nav-logo img { width: 100%; height: 100%; object-fit: cover; }
.nav-brand-name { font-size: 17px; font-weight: 700; color: var(--navy); }

.nav-links { display: flex; align-items: center; gap: 4px; list-style: none; }
.nav-links a { text-decoration: none; font-size: 13px; font-weight: 500; color: var(--slate); padding: 6px 12px; border-radius: 6px; transition: .15s; }
.nav-links a:hover, .nav-links a.active { background: var(--blue-pale); color: var(--blue-main); }

.nav-actions { display: flex; align-items: center; gap: 8px; }
.btn-nav { font-size: 13px; font-weight: 600; padding: 7px 18px; border-radius: 8px; text-decoration: none; transition: .18s; }
.btn-nav-ghost { background: transparent; color: var(--slate); border: 1.5px solid var(--border); }
.btn-nav-ghost:hover { border-color: var(--blue-light); color: var(--blue-main); }
.btn-nav-fill { background: var(--blue-main); color: var(--white); }
.btn-nav-fill:hover { background: var(--blue-mid); }
.lang-btn { background: var(--input-bg); border: 1.5px solid var(--border); color: var(--slate); font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: .18s; }

/* ── حاوية الخريطة ── */
.map-container {
  flex: 1; padding: 90px 4% 40px;
  display: flex; flex-direction: column; align-items: center;
  max-width: 1200px; margin: 0 auto; width: 100%;
}
.page-header { text-align: center; margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 800; color: var(--navy); margin-bottom: 8px; }
.page-subtitle { font-size: 14px; color: var(--slate); }

.map-card {
  background: var(--white); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 24px; width: 100%;
  box-shadow: var(--shadow-md); display: flex; flex-direction: column;
  align-items: center; justify-content: center; min-height: 500px;
  position: relative; overflow: hidden;
}

.public-badge {
  position: absolute; top: 16px; inset-inline-start: 16px;
  background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;
  padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
}

@media (max-width: 768px) { .nav-links { display: none; } }
</style>
</head>
<body>

<nav class="topnav">
  <a class="nav-brand" href="<?= $user ? 'index.php' : 'login.php' ?>">
    <div class="nav-logo"><img src="images/Logo.jpg" alt="Logo"></div>
    <span class="nav-brand-name"><?= __('app_name') ?></span>
  </a>

  <ul class="nav-links">
    <?php if ($lang === 'ar'): ?>
      <li><a href="<?= $user ? 'index.php' : 'login.php' ?>">الرئيسية</a></li>
      <li><a href="login.php?auth_req=1">الخدمات</a></li>
      <li><a href="login.php?auth_req=1">الفعاليات</a></li>
      <li><a href="login.php?auth_req=1">المفقودات</a></li>
      <li><a href="map.php" class="active">الخريطة 🗺️</a></li>
    <?php else: ?>
      <li><a href="<?= $user ? 'index.php' : 'login.php' ?>">Home</a></li>
      <li><a href="login.php?auth_req=1">Services</a></li>
      <li><a href="login.php?auth_req=1">Events</a></li>
      <li><a href="login.php?auth_req=1">Lost & Found</a></li>
      <li><a href="map.php" class="active">Map 🗺️</a></li>
    <?php endif; ?>
  </ul>

  <div class="nav-actions">
    <button class="lang-btn" onclick="toggleLang()">🌐 <?= $lang === 'ar' ? 'EN' : 'ع' ?></button>
    <?php if ($user): ?>
        <a href="index.php" class="btn-nav btn-nav-fill">بوابتي ←</a>
    <?php else: ?>
        <a href="login.php" class="btn-nav btn-nav-ghost"><?= __('login_link') ?></a>
        <a href="register.php" class="btn-nav btn-nav-fill"><?= __('create_acc') ?></a>
    <?php endif; ?>
  </div>
</nav>

<div class="map-container">
  <div class="page-header">
    <h1 class="page-title">🗺️ <?= __('map') ?> <?= __('college') ?></h1>
    <p class="page-subtitle"><?= $lang === 'ar' ? 'استعرض مرافق ومباني الكلية المتاحة للزوار والطلاب' : 'Explore campus facilities and buildings available for visitors and students' ?></p>
  </div>

  <div class="map-card">
    <div class="public-badge">✨ خدمة عامة</div>
    
    <div style="text-align: center; color: var(--muted); padding: 40px;">
        <span style="font-size: 48px; display: block; margin-bottom: 16px;">📍</span>
        <h3 style="color: var(--navy); margin-bottom: 8px;">مخطط الكلية التفاعلي</h3>
        <p>قم بإدراج رسم الـ SVG الخاص بخريطة كليتك هنا ليعمل بشكل متجاوب.</p>
    </div>
  </div>
</div>

<script>
function toggleLang() {
  const cur = document.documentElement.lang === 'ar' ? 'en' : 'ar';
  document.cookie = "lang=" + cur + "; path=/; max-age=31536000";
  window.location.reload();
}
</script>
</body>
</html>