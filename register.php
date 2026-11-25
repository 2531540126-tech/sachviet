<?php
require_once __DIR__ . '/inc/khoi-tao.php';

if (nguoi_dung_hien_tai()) {
    chuyen_trang('index.php');
}

$f = ['ho_ten' => '', 'email' => '', 'so_dien_thoai' => '', 'dong_y' => false];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = doc_form_dang_ky();
    $loi = kiem_tra_dang_ky($db, $f);

    if (!$loi) {
        dang_ky_tai_khoan($db, $f);
        dat_thong_bao('ok', 'Đăng ký thành công! Mời bạn đăng nhập bằng email ' . $f['email'] . '.');
        chuyen_trang('login.php?email=' . urlencode($f['email']));
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng ký | SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<main class="page auth-page">
  <div class="auth-wrap">
    <div class="auth-side" aria-hidden="true">
      <h2>Tạo tài khoản mới</h2>
      <p>Miễn phí, chỉ mất một phút. Đăng ký để nhận ngay mã giảm 15% cho đơn đầu tiên.</p>
      <div class="auth-books">
        <span class="auth-book"><?= bia('Tắt đèn', 'Ngô Tất Tố', '#8B5E9E') ?></span>
        <span class="auth-book"><?= bia('Số đỏ', 'Vũ Trọng Phụng', '#8B5E9E') ?></span>
        <span class="auth-book"><?= bia('Giông tố', 'Vũ Trọng Phụng', '#8B5E9E') ?></span>
      </div>
    </div>

    <div class="auth-card">
      <h1>Đăng ký</h1>
      <p class="auth-sub">Điền thông tin để tạo tài khoản</p>

<?php if ($loi): ?>
      <p class="form-err">Chưa tạo được tài khoản. Vui lòng sửa <?= count($loi) ?> ô được đánh dấu đỏ bên dưới.</p>
<?php endif; ?>

      <form action="register.php" method="post" class="form">
        <div class="field<?= isset($loi['ho_ten']) ? ' has-err' : '' ?>">
          <label for="hoten">Họ và tên</label>
          <div class="input-ico"><span>👤</span>
            <input type="text" id="hoten" name="ho_ten" value="<?= e($f['ho_ten']) ?>" placeholder="Nguyễn Văn A" maxlength="100" required></div>
          <?= loi_o($loi, 'ho_ten') ?>
        </div>

        <div class="field<?= isset($loi['email']) ? ' has-err' : '' ?>">
          <label for="r-email">Email</label>
          <div class="input-ico"><span>✉</span>
            <input type="email" id="r-email" name="email" value="<?= e($f['email']) ?>" placeholder="bạn@email.com" maxlength="150" required></div>
          <?= loi_o($loi, 'email') ?>
        </div>

        <div class="field<?= isset($loi['so_dien_thoai']) ? ' has-err' : '' ?>">
          <label for="sdt">Số điện thoại</label>
          <div class="input-ico"><span>☎</span>
            <input type="tel" id="sdt" name="so_dien_thoai" value="<?= e($f['so_dien_thoai']) ?>" placeholder="09xx xxx xxx" required></div>
          <?= loi_o($loi, 'so_dien_thoai') ?>
        </div>

        <div class="field-2">
          <div class="field<?= isset($loi['mat_khau']) ? ' has-err' : '' ?>">
            <label for="r-pass">Mật khẩu</label>
            <div class="input-ico"><span>🔒</span>
              <input type="password" id="r-pass" name="mat_khau" placeholder="Ít nhất 6 ký tự" required></div>
            <?= loi_o($loi, 'mat_khau') ?>
          </div>
          <div class="field<?= isset($loi['nhap_lai']) ? ' has-err' : '' ?>">
            <label for="r-pass2">Nhập lại mật khẩu</label>
            <div class="input-ico"><span>🔒</span>
              <input type="password" id="r-pass2" name="nhap_lai" placeholder="••••••••" required></div>
            <?= loi_o($loi, 'nhap_lai') ?>
          </div>
        </div>

        <div class="field<?= isset($loi['dong_y']) ? ' has-err' : '' ?>">
          <label class="check"><input type="checkbox" name="dong_y" value="1"<?= $f['dong_y'] ? ' checked' : '' ?>>
            <span>Tôi đồng ý với Điều khoản sử dụng và Chính sách bảo mật của SáchViệt</span></label>
          <?= loi_o($loi, 'dong_y') ?>
        </div>

        <button type="submit" class="btn btn-solid btn-block btn-lg">Tạo tài khoản</button>
      </form>

      <p class="auth-switch">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
