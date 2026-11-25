<?php

function qt_dem_don_cho(PDO $db): int
{
    return (int) $db->query("SELECT COUNT(*) FROM don_hang WHERE trang_thai = 'cho_xac_nhan'")->fetchColumn();
}

function qt_thong_ke(PDO $db): array
{
    $sach = $db->query('
        SELECT COUNT(*) AS tong,
               COALESCE(SUM(hien_thi = 1), 0) AS dang_ban,
               COALESCE(SUM(so_luong = 0), 0) AS het_hang
        FROM sach
    ')->fetch();

    $don = $db->query('
        SELECT trang_thai, COUNT(*) AS so_don
        FROM don_hang
        GROUP BY trang_thai
    ')->fetchAll(PDO::FETCH_KEY_PAIR);

    $doanh_thu = $db->query("
        SELECT COALESCE(SUM(t.tien_sach + dh.phi_van_chuyen), 0) AS doanh_thu, COUNT(*) AS so_don
        FROM don_hang dh
        JOIN " . SQL_TIEN_SACH . " t ON t.don_hang_id = dh.id
        WHERE dh.trang_thai = 'da_giao'
    ")->fetch();

    $khach = $db->query("
        SELECT COUNT(*) AS tong,
               COALESCE(SUM(trang_thai <> 'hoat_dong'), 0) AS chua_hoat_dong
        FROM nguoi_dung
        WHERE vai_tro = 'user'
    ")->fetch();

    return [
        'so_sach'        => (int) $sach['tong'],
        'dang_ban'       => (int) $sach['dang_ban'],
        'het_hang'       => (int) $sach['het_hang'],
        'so_loai'        => (int) $db->query('SELECT COUNT(*) FROM the_loai')->fetchColumn(),
        'don'            => array_map('intval', $don),
        'tong_don'       => (int) array_sum($don),
        'doanh_thu'      => (int) $doanh_thu['doanh_thu'],
        'don_da_giao'    => (int) $doanh_thu['so_don'],
        'so_khach'       => (int) $khach['tong'],
        'khach_chua_hd'  => (int) $khach['chua_hoat_dong'],
    ];
}

function qt_doanh_thu_theo_thang(PDO $db, int $so_thang = 6): array
{
    $ds_thang = $db->query("
        SELECT DATE_FORMAT(dh.ngay_dat, '%Y-%m') AS thang,
               SUM(t.tien_sach + dh.phi_van_chuyen) AS doanh_thu,
               COUNT(*) AS so_don
        FROM don_hang dh
        JOIN " . SQL_TIEN_SACH . " t ON t.don_hang_id = dh.id
        WHERE dh.trang_thai = 'da_giao'
        GROUP BY DATE_FORMAT(dh.ngay_dat, '%Y-%m')
        ORDER BY thang DESC
        LIMIT " . (int) $so_thang
    )->fetchAll();

    $ds_thang = array_reverse($ds_thang);
    $cao_nhat = max(array_merge([1], array_column($ds_thang, 'doanh_thu')));
    foreach ($ds_thang as &$th) {
        [$nam, $thang] = explode('-', $th['thang']);
        $th['nhan']      = 'Tháng ' . (int) $thang . '/' . $nam;
        $th['nhan_ngan'] = 'T' . (int) $thang;
        $th['nam']       = $nam;
        $th['phan_tram'] = max(2, (int) round($th['doanh_thu'] * 100 / $cao_nhat));
    }
    unset($th);
    return $ds_thang;
}

function qt_sach_ban_chay(PDO $db, int $so_luong = 5): array
{
    return $db->query("
        SELECT s.id, s.ten, s.tac_gia, tl.mau,
               SUM(ct.so_luong) AS da_ban,
               SUM(ct.so_luong * ct.don_gia) AS doanh_thu
        FROM chi_tiet_don_hang ct
        JOIN don_hang dh ON dh.id = ct.don_hang_id
        JOIN sach s      ON s.id  = ct.sach_id
        JOIN the_loai tl ON tl.id = s.the_loai_id
        WHERE dh.trang_thai = 'da_giao'
        GROUP BY s.id, s.ten, s.tac_gia, tl.mau
        ORDER BY da_ban DESC, doanh_thu DESC
        LIMIT " . (int) $so_luong
    )->fetchAll();
}

function qt_don_moi_nhat(PDO $db, int $so_luong = 6): array
{
    return $db->query('
        SELECT dh.id, dh.ho_ten_nhan, dh.ngay_dat, dh.trang_thai,
               COALESCE(t.tien_sach, 0) + dh.phi_van_chuyen AS tong
        FROM don_hang dh
        LEFT JOIN ' . SQL_TIEN_SACH . ' t ON t.don_hang_id = dh.id
        ORDER BY dh.ngay_dat DESC, dh.id DESC
        LIMIT ' . (int) $so_luong
    )->fetchAll();
}

function qt_sach_sap_het(PDO $db, int $so_luong = 6): array
{
    $cau_lenh = $db->prepare('
        SELECT s.id, s.ten, s.tac_gia, s.so_luong, tl.mau
        FROM sach s
        JOIN the_loai tl ON tl.id = s.the_loai_id
        WHERE s.hien_thi = 1 AND s.so_luong <= ?
        ORDER BY s.so_luong, s.id
        LIMIT ' . (int) $so_luong
    );
    $cau_lenh->execute([SAP_HET_HANG]);
    return $cau_lenh->fetchAll();
}
