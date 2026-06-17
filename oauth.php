<?php
// ============================================================================
// Microsoft Azure AD OAuth 2.0 Flow Controller — Baseera
// ============================================================================
require_once 'config.php';

$action = $_GET['action'] ?? '';

// 1. توجيه المستخدم إلى صفحة المصادقة الخاصة بـ Microsoft
if ($action === 'login') {
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
    
    $params = [
        'client_id'     => MS_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri'  => MS_REDIRECT_URI,
        'scope'         => 'openid profile email User.Read',
        'response_mode' => 'query',
        'state'         => $_SESSION['oauth_state']
    ];
    
    $authUrl = 'https://login.microsoftonline.com/' . MS_TENANT_ID . '/oauth2/v2.0/authorize?' . http_build_query($params);
    header('Location: ' . $authUrl);
    exit;
}

// 2. استقبال رد مايكروسوفت (Callback Flow)
$code  = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

if ($error) {
    $_SESSION['auth_error'] = "فشلت المصادقة عبر Microsoft: " . htmlspecialchars($_GET['error_description'] ?? $error);
    header('Location: login.php');
    exit;
}

if ($code) {
    // حماية ضد ثغرات الـ CSRF
    if ($state !== ($_SESSION['oauth_state'] ?? '')) {
        $_SESSION['auth_error'] = "جلسة مصادقة غير صالحة. يرجى المحاولة مجدداً.";
        header('Location: login.php');
        exit;
    }

    // --- إرسال طلب للحصول على الـ Access Token ---
    $tokenUrl = 'https://login.microsoftonline.com/' . MS_TENANT_ID . '/oauth2/v2.0/token';
    
    $postData = [
        'client_id'     => MS_CLIENT_ID,
        'client_secret' => MS_CLIENT_SECRET,
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'redirect_uri'  => MS_REDIRECT_URI
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // مفعل لتفادي مشاكل الـ SSL بالسيرفر المحلي
    
    $response = curl_exec($ch);
    $tokenData = json_decode($response, true);
    curl_close($ch);

    if (isset($tokenData['access_token'])) {
        $accessToken = $tokenData['access_token'];

        // --- جلب بيانات المستخدم من Microsoft Graph API ---
        $graphUrl = 'https://graph.microsoft.com/v1.0/me';
        
        $ch2 = curl_init($graphUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        
        $userResponse = curl_exec($ch2);
        $msUser = json_decode($userResponse, true);
        curl_close($ch2);

        if (isset($msUser['mail']) || isset($msUser['userPrincipalName'])) {
            $userEmail = $msUser['mail'] ?? $msUser['userPrincipalName'];
            $userName  = $msUser['displayName'] ?? 'طالب تقني';

            // 🔴 التحقق الصارم من نطاق البريد (يجب أن يكون @tvtc.edu.sa) 🔴
            if (!preg_match('/@tvtc\.edu\.sa$/i', $userEmail)) {
                $_SESSION['auth_error'] = __('err_domain');
                header('Location: login.php');
                exit;
            }

            // استخراج الرقم الجامعي من البريد (في الغالب يبدأ به إيميل الطالب الجامعي)
            $extractedSid = explode('@', $userEmail)[0]; 

            // فحص هل المستخدم مسجل مسبقاً في قاعدة البيانات
            $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$userEmail]);
            $dbUser = $stmt->fetch();

            if (!$dbUser) {
                // إذا لم يكن مسجلاً، نقوم بإنشاء حساب له تلقائياً
                $colors = ['#2563EB','#7C3AED','#10B981','#0072FF','#EC4899','#F59E0B'];
                $color  = $colors[array_rand($colors)];
                // كلمة مرور وهمية ومعقدة لأن الدخول سيظل معتمداً على OAuth
                $randomPass = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);

                $insertStmt = db()->prepare('INSERT INTO users (full_name, student_id, email, password, avatar_color, role) VALUES (?,?,?,?,?,?)');
                $insertStmt->execute([$userName, $extractedSid, $userEmail, $randomPass, $color, 'student']);
                
                // سحب بيانات المستخدم الجديد لبدء الجلسة
                $stmt->execute([$userEmail]);
                $dbUser = $stmt->fetch();
            }

            // --- إتمام عملية تسجيل الدخول بنجاح ---
            $_SESSION['user'] = [
                'id'           => $dbUser['id'],
                'full_name'    => $dbUser['full_name'],
                'student_id'   => $dbUser['student_id'],
                'email'        => $dbUser['email'],
                'avatar_color' => $dbUser['avatar_color'],
                'avatar_image' => $dbUser['avatar_image'],
                'role'         => $dbUser['role'],
            ];

            header('Location: ' . ($dbUser['role'] === 'admin' ? 'admin.php' : 'index.php'));
            exit;
        } else {
            $_SESSION['auth_error'] = "لم نتمكن من قراءة البريد الإلكتروني من حسابك في Microsoft.";
        }
    } else {
        $_SESSION['auth_error'] = "فشل استخراج Access Token من خوادم Microsoft.";
    }
    
    header('Location: login.php');
    exit;
}

// إذا تم الدخول للصفحة بشكل مباشر بدون معاملات
header('Location: login.php');
exit;
?>