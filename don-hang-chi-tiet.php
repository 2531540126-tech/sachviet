<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_quan_tri();
$qt_menu = 'don-hang';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = post_so('id');
    [$loai, $noi_dung] = qt_doi_trang_thai_don($db, $id, post_chuoi('trang_thai'));
    dat_thong_bao($loai, $noi_dung);
    chuyen_trang('don-hang-chi-tiet.php?id=' . $id);
}

$id  = ctype_digit(get_chuoi('id')) ? (int) get_chuoi('id') : 0;
$don = $id > 0 ? qt_lay_don($db, $id) : null;
if ($don) {
    $ds_sp    = qt_lay_sach_cua_don($db, $id);
    $tam_tinh = 0;
    $so_cuon  = 0;
    foreach ($ds_sp as $sp) {
        $tam_tinh += $sp['so_luong'] * $sp['don_gia'];
        $so_cuon  += $sp['so_luong'];
    }
    $tong     = $tam_tinh + $don['phi_van_chuyen'];
    $buoc_sau = CHUYEN_TRANG_THAI[$don['trang_thai']] ?? [];
    $thu_tu   = array_search($don['trang_thai'], ['cho_xac_nhan', 'dang_giao', 'da_giao'], true);
} else {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $don ? 'Đơn ' . ma_don($don['id']) : 'Không tìm thấy đơn hàng' ?> | Quản trị SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="quan-tri.css">
</head>
<body class="qt-body">
<div class="qt-khung">
<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<main class="qt-main">
<?php if (!$don): ?>
  <header class="qt-head">
    <div>
      <p class="qt-duong-dan"><a href="don-hang.php">Đơn hàng</a> › Không tìm thấy</p>
      <h1>Không tìm thấy đơn hàng</h1>
      <p>Đơn hàng bạn tìm không tồn tại hoặc mã đơn không đúng.</p>
    </div>
  </header>
  <?= hien_thong_bao() ?>
  <a href="don-hang.php" class="btn btn-solid">← Về danh sách đơn hàng</a>
<?php else: ?>
  <header class="qt-head">
    <div>
      <p class="qt-duong-dan"><a href="don-hang.php">Đơn hàng</a> › <?= ma_don($don['id']) ?></p>
      <h1>Đơn hàng <?= ma_don($don['id']) ?> <?= nhan_mau(TRANG_THAI_DON, $don['trang_thai']) ?></h1>
      <p>Đặt lúc <?= date('H:i, d/m/Y', strtotime($don['ngay_dat'])) ?> · <?= $so_cuon ?> cuốn · <?= count($ds_sp) ?> đầu sách</p>
    </div>
    <div class="qt-head-nut">
      <a href="don-hang.php" class="btn btn-ghost">← Danh sách đơn hàng</a>
    </div>
  </header>
  <?= hien_thong_bao() ?>

  <div class="qt-luoi-form">
    <div>
      <section class="qt-the">
        <h2>Sách trong đơn</h2>
        <div class="qt-cuon">
          <table class="qt-bang">
            <thead><tr><th>Sách</th><th class="ta-r">Đơn giá</th><th class="ta-c">Số lượng</th><th class="ta-r">Thành tiền</th></tr></thead>
            <tbody>
<?php foreach ($ds_sp as $sp): ?>
              <tr>
                <td>
                  <span class="qt-sp">
                    <span class="qt-sp-bia"><?= bia_nho($sp['ten'], $sp['mau']) ?></span>
                    <span><strong><?= e($sp['ten']) ?></strong><em><?= e($sp['tac_gia']) ?> · còn <?= so($sp['ton_kho']) ?> cuốn trong kho</em></span>
                  </span>
                </td>
                <td class="ta-r nowrap"><?= tien($sp['don_gia']) ?></td>
                <td class="ta-c">x<?= $sp['so_luong'] ?></td>
                <td class="ta-r nowrap"><?= tien($sp['so_luong'] * $sp['don_gia']) ?></td>
              </tr>
<?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr><td colspan="3">Tiền sách</td><td class="ta-r nowrap"><?= tien($tam_tinh) ?></td></tr>
              <tr><td colspan="3">Phí vận chuyển</td><td class="ta-r nowrap"><?= $don['phi_van_chuyen'] > 0 ? tien($don['phi_van_chuyen']) : 'Miễn phí' ?></td></tr>
              <tr><td colspan="3"><strong>Tổng cộng</strong></td><td class="ta-r nowrap"><strong><?= tien($tong) ?></strong></td></tr>
            </tfoot>
          </table>
        </div>
<?php if ($don['ghi_chu'] !== null && $don['ghi_chu'] !== ''): ?>
        <p class="qt-ghi-chu">📝 Ghi chú của khách: <?= e($don['ghi_chu']) ?></p>
<?php endif; ?>
      </section>

      <section class="qt-the">
        <h2>Giao hàng và thanh toán</h2>
        <dl class="qt-dl">
          <dt>Người nhận</dt><dd><?= e($don['ho_ten_nhan']) ?></dd>
          <dt>Số điện thoại</dt><dd><?= e($don['so_dien_thoai']) ?></dd>
          <dt>Địa chỉ</dt><dd><?= e($don['dia_chi_giao']) ?></dd>
          <dt>Thanh toán</dt><dd><?= nhan(PHUONG_THUC_TT, $don['phuong_thuc_tt']) ?> — <?= nhan(PHUONG_THUC_TT, $don['phuong_thuc_tt'], 1) ?></dd>
          <dt>Tài khoản đặt</dt>
          <dd>
<?php if ($nd['vai_tro'] === 'admin'): ?>
            <a href="nguoi-dung-form.php?id=<?= $don['ma_tai_khoan'] ?>"><?= e($don['ten_tai_khoan']) ?></a>
<?php else: ?>
            <?= e($don['ten_tai_khoan']) ?>

<?php endif; ?>
            · <?= e($don['email']) ?>
          </dd>
        </dl>
      </section>
    </div>

    <aside class="qt-ben">
      <section class="qt-the">
        <h2>Trạng thái đơn</h2>
        <ol class="qt-buoc">
<?php foreach (['cho_xac_nhan' => 'Chờ xác nhận', 'dang_giao' => 'Đang giao', 'da_giao' => 'Đã giao'] as $buoc => $ten_buoc):
    $lop = $don['trang_thai'] === 'da_huy' ? '' : ($thu_tu !== false && array_search($buoc, ['cho_xac_nhan', 'dang_giao', 'da_giao'], true) <= $thu_tu ? 'xong' : ''); ?>
          <li<?= $lop ? ' class="' . $lop . '"' : '' ?>><?= $ten_buoc ?></li>
<?php endforeach; ?>
<?php if ($don['trang_thai'] === 'da_huy'): ?>
          <li class="huy">Đã huỷ</li>
<?php endif; ?>
        </ol>

<?php if (!$buoc_sau): ?>
        <p class="qt-canh-bao"><?= $don['trang_thai'] === 'da_giao'
            ? '✓ Đơn đã giao thành công, không cần xử lý thêm.'
            : 'Đơn đã huỷ, sách trong đơn đã được hoàn về kho.' ?></p>
<?php else: ?>
        <form action="don-hang-chi-tiet.php" method="post">
          <input type="hidden" name="id" value="<?= $don['id'] ?>">
          <fieldset class="qt-chon">
            <legend class="sr-only">Chọn trạng thái mới</legend>
<?php foreach ($buoc_sau as $gia_tri => $mo_ta): ?>
            <label><input type="radio" name="trang_thai" value="<?= $gia_tri ?>" required>
              <span><strong><?= nhan(TRANG_THAI_DON, $gia_tri) ?></strong><br><span class="qt-nho"><?= $mo_ta ?></span></span></label>
<?php endforeach; ?>
          </fieldset>
          <button type="submit" class="btn btn-solid btn-block">Cập nhật trạng thái</button>
        </form>
<?php endif; ?>
      </section>
    </aside>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</main>
</div>
</body>
</html>
