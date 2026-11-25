<?php

function doc_form_dang_ky(): array
{
    return [
        'ho_ten'        => preg_replace('/\s+/u', ' ', post_chuoi('ho_ten')),
        'email'         => mb_strtolower(post_chuoi('email')),
        'so_dien_thoai' => preg_replace('/[\s.]/', '', post_chuoi('so_dien_thoai')),
        'mat_khau'      => post_mat_khau('mat_khau'),
        'nhap_lai'      => post_mat_khau('nhap_lai'),
        'dong_y'        => isset($_POST['dong_y']),
    ];
}

function kiem_tra_dang_ky(PDO $db, array $f): array
{
    $loi = [];

    if (mb_strlen($f['ho_ten']) < 2 || mb_strlen($f['ho_ten']) > 100) {
        $loi['ho_ten'] = 'Vui lòng nhập họ và tên (2–100 ký tự).';
    }

    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL) || strlen($f['email']) > 150) {
        $loi['email'] = 'Email không đúng định dạng.';
    } elseif (email_da_ton_tai($db, $f['email'])) {
        $loi['email'] = 'Email này đã được đăng ký, hãy đăng nhập hoặc dùng email khác.';
    }

    if (!preg_match('/^0\d{9}$/', $f['so_dien_thoai'])) {
        $loi['so_dien_thoai'] = 'Số điện thoại gồm 10 chữ số và bắt đầu bằng 0.';
    }

    if (strlen($f['mat_khau']) < 6) {
        $loi['mat_khau'] = 'Mật khẩu cần ít nhất 6 ký tự.';
    } elseif ($f['mat_khau'] !== $f['nhap_lai']) {
        $loi['nhap_lai'] = 'Mật khẩu nhập lại không khớp.';
    }

    if (!$f['dong_y']) {
        $loi['dong_y'] = 'Bạn cần đồng ý với điều khoản sử dụng.';
    }

    return $loi;
}

function email_da_ton_tai(PDO $db, string $email): bool
{
    $cau_lenh = $db->prepare('SELECT COUNT(*) FROM nguoi_dung WHERE email = ?');
    $cau_lenh->execute([$email]);
    return $cau_lenh->fetchColumn() > 0;
}

function dang_ky_tai_khoan(PDO $db, array $f): int
{
    $cau_lenh = $db->prepare("
        INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai, vai_tro, trang_thai)
        VALUES (?, ?, ?, ?, 'user', 'hoat_dong')
    ");
    $cau_lenh->execute([
        $f['ho_ten'],
        $f['email'],
        password_hash($f['mat_khau'], PASSWORD_DEFAULT),
        dinh_dang_sdt($f['so_dien_thoai']),
    ]);
    return (int) $db->lastInsertId();
}

function dang_nhap(PDO $db, string $email, string $mat_khau): array
{
    $cau_lenh = $db->prepare('
        SELECT id, ho_ten, email, mat_khau, vai_tro, trang_thai
        FROM nguoi_dung
        WHERE email = ?
    ');
    $cau_lenh->execute([mb_strtolower($email)]);
    $nd = $cau_lenh->fetch();

    if (!$nd || !password_verify($mat_khau, $nd['mat_khau'])) {
        return ['loi' => 'Email hoặc mật khẩu không đúng.'];
    }
    if ($nd['trang_thai'] !== 'hoat_dong') {
        return ['loi' => 'Tài khoản đang ở trạng thái “' . nhan(TRANG_THAI_TK, $nd['trang_thai']) . '”, chưa thể đăng nhập.'];
    }

    session_regenerate_id(true);
    $_SESSION['nguoi_dung'] = [
        'id'      => (int) $nd['id'],
        'ho_ten'  => $nd['ho_ten'],
        'email'   => $nd['email'],
        'vai_tro' => $nd['vai_tro'],
    ];
    return ['nguoi_dung' => $_SESSION['nguoi_dung']];
}

function dang_xuat(): void
{
    unset($_SESSION['nguoi_dung'], $_SESSION['ma_giam']);
    session_regenerate_id(true);
}

function nguoi_dung_hien_tai(): ?array
{
    return $_SESSION['nguoi_dung'] ?? null;
}

function bat_buoc_dang_nhap(string $thong_bao, ?string $quay_lai = null): array
{
    $nd = nguoi_dung_hien_tai();
    if ($nd === null) {
        dat_thong_bao('loi', $thong_bao);
        chuyen_trang('login.php?quay_lai=' . urlencode($quay_lai ?? trang_hien_tai()));
    }
    return $nd;
}

function doc_form_thong_tin(): array
{
    return [
        'ho_ten'        => preg_replace('/\s+/u', ' ', post_chuoi('ho_ten')),
        'so_dien_thoai' => preg_replace('/[\s.]/', '', post_chuoi('so_dien_thoai')),
        'ngay_sinh'     => post_chuoi('ngay_sinh'),
        'dia_chi'       => preg_replace('/\s+/u', ' ', post_chuoi('dia_chi')),
    ];
}

function kiem_tra_thong_tin(array $f): array
{
    $loi = [];

    if (mb_strlen($f['ho_ten']) < 2 || mb_strlen($f['ho_ten']) > 100) {
        $loi['ho_ten'] = 'Vui lòng nhập họ và tên (2–100 ký tự).';
    }

    if (!preg_match('/^0\d{9}$/', $f['so_dien_thoai'])) {
        $loi['so_dien_thoai'] = 'Số điện thoại gồm 10 chữ số và bắt đầu bằng 0.';
    }

    if ($f['ngay_sinh'] !== '') {
        $ngay = DateTime::createFromFormat('!Y-m-d', $f['ngay_sinh']);
        if (!$ngay || $ngay->format('Y-m-d') !== $f['ngay_sinh']
            || (int) $ngay->format('Y') < 1900 || $ngay > new DateTime()) {
            $loi['ngay_sinh'] = 'Ngày sinh không hợp lệ.';
        }
    }

    if (mb_strlen($f['dia_chi']) > 255) {
        $loi['dia_chi'] = 'Địa chỉ dài tối đa 255 ký tự.';
    }

    return $loi;
}

function luu_thong_tin(PDO $db, int $nguoi_dung_id, array $f): void
{
    $cau_lenh = $db->prepare('
        UPDATE nguoi_dung
        SET ho_ten = ?, so_dien_thoai = ?, ngay_sinh = ?, dia_chi = ?
        WHERE id = ?
    ');
    $cau_lenh->execute([
        $f['ho_ten'],
        dinh_dang_sdt($f['so_dien_thoai']),
        $f['ngay_sinh'] === '' ? null : $f['ngay_sinh'],
        $f['dia_chi'] === '' ? null : $f['dia_chi'],
        $nguoi_dung_id,
    ]);
    $_SESSION['nguoi_dung']['ho_ten'] = $f['ho_ten'];
}

function doi_mat_khau(PDO $db, int $nguoi_dung_id, string $mat_khau_cu, string $mat_khau_moi, string $nhap_lai): array
{
    $cau_lenh = $db->prepare('SELECT mat_khau FROM nguoi_dung WHERE id = ?');
    $cau_lenh->execute([$nguoi_dung_id]);
    $chuoi_bam = $cau_lenh->fetchColumn();

    $loi = [];
    if ($chuoi_bam === false || !password_verify($mat_khau_cu, $chuoi_bam)) {
        $loi['mat_khau_cu'] = 'Mật khẩu hiện tại không đúng.';
    }
    if (strlen($mat_khau_moi) < 6) {
        $loi['mat_khau_moi'] = 'Mật khẩu mới cần ít nhất 6 ký tự.';
    } elseif ($mat_khau_moi !== $nhap_lai) {
        $loi['nhap_lai'] = 'Mật khẩu nhập lại không khớp.';
    }
    if ($loi) {
        return $loi;
    }

    $cau_lenh = $db->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?');
    $cau_lenh->execute([password_hash($mat_khau_moi, PASSWORD_DEFAULT), $nguoi_dung_id]);
    session_regenerate_id(true);
    return [];
}

function la_quan_tri(?array $nd): bool
{
    return $nd !== null && in_array($nd['vai_tro'], ['admin', 'staff'], true);
}

function lam_moi_phien(PDO $db): void
{
    $nd = nguoi_dung_hien_tai();
    if ($nd === null) {
        return;
    }

    $cau_lenh = $db->prepare('SELECT ho_ten, email, vai_tro, trang_thai FROM nguoi_dung WHERE id = ?');
    $cau_lenh->execute([$nd['id']]);
    $dong = $cau_lenh->fetch();

    if (!$dong || $dong['trang_thai'] !== 'hoat_dong') {
        dang_xuat();
        dat_thong_bao('loi', $dong
            ? 'Tài khoản của bạn đang ở trạng thái “' . nhan(TRANG_THAI_TK, $dong['trang_thai']) . '” nên đã bị đăng xuất.'
            : 'Tài khoản của bạn không còn tồn tại nên đã bị đăng xuất.');
        return;
    }

    $_SESSION['nguoi_dung']['ho_ten']  = $dong['ho_ten'];
    $_SESSION['nguoi_dung']['email']   = $dong['email'];
    $_SESSION['nguoi_dung']['vai_tro'] = $dong['vai_tro'];
}
