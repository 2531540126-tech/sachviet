<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$menu = 'danh-muc';
$id = (int) ($_GET['id'] ?? 0);
$sap_xep = get_chuoi('sx');
if (!isset(KIEU_SAP_XEP[$sap_xep])) {
    $sap_xep = 'moi';
}

$the_loai = lay_the_loai($db, $id);
if ($the_loai) {
    $ds_sach     = lay_sach_theo_loai($db, $id, $sap_xep);
    $ds_the_loai = lay_the_loai_kem_so_sach($db);
    $quay_lai    = 'the-loai.php?id=' . $id . '&sx=' . $sap_xep;
} else {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $the_loai ? e($the_loai['ten']) : 'Không tìm thấy thể loại' ?> | SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<main class="page">
<?php if (!$the_loai): ?>
  <nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="index.php">Trang chủ</a> <span>›</span>
    <a href="danh-muc.php">Danh mục</a> <span>›</span> <strong>Không tìm thấy</strong>
  </nav>
  <section class="panel">
    <h2>Không tìm thấy thể loại này</h2>
    <p class="sp-mota">Thể loại bạn tìm không tồn tại hoặc đã ngừng kinh doanh.</p>
    <a href="danh-muc.php" class="btn btn-solid">← Xem tất cả danh mục</a>
  </section>
<?php else: ?>
  <nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="index.php">Trang chủ</a> <span>›</span>
    <a href="danh-muc.php">Danh mục</a> <span>›</span>
    <strong><?= e($the_loai['ten']) ?></strong>
  </nav>

  <section class="tl-head" style="--mau:<?= mau($the_loai['mau']) ?>">
    <span class="tl-icon"><?= e($the_loai['bieu_tuong']) ?></span>
    <div>
      <h1 class="page-title"><?= e($the_loai['ten']) ?></h1>
      <p class="tl-sub"><?= count($ds_sach) ?> đầu sách đang bán trong thể loại này</p>
    </div>
  </section>

  <nav class="tl-chips" aria-label="Thể loại khác">
<?php foreach ($ds_the_loai as $tl): ?>
    <a href="the-loai.php?id=<?= $tl['id'] ?>" class="chip<?= $tl['id'] == $id ? ' chip-on' : '' ?>"><?= e($tl['bieu_tuong']) ?> <?= e($tl['ten']) ?> (<?= $tl['so_sach'] ?>)</a>
<?php endforeach; ?>
  </nav>

  <div class="tl-bar">
    <span>Sắp xếp theo:</span>
<?php foreach (KIEU_SAP_XEP as $ma => $kieu): ?>
    <a href="the-loai.php?id=<?= $id ?>&amp;sx=<?= $ma ?>" class="sx<?= $ma === $sap_xep ? ' sx-on' : '' ?>"><?= $kieu[0] ?></a>
<?php endforeach; ?>
  </div>

  <div class="grid">
<?php foreach ($ds_sach as $s): ?>
    <article class="card" style="--mau:<?= mau($s['mau']) ?>">
      <a class="card-link" href="sach.php?id=<?= $s['id'] ?>">
        <span class="card-bia"><?= bia($s['ten'], $s['tac_gia'], $s['mau']) ?>
          <?php if ($s['co_trich_doan']): ?><span class="tag-doc">Có đọc thử</span><?php endif; ?>

        </span>
        <div class="card-body">
          <span class="card-tl"><?= e($s['ten_the_loai']) ?></span>
          <h3 class="card-ten"><?= e($s['ten']) ?></h3>
          <span class="card-tg"><?= e($s['tac_gia']) ?></span>
          <span class="card-rate"><?= sao($s['sao']) ?><span class="card-luot">(<?= $s['luot_danh_gia'] ?>)</span></span>
          <span class="card-foot">
            <span class="card-gia"><?= tien($s['gia']) ?>
              <?php if ($s['gia_cu'] > $s['gia']): ?><s class="card-gia-cu"><?= tien($s['gia_cu']) ?></s><?php endif; ?>
            </span>
            <span class="card-kho">📍 <?= e($s['ten_kho']) ?></span>
          </span>
        </div>
      </a>
      <form class="card-actions" action="gio-hang.php" method="post">
        <a href="sach.php?id=<?= $s['id'] ?>" class="btn btn-ghost btn-sm">Xem chi tiết</a>
        <input type="hidden" name="hanh_dong" value="them">
        <input type="hidden" name="sach_id" value="<?= $s['id'] ?>">
        <input type="hidden" name="quay_lai" value="<?= e($quay_lai) ?>">
<?php if ($s['so_luong'] > 0): ?>
        <button type="submit" class="btn btn-solid btn-sm">Thêm vào giỏ</button>
<?php else: ?>
        <button type="button" class="btn btn-solid btn-sm" disabled>Hết hàng</button>
<?php endif; ?>
      </form>
    </article>
<?php endforeach; ?>
<?php if (!$ds_sach): ?>
    <p class="grid-empty grid-empty-on">Thể loại này hiện chưa có sách đang bán.</p>
<?php endif; ?>
  </div>
<?php endif; ?>
</main>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
