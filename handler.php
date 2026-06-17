<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    session_destroy();
    jsonOut(['success' => true]);
}

requireLogin();
$user = currentUser();

switch ($action) {

    // ================= CHAT =================
    case 'chat_get':
        $stmt = db()->query('SELECT m.id, m.message, m.sent_at, u.full_name, u.avatar_color, u.avatar_image, u.id AS user_id FROM chat_messages m JOIN users u ON m.user_id = u.id ORDER BY m.id DESC LIMIT 100');
        jsonOut(['messages' => array_reverse($stmt->fetchAll())]);
        break;

    case 'chat_send':
        $message = trim($_POST['message'] ?? '');
        if (!$message) jsonOut(['error' => 'empty'], 400);
        $stmt = db()->prepare('INSERT INTO chat_messages (user_id, message) VALUES (?, ?)');
        $stmt->execute([$user['id'], $message]);
        jsonOut(['success' => true, 'message' => ['id' => db()->lastInsertId(), 'user_id' => $user['id'], 'full_name' => $user['full_name'], 'avatar_color' => $user['avatar_color'], 'avatar_image' => $user['avatar_image'] ?? null, 'message' => $message, 'sent_at' => date('Y-m-d H:i:s')]]);
        break;

    // ================= BOT (GEMINI AI INTEGRATION) =================
    case 'bot_ask':
        $userMsg = trim($_POST['message'] ?? '');
        if (!$userMsg) jsonOut(['reply' => '...']);

        // --- إعدادات Google Gemini API ---
        $apiKey = "AIzaSyCpLgfGtlKsVFdXNrymg_0GZAKmFjkBRlo"; // مفتاحك
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        // --- صياغة شخصية البوت (System Prompt) ---
        $prompt = "أنت المساعد الذكي لمشروع 'بصيرة'، وهو بوابة طلابية لكلية الاتصالات. 
                   مهمتك مساعدة الطلاب في الإجابة على استفساراتهم الأكاديمية أو البرمجية أو العامة بشكل ودود ومختصر.
                   تحدث باللغة التي يتحدث بها الطالب (عربية أو إنجليزية).
                   سؤال الطالب هو: " . $userMsg;

        $data = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ]
        ];

        // --- الاتصال بـ API جوجل ---
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        // 🔴 تخطي حماية SSL الخاصة بسيرفر XAMPP المحلي 🔴
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        
        if(curl_errno($ch)){
            jsonOut(['reply' => 'خطأ في الاتصال بالذكاء الاصطناعي عبر XAMPP: ' . curl_error($ch)]);
        }
        
        $result = json_decode($response, true);
        curl_close($ch);

        // --- استخراج الرد الذكي ---
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $aiReply = $result['candidates'][0]['content']['parts'][0]['text'];
        } else {
            $aiReply = "عذراً، أواجه مشكلة في الاتصال بالذكاء الاصطناعي. تأكد من اتصال جهازك بالإنترنت. 🤔";
        }
        
        jsonOut(['reply' => $aiReply]);
        break;

    // ================= LOST & FOUND =================
    case 'lost_get':
        $stmt = db()->query('SELECT l.*, u.full_name, u.avatar_color FROM lost_items l JOIN users u ON l.user_id = u.id ORDER BY l.id DESC');
        jsonOut(['items' => $stmt->fetchAll()]);
        break;

    case 'lost_add':
        $name    = trim($_POST['item_name']   ?? '');
        $loc     = trim($_POST['location']    ?? '');
        $desc    = trim($_POST['description'] ?? '');
        $contact = trim($_POST['contact']     ?? '');
        $status  = in_array($_POST['status'] ?? '', ['مفقود','وُجد']) ? $_POST['status'] : 'مفقود';

        if (!$name || !$loc) jsonOut(['error' => 'missing fields'], 400);

        $stmt = db()->prepare('INSERT INTO lost_items (user_id, item_name, location, description, contact, status) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$user['id'], $name, $loc, $desc, $contact, $status]);

        if ($status === 'وُجد') {
            $searchStmt = db()->prepare("SELECT user_id FROM lost_items WHERE status = 'مفقود' AND item_name LIKE ? AND user_id != ? LIMIT 1");
            $searchStmt->execute(["%$name%", $user['id']]);
            $lostUser = $searchStmt->fetch();

            if ($lostUser) {
                $botMsg = "البوت الذكي : تم العثور على غرض مطابق لما فقدته ($name) في ($loc). تحقق من قسم المفقودات!";
                $notifStmt = db()->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
                $notifStmt->execute([$lostUser['user_id'], 'تنبيه مفقودات', $botMsg]);
            }
        }
        jsonOut(['success' => true]);
        break;

    case 'lost_delete':
        if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM lost_items WHERE id = ?')->execute([$id]);
        jsonOut(['success' => true]);
        break;

    // ================= EVENTS =================
    case 'events_get':
        $stmt = db()->query('SELECT * FROM events ORDER BY event_date ASC');
        jsonOut(['events' => $stmt->fetchAll()]);
        break;

    case 'events_add':
        if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $date  = trim($_POST['event_date'] ?? '');
        $color = trim($_POST['color'] ?? '#7C3AED');
        if (!$title || !$date) jsonOut(['error' => 'missing fields'], 400);
        db()->prepare('INSERT INTO events (title, description, event_date, color, created_by) VALUES (?,?,?,?,?)')->execute([$title, $desc, $date, $color, $user['id']]);
        jsonOut(['success' => true, 'id' => db()->lastInsertId()]);
        break;

    case 'events_delete':
        if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
        db()->prepare('DELETE FROM events WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        jsonOut(['success' => true]);
        break;

    // ================= POSTS & SUGGESTIONS =================
    case 'get_posts':
        $stmt = db()->query('SELECT p.*, u.full_name AS user_name, u.avatar_color AS user_color, u.avatar_image AS user_avatar FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.id DESC');
        jsonOut(['posts' => $stmt->fetchAll()]);
        break;

    case 'add_post':
        $content = trim($_POST['content'] ?? '');
        $image_path = null;
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $filename = 'post_' . $user['id'] . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], $uploadDir . $filename)) $image_path = 'uploads/' . $filename;
            }
        }
        if ($content !== '' || $image_path !== null) {
            db()->prepare('INSERT INTO posts (user_id, content, post_image) VALUES (?,?,?)')->execute([$user['id'], $content, $image_path]);
            jsonOut(['success' => true]);
        }
        jsonOut(['success' => false, 'error' => 'Empty post']);
        break;

    case 'get_sugg':
        $stmt = db()->query('SELECT s.*, u.full_name AS user_name, u.avatar_color AS user_color, u.avatar_image AS user_avatar FROM suggestions s JOIN users u ON s.user_id = u.id ORDER BY s.id DESC');
        jsonOut(['suggestions' => $stmt->fetchAll()]);
        break;

    case 'add_sugg':
        $content = trim($_POST['content'] ?? '');
        if ($content) {
            db()->prepare('INSERT INTO suggestions (user_id, content) VALUES (?,?)')->execute([$user['id'], $content]);
            jsonOut(['success' => true]);
        }
        jsonOut(['success' => false]);
        break;

    // ================= PROFILE & ADMIN =================
    case 'admin_stats':
        if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
        jsonOut([
            'users'  => db()->query('SELECT COUNT(*) FROM users WHERE role = "student"')->fetchColumn(),
            'lost'   => db()->query('SELECT COUNT(*) FROM lost_items')->fetchColumn(),
            'events' => db()->query('SELECT COUNT(*) FROM events')->fetchColumn(),
            'posts'  => db()->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
        ]);
        break;

    case 'admin_users':
        if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
        $stmt = db()->query('SELECT id, full_name, student_id, email, avatar_color, role, created_at FROM users ORDER BY id DESC');
        jsonOut(['users' => $stmt->fetchAll()]);
        break;

    case 'admin_delete_user':
        if (!isAdmin()) jsonOut(['error' => 'forbidden'], 403);
        $id = (int)($_POST['id'] ?? 0);
        if ($id !== $user['id']) db()->prepare('DELETE FROM users WHERE id = ? AND role != "admin"')->execute([$id]);
        jsonOut(['success' => true]);
        break;

    case 'update_profile':
        $newName = trim($_POST['full_name'] ?? '');
        $bio     = trim($_POST['bio'] ?? '');
        $color   = trim($_POST['color'] ?? '');
        $updates = []; $params = [];

        if ($newName) { $updates[] = 'full_name = ?'; $params[] = $newName; }
        if ($bio !== '') { $updates[] = 'bio = ?'; $params[] = $bio; }
        if ($color) { $updates[] = 'avatar_color = ?'; $params[] = $color; }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                    $updates[] = 'avatar_image = ?'; $params[] = 'uploads/' . $filename;
                }
            }
        }
        if ($updates) {
            $params[] = $user['id'];
            db()->prepare('UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?')->execute($params);
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ?'); 
            $stmt->execute([$user['id']]);
            $_SESSION['user'] = $stmt->fetch();
        }
        jsonOut(['success' => true]);
        break;

    case 'change_password':
        $curr = $_POST['curr_pass'] ?? '';
        $new  = $_POST['new_pass'] ?? '';
        $conf = $_POST['conf_pass'] ?? '';

        if (!$curr || !$new || !$conf) jsonOut(['error' => 'الرجاء ملء جميع الحقول'], 400);
        if ($new !== $conf) jsonOut(['error' => 'كلمتا المرور غير متطابقتين'], 400);
        if (strlen($new) < 6) jsonOut(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);

        $stmt = db()->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($curr, $hash)) {
            jsonOut(['error' => 'كلمة المرور الحالية غير صحيحة'], 400);
        }

        $newHash = password_hash($new, PASSWORD_DEFAULT);
        db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$newHash, $user['id']]);
        jsonOut(['success' => true]);
        break;

    // ================= GPA TRACKER =================
    case 'gpa_get':
        $stmt = db()->prepare('SELECT * FROM gpa_courses WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$user['id']]);
        jsonOut(['courses' => $stmt->fetchAll()]);
        break;

    case 'gpa_add':
        $course  = trim($_POST['course_name'] ?? '');
        $credits = (int)($_POST['credits'] ?? 0);
        $grade   = trim($_POST['grade'] ?? '');
        if ($course && $credits > 0 && $grade) {
            db()->prepare('INSERT INTO gpa_courses (user_id, course_name, credits, grade) VALUES (?, ?, ?, ?)')->execute([$user['id'], $course, $credits, $grade]);
            jsonOut(['success' => true]);
        }
        jsonOut(['error' => 'Missing data'], 400);
        break;

    case 'gpa_delete':
        db()->prepare('DELETE FROM gpa_courses WHERE id = ? AND user_id = ?')->execute([(int)($_POST['id'] ?? 0), $user['id']]);
        jsonOut(['success' => true]);
        break;

    // ================= ACADEMIC RESOURCES =================
    case 'res_get':
        $stmt = db()->query('SELECT r.*, u.full_name, u.avatar_color, u.avatar_image FROM resources r JOIN users u ON r.user_id = u.id ORDER BY r.id DESC');
        jsonOut(['resources' => $stmt->fetchAll()]);
        break;

    case 'res_add':
        $title = trim($_POST['title'] ?? '');
        $course = trim($_POST['course_name'] ?? '');
        
        if (!$title || !$course) jsonOut(['error' => 'Missing fields'], 400);

        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['resource_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip'];
            
            if (in_array($ext, $allowed)) {
                $filename = 'res_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $uploadDir = __DIR__ . '/uploads/resources/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $uploadDir . $filename)) {
                    $filepath = 'uploads/resources/' . $filename;
                    $stmt = db()->prepare('INSERT INTO resources (user_id, title, course_name, file_path) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$user['id'], $title, $course, $filepath]);
                    jsonOut(['success' => true]);
                }
            } else {
                jsonOut(['error' => 'Invalid file type'], 400);
            }
        }
        jsonOut(['error' => 'File upload failed'], 400);
        break;

    case 'res_delete':
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT user_id, file_path FROM resources WHERE id = ?');
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        
        if ($res && ($res['user_id'] == $user['id'] || isAdmin())) {
            db()->prepare('DELETE FROM resources WHERE id = ?')->execute([$id]);
            if (file_exists(__DIR__ . '/' . $res['file_path'])) {
                unlink(__DIR__ . '/' . $res['file_path']);
            }
            jsonOut(['success' => true]);
        }
        jsonOut(['error' => 'Forbidden'], 403);
        break;

    // ================= NOTIFICATIONS =================
    case 'notif_get':
        $stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 10');
        $stmt->execute([$user['id']]);
        $unread = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $unread->execute([$user['id']]);
        jsonOut(['notifications' => $stmt->fetchAll(), 'unread' => $unread->fetchColumn()]);
        break;

    case 'notif_read':
        db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$user['id']]);
        jsonOut(['success' => true]);
        break;

    default:
        jsonOut(['error' => 'unknown action'], 404);
        break;
}