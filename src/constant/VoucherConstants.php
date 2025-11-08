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
        'VCR:STRLR50K' => [
            'nama_promo' => 'Promo Stroller',
            'potongan_harga' => 50000,
            'produk' => [
                '90010011' => 'Stroller Cocollate',
                '90010012' => 'Stroller BabyElle',
            ]
        ],
    ];
}