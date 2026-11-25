<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$quay_lai = duong_dan_an_toan($_SERVER['REQUEST_METHOD'] === 'POST' ? post_chuoi('quay_lai') : get_chuoi('quay_lai'));
$email = get_chuoi('email');
$loi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = post_chuoi('email');
    $mat_khau = post_mat_khau('mat_khau');

    if ($email === '' || $mat_khau === '') {
        $loi = 'Vui lòng nhập đủ email và mật khẩu.';
    } else {
        $ket_qua = dang_nhap($db, $email, $mat_khau);
        if (isset($ket_qua['loi'])) {
            $loi = $ket_qua['loi'];
        } else {
            dat_thong_bao('ok', 'Đăng nhập thành công. Xin chào ' . $ket_qua['nguoi_dung']['ho_ten'] . '!');
            chuyen_trang($quay_lai);
        }
    }
}

$nd = nguoi_dung_hien_tai();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập | SáchViệt</title>
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
      <h2>Chào mừng trở lại</h2>
      <p>Đăng nhập để thêm sách vào giỏ, theo dõi đơn hàng và lưu sách yêu thích.</p>
      <div class="auth-books">
        <span class="auth-book"><?= bia('Truyện Kiều', 'Nguyễn Du', '#8B5E9E') ?></span>
        <span class="auth-book"><?= bia('Chí Phèo', 'Nam Cao', '#3F7D58') ?></span>
        <span class="auth-book"><?= bia('Lão Hạc', 'Nam Cao', '#3F7D58') ?></span>
      </div>
    </div>

    <div class="auth-card">
<?php if ($nd): ?>
      <h1>Bạn đã đăng nhập</h1>
      <p class="auth-sub">Đang dùng tài khoản <strong><?= e($nd['email']) ?></strong>.</p>
      <div class="form">
        <a href="index.php" class="btn btn-solid btn-block btn-lg">Về trang chủ</a>
        <form action="dang-xuat.php" method="post">
          <button type="submit" class="btn btn-ghost btn-block">Đăng xuất để đổi tài khoản</button>
        </form>
      </div>
<?php else: ?>
      <h1>Đăng nhập</h1>
      <p class="auth-sub">Nhập email và mật khẩu của bạn</p>

<?php if ($loi !== ''): ?>
      <p class="form-err"><?= e($loi) ?></p>
<?php endif; ?>

      <form action="login.php" method="post" class="form">
        <input type="hidden" name="quay_lai" value="<?= e($quay_lai) ?>">
        <div class="field">
          <label for="email">Email</label>
          <div class="input-ico">
            <span>✉</span>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="bạn@email.com" required>
          </div>
        </div>

        <div class="field">
          <label for="pass">Mật khẩu</label>
          <div class="input-ico">
            <span>🔒</span>
            <input type="password" id="pass" name="mat_khau" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn btn-solid btn-block btn-lg">Đăng nhập</button>
      </form>

      <p class="auth-switch">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
<?php endif; ?>
    </div>
  </div>
</main>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
