<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$quay_lai = duong_dan_an_toan($_SERVER['REQUEST_METHOD'] === 'POST' ? post_chuoi('quay_lai') : get_chuoi('quay_lai'));
$nd = nguoi_dung_hien_tai();
$email = '';
$loi = '';

if (la_quan_tri($nd)) {
    chuyen_trang($quay_lai);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $nd === null) {
    $email = post_chuoi('email');
    $mat_khau = post_mat_khau('mat_khau');

    if ($email === '' || $mat_khau === '') {
        $loi = 'Vui lòng nhập đủ email và mật khẩu.';
    } else {
        $ket_qua = dang_nhap($db, $email, $mat_khau);
        if (isset($ket_qua['loi'])) {
            $loi = $ket_qua['loi'];
        } elseif (!la_quan_tri($ket_qua['nguoi_dung'])) {
            dang_xuat();
            $loi = 'Tài khoản này là tài khoản khách hàng, không có quyền vào trang quản trị.';
        } else {
            dat_thong_bao('ok', 'Xin chào ' . $ket_qua['nguoi_dung']['ho_ten'] . ', chúc bạn một ngày làm việc hiệu quả!');
            chuyen_trang($quay_lai);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập quản trị | SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="quan-tri.css">
</head>
<body class="qt-body">

<div class="qt-dn">
  <section class="qt-dn-trai">
    <div>
      <a href="../index.php" class="qt-logo">
        <span class="logo-icon">📖</span>
        <span class="qt-logo-chu">Sách<strong>Việt</strong><small>Trang quản trị</small></span>
      </a>
      <h1>Quản lý <span>nhà sách</span> ở một nơi</h1>
      <p>Dành cho quản trị viên và nhân viên của SáchViệt. Đăng nhập để theo dõi doanh thu,
         cập nhật sách, xử lý đơn hàng và quản lý tài khoản khách hàng.</p>
      <ul class="qt-dn-ds">
        <li>📊 Tổng quan doanh thu, đơn hàng, tồn kho</li>
        <li>📚 Thêm, sửa, ẩn hoặc xoá sách và loại sách</li>
        <li>🧾 Xác nhận, giao và huỷ đơn hàng</li>
        <li>👥 Khoá, mở khoá và phân quyền người dùng</li>
      </ul>
    </div>
    <div class="qt-dn-sach" aria-hidden="true">
      <span><?= bia('Truyện Kiều', 'Nguyễn Du', '#A8551E') ?></span>
      <span><?= bia('Số đỏ', 'Vũ Trọng Phụng', '#8B5E9E') ?></span>
      <span><?= bia('Lục Vân Tiên', 'Nguyễn Đình Chiểu', '#4A7FA5') ?></span>
    </div>
  </section>

  <main class="qt-dn-phai">
    <div class="qt-dn-the">
      <?= hien_thong_bao() ?>
<?php if ($nd): ?>
      <h2>Bạn đang dùng tài khoản khách</h2>
      <p>Đang đăng nhập bằng <strong><?= e($nd['email']) ?></strong>. Tài khoản này không có quyền quản trị.</p>
      <form class="form" action="dang-xuat.php" method="post">
        <button type="submit" class="btn btn-solid btn-block btn-lg">Đăng xuất để đổi tài khoản</button>
        <a href="../index.php" class="btn btn-ghost btn-block">Về cửa hàng</a>
      </form>
<?php else: ?>
      <h2>Đăng nhập quản trị</h2>
      <p>Dùng email và mật khẩu của tài khoản quản trị viên hoặc nhân viên.</p>
<?php if ($loi !== ''): ?>
      <p class="form-err"><?= e($loi) ?></p>
<?php endif; ?>
      <form class="form" action="dang-nhap.php" method="post">
        <input type="hidden" name="quay_lai" value="<?= e($quay_lai) ?>">
        <div class="field">
          <label for="email">Email</label>
          <div class="input-ico">
            <span>✉</span>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="admin@sachviet.vn" autocomplete="email" required>
          </div>
        </div>
        <div class="field">
          <label for="mat-khau">Mật khẩu</label>
          <div class="input-ico">
            <span>🔒</span>
            <input type="password" id="mat-khau" name="mat_khau" placeholder="••••••••" autocomplete="current-password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-solid btn-block btn-lg">Đăng nhập</button>
      </form>
      <p class="qt-dn-goi-y">Không phải nhân viên? <a href="../index.php">Quay lại cửa hàng</a></p>
<?php endif; ?>
    </div>
  </main>
</div>

</body>
</html>
