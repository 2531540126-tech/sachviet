<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_quan_tri(true);
$qt_menu = 'nguoi-dung';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch (post_chuoi('hanh_dong')) {
        case 'trang_thai':
            [$loai, $noi_dung] = qt_doi_trang_thai_tk($db, post_so('id'), post_chuoi('trang_thai'), $nd['id']);
            break;
        case 'xoa':
            [$loai, $noi_dung] = qt_xoa_nguoi_dung($db, post_so('id'), $nd['id']);
            break;
        default:
            [$loai, $noi_dung] = ['loi', 'Thao tác không hợp lệ.'];
    }
    dat_thong_bao($loai, $noi_dung);
    chuyen_trang(duong_dan_an_toan(post_chuoi('quay_lai'), 'nguoi-dung.php'));
}

$tu_khoa = tu_khoa_tim();
$vai_tro = get_chuoi('vt');
if (!isset(VAI_TRO[$vai_tro])) {
    $vai_tro = '';
}
$trang_thai = get_chuoi('tt');
if (!isset(TRANG_THAI_TK[$trang_thai])) {
    $trang_thai = '';
}

$tham_so  = ['q' => $tu_khoa, 'vt' => $vai_tro, 'tt' => $trang_thai];
$pt       = phan_trang(qt_dem_nguoi_dung($db, $tu_khoa, $vai_tro, $trang_thai));
$ds_nd    = qt_lay_ds_nguoi_dung($db, $tu_khoa, $vai_tro, $trang_thai, $pt);
$quay_lai = link_trang('nguoi-dung.php', $tham_so + ['trang' => $pt['trang']]);
$dang_loc = $tu_khoa !== '' || $vai_tro !== '' || $trang_thai !== '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Người dùng | Quản trị SáchViệt</title>
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
      <h1>Người dùng</h1>
      <p>Xem, thêm, sửa, khoá và tìm kiếm tài khoản khách hàng, nhân viên và quản trị viên.</p>
    </div>
    <div class="qt-head-nut">
      <a href="nguoi-dung-form.php" class="btn btn-solid">＋ Thêm người dùng</a>
    </div>
  </header>
  <?= hien_thong_bao() ?>

  <section class="qt-the">
    <h2 class="sr-only">Danh sách người dùng</h2>
    <form class="qt-loc" action="nguoi-dung.php" method="get" role="search">
      <div class="field field-rong">
        <label for="q">Tìm người dùng</label>
        <input type="search" id="q" name="q" value="<?= e($tu_khoa) ?>" placeholder="Họ tên, email hoặc số điện thoại...">
      </div>
      <div class="field">
        <label for="vt">Vai trò</label>
        <select id="vt" name="vt">
          <option value="">Tất cả vai trò</option>
<?php foreach (VAI_TRO as $gia_tri => $nhan_vt): ?>
          <option value="<?= $gia_tri ?>"<?= $gia_tri === $vai_tro ? ' selected' : '' ?>><?= $nhan_vt[0] ?></option>
<?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="tt">Trạng thái</label>
        <select id="tt" name="tt">
          <option value="">Tất cả trạng thái</option>
<?php foreach (TRANG_THAI_TK as $gia_tri => $nhan_tt): ?>
          <option value="<?= $gia_tri ?>"<?= $gia_tri === $trang_thai ? ' selected' : '' ?>><?= $nhan_tt[0] ?></option>
<?php endforeach; ?>
        </select>
      </div>
      <div class="qt-loc-nut">
        <button type="submit" class="btn btn-solid">Lọc</button>
<?php if ($dang_loc): ?>
        <a href="nguoi-dung.php" class="btn btn-ghost">Bỏ lọc</a>
<?php endif; ?>
      </div>
    </form>

    <p class="qt-ket-qua">Tìm thấy <?= $pt['tong'] ?> tài khoản<?= $pt['so_trang'] > 1 ? ' · trang ' . $pt['trang'] . '/' . $pt['so_trang'] : '' ?></p>

<?php if (!$ds_nd): ?>
    <p class="qt-rong">Không có tài khoản nào khớp với điều kiện lọc.</p>
<?php else: ?>
    <div class="qt-cuon">
      <table class="qt-bang">
        <thead>
          <tr>
            <th>Người dùng</th><th>Số điện thoại</th><th>Vai trò · Trạng thái</th>
            <th class="ta-r">Mua hàng</th><th>Ngày tạo</th><th class="ta-r">Thao tác</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($ds_nd as $u):
    $la_toi = (int) $u['id'] === $nd['id']; ?>
          <tr<?= $u['trang_thai'] !== 'hoat_dong' ? ' class="qt-mo"' : '' ?>>
            <td>
              <span class="qt-sp qt-nguoi">
                <span class="user-ava"><?= e(viet_tat($u['ho_ten'])) ?></span>
                <span><strong><?= e($u['ho_ten']) ?><?= $la_toi ? ' (bạn)' : '' ?></strong><em>#<?= $u['id'] ?> · <?= e($u['email']) ?></em></span>
              </span>
            </td>
            <td class="nowrap"><?= e($u['so_dien_thoai'] ?? '—') ?></td>
            <td><span class="qt-xep-doc"><?= nhan_mau(VAI_TRO, $u['vai_tro']) ?><?= nhan_mau(TRANG_THAI_TK, $u['trang_thai']) ?></span></td>
            <td class="ta-r nowrap"><strong><?= $u['so_don'] ?> đơn</strong><br><span class="qt-nho"><?= tien($u['da_chi']) ?></span></td>
            <td class="nowrap"><?= ngay($u['ngay_tao']) ?></td>
            <td class="qt-thao-tac-o">
              <div class="qt-thao-tac">
                <a class="qt-nut" href="nguoi-dung-form.php?id=<?= $u['id'] ?>">Sửa</a>
<?php if (!$la_toi): ?>
                <form action="nguoi-dung.php" method="post">
                  <input type="hidden" name="hanh_dong" value="trang_thai">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="trang_thai" value="<?= $u['trang_thai'] === 'hoat_dong' ? 'tam_khoa' : 'hoat_dong' ?>">
                  <input type="hidden" name="quay_lai" value="<?= e($quay_lai) ?>">
                  <button type="submit" class="qt-nut"><?= $u['trang_thai'] === 'hoat_dong' ? 'Khoá' : ($u['trang_thai'] === 'cho_duyet' ? 'Duyệt' : 'Mở khoá') ?></button>
                </form>
<?php if ($u['so_don'] > 0): ?>
                <button type="button" class="qt-nut qt-nut-do" disabled title="Đã có <?= $u['so_don'] ?> đơn hàng, chỉ khoá được">Xoá</button>
<?php else: ?>
                <?= hop_xoa('nguoi-dung.php', ['hanh_dong' => 'xoa', 'id' => $u['id'], 'quay_lai' => $quay_lai],
                            'Xoá tài khoản ' . $u['email'] . '? Giỏ hàng, đánh giá và sách yêu thích của tài khoản này cũng bị xoá.', $quay_lai) ?>

<?php endif; ?>
<?php endif; ?>
              </div>
            </td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= hien_phan_trang('nguoi-dung.php', $tham_so, $pt) ?>

<?php endif; ?>
  </section>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</main>
</div>
</body>
</html>
