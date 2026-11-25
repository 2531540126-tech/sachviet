<?php

const CHUYEN_TRANG_THAI = [
    'cho_xac_nhan' => ['dang_giao' => 'Xác nhận đơn, chuyển sang “Đang giao”',
                       'da_huy'    => 'Huỷ đơn, hoàn sách về kho'],
    'dang_giao'    => ['da_giao'   => 'Khách đã nhận hàng, chuyển sang “Đã giao”',
                       'da_huy'    => 'Giao không thành công, huỷ đơn và hoàn sách về kho'],
    'da_giao'      => [],
    'da_huy'       => [],
];

function qt_dieu_kien_don(string $tu_khoa, string $trang_thai): array
{
    $dieu_kien = [];
    $tham_so = [];
    if ($tu_khoa !== '') {
        $ma = preg_match('/^#?(?:dh)?\s*0*(\d{1,9})$/i', $tu_khoa, $khop) ? (int) $khop[1] : 0;
        $so_dt = chi_so($tu_khoa);
        $dieu_kien[] = '(dh.id = ? OR dh.ho_ten_nhan LIKE ? OR nd.email LIKE ?'
                     . ($so_dt !== '' ? " OR REPLACE(dh.so_dien_thoai, ' ', '') LIKE ?" : '') . ')';
        array_push($tham_so, $ma, mau_like($tu_khoa), mau_like($tu_khoa));
        if ($so_dt !== '') {
            $tham_so[] = mau_like($so_dt);
        }
    }
    if ($trang_thai !== '') {
        $dieu_kien[] = 'dh.trang_thai = ?';
        $tham_so[] = $trang_thai;
    }
    return [$dieu_kien ? ' WHERE ' . implode(' AND ', $dieu_kien) : '', $tham_so];
}

function qt_dem_don_theo_trang_thai(PDO $db, string $tu_khoa): array
{
    [$where, $tham_so] = qt_dieu_kien_don($tu_khoa, '');
    $cau_lenh = $db->prepare('
        SELECT dh.trang_thai, COUNT(*) AS so_don
        FROM don_hang dh
        JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id' . $where . '
        GROUP BY dh.trang_thai
    ');
    $cau_lenh->execute($tham_so);
    return array_map('intval', $cau_lenh->fetchAll(PDO::FETCH_KEY_PAIR));
}

function qt_lay_ds_don(PDO $db, string $tu_khoa, string $trang_thai, array $pt): array
{
    [$where, $tham_so] = qt_dieu_kien_don($tu_khoa, $trang_thai);
    $cau_lenh = $db->prepare('
        SELECT dh.id, dh.ho_ten_nhan, dh.so_dien_thoai, dh.phuong_thuc_tt, dh.trang_thai, dh.ngay_dat,
               nd.email, COALESCE(t.so_cuon, 0) AS so_cuon,
               COALESCE(t.tien_sach, 0) + dh.phi_van_chuyen AS tong
        FROM don_hang dh
        JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id
        LEFT JOIN ' . SQL_TIEN_SACH . ' t ON t.don_hang_id = dh.id' . $where . '
        ORDER BY dh.ngay_dat DESC, dh.id DESC
        LIMIT ' . (int) $pt['moi_trang'] . ' OFFSET ' . (int) $pt['bo_qua']);
    $cau_lenh->execute($tham_so);
    return $cau_lenh->fetchAll();
}

function qt_lay_don(PDO $db, int $id): ?array
{
    $cau_lenh = $db->prepare('
        SELECT dh.*, nd.ho_ten AS ten_tai_khoan, nd.email, nd.id AS ma_tai_khoan
        FROM don_hang dh
        JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id
        WHERE dh.id = ?
    ');
    $cau_lenh->execute([$id]);
    return $cau_lenh->fetch() ?: null;
}

function qt_lay_sach_cua_don(PDO $db, int $don_id): array
{
    $cau_lenh = $db->prepare('
        SELECT ct.sach_id, ct.so_luong, ct.don_gia, s.ten, s.tac_gia, s.so_luong AS ton_kho, tl.mau
        FROM chi_tiet_don_hang ct
        JOIN sach s      ON s.id  = ct.sach_id
        JOIN the_loai tl ON tl.id = s.the_loai_id
        WHERE ct.don_hang_id = ?
        ORDER BY s.id
    ');
    $cau_lenh->execute([$don_id]);
    return $cau_lenh->fetchAll();
}

function qt_doi_trang_thai_don(PDO $db, int $id, string $moi): array
{
    $don = qt_lay_don($db, $id);
    if (!$don) {
        return ['loi', 'Không tìm thấy đơn hàng.'];
    }
    $cu = $don['trang_thai'];
    if (!isset(CHUYEN_TRANG_THAI[$cu][$moi])) {
        return ['loi', 'Không thể chuyển đơn ' . ma_don($id) . ' từ “' . nhan(TRANG_THAI_DON, $cu)
                     . '” sang “' . nhan(TRANG_THAI_DON, $moi) . '”.'];
    }

    $db->beginTransaction();
    try {
        $cau_lenh = $db->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ? AND trang_thai = ?');
        $cau_lenh->execute([$moi, $id, $cu]);
        if ($cau_lenh->rowCount() !== 1) {
            $db->rollBack();
            return ['loi', 'Đơn ' . ma_don($id) . ' vừa được cập nhật ở nơi khác, vui lòng kiểm tra lại.'];
        }
        if ($moi === 'da_huy') {
            $hoan_kho = $db->prepare('UPDATE sach SET so_luong = so_luong + ? WHERE id = ?');
            foreach (qt_lay_sach_cua_don($db, $id) as $sp) {
                $hoan_kho->execute([$sp['so_luong'], $sp['sach_id']]);
            }
        }
        $db->commit();
    } catch (Throwable $loi) {
        $db->rollBack();
        throw $loi;
    }

    return ['ok', 'Đơn ' . ma_don($id) . ' đã chuyển sang “' . nhan(TRANG_THAI_DON, $moi) . '”'
                . ($moi === 'da_huy' ? ', sách đã được hoàn về kho.' : '.')];
}
