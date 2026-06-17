<?php
// ملف: api/posts_sugg.php
require_once '../config.php';
requireLogin();

$action = $_GET['action'] ?? '';
$user = currentUser();

if ($action === 'get_posts') {
    $posts = readJSON('posts.json');
    jsonOut(['posts' => array_reverse($posts)]);
    
} elseif ($action === 'add_post') {
    $content = trim($_POST['content'] ?? '');
    $image_path = null;

    // معالجة رفع صورة المنشور (إن وجدت)
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($ext), $allowed)) {
            $filename = 'post_' . $user['id'] . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../data/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $dest)) {
                $image_path = 'data/uploads/' . $filename;
            }
        }
    }

    // السماح بنشر الصورة حتى لو لم يكن هناك نص، والعكس
    if ($content !== '' || $image_path !== null) {
        $posts = readJSON('posts.json');
        $posts[] = [
            'id'          => nextId($posts),
            'user_name'   => $user['full_name'],
            'user_color'  => $user['avatar_color'],
            'user_avatar' => $user['avatar_image'] ?? null,
            'content'     => $content,
            'post_image'  => $image_path, // مسار الصورة المحفوظ
            'date'        => date('Y-m-d H:i')
        ];
        writeJSON('posts.json', $posts);
        jsonOut(['success' => true]);
    }
    jsonOut(['success' => false, 'error' => 'Empty post']);

} elseif ($action === 'get_sugg') {
    $suggs = readJSON('suggestions.json');
    jsonOut(['suggestions' => array_reverse($suggs)]);

} elseif ($action === 'add_sugg') {
    $content = trim($_POST['content'] ?? '');
    if ($content) {
        $suggs = readJSON('suggestions.json');
        $suggs[] = [
            'id' => nextId($suggs),
            'user_name' => $user['full_name'],
            'user_color' => $user['avatar_color'],
            'user_avatar' => $user['avatar_image'] ?? null,
            'content' => $content,
            'date' => date('Y-m-d H:i')
        ];
        writeJSON('suggestions.json', $suggs);
        jsonOut(['success' => true]);
    }
    jsonOut(['success' => false, 'error' => 'Empty content']);
}
?>