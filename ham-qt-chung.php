<?php

const QT_MOI_TRANG = 10;
const SAP_HET_HANG = 10;

const SQL_TIEN_SACH = '(SELECT don_hang_id, SUM(so_luong * don_gia) AS tien_sach, SUM(so_luong) AS so_cuon
                        FROM chi_tiet_don_hang
                        GROUP BY don_hang_id)';

function bat_buoc_quan_tri(bool $chi_admin = false): array
{
    $nd = nguoi_dung_hien_tai();
    if ($nd === null) {
        if (empty($_SESSION['thong_bao'])) {
            dat_thong_bao('loi', 'Vui lòng đăng nhập bằng tài khoản quản trị.');
        }
        chuyen_trang('dang-nhap.php?quay_lai=' . urlencode(trang_hien_tai()));
    }
    if (!la_quan_tri($nd)) {
        dat_thong_bao('loi', 'Tài khoản ' . $nd['email'] . ' không có quyền vào trang quản trị.');
        chuyen_trang('dang-nhap.php');
    }
    if ($chi_admin && $nd['vai_tro'] !== 'admin') {
        dat_thong_bao('loi', 'Chỉ quản trị viên mới được quản lý người dùng.');
        chuyen_trang('index.php');
    }
    return $nd;
}

function mau_like(string $tu_khoa): string
{
    return '%' . addcslashes($tu_khoa, '%_\\') . '%';
}

function tu_khoa_tim(): string
{
    return mb_substr(preg_replace('/\s+/u', ' ', get_chuoi('q')), 0, 100);
}

function chi_so(string $chuoi): string
{
    return preg_replace('/\D/', '', $chuoi);
}

function phan_trang(int $tong, int $moi_trang = QT_MOI_TRANG): array
{
    $so_trang = max(1, (int) ceil($tong / $moi_trang));
    $trang = ctype_digit(get_chuoi('trang')) ? (int) min(get_chuoi('trang'), $so_trang) : 1;
    $trang = max(1, $trang);
    return [
        'trang'     => $trang,
        'so_trang'  => $so_trang,
        'tong'      => $tong,
        'moi_trang' => $moi_trang,
        'bo_qua'    => ($trang - 1) * $moi_trang,
    ];
}

function link_trang(string $file, array $tham_so): string
{
    if (($tham_so['trang'] ?? 1) === 1) {
        unset($tham_so['trang']);
    }
    $tham_so = array_filter($tham_so, fn($gia_tri) => $gia_tri !== '' && $gia_tri !== 0);
    return $file . ($tham_so ? '?' . http_build_query($tham_so) : '');
}

function hien_phan_trang(string $file, array $tham_so, array $pt): string
{
    if ($pt['so_trang'] <= 1) {
        return '';
    }
    $html = '<nav class="qt-pt" aria-label="Phân trang">';
    if ($pt['trang'] > 1) {
        $html .= '<a href="' . e(link_trang($file, $tham_so + ['trang' => $pt['trang'] - 1])) . '">‹ Trước</a>';
    }
    for ($i = 1; $i <= $pt['so_trang']; $i++) {
        $html .= $i === $pt['trang']
            ? '<span aria-current="page">' . $i . '</span>'
            : '<a href="' . e(link_trang($file, $tham_so + ['trang' => $i])) . '">' . $i . '</a>';
    }
    if ($pt['trang'] < $pt['so_trang']) {
        $html .= '<a href="' . e(link_trang($file, $tham_so + ['trang' => $pt['trang'] + 1])) . '">Sau ›</a>';
    }
    return $html . '</nav>';
}

function hop_xoa(string $action, array $an, string $cau_hoi, string $link_huy, string $nhan_nut = 'Xoá'): string
{
    $html = '<details class="qt-xoa"><summary class="qt-nut qt-nut-do">' . e($nhan_nut) . '</summary>'
          . '<form class="qt-xoa-hop" action="' . e($action) . '" method="post">'
          . '<strong>Xác nhận xoá</strong><p>' . e($cau_hoi) . '</p>';
    foreach ($an as $ten => $gia_tri) {
        $html .= '<input type="hidden" name="' . e($ten) . '" value="' . e($gia_tri) . '">';
    }
    return $html . '<span class="qt-nut-hang"><button type="submit" class="btn btn-danger">Xác nhận xoá</button>'
         . '<a class="btn btn-ghost" href="' . e($link_huy) . '">Huỷ</a></span></form></details>';
}

function nhan_mau(array $bang, $gia_tri): string
{
    return '<span class="tt ' . nhan($bang, $gia_tri, 1) . '">' . nhan($bang, $gia_tri) . '</span>';
}

function so_nguyen(string $chuoi, int $toi_da): ?int
{
    $chuoi = preg_replace('/[\s.,]/', '', $chuoi);
    if (!ctype_digit($chuoi) || strlen($chuoi) > 10 || (int) $chuoi > $toi_da) {
        return null;
    }
    return (int) $chuoi;
}

function van_ban_nhieu_dong(string $ten): string
{
    return trim(str_replace("\r\n", "\n", post_chuoi($ten)));
}
