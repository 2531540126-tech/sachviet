<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_dang_nhap('Vui lòng đăng nhập để xem lịch sử đơn hàng.');

$tt = get_chuoi('tt');
if (!isset(TRANG_THAI_DON[$tt])) $tt = '';

$cau_lenh = $db->prepare('
    SELECT trang_thai, COUNT(*) AS so_don
    FROM don_hang
    WHERE nguoi_dung_id = ?
    GROUP BY trang_thai
');
$cau_lenh->execute([$nd['id']]);
$dem_theo_tt = $cau_lenh->fetchAll(PDO::FETCH_KEY_PAIR);

$sql = 'SELECT id, ho_ten_nhan, so_dien_thoai, dia_chi_giao, phuong_thuc_tt,
               phi_van_chuyen, trang_thai, ngay_dat
        FROM don_hang
        WHERE nguoi_dung_id = ?';
$tham_so = [$nd['id']];
if ($tt !== '') {
    $sql .= ' AND trang_thai = ?';
    $tham_so[] = $tt;
}
$sql .= ' ORDER BY ngay_dat DESC';
$cau_lenh = $db->prepare($sql);
$cau_lenh->execute($tham_so);
$ds_don = $cau_lenh->fetchAll();

$cau_lenh = $db->prepare('
    SELECT ct.don_hang_id, ct.so_luong, ct.don_gia, s.ten, s.tac_gia, tl.mau
    FROM chi_tiet_don_hang ct
    JOIN don_hang dh ON dh.id = ct.don_hang_id
    JOIN sach s      ON s.id  = ct.sach_id
    JOIN the_loai tl ON tl.id = s.the_loai_id
    WHERE dh.nguoi_dung_id = ?
    ORDER BY ct.don_hang_id, s.id
');
$cau_lenh->execute([$nd['id']]);
$sach_cua_don = [];
foreach ($cau_lenh->fetchAll() as $dong) {
    $sach_cua_don[$dong['don_hang_id']][] = $dong;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lịch sử đơn hàng | SáchViệt</title>
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
    <a href="tai-khoan.php">Tài khoản</a> <span>›</span> <strong>Lịch sử đơn hàng</strong>
  </nav>

  <h1 class="page-title">Lịch sử đơn hàng</h1>
  <p class="page-sub">Nhấn vào một đơn để xem chi tiết sản phẩm và thông tin giao hàng.</p>

  <div class="don-filter">
    <a href="lich-su-don-hang.php" class="chip<?= $tt === '' ? ' chip-on' : '' ?>">Tất cả (<?= array_sum($dem_theo_tt) ?>)</a>
<?php foreach (TRANG_THAI_DON as $gia_tri => $nhan_tt): ?>
    <a href="lich-su-don-hang.php?tt=<?= $gia_tri ?>" class="chip<?= $tt === $gia_tri ? ' chip-on' : '' ?>"><?= $nhan_tt[0] ?> (<?= $dem_theo_tt[$gia_tri] ?? 0 ?>)</a>
<?php endforeach; ?>
  </div>

  <div class="don-list">
<?php if (!$ds_don): ?>
      <p class="page-sub">Không có đơn hàng nào<?= $tt !== '' ? ' ở trạng thái “' . nhan(TRANG_THAI_DON, $tt) . '”' : '' ?>.</p>
<?php endif; ?>
<?php foreach ($ds_don as $thu_tu => $don):
    $dong_sach = $sach_cua_don[$don['id']] ?? [];
    $tam_tinh = 0;
    foreach ($dong_sach as $d) $tam_tinh += $d['so_luong'] * $d['don_gia'];
    $tong = $tam_tinh + $don['phi_van_chuyen']; ?>
      <details class="don" <?= $thu_tu === 0 ? 'open' : '' ?>>
        <summary>
          <span class="don-ma"><?= ma_don($don['id']) ?></span>
          <span class="don-ngay"><?= ngay($don['ngay_dat']) ?></span>
          <span class="don-sl"><?= count($dong_sach) ?> sản phẩm</span>
          <span class="don-tong"><?= tien($tong) ?></span>
          <span class="tt <?= nhan(TRANG_THAI_DON, $don['trang_thai'], 1) ?>"><?= nhan(TRANG_THAI_DON, $don['trang_thai']) ?></span>
          <span class="don-caret">▾</span>
        </summary>

        <div class="don-body">
          <div class="don-cols">
            <div>
              <h4>Thông tin đơn hàng</h4>
              <table class="don-table">
                <thead><tr><th>Sản phẩm</th><th class="ta-c">Số lượng</th><th class="ta-r">Thành tiền</th></tr></thead>
                <tbody>
<?php foreach ($dong_sach as $d): ?>
              <tr>
                <td class="h-sp"><span class="h-bia"><?= bia_nho($d['ten'], $d['mau']) ?></span>
                  <span><strong><?= e($d['ten']) ?></strong><em><?= e($d['tac_gia']) ?></em></span></td>
                <td class="ta-c">x<?= $d['so_luong'] ?></td>
                <td class="ta-r"><?= tien($d['so_luong'] * $d['don_gia']) ?></td>
              </tr>
<?php endforeach; ?>
                </tbody>
                <tfoot><tr><td colspan="2">Phí vận chuyển</td>
                  <td class="ta-r"><?= tien($don['phi_van_chuyen']) ?></td></tr>
                  <tr><td colspan="2"><strong>Tổng tiền</strong></td>
                  <td class="ta-r"><strong><?= tien($tong) ?></strong></td></tr></tfoot>
              </table>
            </div>
            <div>
              <h4>Thông tin khách hàng</h4>
              <dl class="don-dl">
                <dt>Họ tên</dt><dd><?= e($don['ho_ten_nhan']) ?></dd>
                <dt>Địa chỉ</dt><dd><?= e($don['dia_chi_giao']) ?></dd>
                <dt>SĐT</dt><dd><?= e($don['so_dien_thoai']) ?></dd>
                <dt>Thanh toán</dt><dd><?= nhan(PHUONG_THUC_TT, $don['phuong_thuc_tt']) ?> — <?= nhan(PHUONG_THUC_TT, $don['phuong_thuc_tt'], 1) ?></dd>
              </dl>
              <div class="don-btns">
                <a href="index.php#danh-sach" class="btn btn-ghost btn-sm">Mua lại</a>
              </div>
            </div>
          </div>
        </div>
      </details>
<?php endforeach; ?>
  </div>
</main>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
