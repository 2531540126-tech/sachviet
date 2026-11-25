<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_quan_tri();
$qt_menu = 'sach';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch (post_chuoi('hanh_dong')) {
        case 'an_hien':
            [$loai, $noi_dung] = qt_doi_hien_thi($db, post_so('id'));
            break;
        case 'xoa':
            [$loai, $noi_dung] = qt_xoa_sach($db, post_so('id'));
            break;
        default:
            [$loai, $noi_dung] = ['loi', 'Thao tác không hợp lệ.'];
    }
    dat_thong_bao($loai, $noi_dung);
    chuyen_trang(duong_dan_an_toan(post_chuoi('quay_lai'), 'sach.php'));
}

$tu_khoa    = tu_khoa_tim();
$loai_id    = ctype_digit(get_chuoi('loai')) ? (int) get_chuoi('loai') : 0;
$tinh_trang = get_chuoi('tt');
if (!isset(TINH_TRANG_SACH[$tinh_trang])) {
    $tinh_trang = '';
}

$ds_ten_loai = qt_lay_ten_loai($db);
$tham_so  = ['q' => $tu_khoa, 'loai' => $loai_id, 'tt' => $tinh_trang];
$pt       = phan_trang(qt_dem_sach($db, $tu_khoa, $loai_id, $tinh_trang));
$ds_sach  = qt_lay_ds_sach($db, $tu_khoa, $loai_id, $tinh_trang, $pt);
$quay_lai = link_trang('sach.php', $tham_so + ['trang' => $pt['trang']]);
$dang_loc = $tu_khoa !== '' || $loai_id > 0 || $tinh_trang !== '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sách | Quản trị SáchViệt</title>
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
      <h1>Sách</h1>
      <p>Xem, thêm, sửa, ẩn, xoá và tìm kiếm sách. Sách đã có trong đơn hàng chỉ ẩn được, không xoá được.</p>
    </div>
    <div class="qt-head-nut">
      <a href="sach-form.php" class="btn btn-solid">＋ Thêm sách</a>
    </div>
  </header>
  <?= hien_thong_bao() ?>

  <section class="qt-the">
    <h2 class="sr-only">Danh sách sách</h2>
    <form class="qt-loc" action="sach.php" method="get" role="search">
      <div class="field field-rong">
        <label for="q">Tìm sách</label>
        <input type="search" id="q" name="q" value="<?= e($tu_khoa) ?>" placeholder="Tên sách, tác giả hoặc mã sách...">
      </div>
      <div class="field">
        <label for="loai">Loại sách</label>
        <select id="loai" name="loai">
          <option value="">Tất cả loại</option>
<?php foreach ($ds_ten_loai as $id_loai => $ten_loai): ?>
          <option value="<?= $id_loai ?>"<?= $id_loai === $loai_id ? ' selected' : '' ?>><?= e($ten_loai) ?></option>
<?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="tt">Tình trạng</label>
        <select id="tt" name="tt">
          <option value="">Tất cả tình trạng</option>
<?php foreach (TINH_TRANG_SACH as $gia_tri => $ten_tt): ?>
          <option value="<?= $gia_tri ?>"<?= $gia_tri === $tinh_trang ? ' selected' : '' ?>><?= $ten_tt ?></option>
<?php endforeach; ?>
        </select>
      </div>
      <div class="qt-loc-nut">
        <button type="submit" class="btn btn-solid">Lọc</button>
<?php if ($dang_loc): ?>
        <a href="sach.php" class="btn btn-ghost">Bỏ lọc</a>
<?php endif; ?>
      </div>
    </form>

    <p class="qt-ket-qua">Tìm thấy <?= $pt['tong'] ?> quyển sách<?= $pt['so_trang'] > 1 ? ' · trang ' . $pt['trang'] . '/' . $pt['so_trang'] : '' ?></p>

<?php if (!$ds_sach): ?>
    <p class="qt-rong">Không có quyển sách nào khớp với điều kiện lọc.</p>
<?php else: ?>
    <div class="qt-cuon">
      <table class="qt-bang">
        <thead>
          <tr>
            <th>Mã</th><th>Sách</th><th>Loại</th><th class="ta-r">Giá bán</th>
            <th class="ta-c">Tồn kho</th><th class="ta-c">Đã bán</th><th>Trạng thái</th><th class="ta-r">Thao tác</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($ds_sach as $s): ?>
          <tr<?= $s['hien_thi'] ? '' : ' class="qt-mo"' ?>>
            <td class="qt-nho">#<?= $s['id'] ?></td>
            <td>
              <span class="qt-sp">
                <span class="qt-sp-bia"><?= bia_nho($s['ten'], $s['mau']) ?></span>
                <span><strong><?= e($s['ten']) ?></strong><em><?= e($s['tac_gia']) ?> · Kho <?= e($s['ten_kho']) ?></em></span>
              </span>
            </td>
            <td><?= e($s['ten_the_loai']) ?></td>
            <td class="ta-r nowrap">
              <strong><?= tien($s['gia']) ?></strong>
<?php if ($s['gia_cu']): ?>
              <br><span class="qt-gach"><?= tien($s['gia_cu']) ?></span>
<?php endif; ?>
            </td>
            <td class="ta-c">
<?php if ($s['so_luong'] == 0): ?>
              <span class="tt tt-low">Hết hàng</span>
<?php elseif ($s['so_luong'] <= SAP_HET_HANG): ?>
              <span class="tt tt-warn"><?= $s['so_luong'] ?></span>
<?php else: ?>
              <?= so($s['so_luong']) ?>

<?php endif; ?>
            </td>
            <td class="ta-c"><?= so($s['da_ban']) ?></td>
            <td><?= $s['hien_thi'] ? '<span class="tt tt-ok">Đang bán</span>' : '<span class="tt tt-an">Đang ẩn</span>' ?></td>
            <td class="qt-thao-tac-o">
              <div class="qt-thao-tac">
                <a class="qt-nut" href="sach-form.php?id=<?= $s['id'] ?>">Sửa</a>
                <form action="sach.php" method="post">
                  <input type="hidden" name="hanh_dong" value="an_hien">
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                  <input type="hidden" name="quay_lai" value="<?= e($quay_lai) ?>">
                  <button type="submit" class="qt-nut"><?= $s['hien_thi'] ? 'Ẩn' : 'Hiện' ?></button>
                </form>
<?php if ($s['so_don'] > 0): ?>
                <button type="button" class="qt-nut qt-nut-do" disabled title="Đã có trong <?= $s['so_don'] ?> đơn hàng, chỉ ẩn được">Xoá</button>
<?php else: ?>
                <?= hop_xoa('sach.php', ['hanh_dong' => 'xoa', 'id' => $s['id'], 'quay_lai' => $quay_lai],
                            'Xoá sách “' . $s['ten'] . '”? Đánh giá và giỏ hàng có sách này cũng bị xoá.', $quay_lai) ?>

<?php endif; ?>
              </div>
            </td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= hien_phan_trang('sach.php', $tham_so, $pt) ?>

<?php endif; ?>
  </section>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</main>
</div>
</body>
</html>
