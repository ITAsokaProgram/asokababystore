<?php

namespace Asoka\Constant;

class VoucherConstants
{
    public const VOUCHERS = [
        'VCR:PROMOSGM10K' => [
            'nama_promo' => 'Promo Pembelian SGM 900G',
            'potongan_harga' => 10000,
            'produk' => [
                '80010001' => 'SGM EKSPLOR 1+ MADU 900G',
                '80010002' => 'SGM EKSPLOR 3+ COKLAT 900G',
                '80010003' => 'SGM EKSPLOR 5+ VANILA 900G',
            ]
        ],
        'VCR:DISKON5K' => [
            'nama_promo' => 'Promo Susu Lain',
            'potongan_harga' => 5000,
            'produk' => [
                '90010011' => 'PRODUK A',
                '90010012' => 'PRODUK B',
            ]
        ],
    ];
}