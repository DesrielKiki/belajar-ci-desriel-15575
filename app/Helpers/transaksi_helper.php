<?php

if (!function_exists('hitung_biaya_admin')) {
    function hitung_biaya_admin($total_harga)
    {
        $tarif = $total_harga > 20000000 ? 0.0075 : 0.005;

        return $total_harga * $tarif;
    }
}

if (!function_exists('hitung_diskon_kupon')) {
    function hitung_diskon_kupon($total_harga, $kupon_code)
    {
        $kupon = [
            'HEMAT' => 0.15,
            'SUPER' => 0.20,
        ];

        $kupon_code = strtoupper((string) $kupon_code);

        if (!isset($kupon[$kupon_code])) {
            return 0;
        }

        return $total_harga * $kupon[$kupon_code];
    }
}

if (!function_exists('hitung_cashback')) {
    function hitung_cashback($total_harga)
    {
        if ($total_harga <= 10000000) {
            return 0;
        }

        return $total_harga * 0.02;
    }
}
