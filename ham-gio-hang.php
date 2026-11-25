<?php

const MA_GIAM_GIA = ['SACHVIET15' => 15];

function dem_sach_trong_gio(PDO $db, int $nguoi_dung_id): int
{
    $cau_lenh = $db->prepare('SELECT COUNT(*) FROM gio_hang WHERE nguoi_dung_id = ?');
    $cau_lenh->execute([$nguoi_dung_id]);
    return (int) $cau_lenh->fetchColumn();
}

function lay_gio_hang(PDO $db, int $nguoi_dung_id): array
{
    $cau_lenh = $db->prepare('
        SELECT gh.sach_id, gh.so_luong,
               s.ten, s.tac_gia, s.gia, s.so_luong AS ton_kho,
               tl.ten AS ten_the_loai, tl.mau
        FROM gio_hang gh
        JOIN sach s      ON s.id  = gh.sach_id
        JOIN the_loai tl ON tl.id = s.the_loai_id
        WHERE gh.nguoi_dung_id = ?
        ORDER BY gh.ngay_them, gh.sach_id
    ');
    $cau_lenh->execute([$nguoi_dung_id]);
    return $cau_lenh->fetchAll();
}

function them_vao_gio(PDO $db, int $nguoi_dung_id, int $sach_id, int $so_luong): array
{
    $cau_lenh = $db->prepare('SELECT ten, so_luong, hien_thi FROM sach WHERE id = ?');
    $cau_lenh->execute([$sach_id]);
    $sach = $cau_lenh->fetch();

    if (!$sach || !$sach['hien_thi']) {
        return ['loi', 'Quyển sách này hiện không còn bán.'];
    }
    if ($sach['so_luong'] < 1) {
        return ['loi', '“' . $sach['ten'] . '” đã hết hàng.'];
    }

    $cau_lenh = $db->prepare('SELECT so_luong FROM gio_hang WHERE nguoi_dung_id = ? AND sach_id = ?');
    $cau_lenh->execute([$nguoi_dung_id, $sach_id]);
    $dang_co = (int) $cau_lenh->fetchColumn();

    $so_moi = min($dang_co + max(1, $so_luong), (int) $sach['so_luong']);

    if ($dang_co > 0) {
        $cau_lenh = $db->prepare('UPDATE gio_hang SET so_luong = ? WHERE nguoi_dung_id = ? AND sach_id = ?');
        $cau_lenh->execute([$so_moi, $nguoi_dung_id, $sach_id]);
    } else {
        $cau_lenh = $db->prepare('INSERT INTO gio_hang (nguoi_dung_id, sach_id, so_luong) VALUES (?, ?, ?)');
        $cau_lenh->execute([$nguoi_dung_id, $sach_id, $so_moi]);
    }

    $thong_bao = 'Đã thêm “' . $sach['ten'] . '” vào giỏ hàng (trong giỏ: ' . $so_moi . ' cuốn).';
    if ($dang_co + $so_luong > $so_moi) {
        $thong_bao .= ' Kho chỉ còn ' . $sach['so_luong'] . ' cuốn.';
    }
    return ['ok', $thong_bao];
}

function cap_nhat_so_luong(PDO $db, int $nguoi_dung_id, int $sach_id, int $so_luong): array
{
    if ($so_luong <= 0) {
        return xoa_khoi_gio($db, $nguoi_dung_id, $sach_id);
    }

    $cau_lenh = $db->prepare('
        SELECT s.ten, s.so_luong
        FROM gio_hang gh
        JOIN sach s ON s.id = gh.sach_id
        WHERE gh.nguoi_dung_id = ? AND gh.sach_id = ?
    ');
    $cau_lenh->execute([$nguoi_dung_id, $sach_id]);
    $sach = $cau_lenh->fetch();
    if (!$sach) {
        return ['loi', 'Quyển sách này không có trong giỏ hàng.'];
    }
    if ($sach['so_luong'] < 1) {
        return ['loi', '“' . $sach['ten'] . '” đã hết hàng, vui lòng xoá khỏi giỏ hàng.'];
    }
    $so_moi = min($so_luong, (int) $sach['so_luong']);

    $cau_lenh = $db->prepare('UPDATE gio_hang SET so_luong = ? WHERE nguoi_dung_id = ? AND sach_id = ?');
    $cau_lenh->execute([$so_moi, $nguoi_dung_id, $sach_id]);

    return $so_moi < $so_luong
        ? ['loi', 'Kho chỉ còn ' . $sach['so_luong'] . ' cuốn “' . $sach['ten'] . '”.']
        : ['ok', 'Đã cập nhật số lượng “' . $sach['ten'] . '”: ' . $so_moi . ' cuốn.'];
}

function xoa_khoi_gio(PDO $db, int $nguoi_dung_id, int $sach_id): array
{
    $cau_lenh = $db->prepare('DELETE FROM gio_hang WHERE nguoi_dung_id = ? AND sach_id = ?');
    $cau_lenh->execute([$nguoi_dung_id, $sach_id]);
    return ['ok', 'Đã xoá sách khỏi giỏ hàng.'];
}

function da_co_don_hang(PDO $db, int $nguoi_dung_id): bool
{
    $cau_lenh = $db->prepare("SELECT COUNT(*) FROM don_hang WHERE nguoi_dung_id = ? AND trang_thai <> 'da_huy'");
    $cau_lenh->execute([$nguoi_dung_id]);
    return $cau_lenh->fetchColumn() > 0;
}

function ap_dung_ma_giam(PDO $db, int $nguoi_dung_id, string $ma): array
{
    $ma = strtoupper(trim($ma));
    if ($ma === '') {
        return ['loi', 'Vui lòng nhập mã giảm giá.'];
    }
    if (!isset(MA_GIAM_GIA[$ma])) {
        return ['loi', 'Mã giảm giá “' . $ma . '” không đúng.'];
    }
    if (da_co_don_hang($db, $nguoi_dung_id)) {
        return ['loi', 'Mã ' . $ma . ' chỉ dùng cho đơn hàng đầu tiên.'];
    }

    $_SESSION['ma_giam'] = $ma;
    return ['ok', 'Đã áp dụng mã ' . $ma . ': giảm ' . MA_GIAM_GIA[$ma] . '% tiền sách.'];
}

function phan_tram_giam(): int
{
    return MA_GIAM_GIA[$_SESSION['ma_giam'] ?? ''] ?? 0;
}

function tinh_tien_gio(array $ds_trong_gio, int $phan_tram_giam = 0): array
{
    $tam_tinh = 0;
    $so_cuon = 0;
    foreach ($ds_trong_gio as $sp) {
        $tam_tinh += $sp['so_luong'] * $sp['gia'];
        $so_cuon  += $sp['so_luong'];
    }
    $giam_gia = (int) round($tam_tinh * $phan_tram_giam / 100);
    $phi_ship = phi_ship($tam_tinh - $giam_gia);
    return [
        'so_cuon'   => $so_cuon,
        'tam_tinh'  => $tam_tinh,
        'giam_gia'  => $giam_gia,
        'phi_ship'  => $phi_ship,
        'tong_tien' => $tam_tinh - $giam_gia + $phi_ship,
    ];
}
