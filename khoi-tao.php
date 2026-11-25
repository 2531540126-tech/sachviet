<?php

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require_once __DIR__ . '/ket-noi.php';

require_once __DIR__ . '/ham.php';
require_once __DIR__ . '/ham-tai-khoan.php';
require_once __DIR__ . '/ham-sach.php';
require_once __DIR__ . '/ham-gio-hang.php';
require_once __DIR__ . '/ham-don-hang.php';

lam_moi_phien($db);
