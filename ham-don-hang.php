<?php

function lay_thong_tin_giao_hang(PDO $db, int $nguoi_dung_id): array
{
    $cau_lenh = $db->prepare('SELECT ho_ten, email, so_dien_thoai, dia_chi FROM nguoi_dung WHERE id = ?');
    $cau_lenh->execute([$nguoi_dung_id]);
    $dong = $cau_lenh->fetch() ?: [];
    return [
        'ho_ten'        => $dong['ho_ten'] ?? '',
        'so_dien_thoai' => $dong['so_dien_thoai'] ?? '',
        'dia_chi'       => $dong['dia_chi'] ?? '',
        'ghi_chu'       => '',
        'phuong_thuc'   => 'cod',
    ];
}

function doc_form_thanh_toan(): array
{
    return [
        'ho_ten'        => preg_replace('/\s+/u', ' ', post_chuoi('ho_ten')),
        'so_dien_thoai' => preg_replace('/[\s.]/', '', post_chuoi('so_dien_thoai')),
        'dia_chi'       => post_chuoi('dia_chi'),
        'ghi_chu'       => mb_substr(post_chuoi('ghi_chu'), 0, 255),
        'phuong_thuc'   => post_chuoi('phuong_thuc'),
    ];
}

function kiem_tra_thanh_toan(array $f): array
{
    $loi = [];
    if (mb_strlen($f['ho_ten']) < 2 || mb_strlen($f['ho_ten']) > 100) {
        $loi['ho_ten'] = 'Vui lòng nhập họ tên người nhận.';
    }
    if (!preg_match('/^0\d{9}$/', $f['so_dien_thoai'])) {
        $loi['so_dien_thoai'] = 'Số điện thoại gồm 10 chữ số và bắt đầu bằng 0.';
    }
    if (mb_strlen($f['dia_chi']) < 5 || mb_strlen($f['dia_chi']) > 255) {
        $loi['dia_chi'] = 'Vui lòng nhập địa chỉ nhận hàng.';
    }
    if (!isset(PHUONG_THUC_TT[$f['phuong_thuc']])) {
        $loi['phuong_thuc'] = 'Vui lòng chọn phương thức thanh toán.';
    }
    return $loi;
}

function dat_hang(PDO $db, int $nguoi_dung_id, array $f, int $phan_tram_giam): array
{
    $ds_trong_gio = lay_gio_hang($db, $nguoi_dung_id);
    if (!$ds_trong_gio) {
        return ['loi', 'Giỏ hàng đang trống.'];
    }
    if ($phan_tram_giam > 0 && da_co_don_hang($db, $nguoi_dung_id)) {
        unset($_SESSION['ma_giam']);
        return ['loi', 'Mã giảm giá chỉ dùng cho đơn hàng đầu tiên nên đã được bỏ. Vui lòng kiểm tra lại giỏ hàng.'];
    }
    foreach ($ds_trong_gio as $sp) {
        if ($sp['so_luong'] > $sp['ton_kho']) {
            return ['loi', 'Kho chỉ còn ' . $sp['ton_kho'] . ' cuốn “' . $sp['ten'] . '”, vui lòng giảm số lượng trong giỏ.'];
        }
    }
    $tien = tinh_tien_gio($ds_trong_gio, $phan_tram_giam);

    $db->beginTransaction();
    try {
        $cau_lenh = $db->prepare("
            INSERT INTO don_hang (nguoi_dung_id, ho_ten_nhan, so_dien_thoai, dia_chi_giao,
                                  ghi_chu, phuong_thuc_tt, phi_van_chuyen, trang_thai)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'cho_xac_nhan')
        ");
        $cau_lenh->execute([
            $nguoi_dung_id, $f['ho_ten'], dinh_dang_sdt($f['so_dien_thoai']), $f['dia_chi'],
            $f['ghi_chu'] === '' ? null : $f['ghi_chu'], $f['phuong_thuc'], $tien['phi_ship'],
        ]);
        $don_id = (int) $db->lastInsertId();

        $them_chi_tiet = $db->prepare('INSERT INTO chi_tiet_don_hang (don_hang_id, sach_id, so_luong, don_gia) VALUES (?, ?, ?, ?)');
        $tru_kho = $db->prepare('UPDATE sach SET so_luong = so_luong - ? WHERE id = ? AND so_luong >= ?');
        foreach ($ds_trong_gio as $sp) {
            $don_gia = (int) round($sp['gia'] * (100 - $phan_tram_giam) / 100);
            $them_chi_tiet->execute([$don_id, $sp['sach_id'], $sp['so_luong'], $don_gia]);
            $tru_kho->execute([$sp['so_luong'], $sp['sach_id'], $sp['so_luong']]);
            if ($tru_kho->rowCount() === 0) {
                throw new RuntimeException('het_hang');
            }
        }

        $db->prepare('DELETE FROM gio_hang WHERE nguoi_dung_id = ?')->execute([$nguoi_dung_id]);
        $db->commit();
    } catch (Throwable $loi) {
        $db->rollBack();
        if ($loi->getMessage() === 'het_hang') {
            return ['loi', 'Có sách vừa hết hàng, vui lòng kiểm tra lại giỏ hàng.'];
        }
        throw $loi;
    }

    unset($_SESSION['ma_giam']);
    return ['ok', $don_id];
}
