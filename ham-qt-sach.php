<?php

const TINH_TRANG_SACH = [
    'dang-ban' => 'Đang bán',
    'dang-an'  => 'Đang ẩn',
    'het-hang' => 'Hết hàng',
    'sap-het'  => 'Sắp hết hàng',
];

function qt_dieu_kien_sach(string $tu_khoa, int $the_loai_id, string $tinh_trang): array
{
    $dieu_kien = [];
    $tham_so = [];
    if ($tu_khoa !== '') {
        $dieu_kien[] = '(s.ten LIKE ? OR s.tac_gia LIKE ? OR s.id = ?)';
        array_push($tham_so, mau_like($tu_khoa), mau_like($tu_khoa), ctype_digit($tu_khoa) ? (int) $tu_khoa : 0);
    }
    if ($the_loai_id > 0) {
        $dieu_kien[] = 's.the_loai_id = ?';
        $tham_so[] = $the_loai_id;
    }
    switch ($tinh_trang) {
        case 'dang-ban':
            $dieu_kien[] = 's.hien_thi = 1';
            break;
        case 'dang-an':
            $dieu_kien[] = 's.hien_thi = 0';
            break;
        case 'het-hang':
            $dieu_kien[] = 's.so_luong = 0';
            break;
        case 'sap-het':
            $dieu_kien[] = 's.so_luong BETWEEN 1 AND ' . SAP_HET_HANG;
            break;
    }
    return [$dieu_kien ? ' WHERE ' . implode(' AND ', $dieu_kien) : '', $tham_so];
}

function qt_dem_sach(PDO $db, string $tu_khoa, int $the_loai_id, string $tinh_trang): int
{
    [$where, $tham_so] = qt_dieu_kien_sach($tu_khoa, $the_loai_id, $tinh_trang);
    $cau_lenh = $db->prepare('SELECT COUNT(*) FROM sach s' . $where);
    $cau_lenh->execute($tham_so);
    return (int) $cau_lenh->fetchColumn();
}

function qt_lay_ds_sach(PDO $db, string $tu_khoa, int $the_loai_id, string $tinh_trang, array $pt): array
{
    [$where, $tham_so] = qt_dieu_kien_sach($tu_khoa, $the_loai_id, $tinh_trang);
    $cau_lenh = $db->prepare("
        SELECT s.id, s.ten, s.tac_gia, s.gia, s.gia_cu, s.so_luong, s.hien_thi,
               tl.ten AS ten_the_loai, tl.mau, k.ten AS ten_kho,
               COALESCE(b.da_ban, 0) AS da_ban, COALESCE(b.so_don, 0) AS so_don
        FROM sach s
        JOIN the_loai tl ON tl.id = s.the_loai_id
        JOIN kho k       ON k.id  = s.kho_id
        LEFT JOIN (SELECT ct.sach_id,
                          SUM(CASE WHEN dh.trang_thai = 'da_giao' THEN ct.so_luong ELSE 0 END) AS da_ban,
                          COUNT(*) AS so_don
                   FROM chi_tiet_don_hang ct
                   JOIN don_hang dh ON dh.id = ct.don_hang_id
                   GROUP BY ct.sach_id) b ON b.sach_id = s.id
        $where
        ORDER BY s.id DESC
        LIMIT " . (int) $pt['moi_trang'] . ' OFFSET ' . (int) $pt['bo_qua']);
    $cau_lenh->execute($tham_so);
    return $cau_lenh->fetchAll();
}

function qt_lay_sach(PDO $db, int $id): ?array
{
    $cau_lenh = $db->prepare('SELECT * FROM sach WHERE id = ?');
    $cau_lenh->execute([$id]);
    return $cau_lenh->fetch() ?: null;
}

function qt_sach_trong(): array
{
    return [
        'ten' => '', 'tac_gia' => '', 'nam_sang_tac' => '', 'the_loai_id' => 0, 'kho_id' => 0,
        'gia' => '', 'gia_cu' => '', 'so_luong' => '', 'mo_ta' => '', 'trich_doan' => '',
        'link_wikisource' => '', 'hien_thi' => 1,
    ];
}

function qt_doc_form_sach(): array
{
    return [
        'ten'             => preg_replace('/\s+/u', ' ', post_chuoi('ten')),
        'tac_gia'         => preg_replace('/\s+/u', ' ', post_chuoi('tac_gia')),
        'nam_sang_tac'    => preg_replace('/\s+/u', ' ', post_chuoi('nam_sang_tac')),
        'the_loai_id'     => post_so('the_loai_id'),
        'kho_id'          => post_so('kho_id'),
        'gia'             => post_chuoi('gia'),
        'gia_cu'          => post_chuoi('gia_cu'),
        'so_luong'        => post_chuoi('so_luong'),
        'mo_ta'           => van_ban_nhieu_dong('mo_ta'),
        'trich_doan'      => van_ban_nhieu_dong('trich_doan'),
        'link_wikisource' => post_chuoi('link_wikisource'),
        'hien_thi'        => isset($_POST['hien_thi']) ? 1 : 0,
    ];
}

function qt_kiem_tra_sach(PDO $db, array &$f): array
{
    $loi = [];

    if ($f['ten'] === '' || mb_strlen($f['ten']) > 200) {
        $loi['ten'] = 'Vui lòng nhập tên sách (tối đa 200 ký tự).';
    }
    if (mb_strlen($f['tac_gia']) > 150) {
        $loi['tac_gia'] = 'Tên tác giả dài tối đa 150 ký tự.';
    }
    if (mb_strlen($f['nam_sang_tac']) > 50) {
        $loi['nam_sang_tac'] = 'Năm sáng tác dài tối đa 50 ký tự.';
    }

    if (!lay_the_loai($db, $f['the_loai_id'])) {
        $loi['the_loai_id'] = 'Vui lòng chọn loại sách.';
    }
    $cau_lenh = $db->prepare('SELECT COUNT(*) FROM kho WHERE id = ?');
    $cau_lenh->execute([$f['kho_id']]);
    if ($cau_lenh->fetchColumn() == 0) {
        $loi['kho_id'] = 'Vui lòng chọn kho hàng.';
    }

    $gia = so_nguyen($f['gia'], 100000000);
    if ($gia === null || $gia < 1000) {
        $loi['gia'] = 'Giá bán là số nguyên từ 1.000 đến 100.000.000 (đồng).';
    }
    $gia_cu = null;
    if ($f['gia_cu'] !== '') {
        $gia_cu = so_nguyen($f['gia_cu'], 100000000);
        if ($gia_cu === null) {
            $loi['gia_cu'] = 'Giá bìa là số nguyên, tối đa 100.000.000 (đồng).';
        } elseif ($gia !== null && $gia_cu <= $gia) {
            $loi['gia_cu'] = 'Giá bìa phải lớn hơn giá bán, hoặc để trống nếu không giảm giá.';
        }
    }
    $so_luong = so_nguyen($f['so_luong'], 1000000);
    if ($so_luong === null) {
        $loi['so_luong'] = 'Số lượng tồn kho là số nguyên từ 0 đến 1.000.000.';
    }

    if (mb_strlen($f['mo_ta']) > 2000) {
        $loi['mo_ta'] = 'Mô tả dài tối đa 2.000 ký tự.';
    }
    if (mb_strlen($f['trich_doan']) > 10000) {
        $loi['trich_doan'] = 'Đoạn đọc thử dài tối đa 10.000 ký tự.';
    }
    if ($f['link_wikisource'] !== ''
        && (!preg_match('~^https?://[^\s<>"\']+$~u', $f['link_wikisource']) || mb_strlen($f['link_wikisource']) > 255)) {
        $loi['link_wikisource'] = 'Link đọc toàn văn phải bắt đầu bằng http:// hoặc https:// (tối đa 255 ký tự).';
    }

    if (!$loi) {
        $f['gia'] = $gia;
        $f['gia_cu'] = $gia_cu;
        $f['so_luong'] = $so_luong;
    }
    return $loi;
}

function qt_gia_tri_sach(array $f): array
{
    return [
        $f['ten'],
        $f['tac_gia'] === '' ? 'Khuyết danh' : $f['tac_gia'],
        $f['nam_sang_tac'] === '' ? null : $f['nam_sang_tac'],
        $f['the_loai_id'],
        $f['kho_id'],
        $f['gia'],
        $f['gia_cu'],
        $f['so_luong'],
        $f['mo_ta'] === '' ? null : $f['mo_ta'],
        $f['trich_doan'] === '' ? null : $f['trich_doan'],
        $f['link_wikisource'] === '' ? null : $f['link_wikisource'],
        $f['hien_thi'],
    ];
}

function qt_them_sach(PDO $db, array $f): int
{
    $cau_lenh = $db->prepare('
        INSERT INTO sach (ten, tac_gia, nam_sang_tac, the_loai_id, kho_id, gia, gia_cu, so_luong,
                          mo_ta, trich_doan, link_wikisource, hien_thi)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $cau_lenh->execute(qt_gia_tri_sach($f));
    return (int) $db->lastInsertId();
}

function qt_sua_sach(PDO $db, int $id, array $f): void
{
    $cau_lenh = $db->prepare('
        UPDATE sach
        SET ten = ?, tac_gia = ?, nam_sang_tac = ?, the_loai_id = ?, kho_id = ?, gia = ?, gia_cu = ?,
            so_luong = ?, mo_ta = ?, trich_doan = ?, link_wikisource = ?, hien_thi = ?
        WHERE id = ?
    ');
    $cau_lenh->execute(array_merge(qt_gia_tri_sach($f), [$id]));
    if (!$f['hien_thi']) {
        $db->prepare('DELETE FROM gio_hang WHERE sach_id = ?')->execute([$id]);
    }
}

function qt_doi_hien_thi(PDO $db, int $id): array
{
    $sach = qt_lay_sach($db, $id);
    if (!$sach) {
        return ['loi', 'Không tìm thấy quyển sách này.'];
    }

    $moi = $sach['hien_thi'] ? 0 : 1;
    $db->prepare('UPDATE sach SET hien_thi = ? WHERE id = ?')->execute([$moi, $id]);
    if ($moi === 0) {
        $db->prepare('DELETE FROM gio_hang WHERE sach_id = ?')->execute([$id]);
        return ['ok', 'Đã ẩn “' . $sach['ten'] . '” khỏi cửa hàng và bỏ sách này khỏi giỏ hàng của khách.'];
    }
    return ['ok', 'Đã mở bán lại “' . $sach['ten'] . '”.'];
}

function qt_dem_don_cua_sach(PDO $db, int $id): int
{
    $cau_lenh = $db->prepare('SELECT COUNT(DISTINCT don_hang_id) FROM chi_tiet_don_hang WHERE sach_id = ?');
    $cau_lenh->execute([$id]);
    return (int) $cau_lenh->fetchColumn();
}

function qt_xoa_sach(PDO $db, int $id): array
{
    $sach = qt_lay_sach($db, $id);
    if (!$sach) {
        return ['loi', 'Không tìm thấy quyển sách cần xoá.'];
    }

    $so_don = qt_dem_don_cua_sach($db, $id);
    if ($so_don > 0) {
        return ['loi', 'Không xoá được “' . $sach['ten'] . '” vì sách đã nằm trong ' . $so_don
                     . ' đơn hàng. Hãy bấm “Ẩn” để ngừng bán mà vẫn giữ lịch sử đơn hàng.'];
    }

    $db->prepare('DELETE FROM sach WHERE id = ?')->execute([$id]);
    return ['ok', 'Đã xoá sách “' . $sach['ten'] . '”.'];
}
