<?php

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'sachviet';
const DB_USER = 'root';
const DB_PASS = '';

date_default_timezone_set('Asia/Ho_Chi_Minh');

try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $loi) {
    $thong_bao = $loi->getMessage();
    if (strpos($thong_bao, '1049') !== false) {
        $goi_y = 'Chưa có database <b>' . DB_NAME . '</b>. Mở phpMyAdmin → tab <b>Nhập (Import)</b> → chọn file <code>database/sachviet.sql</code> → bấm <b>Thực hiện (Go)</b>.';
    } elseif (strpos($thong_bao, '1045') !== false) {
        $goi_y = 'Sai tài khoản hoặc mật khẩu MySQL. Sửa DB_USER / DB_PASS ở đầu file <code>inc/ket-noi.php</code>.';
    } else {
        $goi_y = 'MySQL chưa chạy. Mở <b>XAMPP Control Panel</b> → bấm <b>Start</b> ở dòng <b>MySQL</b> (chữ chuyển xanh là được), rồi tải lại trang này.';
    }
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Chưa kết nối được database</title></head>'
       . '<body style="font-family:system-ui,sans-serif;max-width:640px;margin:60px auto;padding:0 20px;line-height:1.6;color:#2A2320">'
       . '<h1 style="color:#B3453A">Chưa kết nối được database</h1>'
       . '<p>' . $goi_y . '</p>'
       . '<p style="color:#6B5E52;font-size:14px">Chi tiết lỗi: <code>' . htmlspecialchars($thong_bao, ENT_QUOTES, 'UTF-8') . '</code></p>'
       . '</body></html>';
    exit;
}

set_exception_handler(function (Throwable $loi) {
    http_response_code(500);
    echo '<div style="font-family:system-ui,sans-serif;max-width:760px;margin:40px auto;padding:18px 22px;'
       . 'border:2px solid #B3453A;border-radius:12px;background:#FFF6F4;color:#2A2320;line-height:1.6">'
       . '<h2 style="margin:0 0 8px;color:#B3453A">Lỗi khi đọc dữ liệu từ database</h2>'
       . '<p style="margin:0 0 8px">Thông báo của MySQL: <code>' . htmlspecialchars($loi->getMessage(), ENT_QUOTES, 'UTF-8') . '</code></p>'
       . '<p style="margin:0;font-size:14px;color:#6B5E52">Nếu bạn vừa đổi tên/xoá bảng hoặc cột trong phpMyAdmin, hãy đổi lại cho khớp '
       . 'với file <code>database/sachviet.sql</code> (hoặc import lại file đó để khôi phục dữ liệu mẫu).</p></div>';
});
