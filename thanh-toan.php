<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_dang_nhap('Vui lòng đăng nhập để thanh toán.');
$ds_trong_gio = lay_gio_hang($db, $nd['id']);
if (!$ds_trong_gio) {
    dat_thong_bao('loi', 'Giỏ hàng đang trống, hãy chọn sách trước khi thanh toán.');
    chuyen_trang('gio-hang.php');
}

$f = lay_thong_tin_giao_hang($db, $nd['id']);
$loi = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = doc_form_thanh_toan();
    $loi = kiem_tra_thanh_toan($f);
    if (!$loi) {
        [$loai, $ket_qua] = dat_hang($db, $nd['id'], $f, phan_tram_giam());
        if ($loai === 'ok') {
            dat_thong_bao('ok', 'Đặt hàng thành công! Mã đơn của bạn là ' . ma_don($ket_qua) . '.');
            chuyen_trang('lich-su-don-hang.php');
        }
        dat_thong_bao('loi', $ket_qua);
        chuyen_trang('gio-hang.php');
    }
}
$tien = tinh_tien_gio($ds_trong_gio, phan_tram_giam());
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thanh toán | SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<main class="page">
  <nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="index.php">Trang chủ</a> <span>›</span>
    <a href="gio-hang.php">Giỏ hàng</a> <span>›</span> <strong>Thanh toán</strong>
  </nav>

  <h1 class="page-title">Thông tin đơn hàng</h1>

  <ol class="steps">
    <li class="done"><span>1</span> Giỏ hàng</li>
    <li class="done"><span>2</span> Thông tin &amp; thanh toán</li>
    <li><span>3</span> Hoàn tất</li>
  </ol>

<?php if ($loi): ?>
  <p class="form-err">Chưa đặt được hàng. Vui lòng sửa <?= count($loi) ?> ô được đánh dấu đỏ bên dưới.</p>
<?php endif; ?>

  <form class="checkout" action="thanh-toan.php" method="post">
    <div class="ck-main">
      <section class="panel">
        <h2>Thông tin người nhận</h2>
        <div class="field-2">
          <div class="field<?= isset($loi['ho_ten']) ? ' has-err' : '' ?>"><label for="ck-ten">Họ tên</label>
            <input type="text" id="ck-ten" name="ho_ten" value="<?= e($f['ho_ten']) ?>" maxlength="100" required>
            <?= loi_o($loi, 'ho_ten') ?></div>
          <div class="field<?= isset($loi['so_dien_thoai']) ? ' has-err' : '' ?>"><label for="ck-sdt">Số điện thoại</label>
            <input type="tel" id="ck-sdt" name="so_dien_thoai" value="<?= e($f['so_dien_thoai']) ?>" required>
            <?= loi_o($loi, 'so_dien_thoai') ?></div>
        </div>
        <div class="field<?= isset($loi['dia_chi']) ? ' has-err' : '' ?>"><label for="ck-dc">Địa chỉ nhận hàng</label>
          <input type="text" id="ck-dc" name="dia_chi" value="<?= e($f['dia_chi']) ?>" maxlength="255" required>
          <?= loi_o($loi, 'dia_chi') ?></div>
        <div class="field"><label for="ck-note">Ghi chú cho người giao hàng</label>
          <textarea id="ck-note" name="ghi_chu" rows="3" maxlength="255" placeholder="Ví dụ: giao giờ hành chính..."><?= e($f['ghi_chu']) ?></textarea></div>
      </section>

      <section class="panel">
        <h2>Phương thức thanh toán</h2>
        <?= loi_o($loi, 'phuong_thuc') ?>
        <div class="pay-list">
<?php foreach (['cod' => ['💵', 'Thanh toán bằng tiền mặt khi nhận hàng'],
                'banking' => ['🏦', 'Chuyển khoản qua ngân hàng nội địa'],
                'ewallet' => ['📱', 'Momo, ZaloPay, VNPay']] as $ma => [$icon, $mo_ta]): ?>
          <label class="pay-opt">
            <input type="radio" name="phuong_thuc" value="<?= $ma ?>"<?= $f['phuong_thuc'] === $ma ? ' checked' : '' ?>>
            <span class="pay-dot"></span>
            <span class="pay-ico"><?= $icon ?></span>
            <span class="pay-text"><strong><?= nhan(PHUONG_THUC_TT, $ma) ?></strong>
              <em><?= $mo_ta ?></em></span>
          </label>
<?php endforeach; ?>
        </div>
      </section>
    </div>

    <aside class="ck-side">
      <div class="sum-box">
        <h3>Đơn hàng của bạn</h3>
        <table class="ck-table">
          <thead><tr><th>Sản phẩm</th><th class="ta-c">SL</th><th class="ta-r">Tiền</th></tr></thead>
          <tbody>
<?php foreach ($ds_trong_gio as $sp): ?>
          <tr>
            <td><?= e($sp['ten']) ?></td>
            <td class="ta-c">x<?= $sp['so_luong'] ?></td>
            <td class="ta-r"><?= tien($sp['so_luong'] * $sp['gia']) ?></td>
          </tr>
<?php endforeach; ?>
          </tbody>
        </table>
        <div class="sum-row"><span>Tạm tính</span><strong><?= tien($tien['tam_tinh']) ?></strong></div>
<?php if ($tien['giam_gia'] > 0): ?>
        <div class="sum-row sum-giam"><span>Giảm giá (<?= e($_SESSION['ma_giam']) ?>)</span><strong>−<?= tien($tien['giam_gia']) ?></strong></div>
<?php endif; ?>
        <div class="sum-row"><span>Phí vận chuyển</span><strong><?= $tien['phi_ship'] == 0 ? 'Miễn phí' : tien($tien['phi_ship']) ?></strong></div>
        <div class="sum-row sum-total"><span>Tổng tiền</span><strong><?= tien($tien['tong_tien']) ?></strong></div>
        <button type="submit" class="btn btn-solid btn-block btn-lg">Xác nhận đặt hàng</button>
        <a href="gio-hang.php" class="btn btn-ghost btn-block">← Quay lại giỏ hàng</a>
      </div>
    </aside>
  </form>
</main>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
