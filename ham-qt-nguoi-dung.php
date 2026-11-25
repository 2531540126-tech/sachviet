<?php

function qt_dieu_kien_nguoi_dung(string $tu_khoa, string $vai_tro, string $trang_thai): array
{
    $dieu_kien = [];
    $tham_so = [];
    if ($tu_khoa !== '') {
        $so_dt = chi_so($tu_khoa);
        $dieu_kien[] = '(nd.ho_ten LIKE ? OR nd.email LIKE ?'
                     . ($so_dt !== '' ? " OR REPLACE(nd.so_dien_thoai, ' ', '') LIKE ?" : '') . ')';
        array_push($tham_so, mau_like($tu_khoa), mau_like($tu_khoa));
        if ($so_dt !== '') {
            $tham_so[] = mau_like($so_dt);
        }
    }
    if ($vai_tro !== '') {
        $dieu_kien[] = 'nd.vai_tro = ?';
        $tham_so[] = $vai_tro;
    }
    if ($trang_thai !== '') {
        $dieu_kien[] = 'nd.trang_thai = ?';
        $tham_so[] = $trang_thai;
    }
    return [$dieu_kien ? ' WHERE ' . implode(' AND ', $dieu_kien) : '', $tham_so];
}

function qt_dem_nguoi_dung(PDO $db, string $tu_khoa, string $vai_tro, string $trang_thai): int
{
    [$where, $tham_so] = qt_dieu_kien_nguoi_dung($tu_khoa, $vai_tro, $trang_thai);
    $cau_lenh = $db->prepare('SELECT COUNT(*) FROM nguoi_dung nd' . $where);
    $cau_lenh->execute($tham_so);
    return (int) $cau_lenh->fetchColumn();
}

function qt_lay_ds_nguoi_dung(PDO $db, string $tu_khoa, string $vai_tro, string $trang_thai, array $pt): array
{
    [$where, $tham_so] = qt_dieu_kien_nguoi_dung($tu_khoa, $vai_tro, $trang_thai);
    $cau_lenh = $db->prepare("
        SELECT nd.id, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.vai_tro, nd.trang_thai, nd.ngay_tao,
               COALESCE(d.so_don, 0) AS so_don, COALESCE(d.da_chi, 0) AS da_chi
        FROM nguoi_dung nd
        LEFT JOIN (SELECT dh.nguoi_dung_id, COUNT(*) AS so_don,
                          SUM(CASE WHEN dh.trang_thai = 'da_giao'
                                   THEN COALESCE(t.tien_sach, 0) + dh.phi_van_chuyen ELSE 0 END) AS da_chi
                   FROM don_hang dh
                   LEFT JOIN " . SQL_TIEN_SACH . " t ON t.don_hang_id = dh.id
                   GROUP BY dh.nguoi_dung_id) d ON d.nguoi_dung_id = nd.id
        $where
        ORDER BY nd.id DESC
        LIMIT " . (int) $pt['moi_trang'] . ' OFFSET ' . (int) $pt['bo_qua']);
    $cau_lenh->execute($tham_so);
    return $cau_lenh->fetchAll();
}

function qt_lay_nguoi_dung(PDO $db, int $id): ?array
{
    $cau_lenh = $db->prepare('
        SELECT id, ho_ten, email, so_dien_thoai, ngay_sinh, dia_chi, vai_tro, trang_thai, ngay_tao
        FROM nguoi_dung
        WHERE id = ?
    ');
    $cau_lenh->execute([$id]);
    return $cau_lenh->fetch() ?: null;
}

function qt_tong_ket_nguoi_dung(PDO $db, int $id): array
{
    $cau_lenh = $db->prepare("
        SELECT COUNT(*) AS so_don,
               COALESCE(SUM(CASE WHEN dh.trang_thai = 'da_giao'
                                 THEN COALESCE(t.tien_sach, 0) + dh.phi_van_chuyen ELSE 0 END), 0) AS da_chi
        FROM don_hang dh
        LEFT JOIN " . SQL_TIEN_SACH . " t ON t.don_hang_id = dh.id
        WHERE dh.nguoi_dung_id = ?
    ");
    $cau_lenh->execute([$id]);
    $tong = $cau_lenh->fetch();

    $cau_lenh = $db->prepare('
        SELECT dh.id, dh.ngay_dat, dh.trang_thai, COALESCE(t.tien_sach, 0) + dh.phi_van_chuyen AS tong
        FROM don_hang dh
        LEFT JOIN ' . SQL_TIEN_SACH . ' t ON t.don_hang_id = dh.id
        WHERE dh.nguoi_dung_id = ?
        ORDER BY dh.ngay_dat DESC, dh.id DESC
        LIMIT 5
    ');
    $cau_lenh->execute([$id]);

    return [
        'so_don'  => (int) $tong['so_don'],
        'da_chi'  => (int) $tong['da_chi'],
        'don_moi' => $cau_lenh->fetchAll(),
    ];
}

function qt_nguoi_dung_trong(): array
{
    return [
        'ho_ten' => '', 'email' => '', 'so_dien_thoai' => '', 'ngay_sinh' => '', 'dia_chi' => '',
        'vai_tro' => 'user', 'trang_thai' => 'hoat_dong',
    ];
}

function qt_doc_form_nguoi_dung(): array
{
    return [
        'ho_ten'        => preg_replace('/\s+/u', ' ', post_chuoi('ho_ten')),
        'email'         => mb_strtolower(post_chuoi('email')),
        'so_dien_thoai' => preg_replace('/[\s.]/', '', post_chuoi('so_dien_thoai')),
        'ngay_sinh'     => post_chuoi('ngay_sinh'),
        'dia_chi'       => preg_replace('/\s+/u', ' ', post_chuoi('dia_chi')),
        'vai_tro'       => post_chuoi('vai_tro'),
        'trang_thai'    => post_chuoi('trang_thai'),
        'mat_khau'      => post_mat_khau('mat_khau'),
    ];
}

function qt_kiem_tra_nguoi_dung(PDO $db, array $f, int $id = 0): array
{
    $loi = kiem_tra_thong_tin($f);

    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL) || strlen($f['email']) > 150) {
        $loi['email'] = 'Email không đúng định dạng.';
    } else {
        $cau_lenh = $db->prepare('SELECT COUNT(*) FROM nguoi_dung WHERE email = ? AND id <> ?');
        $cau_lenh->execute([$f['email'], $id]);
        if ($cau_lenh->fetchColumn() > 0) {
            $loi['email'] = 'Email này đã thuộc về một tài khoản khác.';
        }
    }

    if (!isset(VAI_TRO[$f['vai_tro']])) {
        $loi['vai_tro'] = 'Vui lòng chọn vai trò.';
    }
    if (!isset(TRANG_THAI_TK[$f['trang_thai']])) {
        $loi['trang_thai'] = 'Vui lòng chọn trạng thái.';
    }

    if ($id === 0 && strlen($f['mat_khau']) < 6) {
        $loi['mat_khau'] = 'Mật khẩu cần ít nhất 6 ký tự.';
    } elseif ($id > 0 && $f['mat_khau'] !== '' && strlen($f['mat_khau']) < 6) {
        $loi['mat_khau'] = 'Mật khẩu mới cần ít nhất 6 ký tự (để trống nếu không đổi).';
    }

    return $loi;
}

function qt_them_nguoi_dung(PDO $db, array $f): int
{
    $cau_lenh = $db->prepare('
        INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai, ngay_sinh, dia_chi, vai_tro, trang_thai)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $cau_lenh->execute([
        $f['ho_ten'],
        $f['email'],
        password_hash($f['mat_khau'], PASSWORD_DEFAULT),
        dinh_dang_sdt($f['so_dien_thoai']),
        $f['ngay_sinh'] === '' ? null : $f['ngay_sinh'],
        $f['dia_chi'] === '' ? null : $f['dia_chi'],
        $f['vai_tro'],
        $f['trang_thai'],
    ]);
    return (int) $db->lastInsertId();
}

function qt_sua_nguoi_dung(PDO $db, int $id, array $f): void
{
    $cau_lenh = $db->prepare('
        UPDATE nguoi_dung
        SET ho_ten = ?, email = ?, so_dien_thoai = ?, ngay_sinh = ?, dia_chi = ?, vai_tro = ?, trang_thai = ?
        WHERE id = ?
    ');
    $cau_lenh->execute([
        $f['ho_ten'],
        $f['email'],
        dinh_dang_sdt($f['so_dien_thoai']),
        $f['ngay_sinh'] === '' ? null : $f['ngay_sinh'],
        $f['dia_chi'] === '' ? null : $f['dia_chi'],
        $f['vai_tro'],
        $f['trang_thai'],
        $id,
    ]);
    if ($f['mat_khau'] !== '') {
        $cau_lenh = $db->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?');
        $cau_lenh->execute([password_hash($f['mat_khau'], PASSWORD_DEFAULT), $id]);
    }
}

function qt_doi_trang_thai_tk(PDO $db, int $id, string $moi, int $id_dang_dung): array
{
    $nd = qt_lay_nguoi_dung($db, $id);
    if (!$nd) {
        return ['loi', 'Không tìm thấy người dùng.'];
    }
    if ($id === $id_dang_dung) {
        return ['loi', 'Bạn không thể tự khoá tài khoản đang đăng nhập.'];
    }
    if (!isset(TRANG_THAI_TK[$moi])) {
        return ['loi', 'Trạng thái không hợp lệ.'];
    }

    $db->prepare('UPDATE nguoi_dung SET trang_thai = ? WHERE id = ?')->execute([$moi, $id]);
    return ['ok', 'Tài khoản ' . $nd['email'] . ' đã chuyển sang “' . nhan(TRANG_THAI_TK, $moi) . '”.'];
}

function qt_xoa_nguoi_dung(PDO $db, int $id, int $id_dang_dung): array
{
    $nd = qt_lay_nguoi_dung($db, $id);
    if (!$nd) {
        return ['loi', 'Không tìm thấy người dùng cần xoá.'];
    }
    if ($id === $id_dang_dung) {
        return ['loi', 'Bạn không thể tự xoá tài khoản đang đăng nhập.'];
    }

    $cau_lenh = $db->prepare('SELECT COUNT(*) FROM don_hang WHERE nguoi_dung_id = ?');
    $cau_lenh->execute([$id]);
    $so_don = (int) $cau_lenh->fetchColumn();
    if ($so_don > 0) {
        return ['loi', 'Không xoá được ' . $nd['email'] . ' vì tài khoản đã có ' . $so_don
                     . ' đơn hàng. Hãy chuyển trạng thái sang “Tạm khoá” để giữ lịch sử mua hàng.'];
    }

    $db->prepare('DELETE FROM nguoi_dung WHERE id = ?')->execute([$id]);
    return ['ok', 'Đã xoá tài khoản ' . $nd['email'] . '.'];
}
