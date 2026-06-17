<?php
// ملف: api/update_profile.php
require_once '../config.php';
requireLogin();

$user_id = currentUser()['id'];
$users = readJSON('users.json');
$userIndex = array_search($user_id, array_column($users, 'id'));

if ($userIndex !== false) {
    // تحديث الاسم
    $newName = trim($_POST['full_name'] ?? '');
    if ($newName) {
        $users[$userIndex]['full_name'] = $newName;
    }

    // معالجة رفع الصورة الشخصية
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($ext), $allowed)) {
            $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
            
            // التأكد من وجود مجلد الرفع
            $uploadDir = __DIR__ . '/../data/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $users[$userIndex]['avatar_image'] = 'data/uploads/' . $filename;
            }
        }
    }

    // حفظ التعديلات في الملف
    writeJSON('users.json', $users);

    // تحديث الجلسة (Session) للمستخدم الحالي
    $_SESSION['user'] = $users[$userIndex];

    jsonOut(['success' => true]);
}

jsonOut(['success' => false, 'error' => 'User not found'], 400);
?>