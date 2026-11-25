<?php
require_once __DIR__ . '/inc/khoi-tao.php';
require_once __DIR__ . '/inc/ham-bo-loc.php';

$menu = 'trang-chu';

$ds_the_loai = lay_the_loai_kem_so_sach($db);
$ds_kho      = lay_kho($db);
$thong_ke    = thong_ke_cua_hang($db);
$tu_khoa     = mb_substr(get_chuoi('q'), 0, 100);
$ds_sach     = gan_nhan_loc(lay_danh_sach_sach($db, $tu_khoa));

$nhom_loc = tao_nhom_loc($ds_sach, $ds_the_loai, $ds_kho);
$css_loc  = tao_css_bo_loc($nhom_loc, $ds_sach);

$quay_lai = 'index.php' . ($tu_khoa !== '' ? '?q=' . urlencode($tu_khoa) : '') . '#danh-sach';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SáchViệt | Mua Bán Sách Online</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
<?= implode(",\n", $css_loc['to_dam']) ?> {
  background: var(--nau); border-color: var(--nau);
}
<?= implode("::after,\n", $css_loc['to_dam']) ?>::after {
  transform: scale(1);
}
<?php if ($css_loc['an']): ?>
<?= implode(",\n", $css_loc['an']) ?> {
  display: none;
}
<?php endif; ?>
<?php if ($css_loc['rong']): ?>
<?= implode(",\n", $css_loc['rong']) ?> {
  display: block;
}
<?php endif; ?>
</style>
</head>
<body>

<?php include __DIR__ . '/inc/dau-trang.php'; ?>

<section class="hero">
  <div class="hero-inner">
    <div class="hero-text">
      <span class="hero-chip">📚 <?= $thong_ke['so_sach'] ?> tác phẩm kinh điển · Đọc thử miễn phí</span>
      <h1>Tủ sách <span>Văn học Việt Nam</span> kinh điển</h1>
      <p>Từ Truyện Kiều đến Chí Phèo — tất cả đều có bản đọc thử hợp pháp
         trên Wikisource tiếng Việt. Đọc trước, ưng rồi hãy mua.</p>
      <div class="hero-btns">
        <a href="#danh-sach" class="btn btn-solid btn-lg">Xem danh sách sách</a>
        <a href="#gioi-thieu" class="btn btn-ghost btn-lg">Về nhà sách</a>
      </div>
      <div class="hero-stats">
        <div><strong><?= $thong_ke['so_sach'] ?></strong><span>Đầu sách</span></div>
        <div><strong><?= $thong_ke['phan_tram_doc_thu'] ?>%</strong><span>Có đọc thử</span></div>
        <div><strong><?= count($ds_the_loai) ?></strong><span>Thể loại</span></div>
      </div>
    </div>
    <div class="hero-art" aria-hidden="true">
      <span class="hero-book hb1"><span class="bia" style="--mau:#A8551E"><span class="bia-vien"></span><span class="bia-ten">Truyện Kiều</span><span class="bia-tg">Nguyễn Du</span><span class="bia-nxb">TỦ SÁCH KINH ĐIỂN</span></span></span>
      <span class="hero-book hb2"><span class="bia" style="--mau:#3F7D58"><span class="bia-vien"></span><span class="bia-ten">Chí Phèo</span><span class="bia-tg">Nam Cao</span><span class="bia-nxb">TỦ SÁCH KINH ĐIỂN</span></span></span>
      <span class="hero-book hb3"><span class="bia" style="--mau:#B3453A"><span class="bia-vien"></span><span class="bia-ten">Bình Ngô đại cáo</span><span class="bia-tg">Nguyễn Trãi</span><span class="bia-nxb">TỦ SÁCH KINH ĐIỂN</span></span></span>
    </div>
  </div>
</section>

<section class="km-bar" id="khuyen-mai">
  <h2 class="sr-only">Ưu đãi của SáchViệt</h2>
  <div class="km-inner">
    <div class="km-item"><span class="km-ico">🚚</span>
      <div><strong>Miễn phí vận chuyển</strong><span>Cho đơn từ <?= tien(MIEN_PHI_TU) ?></span></div></div>
    <div class="km-item"><span class="km-ico">🎁</span>
      <div><strong>Giảm 15% đơn đầu tiên</strong><span>Nhập mã SACHVIET15</span></div></div>
    <div class="km-item"><span class="km-ico">↩️</span>
      <div><strong>Đổi trả trong 7 ngày</strong><span>Miễn phí nếu lỗi in ấn</span></div></div>
    <div class="km-item"><span class="km-ico">📖</span>
      <div><strong>Đọc thử trước khi mua</strong><span>Toàn bộ <?= $thong_ke['so_sach'] ?> đầu sách</span></div></div>
  </div>
</section>

<section class="section" id="danh-muc">
  <div class="section-head section-head-row">
    <div>
      <h2>Danh mục nổi bật</h2>
      <p>Chọn thể loại bạn quan tâm</p>
    </div>
    <a href="danh-muc.php" class="link-more">Xem tất cả danh mục →</a>
  </div>
  <div class="dm-grid">
<?php foreach ($ds_the_loai as $tl): ?>
      <a class="dm-card" href="the-loai.php?id=<?= $tl['id'] ?>" style="--mau:<?= mau($tl['mau']) ?>">
        <span class="dm-icon"><?= e($tl['bieu_tuong']) ?></span>
        <span class="dm-ten"><?= e($tl['ten']) ?></span>
        <span class="dm-so"><?= $tl['so_sach'] ?> đầu sách</span>
      </a>
<?php endforeach; ?>
  </div>
</section>

<section class="section" id="danh-sach">
  <form id="bo-loc"></form>
<?php foreach ($nhom_loc as $nhom => $g): ?>
  <input type="radio" form="bo-loc" name="<?= $g['name'] ?>" id="loc-<?= $nhom ?>-all" class="f-in" checked>
<?php foreach ($g['lua_chon'] as $gia_tri => $ten): ?>
  <input type="radio" form="bo-loc" name="<?= $g['name'] ?>" id="loc-<?= $nhom ?>-<?= e($gia_tri) ?>" class="f-in">
<?php endforeach; ?>
<?php endforeach; ?>

  <div class="section-head">
<?php if ($tu_khoa === ''): ?>
    <h2>Danh sách sách đang bày bán</h2>
    <p><?= count($ds_sach) ?> tác phẩm — nhấn vào ô lọc bên trái để thu hẹp kết quả</p>
<?php else: ?>
    <h2>Kết quả tìm kiếm</h2>
    <p><?= count($ds_sach) ?> tác phẩm khớp với “<?= e($tu_khoa) ?>” — <a href="index.php#danh-sach" style="color:var(--nau);font-weight:600">xem tất cả sách</a></p>
<?php endif; ?>
  </div>

  <div class="catalog">
    <aside class="filters">
      <div class="filters-head">
        <h3>Bộ lọc</h3>
        <button type="reset" form="bo-loc" class="f-reset">Đặt lại</button>
      </div>
<?php foreach ($nhom_loc as $nhom => $g): ?>

      <div class="f-group">
        <h4><?= $g['tieu_de'] ?></h4>
        <label class="f-row" for="loc-<?= $nhom ?>-all">
          <span class="f-box"></span>
          <span class="f-label"><?= $g['tat_ca'] ?></span>
          <span class="f-count"><?= count($ds_sach) ?></span>
        </label>
<?php foreach ($g['lua_chon'] as $gia_tri => $ten): ?>
        <label class="f-row" for="loc-<?= $nhom ?>-<?= e($gia_tri) ?>">
          <span class="f-box"></span>
          <span class="f-label"><?= e($ten) ?></span>
          <span class="f-count"><?= dem($ds_sach, $nhom, (string) $gia_tri) ?></span>
        </label>
<?php endforeach; ?>
      </div>
<?php endforeach; ?>
    </aside>

    <div class="grid">
<?php foreach ($ds_sach as $s): ?>
      <article class="card"
               data-cat="<?= $s['loc']['cat'] ?>"
               data-gia="<?= $s['loc']['gia'] ?>"
               data-kho="<?= $s['loc']['kho'] ?>"
               data-sao="<?= $s['loc']['sao'] ?>"
               style="--mau:<?= mau($s['mau']) ?>">
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
              <span class="card-gia"><?= tien($s['gia']) ?></span>
              <span class="card-kho">📍 <?= e($s['ten_kho']) ?></span>
            </span>
          </div>
        </a>
        <form class="card-actions" action="gio-hang.php" method="post">
          <a href="sach.php?id=<?= $s['id'] ?>#doc-thu" class="btn btn-ghost btn-sm">Đọc thử</a>
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
      <p class="grid-empty"><?= ($tu_khoa !== '' && !$ds_sach)
          ? 'Không tìm thấy quyển sách nào khớp với “' . e($tu_khoa) . '”.'
          : 'Không có quyển sách nào khớp với bộ lọc bạn chọn.' ?></p>
    </div>
  </div>
</section>

<section class="section gt" id="gioi-thieu">
  <div class="gt-inner">
    <div class="gt-text">
      <h2>Vì sao chọn SáchViệt?</h2>
      <p>SáchViệt là nhà sách trực tuyến tập trung vào kho tàng văn học Việt Nam
         kinh điển — những tác phẩm đã đi vào chương trình phổ thông và trở thành
         một phần ký ức chung của nhiều thế hệ.</p>
      <p>Điểm khác biệt của chúng tôi: <strong>mọi đầu sách đều có bản đọc thử
         miễn phí và hợp pháp</strong>. Tất cả tác phẩm trong danh mục đều đã hết
         hạn bản quyền, phần đọc thử được dẫn từ Wikisource tiếng Việt — kho văn
         bản mở của Wikimedia.</p>
      <ul class="gt-list">
        <li>Sách in bản đẹp, giấy tốt, đóng gói kỹ</li>
        <li>Đọc thử trọn vẹn trước khi quyết định mua</li>
        <li>Giao hàng toàn quốc, đổi trả trong 7 ngày</li>
        <li>Hỗ trợ khách hàng từ 8h đến 21h mỗi ngày</li>
      </ul>
    </div>
    <div class="gt-quote">
      <blockquote>
        <p>Trăm năm trong cõi người ta,<br>Chữ tài chữ mệnh khéo là ghét nhau.</p>
        <cite>— Nguyễn Du, <em>Truyện Kiều</em></cite>
      </blockquote>
    </div>
  </div>
</section>

<?php include __DIR__ . '/inc/chan-trang.php'; ?>

</body>
</html>
