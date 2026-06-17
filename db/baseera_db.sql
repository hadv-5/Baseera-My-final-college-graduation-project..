-- =============================================
-- قاعدة بيانات بصيرة - baseera_db.sql (النسخة المحدثة والشاملة)
-- استورد هذا الملف في phpMyAdmin
-- =============================================

CREATE DATABASE IF NOT EXISTS baseera_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE baseera_db;

-- =============================================
-- 1. جدول المستخدمين (طلاب + أدمن)
-- تم إضافة عمود bio (النبذة الشخصية)
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    student_id    VARCHAR(50)  UNIQUE NOT NULL,
    email         VARCHAR(150) UNIQUE NOT NULL,
    password      VARCHAR(255) NOT NULL,
    avatar_color  VARCHAR(20)  DEFAULT '#7C3AED',
    avatar_image  VARCHAR(255) DEFAULT NULL,
    bio           TEXT DEFAULT NULL,
    role          ENUM('student','admin') DEFAULT 'student',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- إنشاء حساب الأدمن الافتراضي
-- كلمة المرور: admin123
-- =============================================
INSERT INTO users (full_name, student_id, email, password, avatar_color, role)
VALUES (
    'مدير النظام',
    'ADMIN001',
    'admin@college.edu',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    '#FF6B2B',
    'admin'
);

-- =============================================
-- 2. جدول رسائل الشات العام
-- =============================================
CREATE TABLE IF NOT EXISTS chat_messages (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    message       TEXT NOT NULL,
    sent_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 3. جدول المفقودات
-- =============================================
CREATE TABLE IF NOT EXISTS lost_items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    item_name     VARCHAR(200) NOT NULL,
    location      VARCHAR(200) NOT NULL,
    description   TEXT,
    contact       VARCHAR(200),
    status        ENUM('مفقود','وُجد') DEFAULT 'مفقود',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 4. جدول الفعاليات (يضيفها الأدمن فقط)
-- =============================================
CREATE TABLE IF NOT EXISTS events (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(200) NOT NULL,
    description   TEXT,
    event_date    DATE NOT NULL,
    color         VARCHAR(20)  DEFAULT '#7C3AED',
    created_by    INT NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 5. جدول المنشورات
-- =============================================
CREATE TABLE IF NOT EXISTS posts (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    content       TEXT,
    post_image    VARCHAR(255) DEFAULT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 6. جدول الاقتراحات
-- =============================================
CREATE TABLE IF NOT EXISTS suggestions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    content       TEXT NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 7. جدول حاسبة المعدل (GPA Tracker) [جديد]
-- =============================================
CREATE TABLE IF NOT EXISTS gpa_courses (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    course_name   VARCHAR(255) NOT NULL,
    credits       INT NOT NULL,
    grade         VARCHAR(5) NOT NULL,
    semester      VARCHAR(50) DEFAULT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 8. جدول الإشعارات الذكية (Notifications) [جديد]
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    title         VARCHAR(255) NOT NULL,
    message       TEXT NOT NULL,
    is_read       TINYINT(1) DEFAULT 0,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 9. جدول بنك الموارد والملخصات (Academic Resources) [جديد]
-- =============================================
CREATE TABLE IF NOT EXISTS resources (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    title         VARCHAR(255) NOT NULL,
    course_name   VARCHAR(255) NOT NULL,
    file_path     VARCHAR(255) NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- بيانات تجريبية للفعاليات
-- =============================================
INSERT INTO events (title, description, event_date, color, created_by) VALUES
('يوم الاتصالات المفتوح', 'فعالية سنوية لعرض مشاريع الطلاب وإنجازاتهم', '2025-05-15', '#FF6B2B', 1),
('ورشة الذكاء الاصطناعي', 'ورشة تدريبية على تقنيات الذكاء الاصطناعي وتطبيقاتها', '2025-04-20', '#7C3AED', 1),
('هاكاثون الكلية', 'منافسة برمجية بين فرق الطلاب لمدة 24 ساعة', '2025-06-01', '#10B981', 1),
('يوم التخرج', 'حفل تكريم الدفعة الجديدة من خريجي الكلية', '2025-07-10', '#FFB800', 1);