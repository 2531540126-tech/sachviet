<?php

function gan_nhan_loc(array $ds_sach): array
{
    foreach ($ds_sach as &$s) {
        $s['loc'] = [
            'cat' => (string) $s['the_loai_id'],
            'gia' => muc_gia((int) $s['gia']),
            'kho' => (string) $s['kho_id'],
            'sao' => (string) $s['sao'],
        ];
    }
    unset($s);
    return $ds_sach;
}

function dem(array $ds_sach, string $nhom, string $gia_tri): int
{
    $n = 0;
    foreach ($ds_sach as $s) {
        if ($s['loc'][$nhom] === $gia_tri) {
            $n++;
        }
    }
    return $n;
}

function tao_nhom_loc(array $ds_sach, array $ds_the_loai, array $ds_kho): array
{
    $lua_chon_sao = ['5' => '★★★★★ 5 sao', '4' => '★★★★ 4 sao', '3' => '★★★ 3 sao'];
    foreach (['2' => '★★ 2 sao', '1' => '★ 1 sao', '0' => 'Chưa có đánh giá'] as $k => $nhan_sao) {
        if (dem($ds_sach, 'sao', (string) $k) > 0) {
            $lua_chon_sao[$k] = $nhan_sao;
        }
    }
    return [
        'cat' => ['name' => 'f-cat', 'tieu_de' => 'Category — Thể loại', 'tat_ca' => 'Tất cả thể loại',
                  'lua_chon' => array_column($ds_the_loai, 'ten', 'id')],
        'gia' => ['name' => 'f-gia', 'tieu_de' => 'Price — Mức giá', 'tat_ca' => 'Tất cả mức giá',
                  'lua_chon' => ['thap' => 'Dưới 70.000₫', 'trung' => '70.000₫ – 120.000₫', 'cao' => 'Trên 120.000₫']],
        'kho' => ['name' => 'f-kho', 'tieu_de' => 'Location — Kho hàng', 'tat_ca' => 'Tất cả kho',
                  'lua_chon' => array_column($ds_kho, 'ten', 'id')],
        'sao' => ['name' => 'f-sao', 'tieu_de' => 'Rating — Đánh giá', 'tat_ca' => 'Tất cả đánh giá',
                  'lua_chon' => $lua_chon_sao],
    ];
}

function tao_css_bo_loc(array $nhom_loc, array $ds_sach): array
{
    $to_dam = [];
    $an = [];
    foreach ($nhom_loc as $nhom => $g) {
        $to_dam[] = "#loc-$nhom-all:checked ~ .catalog .f-row[for=\"loc-$nhom-all\"] .f-box";
        foreach ($g['lua_chon'] as $gia_tri => $ten) {
            $to_dam[] = "#loc-$nhom-$gia_tri:checked ~ .catalog .f-row[for=\"loc-$nhom-$gia_tri\"] .f-box";
            $an[] = "#loc-$nhom-$gia_tri:checked ~ .catalog .grid .card:not([data-$nhom=\"$gia_tri\"])";
        }
    }

    $to_hop = [[]];
    foreach ($nhom_loc as $nhom => $g) {
        $moi = [];
        foreach ($to_hop as $th) {
            foreach (array_merge(['*'], array_map('strval', array_keys($g['lua_chon']))) as $v) {
                $th[$nhom] = $v;
                $moi[] = $th;
            }
        }
        $to_hop = $moi;
    }
    $rong = [];
    foreach ($to_hop as $th) {
        $co_sach = false;
        foreach ($ds_sach as $s) {
            $khop = true;
            foreach ($th as $nhom => $v) {
                if ($v !== '*' && $s['loc'][$nhom] !== $v) {
                    $khop = false;
                    break;
                }
            }
            if ($khop) {
                $co_sach = true;
                break;
            }
        }
        if (!$co_sach) {
            $rong[implode('|', $th)] = $th;
        }
    }

    $css_rong = [];
    foreach ($rong as $th) {
        $toi_gian = true;
        foreach ($th as $nhom => $v) {
            if ($v === '*') {
                continue;
            }
            $bot_mot = $th;
            $bot_mot[$nhom] = '*';
            if (isset($rong[implode('|', $bot_mot)])) {
                $toi_gian = false;
                break;
            }
        }
        if ($toi_gian) {
            $dang_chon = [];
            foreach ($th as $nhom => $v) {
                if ($v !== '*') {
                    $dang_chon[] = "#loc-$nhom-$v:checked";
                }
            }
            $css_rong[] = ($dang_chon ? implode(' ~ ', $dang_chon) . ' ~ ' : '#danh-sach ') . '.catalog .grid-empty';
        }
    }

    return ['to_dam' => $to_dam, 'an' => $an, 'rong' => $css_rong];
}
