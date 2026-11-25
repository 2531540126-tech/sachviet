<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_quan_tri(true);
$qt_menu = 'nguoi-dung';

$la_post = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = $la_post ? post_so('id') : (ctype_digit(get_chuoi('id')) ? (int) get_chuoi('id') : 0);
$tk = $id > 0 ? qt_lay_nguoi_dung($db, $id) : null;
if ($id > 0 && !$tk) {
    dat_thong_bao('loi', 'Không tìm thấy người dùng có mã ' . $id . '.');
    chuyen_trang('nguoi-dung.php');
}
$la_toi = $tk && (int) $tk['id'] === $nd['id'];

$f = $tk ? array_map(fn($gia_tri) => (string) $gia_tri, $tk) : qt_nguoi_dung_trong();
$loi = [];

if ($la_post) {
    $f = qt_doc_form_nguoi_dung();
    if ($la_toi) {
        $f['vai_tro'] = $tk['vai_tro'];
        $f['trang_thai'] = $tk['trang_thai'];
    }
    $loi = qt_kiem_tra_nguoi_dung($db, $f, $id);
    if (!$loi) {
        if ($tk) {
            qt_sua_nguoi_dung($db, $id, $f);
            dat_thong_bao('ok', 'Đã lưu thông tin tài khoản ' . $f['email'] . ($f['mat_khau'] !== '' ? ' và đặt mật khẩu mới.' : '.'));
        } else {
            $id_moi = qt_them_nguoi_dung($db, $f);
            dat_thong_bao('ok', 'Đã tạo tài khoản ' . $f['email'] . ' (mã #' . $id_moi . ').');
        }
        chuyen_trang('nguoi-dung.php');
    }
}

$tong_ket = $tk ? qt_tong_ket_nguoi_dung($db, $id) : null;
$ngay_sinh_hop_le = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['ngay_sinh']) ? $f['ngay_sinh'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $tk ? 'Sửa người dùng' : 'Thêm người dùng' ?> | Quản trị SáchViệt</title>
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
      <p class="qt-duong-dan"><a href="nguoi-dung.php">Người dùng</a> › <?= $tk ? 'Sửa tài khoản #' . $tk['id'] : 'Thêm người dùng' ?></p>
      <h1><?= $tk ? e($tk['ho_ten']) : 'Thêm người dùng mới' ?></h1>
      <p><?= $tk ? 'Sửa thông tin, phân quyền, khoá tài khoản hoặc đặt lại mật khẩu.' : 'Tạo tài khoản cho khách hàng hoặc nhân viên, các ô có dấu * là bắt buộc.' ?></p>
    </div>
  </header>
  <?= hien_thong_bao() ?>
<?php if ($loi): ?>
  <p class="form-err">Chưa lưu được. Vui lòng sửa <?= count($loi) ?> ô được đánh dấu đỏ bên dưới.</p>
<?php endif; ?>

  <div class="qt-luoi-form">
    <section class="qt-the">
      <h2>Thông tin tài khoản</h2>
      <form class="form" action="nguoi-dung-form.php" method="post">
        <input type="hidden" name="id" value="<?= $tk ? $tk['id'] : 0 ?>">
        <div class="field-2">
          <div class="field<?= isset($loi['ho_ten']) ? ' has-err' : '' ?>">
            <label for="ho-ten">Họ và tên <span class="bat-buoc">*</span></label>
            <input type="text" id="ho-ten" name="ho_ten" value="<?= e($f['ho_ten']) ?>" maxlength="100" required>
            <?= loi_o($loi, 'ho_ten') ?>
          </div>
          <div class="field<?= isset($loi['email']) ? ' has-err' : '' ?>">
            <label for="email">Email đăng nhập <span class="bat-buoc">*</span></label>
            <input type="email" id="email" name="email" value="<?= e($f['email']) ?>" maxlength="150" required>
            <?= loi_o($loi, 'email') ?>
          </div>
        </div>
        <div class="field-2">
          <div class="field<?= isset($loi['so_dien_thoai']) ? ' has-err' : '' ?>">
            <label for="sdt">Số điện thoại <span class="bat-buoc">*</span></label>
            <input type="tel" id="sdt" name="so_dien_thoai" value="<?= e($f['so_dien_thoai']) ?>" placeholder="VD: 0901 234 567" required>
            <?= loi_o($loi, 'so_dien_thoai') ?>
          </div>
          <div class="field<?= isset($loi['ngay_sinh']) ? ' has-err' : '' ?>">
            <label for="ngay-sinh">Ngày sinh</label>
            <input type="date" id="ngay-sinh" name="ngay_sinh" value="<?= e($ngay_sinh_hop_le) ?>" max="<?= date('Y-m-d') ?>">
            <?= loi_o($loi, 'ngay_sinh') ?>
          </div>
        </div>
        <div class="field<?= isset($loi['dia_chi']) ? ' has-err' : '' ?>">
          <label for="dia-chi">Địa chỉ</label>
          <input type="text" id="dia-chi" name="dia_chi" value="<?= e($f['dia_chi']) ?>" maxlength="255">
          <?= loi_o($loi, 'dia_chi') ?>
        </div>
        <div class="field-2">
          <div class="field<?= isset($loi['vai_tro']) ? ' has-err' : '' ?>">
            <label for="vai-tro">Vai trò <span class="bat-buoc">*</span></label>
            <select id="vai-tro" name="vai_tro"<?= $la_toi ? ' disabled' : '' ?>>
<?php foreach (VAI_TRO as $gia_tri => $nhan_vt): ?>
              <option value="<?= $gia_tri ?>"<?= $gia_tri === $f['vai_tro'] ? ' selected' : '' ?>><?= $nhan_vt[0] ?></option>
<?php endforeach; ?>
            </select>
            <?= loi_o($loi, 'vai_tro') ?>
          </div>
          <div class="field<?= isset($loi['trang_thai']) ? ' has-err' : '' ?>">
            <label for="trang-thai">Trạng thái <span class="bat-buoc">*</span></label>
            <select id="trang-thai" name="trang_thai"<?= $la_toi ? ' disabled' : '' ?>>
<?php foreach (TRANG_THAI_TK as $gia_tri => $nhan_tt): ?>
              <option value="<?= $gia_tri ?>"<?= $gia_tri === $f['trang_thai'] ? ' selected' : '' ?>><?= $nhan_tt[0] ?></option>
<?php endforeach; ?>
            </select>
            <?= loi_o($loi, 'trang_thai') ?>
          </div>
        </div>
<?php if ($la_toi): ?>
        <p class="qt-canh-bao">Đây là tài khoản bạn đang dùng nên không đổi được vai trò và trạng thái, tránh tự khoá mình khỏi trang quản trị.</p>
<?php endif; ?>
        <div class="field<?= isset($loi['mat_khau']) ? ' has-err' : '' ?>">
          <label for="mat-khau"><?= $tk ? 'Mật khẩu mới' : 'Mật khẩu' ?><?= $tk ? '' : ' <span class="bat-buoc">*</span>' ?></label>
          <input type="password" id="mat-khau" name="mat_khau" autocomplete="new-password"
                 placeholder="<?= $tk ? 'Để trống nếu không đổi mật khẩu' : 'Ít nhất 6 ký tự' ?>"<?= $tk ? '' : ' required' ?>>
          <?= loi_o($loi, 'mat_khau') ?>
        </div>
        <div class="qt-nut-hang">
          <button type="submit" class="btn btn-solid"><?= $tk ? 'Lưu thay đổi' : 'Tạo tài khoản' ?></button>
          <a href="nguoi-dung.php" class="btn btn-ghost">Huỷ</a>
        </div>
      </form>
    </section>

<?php if ($tk): ?>
    <aside class="qt-ben">
      <section class="qt-the">
        <h2 class="sr-only">Tóm tắt tài khoản</h2>
        <div class="qt-ho-so">
          <span class="user-ava"><?= e(viet_tat($tk['ho_ten'])) ?></span>
          <strong><?= e($tk['ho_ten']) ?></strong>
          <em><?= e($tk['email']) ?></em>
          <span><?= nhan_mau(VAI_TRO, $tk['vai_tro']) ?> <?= nhan_mau(TRANG_THAI_TK, $tk['trang_thai']) ?></span>
          <span class="qt-nho">Tham gia ngày <?= ngay($tk['ngay_tao']) ?></span>
          <div class="qt-ho-so-so">
            <div><strong><?= $tong_ket['so_don'] ?></strong><span>Đơn hàng</span></div>
            <div><strong><?= tien($tong_ket['da_chi']) ?></strong><span>Đã chi (đơn đã giao)</span></div>
          </div>
        </div>
      </section>

      <section class="qt-the">
        <h2>Đơn hàng gần đây</h2>
<?php if (!$tong_ket['don_moi']): ?>
        <p class="qt-nho">Tài khoản này chưa đặt đơn hàng nào.</p>
<?php else: ?>
        <ul class="qt-ds">
<?php foreach ($tong_ket['don_moi'] as $don): ?>
          <li>
            <a class="qt-ma" href="don-hang-chi-tiet.php?id=<?= $don['id'] ?>"><?= ma_don($don['id']) ?></a>
            <span class="qt-nho"><?= ngay($don['ngay_dat']) ?></span>
            <span class="qt-ds-so"><strong><?= tien($don['tong']) ?></strong><?= nhan_mau(TRANG_THAI_DON, $don['trang_thai']) ?></span>
          </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
      </section>

      <section class="qt-the">
        <h2>Xoá tài khoản</h2>
<?php if ($la_toi): ?>
        <p class="qt-nho">Bạn không thể tự xoá tài khoản đang đăng nhập.</p>
<?php elseif ($tong_ket['so_don'] > 0): ?>
        <p class="qt-nho">Tài khoản đã có <?= $tong_ket['so_don'] ?> đơn hàng nên không xoá được. Hãy chuyển trạng thái sang “Tạm khoá” để chặn đăng nhập mà vẫn giữ lịch sử mua hàng.</p>
<?php else: ?>
        <p class="qt-nho">Tài khoản chưa có đơn hàng nên có thể xoá. Giỏ hàng, đánh giá và sách yêu thích của tài khoản cũng bị xoá theo.</p>
        <div class="qt-thao-tac qt-thao-tac-trai">
          <?= hop_xoa('nguoi-dung.php', ['hanh_dong' => 'xoa', 'id' => $tk['id'], 'quay_lai' => 'nguoi-dung.php'],
                      'Xoá tài khoản ' . $tk['email'] . '? Giỏ hàng, đánh giá và sách yêu thích của tài khoản này cũng bị xoá. Thao tác này không hoàn tác được.',
                      'nguoi-dung-form.php?id=' . $tk['id'], 'Xoá tài khoản') ?>

        </div>
<?php endif; ?>
      </section>
    </aside>
<?php else: ?>
    <aside class="qt-ben">
      <section class="qt-the">
        <h2>Quyền của từng vai trò</h2>
        <dl class="qt-dl">
          <dt><?= nhan_mau(VAI_TRO, 'admin') ?></dt><dd>Toàn quyền trang quản trị, gồm cả quản lý người dùng.</dd>
          <dt><?= nhan_mau(VAI_TRO, 'staff') ?></dt><dd>Quản lý loại sách, sách và đơn hàng; không vào được mục Người dùng.</dd>
          <dt><?= nhan_mau(VAI_TRO, 'user') ?></dt><dd>Chỉ mua hàng trên cửa hàng, không vào được trang quản trị.</dd>
        </dl>
        <p class="qt-canh-bao qt-cach-tren">Tài khoản ở trạng thái “Tạm khoá” hoặc “Chờ duyệt” không đăng nhập được.</p>
      </section>
    </aside>
<?php endif; ?>
  </div>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</main>
</div>
</body>
</html>
