<?php

function qt_lay_ds_loai(PDO $db, string $tu_khoa = ''): array
{
    $sql = '
        SELECT tl.id, tl.ten, tl.mau, tl.bieu_tuong,
               COUNT(s.id) AS so_sach,
               COALESCE(SUM(s.hien_thi = 1), 0) AS dang_ban,
               COALESCE(SUM(s.so_luong), 0) AS ton_kho
        FROM the_loai tl
        LEFT JOIN sach s ON s.the_loai_id = tl.id';
    $tham_so = [];
    if ($tu_khoa !== '') {
        $sql .= ' WHERE tl.ten LIKE ?';
        $tham_so[] = mau_like($tu_khoa);
    }
    $sql .= ' GROUP BY tl.id, tl.ten, tl.mau, tl.bieu_tuong ORDER BY tl.id';

    $cau_lenh = $db->prepare($sql);
    $cau_lenh->execute($tham_so);
    return $cau_lenh->fetchAll();
}

function qt_lay_ten_loai(PDO $db): array
{
    return $db->query('SELECT id, ten FROM the_loai ORDER BY ten')->fetchAll(PDO::FETCH_KEY_PAIR);
}

function qt_doc_form_loai(): array
{
    return [
        'ten'        => preg_replace('/\s+/u', ' ', post_chuoi('ten')),
        'mau'        => strtoupper(post_chuoi('mau')),
        'bieu_tuong' => post_chuoi('bieu_tuong'),
    ];
}

function qt_kiem_tra_loai(PDO $db, array $f, int $id = 0): array
{
    $loi = [];

    if (mb_strlen($f['ten']) < 2 || mb_strlen($f['ten']) > 100) {
        $loi['ten'] = 'Tên loại sách dài từ 2 đến 100 ký tự.';
    } else {
        $cau_lenh = $db->prepare('SELECT ten FROM the_loai WHERE ten = ? AND id <> ?');
        $cau_lenh->execute([$f['ten'], $id]);
        $trung = $cau_lenh->fetchColumn();
        if ($trung !== false) {
            $loi['ten'] = 'Đã có loại sách “' . $trung . '”.';
        }
    }

    if (!preg_match('/^#[0-9A-F]{6}$/', $f['mau'])) {
        $loi['mau'] = 'Màu bìa có dạng #RRGGBB, ví dụ #A8551E.';
    }

    if ($f['bieu_tuong'] === '' || mb_strlen($f['bieu_tuong']) > 16) {
        $loi['bieu_tuong'] = 'Biểu tượng dài từ 1 đến 16 ký tự (thường là một emoji).';
    }

    return $loi;
}

function qt_them_loai(PDO $db, array $f): int
{
    $cau_lenh = $db->prepare('INSERT INTO the_loai (ten, mau, bieu_tuong) VALUES (?, ?, ?)');
    $cau_lenh->execute([$f['ten'], $f['mau'], $f['bieu_tuong']]);
    return (int) $db->lastInsertId();
}

function qt_sua_loai(PDO $db, int $id, array $f): void
{
    $cau_lenh = $db->prepare('UPDATE the_loai SET ten = ?, mau = ?, bieu_tuong = ? WHERE id = ?');
    $cau_lenh->execute([$f['ten'], $f['mau'], $f['bieu_tuong'], $id]);
}

function qt_xoa_loai(PDO $db, int $id): array
{
    $loai = lay_the_loai($db, $id);
    if (!$loai) {
        return ['loi', 'Không tìm thấy loại sách cần xoá.'];
    }

    $cau_lenh = $db->prepare('SELECT COUNT(*) FROM sach WHERE the_loai_id = ?');
    $cau_lenh->execute([$id]);
    $so_sach = (int) $cau_lenh->fetchColumn();
    if ($so_sach > 0) {
        return ['loi', 'Không xoá được “' . $loai['ten'] . '” vì còn ' . $so_sach
                     . ' quyển sách thuộc loại này. Hãy chuyển các sách đó sang loại khác trước.'];
    }

    $db->prepare('DELETE FROM the_loai WHERE id = ?')->execute([$id]);
    return ['ok', 'Đã xoá loại sách “' . $loai['ten'] . '”.'];
}
