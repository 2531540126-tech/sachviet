<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_quan_tri();
$qt_menu = 'tong-quan';

$tk         = qt_thong_ke($db);
$theo_thang = qt_doanh_thu_theo_thang($db);
$ban_chay   = qt_sach_ban_chay($db);
$don_moi    = qt_don_moi_nhat($db);
$sap_het    = qt_sach_sap_het($db);

$mau_trang_thai = [
    'cho_xac_nhan' => 'var(--vang)',
    'dang_giao'    => 'var(--lam)',
    'da_giao'      => 'var(--xanh)',
    'da_huy'       => 'var(--do)',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tổng quan | Quản trị SáchViệt</title>
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
      <h1>Tổng quan</h1>
      <p>Chào <?= e($nd['ho_ten']) ?>, đây là tình hình cửa hàng lúc <?= date('H:i, d/m/Y') ?>.</p>
    </div>
    <div class="qt-head-nut">
      <a href="don-hang.php?tt=cho_xac_nhan" class="btn btn-ghost">🧾 Đơn chờ xác nhận (<?= $tk['don']['cho_xac_nhan'] ?? 0 ?>)</a>
      <a href="sach-form.php" class="btn btn-solid">＋ Thêm sách</a>
    </div>
  </header>
  <?= hien_thong_bao() ?>

  <section class="qt-so-lieu" aria-label="Số liệu chính">
    <div class="qt-o-so" style="--mau:var(--xanh)">
      <span>Doanh thu</span>
      <strong><?= tien($tk['doanh_thu']) ?></strong>
      <em>Từ <?= $tk['don_da_giao'] ?> đơn đã giao</em>
      <a href="don-hang.php?tt=da_giao">Xem đơn đã giao →</a>
    </div>
    <div class="qt-o-so" style="--mau:var(--nau)">
      <span>Đơn hàng</span>
      <strong><?= so($tk['tong_don']) ?></strong>
      <em><?= $tk['don']['cho_xac_nhan'] ?? 0 ?> chờ xác nhận · <?= $tk['don']['dang_giao'] ?? 0 ?> đang giao</em>
      <a href="don-hang.php">Quản lý đơn hàng →</a>
    </div>
    <div class="qt-o-so" style="--mau:var(--lam)">
      <span>Đầu sách</span>
      <strong><?= so($tk['so_sach']) ?></strong>
      <em><?= $tk['dang_ban'] ?> đang bán · <?= $tk['het_hang'] ?> hết hàng · <?= $tk['so_loai'] ?> loại</em>
      <a href="sach.php">Quản lý sách →</a>
    </div>
    <div class="qt-o-so" style="--mau:var(--vang)">
      <span>Khách hàng</span>
      <strong><?= so($tk['so_khach']) ?></strong>
      <em><?= $tk['khach_chua_hd'] ?> tài khoản tạm khoá hoặc chờ duyệt</em>
<?php if ($nd['vai_tro'] === 'admin'): ?>
      <a href="nguoi-dung.php?vt=user">Quản lý người dùng →</a>
<?php endif; ?>
    </div>
  </section>

  <div class="qt-luoi-2">
    <section class="qt-the">
      <h2>Doanh thu theo tháng <small>Tính trên đơn đã giao, gồm phí vận chuyển</small></h2>
<?php if (!$theo_thang): ?>
      <p class="qt-rong">Chưa có đơn hàng nào được giao.</p>
<?php else: ?>
      <div class="qt-bieu-do">
<?php foreach ($theo_thang as $th): ?>
        <div class="qt-cot" title="<?= $th['nhan'] ?>: <?= tien($th['doanh_thu']) ?> (<?= $th['so_don'] ?> đơn)">
          <em><?= so(round($th['doanh_thu'] / 1000)) ?>k</em>
          <b style="--cao:<?= $th['phan_tram'] ?>%"></b>
        </div>
<?php endforeach; ?>
      </div>
      <div class="qt-cot-nhan">
<?php foreach ($theo_thang as $th): ?>
        <span><?= $th['nhan_ngan'] ?><small><?= $th['nam'] ?></small></span>
<?php endforeach; ?>
      </div>
<?php endif; ?>
    </section>

    <section class="qt-the">
      <h2>Đơn hàng theo trạng thái <a href="don-hang.php">Xem tất cả →</a></h2>
      <div class="qt-thanh">
<?php foreach (TRANG_THAI_DON as $gia_tri => $nhan_tt):
    $so_don = $tk['don'][$gia_tri] ?? 0;
    $rong = $tk['tong_don'] ? round($so_don * 100 / $tk['tong_don']) : 0; ?>
        <a class="qt-thanh-dong" href="don-hang.php?tt=<?= $gia_tri ?>">
          <span><?= $nhan_tt[0] ?></span>
          <span class="qt-thanh-nen"><i style="--rong:<?= $rong ?>%;--mau:<?= $mau_trang_thai[$gia_tri] ?>"></i></span>
          <strong><?= $so_don ?></strong>
        </a>
<?php endforeach; ?>
      </div>
    </section>
  </div>

  <div class="qt-luoi-2">
    <section class="qt-the">
      <h2>Đơn hàng mới nhất <a href="don-hang.php">Tất cả đơn hàng →</a></h2>
<?php if (!$don_moi): ?>
      <p class="qt-rong">Chưa có đơn hàng nào.</p>
<?php else: ?>
      <div class="qt-cuon">
        <table class="qt-bang">
          <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th class="ta-r">Tổng tiền</th><th>Trạng thái</th></tr></thead>
          <tbody>
<?php foreach ($don_moi as $don): ?>
            <tr>
              <td><a class="qt-ma" href="don-hang-chi-tiet.php?id=<?= $don['id'] ?>"><?= ma_don($don['id']) ?></a></td>
              <td><?= e($don['ho_ten_nhan']) ?></td>
              <td class="nowrap"><?= ngay($don['ngay_dat']) ?></td>
              <td class="ta-r nowrap"><?= tien($don['tong']) ?></td>
              <td><?= nhan_mau(TRANG_THAI_DON, $don['trang_thai']) ?></td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
<?php endif; ?>
    </section>

    <section class="qt-the">
      <h2>Sách bán chạy <small>Theo số cuốn đã giao</small></h2>
<?php if (!$ban_chay): ?>
      <p class="qt-rong">Chưa có dữ liệu bán hàng.</p>
<?php else: ?>
      <ol class="qt-ds">
<?php foreach ($ban_chay as $thu_tu => $s): ?>
        <li>
          <span class="qt-hang"><?= $thu_tu + 1 ?></span>
          <span class="qt-sp-bia"><?= bia_nho($s['ten'], $s['mau']) ?></span>
          <span class="qt-sp"><span><strong><?= e($s['ten']) ?></strong><em><?= e($s['tac_gia']) ?></em></span></span>
          <span class="qt-ds-so"><strong><?= so($s['da_ban']) ?> cuốn</strong><em><?= tien($s['doanh_thu']) ?></em></span>
        </li>
<?php endforeach; ?>
      </ol>
<?php endif; ?>

      <h3>Sắp hết hàng <span class="qt-nho">(còn từ <?= SAP_HET_HANG ?> cuốn trở xuống)</span></h3>
<?php if (!$sap_het): ?>
      <p class="qt-canh-bao">✓ Tất cả sách đang bán đều còn trên <?= SAP_HET_HANG ?> cuốn.</p>
<?php else: ?>
      <ul class="qt-ds">
<?php foreach ($sap_het as $s): ?>
        <li>
          <span class="qt-sp-bia"><?= bia_nho($s['ten'], $s['mau']) ?></span>
          <span class="qt-sp"><span><strong><?= e($s['ten']) ?></strong><em><?= e($s['tac_gia']) ?></em></span></span>
          <span class="qt-ds-so"><?= $s['so_luong'] > 0
              ? '<span class="tt tt-warn">Còn ' . $s['so_luong'] . '</span>'
              : '<span class="tt tt-low">Hết hàng</span>' ?></span>
          <a class="qt-nut" href="sach-form.php?id=<?= $s['id'] ?>">Nhập thêm</a>
        </li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
    </section>
  </div>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</main>
</div>
</body>
</html>
