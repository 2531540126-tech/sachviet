<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$menu = 'danh-muc';
$ds_danh_muc = lay_danh_muc($db, 3);
$tong_sach = array_sum(array_column($ds_danh_muc, 'so_sach'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Danh mục sách | SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<main class="page">
  <nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="index.php">Trang chủ</a> <span>›</span> <strong>Danh mục</strong>
  </nav>

  <h1 class="page-title">Danh mục sách</h1>
  <p class="page-sub"><?= count($ds_danh_muc) ?> thể loại · <?= $tong_sach ?> đầu sách đang bán.
     Chọn một thể loại để xem toàn bộ sách thuộc thể loại đó.</p>

  <div class="dmuc-grid">
<?php foreach ($ds_danh_muc as $tl): ?>
    <a class="dmuc-card" href="the-loai.php?id=<?= $tl['id'] ?>" style="--mau:<?= mau($tl['mau']) ?>">
      <span class="dmuc-top">
        <span class="dmuc-icon"><?= e($tl['bieu_tuong']) ?></span>
        <span class="dmuc-so"><?= $tl['so_sach'] ?> đầu sách</span>
      </span>
      <h2 class="dmuc-ten"><?= e($tl['ten']) ?></h2>
<?php if ($tl['so_sach'] > 0): ?>
      <span class="dmuc-gia">Giá từ <strong><?= tien($tl['gia_thap']) ?></strong> đến <strong><?= tien($tl['gia_cao']) ?></strong></span>
      <span class="dmuc-bia">
<?php foreach ($tl['sach_mau'] as $s): ?>
        <span class="dmuc-bia-item" title="<?= e($s['ten']) ?>"><?= bia($s['ten'], $s['tac_gia'], $tl['mau']) ?></span>
<?php endforeach; ?>
      </span>
<?php else: ?>
      <span class="dmuc-gia">Thể loại này chưa có sách.</span>
<?php endif; ?>
      <span class="dmuc-xem">Xem sách thể loại này →</span>
    </a>
<?php endforeach; ?>
  </div>
</main>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
