<?php
require_once __DIR__ . '/inc/khoi-tao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dang_xuat();
    dat_thong_bao('ok', 'Bạn đã đăng xuất khỏi trang quản trị.');
}
chuyen_trang('dang-nhap.php');
