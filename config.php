<?php
// ======[ Core Configuration ]======

define('APP_NAME', 'Baseera');

// ======[ Database Setup ]======
define('DB_HOST', 'localhost');
define('DB_NAME', 'baseera_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ======[ Azure AD Config ]======
define('MS_CLIENT_ID', 'YOUR_MICROSOFT_CLIENT_ID');
define('MS_CLIENT_SECRET', 'YOUR_MICROSOFT_CLIENT_SECRET');
define('MS_REDIRECT_URI', 'http://localhost/baseera/oauth.php');
define('MS_TENANT_ID', 'common');

// ======[ DB Connection ]======
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
        }
    }
    return $pdo;
}

// ======[ Session Init ]======
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
    session_start();
}

$lang = $_COOKIE['lang'] ?? 'ar';
if (!in_array($lang, ['ar', 'en'])) $lang = 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

// ======[ Dictionary ]======
$translations = [
    'ar' => [
        'app_name'     => 'بصيرة',
        'app_sub'      => 'بوابة الطلاب',
        'college'      => 'كلية الاتصالات',
        'welcome'      => 'أهلاً بك مجدداً',
        'login_sub'    => 'سجّل دخولك للوصول إلى خدمات الكلية',
        'email'        => 'البريد الجامعي',
        'pass'         => 'كلمة المرور',
        'login_btn'    => 'تسجيل الدخول', // تم مسح السهم
        'no_acc'       => 'ليس لديك حساب؟',
        'create_acc'   => 'إنشاء حساب',
        'have_acc'     => 'لديك حساب؟',
        'name'         => 'الاسم الكامل',
        'sid'          => 'الرقم الجامعي',
        'pass_conf'    => 'تأكيد كلمة المرور',
        'create_btn'   => 'إنشاء الحساب', // تم مسح السهم
        'menu'         => 'القائمة',
        'home'         => 'الرئيسية',
        'chat'         => 'الشات العام',
        'bot'          => 'المستشار الذكي',
        'services'     => 'الخدمات',
        'lost'         => 'المفقودات',
        'map'          => 'خريطة الكلية',
        'events'       => 'الفعاليات',
        'logout'       => 'تسجيل الخروج',
        'hello'        => 'أهلاً،',
        'chat_msgs'    => 'رسائل الشات',
        'active_lost'  => 'بلاغات مفقودات',
        'up_events'    => 'فعاليات قادمة',
        'chat_desc'    => 'تواصل مع زملائك في الكلية',
        'bot_desc'     => 'اسأل عن جداولك ومواد دراستك بالذكاء الاصطناعي',
        'lost_desc'    => 'أبلغ عن غرض مفقود أو أعلن عن غرض وجدته',
        'events_desc'  => 'تابع آخر الفعاليات والأنشطة الجامعية',
        'type_here'    => 'اكتب رسالتك هنا...',
        'ask_here'     => 'اسألني أي شيء عن الكلية...',
        'lost_sys'     => 'نظام المفقودات',
        'add_report'   => '+ إضافة بلاغ',
        'item_name'    => 'اسم الغرض *',
        'loss_loc'     => 'مكان الفقدان *',
        'contact_info' => 'معلومات التواصل',
        'status'       => 'الحالة',
        'desc'         => 'وصف إضافي',
        'send_report'  => 'إرسال البلاغ',
        'status_lost'  => 'مفقود',
        'status_found' => 'وُجد',
        'demo_hint'    => 'للتجربة:',
        'login_link'   => 'تسجيل الدخول',
        'err_login'    => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
        'fill_all'     => 'يرجى ملء جميع الحقول',
        'err_pass_match'  => 'كلمتا المرور غير متطابقتان',
        'err_pass_len'    => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
        'err_email_exist' => 'البريد الإلكتروني مستخدم بالفعل',
        'err_sid_exist'   => 'الرقم الجامعي مستخدم بالفعل',
        'err_domain'      => 'عذراً، التسجيل متاح فقط لحاملي البريد الجامعي المعتمد (@tvtc.edu.sa)',
        'succ_reg'        => 'تم إنشاء الحساب بنجاح!',
        'profile'        => 'الملف الشخصي',
        'edit_profile'   => 'تعديل الملف الشخصي',
        'save_changes'   => 'حفظ التعديلات',
        'upload_avatar'  => 'تغيير الصورة الشخصية',
        'update_success' => 'تم تحديث البيانات بنجاح!',
        'posts'          => 'المنشورات',
        'suggestions'    => 'الاقتراحات',
        'add_post'       => 'نشر',
        'add_sugg'       => 'تقديم الاقتراح',
        'write_post'     => 'بم تفكر؟ شارك أخبارك مع زملائك...',
        'write_sugg'     => 'اكتب اقتراحك لتطوير الكلية هنا...',
        'posts_desc'     => 'شارك يومياتك وأخبارك مع الجميع',
        'sugg_desc'      => 'شاركنا أفكارك واقتراحاتك للتطوير',
        'gpa_tracker'    => 'حاسبة المعدل',
        'course_name'    => 'اسم المادة',
        'credits'        => 'عدد الساعات',
        'grade'          => 'التقدير (A+, B...)',
        'add_course'     => 'إضافة مادة',
        'your_gpa'       => 'معدلك التراكمي:',
        'notifications'  => 'الإشعارات الذكية',
        'no_notif'       => 'لا توجد إشعارات جديدة',
        'profile_desc'   => 'إدارة إعدادات حسابك ومعلوماتك الشخصية',
        'bio'            => 'نبذة عنك (اختياري)',
        'accent_color'   => 'لونك المفضل (لون الأيقونة)',
        'account_info'   => 'معلومات الحساب الجامعي',
        'security'       => 'الأمان وكلمة المرور',
        'current_pass'   => 'كلمة المرور الحالية',
        'new_pass'       => 'كلمة المرور الجديدة',
        'save_pass'      => 'تحديث كلمة المرور',
        'read_only'      => 'ثابت (لا يمكن تعديله)',
        'resources'      => 'بنك الموارد',
        'res_desc'       => 'مكتبة الملخصات والمواد الدراسية',
        'add_resource'   => 'رفع ملف',
        'res_title'      => 'عنوان الملف (مثال: ملخص الفصل الأول)',
        'res_file'       => 'الملف (PDF, DOCX, ZIP)',
        'download'       => 'تحميل',
        'admin_panel'    => 'لوحة الإدارة',
        'admin_events'   => 'إدارة الفعاليات',
        'admin_lost'     => 'إدارة المفقودات',
        'admin_users'    => 'إدارة المستخدمين',
        'add_event'      => 'إضافة فعالية',
        'event_title'    => 'عنوان الفعالية *',
        'event_date'     => 'التاريخ *',
        'event_color'    => 'اللون',
        'event_desc'     => 'الوصف',
        'save_event'     => 'حفظ الفعالية',
        'delete'         => 'حذف',
        'confirm_delete' => 'هل أنت متأكد من الحذف؟',
        'total_users'    => 'إجمالي المستخدمين',
        'total_lost'     => 'إجمالي المفقودات',
        'total_events'   => 'إجمالي الفعاليات',
        'total_posts'    => 'إجمالي المنشورات',
        'sign_ms'        => 'الدخول بحساب Microsoft',
    ],
    'en' => [
        'app_name'     => 'Baseera',
        'app_sub'      => 'Student Portal',
        'college'      => 'Telecom College',
        'welcome'      => 'Welcome Back',
        'login_sub'    => 'Sign in to access all college services',
        'email'        => 'University Email',
        'pass'         => 'Password',
        'login_btn'    => 'Login', // Removed arrow
        'no_acc'       => "Don't have an account?",
        'create_acc'   => 'Create Account',
        'have_acc'     => 'Already have an account?',
        'name'         => 'Full Name',
        'sid'          => 'Student ID',
        'pass_conf'    => 'Confirm Password',
        'create_btn'   => 'Create Account', // Removed arrow
        'menu'         => 'MENU',
        'home'         => 'Home',
        'chat'         => 'Public Chat',
        'bot'          => 'Smart Assistant',
        'services'     => 'SERVICES',
        'lost'         => 'Lost & Found',
        'map'          => 'College Map',
        'events'       => 'Events',
        'logout'       => 'Logout',
        'hello'        => 'Hello,',
        'chat_msgs'    => 'Chat Messages',
        'active_lost'  => 'Lost Reports',
        'up_events'    => 'Upcoming Events',
        'chat_desc'    => 'Communicate with peers in real-time',
        'bot_desc'     => 'Ask about schedules and courses via AI',
        'lost_desc'    => 'Report a lost or found item',
        'events_desc'  => 'Follow latest events and activities',
        'type_here'    => 'Type your message here...',
        'ask_here'     => 'Ask me anything about the college...',
        'lost_sys'     => 'Lost & Found',
        'add_report'   => '+ Add Report',
        'item_name'    => 'Item Name *',
        'loss_loc'     => 'Location *',
        'contact_info' => 'Contact Info',
        'status'       => 'Status',
        'desc'         => 'Additional Description',
        'send_report'  => 'Submit Report',
        'status_lost'  => 'Lost',
        'status_found' => 'Found',
        'demo_hint'    => 'Demo:',
        'login_link'   => 'Login',
        'err_login'    => 'Invalid email or password',
        'fill_all'     => 'Please fill all fields',
        'err_pass_match'  => 'Passwords do not match',
        'err_pass_len'    => 'Password must be at least 6 characters',
        'err_email_exist' => 'Email already exists',
        'err_sid_exist'   => 'Student ID already exists',
        'err_domain'      => 'Registration is restricted to approved university emails (@tvtc.edu.sa)',
        'succ_reg'        => 'Account created successfully!',
        'profile'        => 'Profile',
        'edit_profile'   => 'Edit Profile',
        'save_changes'   => 'Save Changes',
        'upload_avatar'  => 'Upload Avatar',
        'update_success' => 'Profile updated successfully!',
        'posts'          => 'Posts',
        'suggestions'    => 'Suggestions',
        'add_post'       => 'Post',
        'add_sugg'       => 'Submit Suggestion',
        'write_post'     => 'What is on your mind?',
        'write_sugg'     => 'Write your suggestion here...',
        'posts_desc'     => 'Share your news with everyone',
        'sugg_desc'      => 'Share your ideas for improvement',
        'gpa_tracker'    => 'GPA Tracker',
        'course_name'    => 'Course Name',
        'credits'        => 'Credit Hours',
        'grade'          => 'Grade (A+, B...)',
        'add_course'     => 'Add Course',
        'your_gpa'       => 'Your Cumulative GPA:',
        'notifications'  => 'Smart Alerts',
        'no_notif'       => 'No new notifications',
        'profile_desc'   => 'Manage your account settings and personal info',
        'bio'            => 'About me (Optional)',
        'accent_color'   => 'Favorite Color (Avatar Color)',
        'account_info'   => 'University Account Info',
        'security'       => 'Security & Password',
        'current_pass'   => 'Current Password',
        'new_pass'       => 'New Password',
        'save_pass'      => 'Update Password',
        'read_only'      => 'Read-only',
        'resources'      => 'Academic Resources',
        'res_desc'       => 'Study materials and summaries',
        'add_resource'   => 'Upload File',
        'res_title'      => 'File Title',
        'res_file'       => 'File (PDF, DOCX, ZIP)',
        'download'       => 'Download',
        'admin_panel'    => 'Admin Panel',
        'admin_events'   => 'Manage Events',
        'admin_lost'     => 'Manage Lost Items',
        'admin_users'    => 'Manage Users',
        'add_event'      => 'Add Event',
        'event_title'    => 'Event Title *',
        'event_date'     => 'Date *',
        'event_color'    => 'Color',
        'event_desc'     => 'Description',
        'save_event'     => 'Save Event',
        'delete'         => 'Delete',
        'confirm_delete' => 'Are you sure you want to delete?',
        'total_users'    => 'Total Users',
        'total_lost'     => 'Total Lost Items',
        'total_events'   => 'Total Events',
        'total_posts'    => 'Total Posts',
        'sign_ms'        => 'Sign in with Microsoft',
    ]
];

// ======[ Helpers ]======
function __($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $key;
}

function jsonOut(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function currentUser(): ?array { return $_SESSION['user'] ?? null; }
function isAdmin(): bool { return (currentUser()['role'] ?? '') === 'admin'; }
function requireLogin(): void { if (!currentUser()) { header('Location: login.php'); exit; } }
function requireAdmin(): void { requireLogin(); if (!isAdmin()) { header('Location: index.php'); exit; } }

// =======[ Team Collage 2026 ]=====
?>