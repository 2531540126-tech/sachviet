<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_quan_tri();
$qt_menu = 'don-hang';

$tu_khoa = tu_khoa_tim();
$tt = get_chuoi('tt');
if (!isset(TRANG_THAI_DON[$tt])) {
    $tt = '';
}

$dem_theo_tt = qt_dem_don_theo_trang_thai($db, $tu_khoa);
$tong_tim    = $tt === '' ? array_sum($dem_theo_tt) : ($dem_theo_tt[$tt] ?? 0);
$pt          = phan_trang($tong_tim);
$ds_don      = qt_lay_ds_don($db, $tu_khoa, $tt, $pt);
$tham_so     = ['q' => $tu_khoa, 'tt' => $tt];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đơn hàng | Quản trị SáchViệt</title>
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
  <header class="qt-head">
    <div>
      <h1>Đơn hàng</h1>
      <p>Theo dõi, tìm kiếm và cập nhật trạng thái đơn hàng của khách.</p>
    </div>
  </header>
  <?= hien_thong_bao() ?>

  <section class="qt-the">
    <h2 class="sr-only">Danh sách đơn hàng</h2>
    <div class="qt-chip-hang">
      <a href="<?= e(link_trang('don-hang.php', ['q' => $tu_khoa])) ?>" class="chip<?= $tt === '' ? ' chip-on' : '' ?>">Tất cả (<?= array_sum($dem_theo_tt) ?>)</a>
<?php foreach (TRANG_THAI_DON as $gia_tri => $nhan_tt): ?>
      <a href="<?= e(link_trang('don-hang.php', ['q' => $tu_khoa, 'tt' => $gia_tri])) ?>" class="chip<?= $tt === $gia_tri ? ' chip-on' : '' ?>"><?= $nhan_tt[0] ?> (<?= $dem_theo_tt[$gia_tri] ?? 0 ?>)</a>
<?php endforeach; ?>
    </div>

    <form class="qt-loc" action="don-hang.php" method="get" role="search">
<?php if ($tt !== ''): ?>
      <input type="hidden" name="tt" value="<?= $tt ?>">
<?php endif; ?>
      <div class="field field-rong">
        <label for="q">Tìm đơn hàng</label>
        <input type="search" id="q" name="q" value="<?= e($tu_khoa) ?>" placeholder="Mã đơn (VD: DH045), tên người nhận, số điện thoại hoặc email...">
      </div>
      <div class="qt-loc-nut">
        <button type="submit" class="btn btn-solid">Tìm</button>
<?php if ($tu_khoa !== '' || $tt !== ''): ?>
        <a href="don-hang.php" class="btn btn-ghost">Bỏ lọc</a>
<?php endif; ?>
      </div>
    </form>

    <p class="qt-ket-qua"><?= $pt['tong'] ?> đơn hàng<?= $tt !== '' ? ' ở trạng thái “' . nhan(TRANG_THAI_DON, $tt) . '”' : '' ?><?= $tu_khoa !== '' ? ' khớp với “' . e($tu_khoa) . '”' : '' ?><?= $pt['so_trang'] > 1 ? ' · trang ' . $pt['trang'] . '/' . $pt['so_trang'] : '' ?></p>

<?php if (!$ds_don): ?>
    <p class="qt-rong">Không có đơn hàng nào.</p>
<?php else: ?>
    <div class="qt-cuon">
      <table class="qt-bang">
        <thead>
          <tr>
            <th>Mã đơn</th><th>Ngày đặt</th><th>Người nhận</th>
            <th class="ta-r">Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th class="ta-r">Thao tác</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($ds_don as $don): ?>
          <tr>
            <td><a class="qt-ma" href="don-hang-chi-tiet.php?id=<?= $don['id'] ?>"><?= ma_don($don['id']) ?></a></td>
            <td class="nowrap"><?= ngay($don['ngay_dat']) ?><br><span class="qt-nho"><?= date('H:i', strtotime($don['ngay_dat'])) ?></span></td>
            <td><strong><?= e($don['ho_ten_nhan']) ?></strong><br><span class="qt-nho"><?= e($don['so_dien_thoai']) ?> · <?= e($don['email']) ?></span></td>
            <td class="ta-r nowrap"><strong><?= tien($don['tong']) ?></strong><br><span class="qt-nho"><?= $don['so_cuon'] ?> cuốn</span></td>
            <td><?= nhan(PHUONG_THUC_TT, $don['phuong_thuc_tt']) ?></td>
            <td><?= nhan_mau(TRANG_THAI_DON, $don['trang_thai']) ?></td>
            <td class="qt-thao-tac-o"><div class="qt-thao-tac"><a class="qt-nut" href="don-hang-chi-tiet.php?id=<?= $don['id'] ?>">Chi tiết</a></div></td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= hien_phan_trang('don-hang.php', $tham_so, $pt) ?>

<?php endif; ?>
  </section>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</main>
</div>
</body>
</html>
