<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_quan_tri();
$qt_menu = 'sach';

$la_post = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = $la_post ? post_so('id') : (ctype_digit(get_chuoi('id')) ? (int) get_chuoi('id') : 0);
$sach = $id > 0 ? qt_lay_sach($db, $id) : null;
if ($id > 0 && !$sach) {
    dat_thong_bao('loi', 'Không tìm thấy sách có mã ' . $id . '.');
    chuyen_trang('sach.php');
}

$f = $sach ?? qt_sach_trong();
$loi = [];

if ($la_post) {
    $f = qt_doc_form_sach();
    $loi = qt_kiem_tra_sach($db, $f);
    if (!$loi) {
        if ($sach) {
            qt_sua_sach($db, $id, $f);
            dat_thong_bao('ok', 'Đã lưu thay đổi của sách “' . $f['ten'] . '”'
                              . ($f['hien_thi'] ? '.' : ' (đang ẩn khỏi cửa hàng).'));
        } else {
            $id_moi = qt_them_sach($db, $f);
            dat_thong_bao('ok', 'Đã thêm sách “' . $f['ten'] . '” với mã #' . $id_moi . '.');
        }
        chuyen_trang('sach.php');
    }
}

$ds_ten_loai = qt_lay_ten_loai($db);
$ds_kho = lay_kho($db);
$loai_cua_sach = $sach ? lay_the_loai($db, (int) $sach['the_loai_id']) : null;
$so_don_co_sach = $sach ? qt_dem_don_cua_sach($db, $id) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $sach ? 'Sửa sách' : 'Thêm sách' ?> | Quản trị SáchViệt</title>
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
      <p class="qt-duong-dan"><a href="sach.php">Sách</a> › <?= $sach ? 'Sửa sách #' . $sach['id'] : 'Thêm sách' ?></p>
      <h1><?= $sach ? e($sach['ten']) : 'Thêm sách mới' ?></h1>
      <p><?= $sach ? 'Chỉnh sửa thông tin, giá bán và tồn kho của sách.' : 'Điền thông tin sách, các ô có dấu * là bắt buộc.' ?></p>
    </div>
<?php if ($sach): ?>
    <div class="qt-head-nut">
      <a href="../sach.php?id=<?= $sach['id'] ?>" class="btn btn-ghost">Xem trên cửa hàng ↗</a>
    </div>
<?php endif; ?>
  </header>
  <?= hien_thong_bao() ?>
<?php if ($loi): ?>
  <p class="form-err">Chưa lưu được. Vui lòng sửa <?= count($loi) ?> ô được đánh dấu đỏ bên dưới.</p>
<?php endif; ?>

  <form class="qt-luoi-form" action="sach-form.php" method="post">
    <input type="hidden" name="id" value="<?= $sach ? $sach['id'] : 0 ?>">

    <section class="qt-the">
      <h2>Thông tin sách</h2>
      <div class="form">
        <div class="field<?= isset($loi['ten']) ? ' has-err' : '' ?>">
          <label for="ten">Tên sách <span class="bat-buoc">*</span></label>
          <input type="text" id="ten" name="ten" value="<?= e($f['ten']) ?>" maxlength="200" required>
          <?= loi_o($loi, 'ten') ?>
        </div>
        <div class="field-2">
          <div class="field<?= isset($loi['tac_gia']) ? ' has-err' : '' ?>">
            <label for="tac-gia">Tác giả</label>
            <input type="text" id="tac-gia" name="tac_gia" value="<?= e($f['tac_gia']) ?>" maxlength="150" placeholder="Để trống nếu khuyết danh">
            <?= loi_o($loi, 'tac_gia') ?>
          </div>
          <div class="field<?= isset($loi['nam_sang_tac']) ? ' has-err' : '' ?>">
            <label for="nam">Năm sáng tác</label>
            <input type="text" id="nam" name="nam_sang_tac" value="<?= e($f['nam_sang_tac']) ?>" maxlength="50" placeholder="VD: 1820 hoặc Thế kỷ XVIII">
            <?= loi_o($loi, 'nam_sang_tac') ?>
          </div>
        </div>
        <div class="field<?= isset($loi['mo_ta']) ? ' has-err' : '' ?>">
          <label for="mo-ta">Mô tả ngắn</label>
          <textarea id="mo-ta" name="mo_ta" rows="4" maxlength="2000"><?= e($f['mo_ta']) ?></textarea>
          <?= loi_o($loi, 'mo_ta') ?>
        </div>
        <div class="field<?= isset($loi['trich_doan']) ? ' has-err' : '' ?>">
          <label for="trich-doan">Đoạn đọc thử</label>
          <textarea id="trich-doan" name="trich_doan" rows="8" maxlength="10000"><?= e($f['trich_doan']) ?></textarea>
          <small>Các đoạn cách nhau bằng một dòng trống. Để trống nếu sách chưa có đoạn đọc thử.</small>
          <?= loi_o($loi, 'trich_doan') ?>
        </div>
        <div class="field<?= isset($loi['link_wikisource']) ? ' has-err' : '' ?>">
          <label for="link">Link đọc toàn văn (Wikisource)</label>
          <input type="text" id="link" name="link_wikisource" value="<?= e($f['link_wikisource']) ?>" maxlength="255" placeholder="https://vi.wikisource.org/wiki/...">
          <?= loi_o($loi, 'link_wikisource') ?>
        </div>
      </div>
    </section>

    <aside class="qt-ben">
      <section class="qt-the">
        <h2>Phân loại, giá và kho</h2>
<?php if ($sach): ?>
        <div class="qt-bia-xem"><?= bia($sach['ten'], $sach['tac_gia'], $loai_cua_sach['mau'] ?? '') ?></div>
<?php endif; ?>
        <div class="form">
          <div class="field<?= isset($loi['the_loai_id']) ? ' has-err' : '' ?>">
            <label for="the-loai">Loại sách <span class="bat-buoc">*</span></label>
            <select id="the-loai" name="the_loai_id" required>
              <option value="">— Chọn loại sách —</option>
<?php foreach ($ds_ten_loai as $id_loai => $ten_loai): ?>
              <option value="<?= $id_loai ?>"<?= $id_loai === (int) $f['the_loai_id'] ? ' selected' : '' ?>><?= e($ten_loai) ?></option>
<?php endforeach; ?>
            </select>
            <?= loi_o($loi, 'the_loai_id') ?>
          </div>
          <div class="field<?= isset($loi['kho_id']) ? ' has-err' : '' ?>">
            <label for="kho">Kho hàng <span class="bat-buoc">*</span></label>
            <select id="kho" name="kho_id" required>
              <option value="">— Chọn kho —</option>
<?php foreach ($ds_kho as $kho): ?>
              <option value="<?= $kho['id'] ?>"<?= (int) $kho['id'] === (int) $f['kho_id'] ? ' selected' : '' ?>><?= e($kho['ten']) ?></option>
<?php endforeach; ?>
            </select>
            <?= loi_o($loi, 'kho_id') ?>
          </div>
          <div class="field-2">
            <div class="field<?= isset($loi['gia']) ? ' has-err' : '' ?>">
              <label for="gia">Giá bán (₫) <span class="bat-buoc">*</span></label>
              <input type="text" id="gia" name="gia" value="<?= e($f['gia']) ?>" placeholder="VD: 125000" required>
              <?= loi_o($loi, 'gia') ?>
            </div>
            <div class="field<?= isset($loi['gia_cu']) ? ' has-err' : '' ?>">
              <label for="gia-cu">Giá bìa (₫)</label>
              <input type="text" id="gia-cu" name="gia_cu" value="<?= e($f['gia_cu']) ?>" placeholder="Để trống nếu không giảm">
              <?= loi_o($loi, 'gia_cu') ?>
            </div>
          </div>
          <div class="field<?= isset($loi['so_luong']) ? ' has-err' : '' ?>">
            <label for="so-luong">Số lượng tồn kho <span class="bat-buoc">*</span></label>
            <input type="text" id="so-luong" name="so_luong" value="<?= e($f['so_luong']) ?>" placeholder="VD: 50" required>
            <?= loi_o($loi, 'so_luong') ?>
          </div>
          <label class="check"><input type="checkbox" name="hien_thi" value="1"<?= $f['hien_thi'] ? ' checked' : '' ?>>
            <span>Đang bán trên cửa hàng (bỏ chọn để ẩn sách, sách cũng bị bỏ khỏi giỏ hàng của khách)</span></label>
          <div class="qt-nut-hang">
            <button type="submit" class="btn btn-solid"><?= $sach ? 'Lưu thay đổi' : 'Thêm sách' ?></button>
            <a href="sach.php" class="btn btn-ghost">Huỷ</a>
          </div>
        </div>
      </section>
    </aside>
  </form>

<?php if ($sach): ?>
  <section class="qt-the qt-vung-xoa">
    <h2>Xoá sách</h2>
<?php if ($so_don_co_sach > 0): ?>
    <p class="qt-nho">Sách đã nằm trong <?= $so_don_co_sach ?> đơn hàng nên không xoá được, để giữ đúng lịch sử đơn hàng.
       Muốn ngừng bán, hãy bỏ chọn “Đang bán trên cửa hàng” rồi bấm “Lưu thay đổi”.</p>
<?php else: ?>
    <p class="qt-nho">Sách chưa nằm trong đơn hàng nào nên có thể xoá. Đánh giá, giỏ hàng và danh sách yêu thích có sách này cũng bị xoá theo.</p>
    <div class="qt-thao-tac qt-thao-tac-trai">
      <?= hop_xoa('sach.php', ['hanh_dong' => 'xoa', 'id' => $sach['id'], 'quay_lai' => 'sach.php'],
                  'Xoá sách “' . $sach['ten'] . '”? Đánh giá, giỏ hàng và danh sách yêu thích có sách này cũng bị xoá. Thao tác này không hoàn tác được.',
                  'sach-form.php?id=' . $sach['id'], 'Xoá sách này') ?>

    </div>
<?php endif; ?>
  </section>
<?php endif; ?>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</main>
</div>
</body>
</html>
