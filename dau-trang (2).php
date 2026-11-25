<?php

$so_don_cho = qt_dem_don_cho($db);
$menu_qt = [
    'tong-quan'  => ['index.php',      '📊', 'Tổng quan'],
    'loai-sach'  => ['loai-sach.php',  '🗂️', 'Loại sách'],
    'sach'       => ['sach.php',       '📚', 'Sách'],
    'don-hang'   => ['don-hang.php',   '🧾', 'Đơn hàng'],
    'nguoi-dung' => ['nguoi-dung.php', '👥', 'Người dùng'],
];
if ($nd['vai_tro'] !== 'admin') {
    unset($menu_qt['nguoi-dung']);
}
?>
<aside class="qt-side">
  <a href="index.php" class="qt-logo">
    <span class="logo-icon">📖</span>
    <span class="qt-logo-chu">Sách<strong>Việt</strong><small>Trang quản trị</small></span>
  </a>

  <nav class="qt-menu" aria-label="Menu quản trị">
<?php foreach ($menu_qt as $khoa => [$link, $bieu_tuong, $ten]): ?>
    <a href="<?= $link ?>"<?= $qt_menu === $khoa ? ' class="dang-mo" aria-current="page"' : '' ?>>
      <span class="qt-menu-ico" aria-hidden="true"><?= $bieu_tuong ?></span><?= $ten ?>
<?php if ($khoa === 'don-hang' && $so_don_cho > 0): ?>
      <span class="qt-dem" title="Đơn chờ xác nhận"><?= $so_don_cho ?></span>
<?php endif; ?>
    </a>
<?php endforeach; ?>
  </nav>

  <div class="qt-side-cuoi">
    <a href="../index.php" class="qt-ve-shop">↗ Xem cửa hàng</a>
    <div class="qt-toi">
      <span class="user-ava"><?= e(viet_tat($nd['ho_ten'])) ?></span>
      <span class="qt-toi-ten"><strong><?= e($nd['ho_ten']) ?></strong><em><?= nhan(VAI_TRO, $nd['vai_tro']) ?></em></span>
    </div>
    <form action="dang-xuat.php" method="post">
      <button type="submit" class="qt-dang-xuat">⏻ Đăng xuất</button>
    </form>
  </div>
</aside>
