<?php

return [
    [
        'name' => '优惠券中心',
        'url' => '',
        'fa_icon' => 'bx bxs-coupon',
        'lang' => [
            'chinese' => '优惠券中心',
            'chinese_tw' => '優惠券中心',
            'english' => 'Coupons',
        ],
        'child' => [
            [
                'name' => '领券中心',
                'url' => 'QingjiyunCoupon://Index/center',
                'fa_icon' => '',
                'lang' => [
                    'chinese' => '领券中心',
                    'chinese_tw' => '領券中心',
                    'english' => 'Coupon Center',
                ],
                'child' => [],
            ],
            [
                'name' => '我的优惠券',
                'url' => 'QingjiyunCoupon://Index/wallet',
                'fa_icon' => '',
                'lang' => [
                    'chinese' => '我的优惠券',
                    'chinese_tw' => '我的優惠券',
                    'english' => 'My Coupons',
                ],
                'child' => [],
            ],
            [
                'name' => '每日签到',
                'url' => 'QingjiyunCoupon://Index/signin',
                'fa_icon' => '',
                'lang' => [
                    'chinese' => '每日签到',
                    'chinese_tw' => '每日簽到',
                    'english' => 'Check-in',
                ],
                'child' => [],
            ],
        ],
    ],
];
