<?php

function lay_the_loai_kem_so_sach(PDO $db): array
{
    return $db->query('
        SELECT tl.id, tl.ten, tl.mau, tl.bieu_tuong, COUNT(s.id) AS so_sach
        FROM the_loai tl
        LEFT JOIN sach s ON s.the_loai_id = tl.id AND s.hien_thi = 1
        GROUP BY tl.id, tl.ten, tl.mau, tl.bieu_tuong
        ORDER BY tl.id
    ')->fetchAll();
}

function lay_kho(PDO $db): array
{
    return $db->query('SELECT id, ten FROM kho ORDER BY id')->fetchAll();
}

function gan_so_sao(array $ds_sach): array
{
    foreach ($ds_sach as &$s) {
        $s['sao'] = $s['luot_danh_gia'] ? sao_tron($s['diem']) : 0;
    }
    unset($s);
    return $ds_sach;
}

function thong_ke_cua_hang(PDO $db): array
{
    $dong = $db->query('
        SELECT COUNT(*) AS so_sach,
               SUM(trich_doan IS NOT NULL OR link_wikisource IS NOT NULL) AS co_doc_thu
        FROM sach
        WHERE hien_thi = 1
    ')->fetch();
    $so_sach = (int) $dong['so_sach'];
    return [
        'so_sach'          => $so_sach,
        'phan_tram_doc_thu' => $so_sach ? (int) round($dong['co_doc_thu'] * 100 / $so_sach) : 0,
    ];
}

function lay_danh_sach_sach(PDO $db, string $tu_khoa = ''): array
{
    $sql = "
        SELECT s.id, s.ten, s.tac_gia, s.gia, s.so_luong, s.the_loai_id, s.kho_id,
               tl.ten AS ten_the_loai, tl.mau, k.ten AS ten_kho,
               (s.trich_doan IS NOT NULL AND s.trich_doan <> '') AS co_trich_doan,
               COALESCE(dg.luot, 0) AS luot_danh_gia, dg.diem
        FROM sach s
        JOIN the_loai tl ON tl.id = s.the_loai_id
        JOIN kho k       ON k.id  = s.kho_id
        LEFT JOIN (SELECT sach_id, COUNT(*) AS luot, AVG(so_sao) AS diem
                   FROM danh_gia
                   GROUP BY sach_id) dg ON dg.sach_id = s.id
        WHERE s.hien_thi = 1";
    $tham_so = [];
    if ($tu_khoa !== '') {
        $sql .= ' AND (s.ten LIKE ? OR s.tac_gia LIKE ?)';
        $mau_tim = '%' . addcslashes($tu_khoa, '%_\\') . '%';
        $tham_so = [$mau_tim, $mau_tim];
    }
    $sql .= ' ORDER BY s.id';

    $cau_lenh = $db->prepare($sql);
    $cau_lenh->execute($tham_so);
    return gan_so_sao($cau_lenh->fetchAll());
}

function lay_danh_muc(PDO $db, int $so_sach_mau = 3): array
{
    $ds_the_loai = $db->query('
        SELECT tl.id, tl.ten, tl.mau, tl.bieu_tuong,
               COUNT(s.id) AS so_sach,
               MIN(s.gia)  AS gia_thap,
               MAX(s.gia)  AS gia_cao
        FROM the_loai tl
        LEFT JOIN sach s ON s.the_loai_id = tl.id AND s.hien_thi = 1
        GROUP BY tl.id, tl.ten, tl.mau, tl.bieu_tuong
        ORDER BY tl.id
    ')->fetchAll();

    $ds_sach = $db->query('
        SELECT id, ten, tac_gia, the_loai_id
        FROM sach
        WHERE hien_thi = 1
        ORDER BY the_loai_id, id
    ')->fetchAll();
    $sach_theo_loai = [];
    foreach ($ds_sach as $s) {
        if (count($sach_theo_loai[$s['the_loai_id']] ?? []) < $so_sach_mau) {
            $sach_theo_loai[$s['the_loai_id']][] = $s;
        }
    }

    foreach ($ds_the_loai as &$tl) {
        $tl['sach_mau'] = $sach_theo_loai[$tl['id']] ?? [];
    }
    unset($tl);
    return $ds_the_loai;
}

const KIEU_SAP_XEP = [
    'moi'      => ['Mới nhất',       's.ngay_them DESC, s.id DESC'],
    'gia-tang' => ['Giá thấp → cao', 's.gia ASC, s.id ASC'],
    'gia-giam' => ['Giá cao → thấp', 's.gia DESC, s.id ASC'],
    'danh-gia' => ['Đánh giá cao',   'dg.diem DESC, dg.luot DESC, s.id ASC'],
];

function lay_the_loai(PDO $db, int $id): ?array
{
    $cau_lenh = $db->prepare('SELECT id, ten, mau, bieu_tuong FROM the_loai WHERE id = ?');
    $cau_lenh->execute([$id]);
    return $cau_lenh->fetch() ?: null;
}

function lay_sach_theo_loai(PDO $db, int $the_loai_id, string $sap_xep = 'moi'): array
{
    $order_by = (KIEU_SAP_XEP[$sap_xep] ?? KIEU_SAP_XEP['moi'])[1];

    $cau_lenh = $db->prepare("
        SELECT s.id, s.ten, s.tac_gia, s.gia, s.gia_cu, s.so_luong,
               tl.ten AS ten_the_loai, tl.mau, k.ten AS ten_kho,
               (s.trich_doan IS NOT NULL AND s.trich_doan <> '') AS co_trich_doan,
               COALESCE(dg.luot, 0) AS luot_danh_gia, dg.diem
        FROM sach s
        JOIN the_loai tl ON tl.id = s.the_loai_id
        JOIN kho k       ON k.id  = s.kho_id
        LEFT JOIN (SELECT sach_id, COUNT(*) AS luot, AVG(so_sao) AS diem
                   FROM danh_gia
                   GROUP BY sach_id) dg ON dg.sach_id = s.id
        WHERE s.the_loai_id = ? AND s.hien_thi = 1
        ORDER BY $order_by
    ");
    $cau_lenh->execute([$the_loai_id]);
    return gan_so_sao($cau_lenh->fetchAll());
}

function lay_chi_tiet_sach(PDO $db, int $id): ?array
{
    $cau_lenh = $db->prepare('
        SELECT s.*, tl.ten AS ten_the_loai, tl.mau, k.ten AS ten_kho
        FROM sach s
        JOIN the_loai tl ON tl.id = s.the_loai_id
        JOIN kho k       ON k.id  = s.kho_id
        WHERE s.id = ?
    ');
    $cau_lenh->execute([$id]);
    $sach = $cau_lenh->fetch();
    if (!$sach) {
        return null;
    }

    $sach['giam_gia'] = ($sach['gia_cu'] && $sach['gia_cu'] > $sach['gia'])
        ? (int) round(($sach['gia_cu'] - $sach['gia']) * 100 / $sach['gia_cu']) : 0;
    $sach['nam'] = ($sach['nam_sang_tac'] ?? '') !== '' ? $sach['nam_sang_tac'] : null;
    return $sach;
}

function lay_danh_gia_cua_sach(PDO $db, int $sach_id): array
{
    $cau_lenh = $db->prepare('
        SELECT dg.so_sao, dg.noi_dung, dg.ngay_danh_gia, dg.nguoi_dung_id, nd.ho_ten
        FROM danh_gia dg
        JOIN nguoi_dung nd ON nd.id = dg.nguoi_dung_id
        WHERE dg.sach_id = ?
        ORDER BY dg.ngay_danh_gia DESC
    ');
    $cau_lenh->execute([$sach_id]);
    return $cau_lenh->fetchAll();
}

function tinh_diem_danh_gia(array $ds_danh_gia): array
{
    $luot = count($ds_danh_gia);
    $phan_bo = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    $tong_sao = 0;
    foreach ($ds_danh_gia as $dg) {
        $phan_bo[(int) $dg['so_sao']]++;
        $tong_sao += (int) $dg['so_sao'];
    }
    $diem = $luot ? $tong_sao / $luot : null;
    return ['luot' => $luot, 'phan_bo' => $phan_bo, 'diem' => $diem, 'sao' => sao_tron($diem)];
}

function dem_so_da_ban(PDO $db, int $sach_id): int
{
    $cau_lenh = $db->prepare("
        SELECT COALESCE(SUM(ct.so_luong), 0)
        FROM chi_tiet_don_hang ct
        JOIN don_hang dh ON dh.id = ct.don_hang_id
        WHERE ct.sach_id = ? AND dh.trang_thai = 'da_giao'
    ");
    $cau_lenh->execute([$sach_id]);
    return (int) $cau_lenh->fetchColumn();
}

function lay_sach_lien_quan(PDO $db, int $sach_id, int $the_loai_id, int $so_luong = 4): array
{
    $cau_lenh = $db->prepare('
        SELECT s.id, s.ten, s.tac_gia, s.gia, tl.mau
        FROM sach s
        JOIN the_loai tl ON tl.id = s.the_loai_id
        WHERE s.hien_thi = 1 AND s.id <> ?
        ORDER BY (s.the_loai_id = ?) DESC, s.id
        LIMIT ' . (int) $so_luong
    );
    $cau_lenh->execute([$sach_id, $the_loai_id]);
    return $cau_lenh->fetchAll();
}

function lay_danh_gia_cua_toi(PDO $db, int $sach_id, int $nguoi_dung_id): ?array
{
    $cau_lenh = $db->prepare('SELECT so_sao, noi_dung FROM danh_gia WHERE sach_id = ? AND nguoi_dung_id = ?');
    $cau_lenh->execute([$sach_id, $nguoi_dung_id]);
    return $cau_lenh->fetch() ?: null;
}

function luu_danh_gia(PDO $db, int $sach_id, int $nguoi_dung_id, int $so_sao, string $noi_dung): string
{
    $noi_dung = $noi_dung === '' ? null : $noi_dung;

    if (lay_danh_gia_cua_toi($db, $sach_id, $nguoi_dung_id)) {
        $cau_lenh = $db->prepare('
            UPDATE danh_gia
            SET so_sao = ?, noi_dung = ?, ngay_danh_gia = NOW()
            WHERE sach_id = ? AND nguoi_dung_id = ?
        ');
        $cau_lenh->execute([$so_sao, $noi_dung, $sach_id, $nguoi_dung_id]);
        return 'sua';
    }

    $cau_lenh = $db->prepare('
        INSERT INTO danh_gia (sach_id, nguoi_dung_id, so_sao, noi_dung)
        VALUES (?, ?, ?, ?)
    ');
    $cau_lenh->execute([$sach_id, $nguoi_dung_id, $so_sao, $noi_dung]);
    return 'them';
}
