<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_dang_nhap('Vui lòng đăng nhập để xem tài khoản.');
$loi_tt = [];
$loi_mk = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (post_chuoi('hanh_dong') === 'doi_mat_khau') {
        $loi_mk = doi_mat_khau($db, $nd['id'], post_mat_khau('mat_khau_cu'), post_mat_khau('mat_khau_moi'), post_mat_khau('nhap_lai'));
        if (!$loi_mk) {
            dat_thong_bao('ok', 'Đã đổi mật khẩu. Lần đăng nhập sau hãy dùng mật khẩu mới.');
            chuyen_trang('tai-khoan.php');
        }
    } else {
        $f = doc_form_thong_tin();
        $loi_tt = kiem_tra_thong_tin($f);
        if (!$loi_tt) {
            luu_thong_tin($db, $nd['id'], $f);
            dat_thong_bao('ok', 'Đã lưu thông tin cá nhân.');
            chuyen_trang('tai-khoan.php');
        }
    }
}

$cau_lenh = $db->prepare('SELECT * FROM nguoi_dung WHERE id = ?');
$cau_lenh->execute([$nd['id']]);
$nguoi = $cau_lenh->fetch();

if (!$nguoi) {
    dang_xuat();
    dat_thong_bao('loi', 'Không tìm thấy tài khoản, vui lòng đăng nhập lại.');
    chuyen_trang('login.php');
}

if (!$loi_tt) {
    $f = [
        'ho_ten'        => $nguoi['ho_ten'],
        'so_dien_thoai' => (string) $nguoi['so_dien_thoai'],
        'ngay_sinh'     => (string) $nguoi['ngay_sinh'],
        'dia_chi'       => (string) $nguoi['dia_chi'],
    ];
}

$cau_lenh = $db->prepare('SELECT COUNT(*) FROM don_hang WHERE nguoi_dung_id = ?');
$cau_lenh->execute([$nd['id']]);
$so_don = (int) $cau_lenh->fetchColumn();

$cau_lenh = $db->prepare("
    SELECT COALESCE(SUM(t.tong_don), 0)
    FROM (
        SELECT dh.id, SUM(ct.so_luong * ct.don_gia) + dh.phi_van_chuyen AS tong_don
        FROM don_hang dh
        JOIN chi_tiet_don_hang ct ON ct.don_hang_id = dh.id
        WHERE dh.nguoi_dung_id = ? AND dh.trang_thai = 'da_giao'
        GROUP BY dh.id, dh.phi_van_chuyen
    ) AS t
");
$cau_lenh->execute([$nd['id']]);
$da_chi = (int) $cau_lenh->fetchColumn();

if ($da_chi >= 1000000)     $hang = 'Thành viên Vàng';
elseif ($da_chi >= 500000)  $hang = 'Thành viên Bạc';
else                        $hang = 'Thành viên Đồng';

$cau_lenh = $db->prepare('
    SELECT s.id, s.ten, s.tac_gia, s.gia, tl.mau
    FROM yeu_thich yt
    JOIN sach s      ON s.id  = yt.sach_id
    JOIN the_loai tl ON tl.id = s.the_loai_id
    WHERE yt.nguoi_dung_id = ?
    ORDER BY yt.ngay_them
');
$cau_lenh->execute([$nd['id']]);
$ds_yeu_thich = $cau_lenh->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tài khoản | SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<main class="page">
  <nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="index.php">Trang chủ</a> <span>›</span> <strong>Tài khoản của tôi</strong>
  </nav>

  <div class="profile-layout">
    <aside class="pf-side">
      <div class="pf-user">
        <span class="pf-avatar"><?= e(viet_tat($nguoi['ho_ten'])) ?></span>
        <strong><?= e($nguoi['ho_ten']) ?></strong>
        <em><?= e($nguoi['email']) ?></em>
        <span class="pf-rank">🏅 <?= $hang ?></span>
      </div>
      <nav class="pf-menu">
        <a href="tai-khoan.php" class="active">👤 Thông tin cá nhân</a>
        <a href="lich-su-don-hang.php">📦 Đơn hàng</a>
        <a href="lich-su-don-hang.php">🕘 Lịch sử mua hàng</a>
        <a href="#yeu-thich">❤️ Sản phẩm yêu thích</a>
        <a href="#doi-mat-khau">🔑 Đổi mật khẩu</a>
<?php if (la_quan_tri($nd)): ?>
        <a href="admin/index.php">🛠️ Trang quản trị</a>
<?php endif; ?>
        <form action="dang-xuat.php" method="post">
          <button type="submit" class="pf-out">⏻ Đăng xuất</button>
        </form>
      </nav>
    </aside>

    <div class="pf-main">
      <div class="pf-stats">
        <div class="pf-stat"><span class="pf-stat-ico">📦</span>
          <div><strong><?= $so_don ?></strong><em>Đơn hàng</em></div></div>
        <div class="pf-stat"><span class="pf-stat-ico">❤️</span>
          <div><strong><?= count($ds_yeu_thich) ?></strong><em>Yêu thích</em></div></div>
        <div class="pf-stat"><span class="pf-stat-ico">💰</span>
          <div><strong><?= tien($da_chi) ?></strong><em>Đã chi tiêu</em></div></div>
        <div class="pf-stat"><span class="pf-stat-ico">🛒</span>
          <div><strong><?= dem_sach_trong_gio($db, $nd['id']) ?></strong><em>Sách trong giỏ</em></div></div>
      </div>

      <section class="panel" id="thong-tin">
        <h2>Thông tin cá nhân</h2>
<?php if ($loi_tt): ?>
        <p class="form-err">Chưa lưu được. Vui lòng sửa <?= count($loi_tt) ?> ô được đánh dấu đỏ bên dưới.</p>
<?php endif; ?>
        <form class="form" action="tai-khoan.php#thong-tin" method="post">
          <input type="hidden" name="hanh_dong" value="thong_tin">
          <div class="field-2">
            <div class="field<?= isset($loi_tt['ho_ten']) ? ' has-err' : '' ?>"><label for="p-ten">Họ tên</label>
              <input type="text" id="p-ten" name="ho_ten" value="<?= e($f['ho_ten']) ?>" maxlength="100" required>
              <?= loi_o($loi_tt, 'ho_ten') ?></div>
            <div class="field<?= isset($loi_tt['so_dien_thoai']) ? ' has-err' : '' ?>"><label for="p-sdt">Số điện thoại</label>
              <input type="tel" id="p-sdt" name="so_dien_thoai" value="<?= e($f['so_dien_thoai']) ?>" required>
              <?= loi_o($loi_tt, 'so_dien_thoai') ?></div>
          </div>
          <div class="field-2">
            <div class="field"><label for="p-mail">Email (dùng để đăng nhập, không đổi được)</label>
              <input type="email" id="p-mail" value="<?= e($nguoi['email']) ?>" readonly></div>
            <div class="field<?= isset($loi_tt['ngay_sinh']) ? ' has-err' : '' ?>"><label for="p-ns">Ngày sinh</label>
              <input type="date" id="p-ns" name="ngay_sinh" value="<?= e($f['ngay_sinh']) ?>" max="<?= date('Y-m-d') ?>">
              <?= loi_o($loi_tt, 'ngay_sinh') ?></div>
          </div>
          <div class="field<?= isset($loi_tt['dia_chi']) ? ' has-err' : '' ?>"><label for="p-dc">Địa chỉ</label>
            <input type="text" id="p-dc" name="dia_chi" value="<?= e($f['dia_chi']) ?>" maxlength="255">
            <?= loi_o($loi_tt, 'dia_chi') ?></div>
          <button type="submit" class="btn btn-solid">Lưu thay đổi</button>
        </form>
      </section>

      <section class="panel" id="yeu-thich">
        <h2>Sản phẩm yêu thích</h2>
        <div class="yt-grid">
<?php foreach ($ds_yeu_thich as $s): ?>
          <a class="yt-card" href="sach.php?id=<?= $s['id'] ?>" style="--mau:<?= mau($s['mau']) ?>">
            <span class="yt-bia"><?= bia($s['ten'], $s['tac_gia'], $s['mau']) ?></span>
            <span class="yt-ten"><?= e($s['ten']) ?></span>
            <span class="yt-gia"><?= tien($s['gia']) ?></span>
          </a>
<?php endforeach; ?>
        </div>
<?php if (!$ds_yeu_thich): ?>
        <p class="page-sub">Chưa có sách yêu thích nào.</p>
<?php endif; ?>
      </section>

      <section class="panel" id="doi-mat-khau">
        <h2>Đổi mật khẩu</h2>
<?php if ($loi_mk): ?>
        <p class="form-err">Chưa đổi được mật khẩu. Vui lòng sửa <?= count($loi_mk) ?> ô được đánh dấu đỏ bên dưới.</p>
<?php endif; ?>
        <form class="form" action="tai-khoan.php#doi-mat-khau" method="post">
          <input type="hidden" name="hanh_dong" value="doi_mat_khau">
          <div class="field<?= isset($loi_mk['mat_khau_cu']) ? ' has-err' : '' ?>"><label for="mk-cu">Mật khẩu hiện tại</label>
            <input type="password" id="mk-cu" name="mat_khau_cu" placeholder="••••••••" autocomplete="current-password" required>
            <?= loi_o($loi_mk, 'mat_khau_cu') ?></div>
          <div class="field-2">
            <div class="field<?= isset($loi_mk['mat_khau_moi']) ? ' has-err' : '' ?>"><label for="mk-moi">Mật khẩu mới</label>
              <input type="password" id="mk-moi" name="mat_khau_moi" placeholder="Ít nhất 6 ký tự" autocomplete="new-password" required>
              <?= loi_o($loi_mk, 'mat_khau_moi') ?></div>
            <div class="field<?= isset($loi_mk['nhap_lai']) ? ' has-err' : '' ?>"><label for="mk-xn">Xác nhận mật khẩu mới</label>
              <input type="password" id="mk-xn" name="nhap_lai" placeholder="••••••••" autocomplete="new-password" required>
              <?= loi_o($loi_mk, 'nhap_lai') ?></div>
          </div>
          <button type="submit" class="btn btn-solid">Cập nhật mật khẩu</button>
        </form>
      </section>
    </div>
  </div>
</main>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
