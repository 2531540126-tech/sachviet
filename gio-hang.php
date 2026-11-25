<?php
require_once __DIR__ . '/inc/khoi-tao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanh_dong = post_chuoi('hanh_dong');
    $quay_lai  = duong_dan_an_toan(post_chuoi('quay_lai'), 'gio-hang.php');
    $sach_id   = post_so('sach_id');

    $nd = $hanh_dong === 'them'
        ? bat_buoc_dang_nhap('Vui lòng đăng nhập để thêm sách vào giỏ hàng.', $quay_lai)
        : bat_buoc_dang_nhap('Vui lòng đăng nhập để dùng giỏ hàng.', 'gio-hang.php');

    $di_toi = 'gio-hang.php';
    switch ($hanh_dong) {
        case 'them':
            [$loai, $noi_dung] = them_vao_gio($db, $nd['id'], $sach_id, max(1, post_so('so_luong')));
            if (post_chuoi('sau_khi') !== 'gio-hang') {
                $di_toi = $quay_lai;
            }
            break;
        case 'cap_nhat':
            [$loai, $noi_dung] = cap_nhat_so_luong($db, $nd['id'], $sach_id, post_so('so_luong'));
            break;
        case 'xoa':
            [$loai, $noi_dung] = xoa_khoi_gio($db, $nd['id'], $sach_id);
            break;
        case 'ma_giam':
            [$loai, $noi_dung] = ap_dung_ma_giam($db, $nd['id'], post_chuoi('ma'));
            break;
        case 'bo_ma':
            unset($_SESSION['ma_giam']);
            [$loai, $noi_dung] = ['ok', 'Đã bỏ mã giảm giá.'];
            break;
        default:
            [$loai, $noi_dung] = ['loi', 'Thao tác không hợp lệ.'];
    }
    dat_thong_bao($loai, $noi_dung);
    chuyen_trang($di_toi);
}

$nd = bat_buoc_dang_nhap('Vui lòng đăng nhập để xem giỏ hàng của bạn.');
$ds_trong_gio = lay_gio_hang($db, $nd['id']);
$ma_giam      = $_SESSION['ma_giam'] ?? '';
$tien         = tinh_tien_gio($ds_trong_gio, phan_tram_giam());
$con_thieu    = MIEN_PHI_TU - ($tien['tam_tinh'] - $tien['giam_gia']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Giỏ hàng | SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<main class="page">
  <nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="index.php">Trang chủ</a> <span>›</span> <strong>Giỏ hàng</strong>
  </nav>

  <h1 class="page-title">Giỏ hàng của bạn</h1>
  <p class="page-sub"><?= count($ds_trong_gio) ?> đầu sách · <?= $tien['so_cuon'] ?> cuốn</p>

  <ol class="steps">
    <li class="done"><span>1</span> Giỏ hàng</li>
    <li><span>2</span> Thông tin &amp; thanh toán</li>
    <li><span>3</span> Hoàn tất</li>
  </ol>

  <div class="cart-layout">
    <div class="cart-main">
      <table class="cart-table">
        <thead>
          <tr>
            <th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th><th></th>
          </tr>
        </thead>
        <tbody>
<?php if (!$ds_trong_gio): ?>
        <tr>
          <td colspan="5" class="cart-trong">
            Giỏ hàng đang trống — <a href="index.php#danh-sach">chọn sách ngay</a></td>
        </tr>
<?php endif; ?>
<?php foreach ($ds_trong_gio as $sp): ?>
        <tr>
          <td class="c-sp">
            <span class="c-bia"><?= bia_nho($sp['ten'], $sp['mau']) ?></span>
            <span class="c-info">
              <a href="sach.php?id=<?= $sp['sach_id'] ?>"><strong><?= e($sp['ten']) ?></strong></a>
              <em><?= e($sp['tac_gia']) ?></em>
              <span class="c-tl"><?= e($sp['ten_the_loai']) ?></span>
            </span>
          </td>
          <td class="c-gia"><?= tien($sp['gia']) ?></td>
          <td class="c-sl">
            <form class="qty" action="gio-hang.php" method="post">
              <input type="hidden" name="hanh_dong" value="cap_nhat">
              <input type="hidden" name="sach_id" value="<?= $sp['sach_id'] ?>">
              <button type="submit" class="qty-btn" name="so_luong" value="<?= $sp['so_luong'] - 1 ?>" aria-label="Bớt 1 cuốn">−</button>
              <span class="qty-num"><?= $sp['so_luong'] ?></span>
              <button type="submit" class="qty-btn" name="so_luong" value="<?= $sp['so_luong'] + 1 ?>" aria-label="Thêm 1 cuốn"<?= $sp['so_luong'] >= $sp['ton_kho'] ? ' disabled' : '' ?>>+</button>
            </form>
<?php if ($sp['so_luong'] > $sp['ton_kho']): ?>
            <span class="c-canh-bao">Kho chỉ còn <?= $sp['ton_kho'] ?> cuốn</span>
<?php endif; ?>
          </td>
          <td class="c-tt"><?= tien($sp['so_luong'] * $sp['gia']) ?></td>
          <td class="c-xoa">
            <form action="gio-hang.php" method="post">
              <input type="hidden" name="hanh_dong" value="xoa">
              <input type="hidden" name="sach_id" value="<?= $sp['sach_id'] ?>">
              <button type="submit" class="nut-xoa" aria-label="Xoá <?= e($sp['ten']) ?> khỏi giỏ">✕</button>
            </form>
          </td>
        </tr>
<?php endforeach; ?>
        </tbody>
      </table>

      <div class="cart-actions">
        <a href="index.php#danh-sach" class="btn btn-ghost">← Tiếp tục mua hàng</a>
<?php if ($ma_giam !== ''): ?>
        <form class="coupon" action="gio-hang.php" method="post">
          <input type="hidden" name="hanh_dong" value="bo_ma">
          <span class="coupon-on">🎟️ <?= e($ma_giam) ?> · giảm <?= phan_tram_giam() ?>%</span>
          <button type="submit" class="btn btn-outline">Bỏ mã</button>
        </form>
<?php else: ?>
        <form class="coupon" action="gio-hang.php" method="post">
          <input type="hidden" name="hanh_dong" value="ma_giam">
          <label class="sr-only" for="ma">Mã giảm giá</label>
          <input type="text" id="ma" name="ma" placeholder="Nhập mã giảm giá" maxlength="20" required>
          <button type="submit" class="btn btn-outline">Áp dụng</button>
        </form>
<?php endif; ?>
      </div>
    </div>

    <aside class="cart-side">
      <div class="sum-box">
        <h3>Tóm tắt đơn hàng</h3>
        <div class="sum-row"><span>Tạm tính (<?= $tien['so_cuon'] ?> cuốn)</span><strong><?= tien($tien['tam_tinh']) ?></strong></div>
<?php if ($tien['giam_gia'] > 0): ?>
        <div class="sum-row sum-giam"><span>Giảm giá (<?= e($ma_giam) ?>)</span><strong>−<?= tien($tien['giam_gia']) ?></strong></div>
<?php endif; ?>
        <div class="sum-row"><span>Phí vận chuyển</span><strong><?= $ds_trong_gio && $tien['phi_ship'] == 0 ? 'Miễn phí' : tien($tien['phi_ship']) ?></strong></div>
        <div class="sum-row sum-total"><span>Tổng tiền</span><strong><?= tien($tien['tong_tien']) ?></strong></div>
<?php if ($ds_trong_gio): ?>
        <a href="thanh-toan.php" class="btn btn-solid btn-block btn-lg">Tiến hành thanh toán</a>
<?php if ($tien['phi_ship'] > 0 && $con_thieu > 0): ?>
        <p class="sum-note">Mua thêm <?= tien($con_thieu) ?> để được miễn phí vận chuyển</p>
<?php endif; ?>
<?php else: ?>
        <span class="btn btn-solid btn-block btn-lg is-disabled">Tiến hành thanh toán</span>
<?php endif; ?>
      </div>

      <div class="sum-box">
        <h3>Ưu đãi đang áp dụng</h3>
        <ul class="uu-dai">
          <li>🚚 Miễn phí ship cho đơn từ <?= tien(MIEN_PHI_TU) ?></li>
          <li>🎁 Giảm 15% đơn đầu tiên (SACHVIET15)</li>
          <li>📖 Tặng bookmark cho mọi đơn hàng</li>
        </ul>
      </div>
    </aside>
  </div>
</main>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
