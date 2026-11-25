<?php
require_once __DIR__ . '/inc/khoi-tao.php';

$id = (int) ($_GET['id'] ?? 0);
$nd = nguoi_dung_hien_tai();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nd = bat_buoc_dang_nhap('Vui lòng đăng nhập để viết đánh giá.', 'sach.php?id=' . $id . '#danh-gia');
    $so_sao   = post_so('so_sao');
    $noi_dung = mb_substr(post_chuoi('noi_dung'), 0, 1000);

    if (!lay_chi_tiet_sach($db, $id)) {
        dat_thong_bao('loi', 'Không tìm thấy quyển sách này.');
    } elseif ($so_sao < 1 || $so_sao > 5) {
        dat_thong_bao('loi', 'Vui lòng chọn số sao (từ 1 đến 5) trước khi gửi đánh giá.');
    } else {
        $ket_qua = luu_danh_gia($db, $id, $nd['id'], $so_sao, $noi_dung);
        dat_thong_bao('ok', $ket_qua === 'sua' ? 'Đã cập nhật đánh giá của bạn.' : 'Cảm ơn bạn đã đánh giá quyển sách này!');
    }
    chuyen_trang('sach.php?id=' . $id . '#danh-gia');
}

$sach = lay_chi_tiet_sach($db, $id);
if ($sach) {
    $ds_danh_gia  = lay_danh_gia_cua_sach($db, $id);
    $diem         = tinh_diem_danh_gia($ds_danh_gia);
    $da_ban       = dem_so_da_ban($db, $id);
    $ds_lien_quan = lay_sach_lien_quan($db, $id, (int) $sach['the_loai_id']);
    $dg_cua_toi   = $nd ? lay_danh_gia_cua_toi($db, $id, $nd['id']) : null;
    $mau_sach     = mau($sach['mau']);
    $trich_doan   = tach_doan($sach['trich_doan']);
    $con_ban      = $sach['hien_thi'] && $sach['so_luong'] > 0;
} else {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $sach ? e($sach['ten']) . ' — ' . e($sach['tac_gia']) : 'Không tìm thấy sách' ?> | SáchViệt</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<?php if (!$sach): ?>
<main class="page">
  <nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="index.php">Trang chủ</a> <span>›</span> <strong>Không tìm thấy sách</strong>
  </nav>
  <section class="panel">
    <h2>Không tìm thấy quyển sách này</h2>
    <p class="sp-mota">Quyển sách bạn tìm không tồn tại hoặc đã ngừng kinh doanh.</p>
    <a href="index.php#danh-sach" class="btn btn-solid">← Về danh sách sách</a>
  </section>
</main>
<?php else: ?>
<main class="page">
  <nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="index.php">Trang chủ</a> <span>›</span>
    <a href="the-loai.php?id=<?= $sach['the_loai_id'] ?>"><?= e($sach['ten_the_loai']) ?></a> <span>›</span>
    <strong><?= e($sach['ten']) ?></strong>
  </nav>

<?php if (!$sach['hien_thi']): ?>
  <p class="sp-ngung">Quyển sách này hiện tạm ngừng kinh doanh.</p>
<?php endif; ?>

  <div class="sp-top" style="--mau:<?= $mau_sach ?>">
    <div class="sp-anh">
      <div class="sp-bia"><?= bia($sach['ten'], $sach['tac_gia'], $mau_sach) ?></div>
      <div class="sp-thumbs" aria-hidden="true">
        <span class="thumb active"><?= bia_nho($sach['ten'], $mau_sach) ?></span>
        <span class="thumb">Bìa</span>
        <span class="thumb">Ruột</span>
        <span class="thumb">Gáy</span>
      </div>
    </div>

    <div class="sp-info">
      <a class="sp-tl" href="the-loai.php?id=<?= $sach['the_loai_id'] ?>"><?= e($sach['ten_the_loai']) ?></a>
      <h1 class="sp-ten"><?= e($sach['ten']) ?></h1>
      <p class="sp-tg">Tác giả: <strong><?= e($sach['tac_gia']) ?></strong><?= $sach['nam'] ? ' · ' . e($sach['nam']) : '' ?></p>

      <div class="sp-rate">
        <?= sao($diem['sao']) ?>

        <span class="sp-rate-so"><?= $diem['luot'] ? number_format($diem['diem'], 1) : '—' ?></span>
        <a class="sp-rate-luot" href="#danh-gia">(<?= $diem['luot'] ?> lượt đánh giá)</a>
        <span class="sp-daban">Đã bán <?= so($da_ban) ?></span>
      </div>

      <div class="sp-gia-box">
        <span class="sp-gia"><?= tien($sach['gia']) ?></span>
<?php if ($sach['giam_gia'] > 0): ?>
        <span class="sp-gia-cu"><?= tien($sach['gia_cu']) ?></span>
        <span class="sp-sale">-<?= $sach['giam_gia'] ?>%</span>
<?php endif; ?>
      </div>

      <table class="sp-bang">
<?php if ($sach['so_luong'] > 0): ?>
        <tr><th>Tình trạng</th><td class="con-hang">● Còn hàng (<?= so($sach['so_luong']) ?> cuốn)</td></tr>
<?php else: ?>
        <tr><th>Tình trạng</th><td class="het-hang">● Hết hàng</td></tr>
<?php endif; ?>
        <tr><th>Kho hàng</th><td><?= e($sach['ten_kho']) ?></td></tr>
        <tr><th>Năm sáng tác</th><td><?= $sach['nam'] ? e($sach['nam']) : 'Không rõ' ?></td></tr>
        <tr><th>Bản quyền</th><td>Đã hết hạn — đọc thử tự do</td></tr>
      </table>

      <form class="sp-mua" action="gio-hang.php" method="post">
        <input type="hidden" name="hanh_dong" value="them">
        <input type="hidden" name="sach_id" value="<?= $sach['id'] ?>">
        <input type="hidden" name="quay_lai" value="sach.php?id=<?= $sach['id'] ?>">
<?php if ($con_ban): ?>
        <label class="sp-sl" for="so-luong">Số lượng</label>
        <input class="sp-sl-o" type="number" id="so-luong" name="so_luong" value="1" min="1" max="<?= $sach['so_luong'] ?>">
<?php endif; ?>
        <div class="sp-btns">
<?php if ($con_ban): ?>
          <button type="submit" name="sau_khi" value="gio-hang" class="btn btn-solid btn-lg">⚡ Mua ngay</button>
          <button type="submit" name="sau_khi" value="o-lai" class="btn btn-ghost btn-lg">🛒 Thêm vào giỏ hàng</button>
<?php else: ?>
          <button type="button" class="btn btn-solid btn-lg" disabled>Tạm hết hàng</button>
<?php endif; ?>
          <a href="#doc-thu" class="btn btn-outline btn-lg">📖 Đọc thử</a>
        </div>
      </form>

      <div class="sp-cam-ket">
        <span>🚚 Giao 2–4 ngày</span>
        <span>↩️ Đổi trả 7 ngày</span>
        <span>🔒 Thanh toán an toàn</span>
      </div>
    </div>
  </div>

  <section class="panel">
    <h2>Mô tả sản phẩm</h2>
<?php foreach (tach_doan($sach['mo_ta']) as $doan): ?>
    <p class="sp-mota"><?= nl2br(e($doan)) ?></p>
<?php endforeach; ?>
    <p class="sp-mota">Bản in của SáchViệt được trình bày trang nhã, chú thích đầy đủ,
       phù hợp cho cả bạn đọc phổ thông lẫn người nghiên cứu. Sách được đóng gói
       cẩn thận bằng bìa carton và màng chống ẩm trước khi giao.</p>
  </section>

  <section class="panel" id="danh-gia">
    <h2>Đánh giá từ khách hàng</h2>
    <div class="dg-tong">
      <div class="dg-diem">
        <strong><?= $diem['luot'] ? number_format($diem['diem'], 1) : '—' ?></strong>
        <?= sao($diem['sao']) ?>

        <span><?= $diem['luot'] ?> đánh giá</span>
      </div>
      <div class="dg-bars">
<?php foreach ($diem['phan_bo'] as $muc => $so_dg):
        $phan_tram = $diem['luot'] ? (int) round($so_dg * 100 / $diem['luot']) : 0; ?>
        <div class="dg-bar"><span><?= $muc ?>★</span><span class="bar"><span style="width:<?= $phan_tram ?>%"></span></span><span><?= $phan_tram ?>%</span></div>
<?php endforeach; ?>
      </div>
    </div>

    <ul class="dg-list">
<?php if (!$ds_danh_gia): ?>
      <li class="dg-item"><span class="dg-ava">?</span>
        <div><p>Chưa có đánh giá nào cho quyển sách này. Hãy là người đầu tiên!</p></div></li>
<?php endif; ?>
<?php foreach ($ds_danh_gia as $dg): ?>
      <li class="dg-item<?= $nd && $dg['nguoi_dung_id'] == $nd['id'] ? ' dg-cua-toi' : '' ?>">
        <span class="dg-ava"><?= e(viet_tat($dg['ho_ten'])) ?></span>
        <div>
          <strong><?= e($dg['ho_ten']) ?></strong> <?= sao((int) $dg['so_sao']) ?>

          <?php if ($dg['noi_dung'] !== null && $dg['noi_dung'] !== ''): ?><p><?= nl2br(e($dg['noi_dung'])) ?></p><?php endif; ?>

          <span class="dg-ngay"><?= ngay($dg['ngay_danh_gia']) ?></span>
        </div>
      </li>
<?php endforeach; ?>
    </ul>

<?php if ($nd): ?>
    <form class="dg-form" action="sach.php?id=<?= $sach['id'] ?>" method="post">
      <h3><?= $dg_cua_toi ? 'Sửa đánh giá của bạn' : 'Viết đánh giá của bạn' ?></h3>
      <div class="dg-stars-pick" role="radiogroup" aria-label="Chọn số sao">
<?php for ($k = 5; $k >= 1; $k--): ?>
        <label title="<?= $k ?> sao"><input type="radio" name="so_sao" value="<?= $k ?>"<?= $k === 5 ? ' required' : '' ?><?= $dg_cua_toi && (int) $dg_cua_toi['so_sao'] === $k ? ' checked' : '' ?>><span>★</span></label>
<?php endfor; ?>
      </div>
      <textarea name="noi_dung" rows="4" maxlength="1000" placeholder="Chia sẻ cảm nhận của bạn về quyển sách này..."><?= e($dg_cua_toi['noi_dung'] ?? '') ?></textarea>
      <button type="submit" class="btn btn-solid"><?= $dg_cua_toi ? 'Cập nhật đánh giá' : 'Gửi đánh giá' ?></button>
    </form>
<?php else: ?>
    <div class="dg-form dg-moi">
      <p>Bạn cần <a href="login.php?quay_lai=<?= urlencode('sach.php?id=' . $sach['id'] . '#danh-gia') ?>">đăng nhập</a>
         để viết đánh giá cho quyển sách này.</p>
    </div>
<?php endif; ?>
  </section>

  <section class="panel">
    <h2>Có thể bạn cũng thích</h2>
    <div class="lq-grid">
<?php foreach ($ds_lien_quan as $lq): ?>
        <a class="lq-card" href="sach.php?id=<?= $lq['id'] ?>" style="--mau:<?= mau($lq['mau']) ?>">
          <span class="lq-bia"><?= bia($lq['ten'], $lq['tac_gia'], $lq['mau']) ?></span>
          <span class="lq-ten"><?= e($lq['ten']) ?></span>
          <span class="lq-gia"><?= tien($lq['gia']) ?></span>
        </a>
<?php endforeach; ?>
    </div>
  </section>
</main>

<div class="overlay" id="doc-thu">
  <div class="modal modal-doc">
    <div class="modal-head">
      <div>
        <h2>Đọc thử — <?= e($sach['ten']) ?></h2>
        <p><?= e($sach['tac_gia']) ?><?= $sach['nam'] ? ' · ' . e($sach['nam']) : '' ?></p>
      </div>
      <a href="#" class="modal-x" aria-label="Đóng">✕</a>
    </div>
    <div class="modal-body">
<?php if ($trich_doan): ?>
        <div class="doc-noidung"><?php foreach ($trich_doan as $doan): ?><p><?= e($doan) ?></p><?php endforeach; ?></div>
<?php if ($sach['link_wikisource']): ?>
        <p class="doc-note">Đây là phần trích mở đầu. Đọc trọn vẹn miễn phí tại
          <a href="<?= e($sach['link_wikisource']) ?>" target="_blank" rel="noopener">Wikisource tiếng Việt</a>.</p>
<?php endif; ?>
<?php else: ?>
        <div class="doc-trong">
<?php if ($sach['link_wikisource']): ?>
          <p><strong>Bản đọc thử của quyển này có trên Wikisource tiếng Việt.</strong></p>
          <p>Tác phẩm đã hết hạn bản quyền nên bạn có thể đọc toàn văn miễn phí trước khi mua bản in.</p>
          <a class="doc-link" href="<?= e($sach['link_wikisource']) ?>" target="_blank" rel="noopener">
            🔗 Mở toàn văn trên Wikisource tiếng Việt</a>
<?php else: ?>
          <p><strong>Quyển này chưa có bản đọc thử.</strong></p>
<?php endif; ?>
        </div>
<?php endif; ?>
    </div>
    <div class="modal-foot">
      <span class="pd-tag">Tác phẩm đã hết hạn bản quyền</span>
<?php if ($con_ban): ?>
      <a href="#so-luong" class="btn btn-solid">Mua bản in — <?= tien($sach['gia']) ?></a>
<?php else: ?>
      <a href="#" class="btn btn-solid">Đóng</a>
<?php endif; ?>
    </div>
  </div>
  <a href="#" class="overlay-close" aria-label="Đóng"></a>
</div>
<?php endif; ?>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>
</body>
</html>
