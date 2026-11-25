<?php

$nd_header = nguoi_dung_hien_tai();

$so_dau_sach_trong_gio = $nd_header ? dem_sach_trong_gio($db, $nd_header['id']) : 0;

$tu_khoa_header = get_chuoi('q');
$menu = $menu ?? '';
?>
<header class="site-header">
  <div class="header-inner">
    <a href="index.php" class="logo">
      <span class="logo-icon">📖</span>
      <span class="logo-text">Sách<strong>Việt</strong></span>
    </a>

    <nav class="main-nav" aria-label="Menu Chính">
      <a href="index.php"<?= $menu === 'trang-chu' ? ' class="active"' : '' ?>>Trang chủ</a>
      <a href="danh-muc.php"<?= $menu === 'danh-muc' ? ' class="active"' : '' ?>>Danh mục</a>
      <a href="index.php#khuyen-mai">Khuyến mãi</a>
      <a href="index.php#gioi-thieu">Giới thiệu</a>
    </nav>

    <form class="search-box" action="index.php#danh-sach" method="get" role="search">
      <label class="sr-only" for="q">Tìm sách</label>
      <input type="search" id="q" name="q" value="<?= e($tu_khoa_header) ?>" placeholder="Tìm tên sách, tác giả...">
      <button type="submit" aria-label="Tìm">🔍</button>
    </form>

    <div class="header-actions">
      <a href="gio-hang.php" class="icon-btn" aria-label="Giỏ hàng">
        🛒<?php if ($so_dau_sach_trong_gio > 0): ?><span class="badge"><?= $so_dau_sach_trong_gio ?></span><?php endif; ?>
      </a>
<?php if ($nd_header): ?>
      <a href="tai-khoan.php" class="user-chip" title="Tài khoản của tôi">
        <span class="user-ava"><?= e(viet_tat($nd_header['ho_ten'])) ?></span>
        <span class="user-ten"><?= e($nd_header['ho_ten']) ?></span>
      </a>
      <form class="form-dang-xuat" action="dang-xuat.php" method="post">
        <button type="submit" class="btn btn-ghost">Đăng xuất</button>
      </form>
<?php else: ?>
      <a href="login.php" class="icon-btn" aria-label="Tài khoản">👤</a>
      <a href="login.php" class="btn btn-ghost">Đăng nhập</a>
      <a href="register.php" class="btn btn-solid">Đăng ký</a>
<?php endif; ?>
    </div>
  </div>
</header>
<?= hien_thong_bao() ?>
