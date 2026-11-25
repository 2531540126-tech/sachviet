<?php

const PHI_VAN_CHUYEN = 30000;
const MIEN_PHI_TU    = 300000;

function e($chuoi): string
{
    return htmlspecialchars((string) $chuoi, ENT_QUOTES, 'UTF-8');
}

function tien($so): string
{
    return number_format((int) $so, 0, ',', '.') . '₫';
}

function so($so): string
{
    return number_format((int) $so, 0, ',', '.');
}

function ngay($thoi_gian): string
{
    return date('d/m/Y', strtotime($thoi_gian));
}

function ma_don($id): string
{
    return '#DH' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
}

function dinh_dang_sdt(string $sdt): string
{
    return substr($sdt, 0, 4) . ' ' . substr($sdt, 4, 3) . ' ' . substr($sdt, 7);
}

function mau($ma_mau): string
{
    return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $ma_mau) ? $ma_mau : '#7A6A4F';
}

function viet_tat($ho_ten): string
{
    $tu = preg_split('/\s+/u', trim((string) $ho_ten));
    $dau = mb_substr($tu[0], 0, 1);
    $cuoi = count($tu) > 1 ? mb_substr(end($tu), 0, 1) : '';
    return mb_strtoupper($dau . $cuoi);
}

function sao(int $n): string
{
    $n = max(0, min(5, $n));
    $nhan = $n ? "Đánh giá $n trên 5 sao" : 'Chưa có đánh giá';
    return '<span class="stars" role="img" aria-label="' . $nhan . '">' . str_repeat('★', $n)
         . '<span class="star-off">' . str_repeat('☆', 5 - $n) . '</span></span>';
}

function sao_tron($diem): int
{
    return $diem === null ? 0 : (int) round((float) $diem);
}

function bia($ten, $tac_gia, $mau): string
{
    return '<span class="bia" style="--mau:' . mau($mau) . '"><span class="bia-vien"></span>'
         . '<span class="bia-ten">' . e($ten) . '</span><span class="bia-tg">' . e($tac_gia) . '</span>'
         . '<span class="bia-nxb">TỦ SÁCH KINH ĐIỂN</span></span>';
}

function bia_nho($ten, $mau): string
{
    return '<span class="bia bia--tiny" style="--mau:' . mau($mau) . '">' . e(mb_substr((string) $ten, 0, 1)) . '</span>';
}

function muc_gia(int $gia): string
{
    if ($gia < 70000) return 'thap';
    if ($gia <= 120000) return 'trung';
    return 'cao';
}

function phi_ship(int $tam_tinh): int
{
    return ($tam_tinh == 0 || $tam_tinh >= MIEN_PHI_TU) ? 0 : PHI_VAN_CHUYEN;
}

function tach_doan($van_ban): array
{
    $van_ban = str_replace("\r\n", "\n", trim((string) $van_ban));
    if ($van_ban === '') return [];
    return preg_split('/\n\s*\n/u', $van_ban);
}

const TRANG_THAI_DON = [
    'cho_xac_nhan' => ['Chờ xác nhận', 'tt-pend'],
    'dang_giao'    => ['Đang giao',    'tt-ship'],
    'da_giao'      => ['Đã giao',      'tt-paid'],
    'da_huy'       => ['Đã huỷ',       'tt-cancel'],
];

const PHUONG_THUC_TT = [
    'cod'     => ['COD', 'tiền mặt khi nhận hàng'],
    'banking' => ['Banking', 'chuyển khoản ngân hàng'],
    'ewallet' => ['E-wallet', 'Momo, ZaloPay, VNPay'],
];

const TRANG_THAI_TK = [
    'hoat_dong' => ['Hoạt động', 'tt-ok'],
    'tam_khoa'  => ['Tạm khoá',  'tt-low'],
    'cho_duyet' => ['Chờ duyệt', 'tt-warn'],
];

const VAI_TRO = [
    'admin' => ['Quản trị viên', 'vt-admin'],
    'staff' => ['Nhân viên',     'vt-staff'],
    'user'  => ['Khách hàng',    'vt-user'],
];

function nhan(array $bang, $gia_tri, int $vi_tri = 0): string
{
    return isset($bang[$gia_tri]) ? $bang[$gia_tri][$vi_tri] : e($gia_tri);
}

function get_chuoi(string $ten): string
{
    $gia_tri = $_GET[$ten] ?? '';
    return is_string($gia_tri) ? trim(mb_scrub($gia_tri, 'UTF-8')) : '';
}

function post_chuoi(string $ten): string
{
    $gia_tri = $_POST[$ten] ?? '';
    return is_string($gia_tri) ? trim(mb_scrub($gia_tri, 'UTF-8')) : '';
}

function post_so(string $ten): int
{
    return (int) post_chuoi($ten);
}

function post_mat_khau(string $ten): string
{
    $gia_tri = $_POST[$ten] ?? '';
    return is_string($gia_tri) ? str_replace("\0", '', $gia_tri) : '';
}

function chuyen_trang(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function dat_thong_bao(string $loai, string $noi_dung): void
{
    $_SESSION['thong_bao'] = ['loai' => $loai, 'noi_dung' => $noi_dung];
}

function hien_thong_bao(): string
{
    if (empty($_SESSION['thong_bao'])) {
        return '';
    }
    $tb = $_SESSION['thong_bao'];
    unset($_SESSION['thong_bao']);
    $loai = $tb['loai'] === 'ok' ? 'ok' : 'loi';
    return '<div class="thong-bao thong-bao-' . $loai . '" role="status">'
         . '<span class="tb-icon">' . ($loai === 'ok' ? '✓' : '!') . '</span>'
         . '<span>' . e($tb['noi_dung']) . '</span></div>';
}

function duong_dan_an_toan(string $url, string $mac_dinh = 'index.php'): string
{
    return preg_match('/^[a-z0-9-]+\.php(\?[\w=&%.+-]*)?(#[\w-]+)?$/i', $url) ? $url : $mac_dinh;
}

function loi_o(array $loi, string $ten_o): string
{
    return isset($loi[$ten_o]) ? '<p class="field-err">' . e($loi[$ten_o]) . '</p>' : '';
}

function trang_hien_tai(): string
{
    $tham_so = $_SERVER['QUERY_STRING'] ?? '';
    return basename($_SERVER['SCRIPT_NAME']) . ($tham_so !== '' ? '?' . $tham_so : '');
}
