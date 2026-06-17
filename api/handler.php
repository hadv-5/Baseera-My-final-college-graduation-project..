<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

// =================== LOGOUT ===================
if ($action === 'logout') {
    session_destroy();
    jsonOut(['success' => true]);
}

requireLogin();
$user = currentUser();

// =================== CHAT GET ===================
if ($action === 'chat_get') {
    $stmt = db()->query('
        SELECT m.id, m.message, m.sent_at,
               u.full_name, u.avatar_color, u.avatar_image, u.id AS user_id
        FROM chat_messages m
        JOIN users u ON m.user_id = u.id
        ORDER BY m.id DESC LIMIT 100
    ');
    $messages = array_reverse($stmt->fetchAll());
    jsonOut(['messages' => $messages]);
}

// =================== CHAT SEND ===================
if ($action === 'chat_send') {
    $message = trim($_POST['message'] ?? '');
    if (!$message) jsonOut(['error' => 'empty'], 400);

    $stmt = db()->prepare('INSERT INTO chat_messages (user_id, message) VALUES (?, ?)');
    $stmt->execute([$user['id'], $message]);

    $newMsg = [
        'id'           => db()->lastInsertId(),
        'user_id'      => $user['id'],
        'full_name'    => $user['full_name'],
        'avatar_color' => $user['avatar_color'],
        'avatar_image' => $user['avatar_image'] ?? null,
        'message'      => $message,
        'sent_at'      => date('Y-m-d H:i:s'),
    ];
    jsonOut(['success' => true, 'message' => $newMsg]);
}

// =================== BOT ASK ===================
if ($action === 'bot_ask') {
    $message = trim($_POST['message'] ?? '');
    if (!$message) jsonOut(['reply' => '...']);

    $lang = $_COOKIE['lang'] ?? 'ar';
    $msg  = mb_strtolower($message);

    $replies_ar = [
        ['keys' => ['جدول', 'مواعيد', 'محاضرة', 'درس'],
         'reply' => "يمكنك الاطلاع على جدولك الدراسي من خلال البوابة الإلكترونية للكلية 📅\nتأكد من تسجيل دخولك بالرقم الجامعي."],
        ['keys' => ['مفقود', 'ضائع', 'فقدت'],
         'reply' => "لتقديم بلاغ عن غرض مفقود، توجه إلى قسم 🔍 المفقودات من القائمة الجانبية واضغط على (+ إضافة بلاغ)."],
        ['keys' => ['فعالية', 'نشاط', 'حفل', 'ندوة'],
         'reply' => "يمكنك متابعة آخر الفعاليات من قسم 📅 الفعاليات في القائمة الجانبية."],
        ['keys' => ['كافتيريا', 'طعام', 'أكل', 'مطعم'],
         'reply' => "الكافتيريا موجودة في الجناح الجنوبي من الكلية ☕\nأوقات العمل: 7:00 صباحاً - 9:00 مساءً."],
        ['keys' => ['مكتبة', 'كتاب', 'مرجع'],
         'reply' => "المكتبة مفتوحة من الأحد إلى الخميس 📚\nمن 8:00 صباحاً حتى 8:00 مساءً."],
        ['keys' => ['مختبر', 'lab', 'حاسب', 'كمبيوتر'],
         'reply' => "مختبرات الحاسب موجودة في مبنى التقنية 💻\nيمكن حجز وقت في المختبر عبر البوابة."],
        ['keys' => ['تسجيل', 'مادة', 'إضافة', 'حذف'],
         'reply' => "لتسجيل أو حذف المواد، توجه إلى البوابة الأكاديمية الرسمية للكلية.\nفترة التسجيل تبدأ عادةً في بداية كل فصل دراسي."],
        ['keys' => ['شكر', 'شكراً', 'ممتاز', 'مساعدة'],
         'reply' => "يسعدني مساعدتك دائماً! 😊\nلا تتردد في سؤالي عن أي شيء يخص الكلية."],
    ];

    $replies_en = [
        ['keys' => ['schedule', 'class', 'lecture', 'timetable'],
         'reply' => "You can check your schedule through the college portal 📅\nLogin with your student ID to view all your classes."],
        ['keys' => ['lost', 'missing', 'found'],
         'reply' => "To report a lost item, go to the 🔍 Lost & Found section from the sidebar and click '+ Add Report'."],
        ['keys' => ['event', 'activity', 'seminar', 'conference'],
         'reply' => "Check the latest events in the 📅 Events section from the sidebar menu."],
        ['keys' => ['cafeteria', 'food', 'eat', 'restaurant'],
         'reply' => "The cafeteria is in the south wing ☕\nOpen hours: 7:00 AM - 9:00 PM."],
        ['keys' => ['library', 'book', 'reference'],
         'reply' => "The library is open Sunday–Thursday 📚\nFrom 8:00 AM to 8:00 PM."],
        ['keys' => ['lab', 'computer', 'pc'],
         'reply' => "Computer labs are in the Technology Building 💻\nYou can book time via the student portal."],
        ['keys' => ['register', 'course', 'add', 'drop'],
         'reply' => "For course registration or dropping, visit the official academic portal.\nRegistration opens at the start of each semester."],
        ['keys' => ['thank', 'thanks', 'great', 'help'],
         'reply' => "Happy to help anytime! 😊\nFeel free to ask me anything about the college."],
    ];

    $replies = $lang === 'ar' ? $replies_ar : $replies_en;
    $reply = $lang === 'ar'
        ? "عذراً، لم أفهم سؤالك جيداً. 🤔\nيمكنك سؤالي عن: الجداول، المفقودات، الفعاليات، المكتبة، المختبرات، الكافتيريا."
        : "Sorry, I didn't quite understand. 🤔\nYou can ask me about: schedules, lost items, events, library, labs, or the cafeteria.";

    foreach ($replies as $r) {
        foreach ($r['keys'] as $key) {
            if (str_contains($msg, $key)) { $reply = $r['reply']; break 2; }
        }
    }
    jsonOut(['reply' => $reply]);
}

// =================== LOST GET ===================
if ($action === 'lost_get') {
    $stmt = db()->query('
        SELECT l.*, u.full_name, u.avatar_color
        FROM lost_items l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.id DESC
    ');
    jsonOut(['items' => $stmt->fetchAll()]);
}

// =================== LOST ADD ===================
if ($action === 'lost_add') {
    $name    = trim($_POST['item_name']   ?? '');
    $loc     = trim($_POST['location']    ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $contact = trim($_POST['contact']     ?? '');
    $status  = in_array($_POST['status'] ?? '', ['مفقود','وُجد']) ? $_POST['status'] : 'مفقود';

    if (!$name || !$loc) jsonOut(['error' => 'missing fields'], 400);

    $stmt = db()->prepare(
        'INSERT INTO lost_items (user_id, item_name, location, description, contact, status) VALUES (?,?,?,?,?,?)'
    );
    $stmt->execute([$user['id'], $name, $loc, $desc, $contact, $status]);
    jsonOut(['success' => true]);
}

// =================== LOST DELETE (أدمن فقط) ===================
if ($action === 'lost_delete') {
    if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare('DELETE FROM lost_items WHERE id = ?')->execute([$id]);
    jsonOut(['success' => true]);
}

// =================== EVENTS GET ===================
if ($action === 'events_get') {
    $stmt = db()->query('SELECT * FROM events ORDER BY event_date ASC');
    jsonOut(['events' => $stmt->fetchAll()]);
}

// =================== EVENTS ADD (أدمن فقط) ===================
if ($action === 'events_add') {
    if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $date  = trim($_POST['event_date'] ?? '');
    $color = trim($_POST['color'] ?? '#7C3AED');

    if (!$title || !$date) jsonOut(['error' => 'missing fields'], 400);

    $stmt = db()->prepare(
        'INSERT INTO events (title, description, event_date, color, created_by) VALUES (?,?,?,?,?)'
    );
    $stmt->execute([$title, $desc, $date, $color, $user['id']]);
    jsonOut(['success' => true, 'id' => db()->lastInsertId()]);
}

// =================== EVENTS DELETE (أدمن فقط) ===================
if ($action === 'events_delete') {
    if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
    jsonOut(['success' => true]);
}

// =================== POSTS GET ===================
if ($action === 'get_posts') {
    $stmt = db()->query('
        SELECT p.*, u.full_name AS user_name, u.avatar_color AS user_color, u.avatar_image AS user_avatar
        FROM posts p JOIN users u ON p.user_id = u.id
        ORDER BY p.id DESC
    ');
    jsonOut(['posts' => $stmt->fetchAll()]);
}

// =================== POSTS ADD ===================
if ($action === 'add_post') {
    $content    = trim($_POST['content'] ?? '');
    $image_path = null;

    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $filename  = 'post_' . $user['id'] . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $uploadDir . $filename)) {
                $image_path = 'uploads/' . $filename;
            }
        }
    }

    if ($content !== '' || $image_path !== null) {
        $stmt = db()->prepare('INSERT INTO posts (user_id, content, post_image) VALUES (?,?,?)');
        $stmt->execute([$user['id'], $content, $image_path]);
        jsonOut(['success' => true]);
    }
    jsonOut(['success' => false, 'error' => 'Empty post']);
}

// =================== SUGGESTIONS GET ===================
if ($action === 'get_sugg') {
    $stmt = db()->query('
        SELECT s.*, u.full_name AS user_name, u.avatar_color AS user_color, u.avatar_image AS user_avatar
        FROM suggestions s JOIN users u ON s.user_id = u.id
        ORDER BY s.id DESC
    ');
    jsonOut(['suggestions' => $stmt->fetchAll()]);
}

// =================== SUGGESTIONS ADD ===================
if ($action === 'add_sugg') {
    $content = trim($_POST['content'] ?? '');
    if ($content) {
        $stmt = db()->prepare('INSERT INTO suggestions (user_id, content) VALUES (?,?)');
        $stmt->execute([$user['id'], $content]);
        jsonOut(['success' => true]);
    }
    jsonOut(['success' => false, 'error' => 'Empty content']);
}

// =================== ADMIN: STATS ===================
if ($action === 'admin_stats') {
    if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
    $stats = [
        'users'  => db()->query('SELECT COUNT(*) FROM users WHERE role = "student"')->fetchColumn(),
        'lost'   => db()->query('SELECT COUNT(*) FROM lost_items')->fetchColumn(),
        'events' => db()->query('SELECT COUNT(*) FROM events')->fetchColumn(),
        'posts'  => db()->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    ];
    jsonOut($stats);
}

// =================== ADMIN: USERS LIST ===================
if ($action === 'admin_users') {
    if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
    $stmt = db()->query('SELECT id, full_name, student_id, email, avatar_color, role, created_at FROM users ORDER BY id DESC');
    jsonOut(['users' => $stmt->fetchAll()]);
}

// =================== ADMIN: DELETE USER ===================
if ($action === 'admin_delete_user') {
    if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
    $id = (int)($_POST['id'] ?? 0);
    if ($id === $user['id']) jsonOut(['error' => 'Cannot delete yourself'], 400);
    db()->prepare('DELETE FROM users WHERE id = ? AND role != "admin"')->execute([$id]);
    jsonOut(['success' => true]);
}

// =================== UPDATE PROFILE ===================
if ($action === 'update_profile') {
    $newName = trim($_POST['full_name'] ?? '');
    $updates = [];
    $params  = [];

    if ($newName) {
        $updates[] = 'full_name = ?';
        $params[]  = $newName;
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $filename  = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                $updates[] = 'avatar_image = ?';
                $params[]  = 'uploads/' . $filename;
            }
        }
    }

    if ($updates) {
        $params[] = $user['id'];
        db()->prepare('UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?')->execute($params);
        // تحديث الجلسة
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $updated = $stmt->fetch();
        $_SESSION['user'] = [
            'id'           => $updated['id'],
            'full_name'    => $updated['full_name'],
            'student_id'   => $updated['student_id'],
            'email'        => $updated['email'],
            'avatar_color' => $updated['avatar_color'],
            'avatar_image' => $updated['avatar_image'],
            'role'         => $updated['role'],
        ];
    }
    jsonOut(['success' => true]);
}

jsonOut(['error' => 'unknown action'], 404);