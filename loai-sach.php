<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$nd = bat_buoc_quan_tri();
$qt_menu = 'loai-sach';

$tu_khoa  = tu_khoa_tim();
$id_sua   = ctype_digit(get_chuoi('sua')) ? (int) get_chuoi('sua') : 0;
$loai_sua = $id_sua > 0 ? lay_the_loai($db, $id_sua) : null;
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanh_dong = post_chuoi('hanh_dong');
    $id = post_so('id');

    if ($hanh_dong === 'xoa') {
        [$loai, $noi_dung] = qt_xoa_loai($db, $id);
        dat_thong_bao($loai, $noi_dung);
        chuyen_trang(duong_dan_an_toan(post_chuoi('quay_lai'), 'loai-sach.php'));
    }
    if ($hanh_dong !== 'luu') {
        dat_thong_bao('loi', 'Thao tác không hợp lệ.');
        chuyen_trang('loai-sach.php');
    }

    $loai_sua = $id > 0 ? lay_the_loai($db, $id) : null;
    if ($id > 0 && !$loai_sua) {
        dat_thong_bao('loi', 'Không tìm thấy loại sách cần sửa.');
        chuyen_trang('loai-sach.php');
    }

    $f = qt_doc_form_loai();
    $loi = qt_kiem_tra_loai($db, $f, $id);
    if (!$loi) {
        if ($loai_sua) {
            qt_sua_loai($db, $id, $f);
            dat_thong_bao('ok', 'Đã cập nhật loại sách “' . $f['ten'] . '”.');
        } else {
            qt_them_loai($db, $f);
            dat_thong_bao('ok', 'Đã thêm loại sách “' . $f['ten'] . '”.');
        }
        chuyen_trang('loai-sach.php');
    }
} else {
    if ($id_sua > 0 && !$loai_sua) {
        dat_thong_bao('loi', 'Không tìm thấy loại sách có mã ' . $id_sua . '.');
    }
    $f = $loai_sua ?? ['ten' => '', 'mau' => '#A8551E', 'bieu_tuong' => '📚'];
}

$ds_loai = qt_lay_ds_loai($db, $tu_khoa);
$quay_lai = link_trang('loai-sach.php', ['q' => $tu_khoa]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Loại sách | Quản trị SáchViệt</title>
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
      <h1>Loại sách</h1>
      <p>Xem, thêm, sửa, xoá và tìm kiếm các loại (thể loại) sách đang có trên cửa hàng.</p>
    </div>
    <div class="qt-head-nut">
      <a href="loai-sach.php#form-loai" class="btn btn-solid">＋ Thêm loại sách</a>
    </div>
  </header>
  <?= hien_thong_bao() ?>

  <div class="qt-luoi-form">
    <section class="qt-the">
      <h2 class="sr-only">Danh sách loại sách</h2>
      <form class="qt-loc" action="loai-sach.php" method="get" role="search">
        <div class="field field-rong">
          <label for="q">Tìm loại sách</label>
          <input type="search" id="q" name="q" value="<?= e($tu_khoa) ?>" placeholder="Nhập tên loại sách...">
        </div>
        <div class="qt-loc-nut">
          <button type="submit" class="btn btn-solid">Tìm</button>
<?php if ($tu_khoa !== ''): ?>
          <a href="loai-sach.php" class="btn btn-ghost">Bỏ lọc</a>
<?php endif; ?>
        </div>
      </form>

      <p class="qt-ket-qua"><?= count($ds_loai) ?> loại sách<?= $tu_khoa !== '' ? ' khớp với “' . e($tu_khoa) . '”' : '' ?></p>

<?php if (!$ds_loai): ?>
      <p class="qt-rong">Không tìm thấy loại sách nào.</p>
<?php else: ?>
      <div class="qt-cuon">
        <table class="qt-bang">
          <thead>
            <tr><th>Loại sách</th><th class="ta-c">Số sách</th><th class="ta-r">Tồn kho</th><th class="ta-r">Thao tác</th></tr>
          </thead>
          <tbody>
<?php foreach ($ds_loai as $l): ?>
            <tr>
              <td>
                <span class="qt-sp">
                  <span class="qt-loai-ico" style="--mau:<?= mau($l['mau']) ?>"><?= e($l['bieu_tuong']) ?></span>
                  <span><strong><?= e($l['ten']) ?></strong>
                    <em>Mã #<?= $l['id'] ?> · <span class="qt-mau-o" style="--mau:<?= mau($l['mau']) ?>"></span> <?= e($l['mau']) ?></em></span>
                </span>
              </td>
              <td class="ta-c nowrap"><strong><?= $l['so_sach'] ?></strong><br><span class="qt-nho"><?= $l['dang_ban'] ?> đang bán</span></td>
              <td class="ta-r"><?= so($l['ton_kho']) ?></td>
              <td class="qt-thao-tac-o">
                <div class="qt-thao-tac">
                  <a class="qt-nut" href="<?= e(link_trang('loai-sach.php', ['q' => $tu_khoa, 'sua' => (int) $l['id']])) ?>#form-loai">Sửa</a>
                  <a class="qt-nut" href="../the-loai.php?id=<?= $l['id'] ?>">Xem ↗</a>
<?php if ($l['so_sach'] > 0): ?>
                  <button type="button" class="qt-nut qt-nut-do" disabled title="Còn <?= $l['so_sach'] ?> sách thuộc loại này">Xoá</button>
<?php else: ?>
                  <?= hop_xoa('loai-sach.php', ['hanh_dong' => 'xoa', 'id' => $l['id'], 'quay_lai' => $quay_lai],
                              'Xoá loại sách “' . $l['ten'] . '”? Thao tác này không hoàn tác được.', $quay_lai) ?>

<?php endif; ?>
                </div>
              </td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
<?php endif; ?>
    </section>

    <aside class="qt-ben">
      <section class="qt-the" id="form-loai">
        <h2><?= $loai_sua ? 'Sửa loại sách' : 'Thêm loại sách mới' ?></h2>
<?php if ($loai_sua): ?>
        <p class="qt-dang-sua"><span>Đang sửa: <strong><?= e($loai_sua['ten']) ?></strong></span><a href="loai-sach.php">Huỷ sửa</a></p>
<?php endif; ?>
<?php if ($loi): ?>
        <p class="form-err">Chưa lưu được. Vui lòng sửa <?= count($loi) ?> ô được đánh dấu đỏ.</p>
<?php endif; ?>
        <form class="form" action="loai-sach.php" method="post">
          <input type="hidden" name="hanh_dong" value="luu">
          <input type="hidden" name="id" value="<?= $loai_sua ? $loai_sua['id'] : 0 ?>">
          <div class="field<?= isset($loi['ten']) ? ' has-err' : '' ?>">
            <label for="ten">Tên loại sách <span class="bat-buoc">*</span></label>
            <input type="text" id="ten" name="ten" value="<?= e($f['ten']) ?>" maxlength="100" placeholder="VD: Truyện thiếu nhi" required>
            <?= loi_o($loi, 'ten') ?>
          </div>
          <div class="field-2">
            <div class="field<?= isset($loi['mau']) ? ' has-err' : '' ?>">
              <label for="mau">Màu bìa</label>
              <input type="color" id="mau" name="mau" value="<?= e(mau($f['mau'])) ?>">
              <?= loi_o($loi, 'mau') ?>
            </div>
            <div class="field<?= isset($loi['bieu_tuong']) ? ' has-err' : '' ?>">
              <label for="bieu-tuong">Biểu tượng</label>
              <input type="text" id="bieu-tuong" name="bieu_tuong" value="<?= e($f['bieu_tuong']) ?>" maxlength="16" required>
              <?= loi_o($loi, 'bieu_tuong') ?>
            </div>
          </div>
          <p class="qt-nho">Màu bìa dùng cho bìa sách thuộc loại này, biểu tượng hiện ở mục “Danh mục nổi bật” trên trang chủ.</p>
          <div class="qt-nut-hang">
            <button type="submit" class="btn btn-solid"><?= $loai_sua ? 'Lưu thay đổi' : 'Thêm loại sách' ?></button>
<?php if ($loai_sua): ?>
            <a href="loai-sach.php" class="btn btn-ghost">Huỷ</a>
<?php endif; ?>
          </div>
        </form>
      </section>
    </aside>
  </div>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</main>
</div>
</body>
</html>
