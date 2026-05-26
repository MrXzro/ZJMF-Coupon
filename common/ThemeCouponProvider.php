<?php

namespace addons\qingjiyun_coupon\common;

use think\Db;

class ThemeCouponProvider
{
    public static function usableCoupons($uid)
    {
        $uid = intval($uid);
        if ($uid <= 0) {
            return [];
        }

        try {
            $rows = Db::name('qingjiyun_coupon_user')->alias('u')
                ->leftJoin('qingjiyun_coupon_template t', 'u.template_id = t.id')
                ->field('u.id,u.code,u.expires_at,t.title,t.type,t.value,t.require_paid,t.enabled,t.requires,t.requires_exist')
                ->where('u.uid', $uid)
                ->where('u.status', 'unused')
                ->select();
        } catch (\Throwable $exception) {
            return [];
        }

        if (is_object($rows) && method_exists($rows, 'toArray')) {
            $rows = $rows->toArray();
        }
        if (!is_array($rows)) {
            return [];
        }

        $productIds = [];
        foreach ($rows as $coupon) {
            if (is_array($coupon)) {
                $productIds = array_merge($productIds, self::numbersFromCsv(isset($coupon['requires']) ? $coupon['requires'] : ''));
            }
        }
        $productNames = self::productNames(array_values(array_unique(array_filter($productIds))));

        $now = time();
        $hasPaid = null;
        $usable = [];
        foreach ($rows as $coupon) {
            if (!is_array($coupon)) {
                continue;
            }
            if (intval(isset($coupon['enabled']) ? $coupon['enabled'] : 0) !== 1) {
                continue;
            }
            if (intval(isset($coupon['expires_at']) ? $coupon['expires_at'] : 0) > 0
                && intval($coupon['expires_at']) < $now) {
                continue;
            }
            if (intval(isset($coupon['require_paid']) ? $coupon['require_paid'] : 0) === 1) {
                if ($hasPaid === null) {
                    $hasPaid = self::hasPaidInvoice($uid);
                }
                if (!$hasPaid) {
                    continue;
                }
            }

            $usable[] = [
                'id' => intval(isset($coupon['id']) ? $coupon['id'] : 0),
                'code' => (string) (isset($coupon['code']) ? $coupon['code'] : ''),
                'expires_at' => intval(isset($coupon['expires_at']) ? $coupon['expires_at'] : 0),
                'title' => (string) (isset($coupon['title']) ? $coupon['title'] : ''),
                'type' => (string) (isset($coupon['type']) ? $coupon['type'] : ''),
                'value' => floatval(isset($coupon['value']) ? $coupon['value'] : 0),
                'require_paid' => intval(isset($coupon['require_paid']) ? $coupon['require_paid'] : 0),
                'requires' => (string) (isset($coupon['requires']) ? $coupon['requires'] : ''),
                'requires_exist' => intval(isset($coupon['requires_exist']) ? $coupon['requires_exist'] : 0),
                'requires_text' => self::requiresText($coupon, $productNames),
            ];
        }

        usort($usable, function ($left, $right) {
            $leftScore = self::score($left);
            $rightScore = self::score($right);
            if ($leftScore === $rightScore) {
                return intval($right['id']) - intval($left['id']);
            }

            return $leftScore < $rightScore ? 1 : -1;
        });

        return $usable;
    }

    private static function score($coupon)
    {
        $type = isset($coupon['type']) ? $coupon['type'] : '';
        $value = floatval(isset($coupon['value']) ? $coupon['value'] : 0);
        if ($type === 'free') {
            return 1000000;
        }
        if ($type === 'fixed') {
            return 500000 + $value;
        }
        if ($type === 'percent') {
            return 300000 + $value;
        }
        if ($type === 'override') {
            return 400000 - $value;
        }

        return 0;
    }

    private static function hasPaidInvoice($uid)
    {
        try {
            return Db::name('invoices')->where('uid', intval($uid))
                ->where('status', 'Paid')->where('total', '>', 0)->count() > 0;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private static function requiresText($coupon, array $productNames)
    {
        $ids = self::numbersFromCsv(isset($coupon['requires']) ? $coupon['requires'] : '');
        if (empty($ids)) {
            return '';
        }

        $names = [];
        foreach ($ids as $id) {
            $names[] = isset($productNames[$id]) ? $productNames[$id] : ('产品 #' . $id);
        }
        if (count($names) > 2) {
            $text = implode('、', array_slice($names, 0, 2)) . ' 等 ' . count($names) . ' 个产品';
        } else {
            $text = implode('、', $names);
        }

        return (intval(isset($coupon['requires_exist']) ? $coupon['requires_exist'] : 0) === 1 ? '需已购：' : '需同购：') . $text;
    }

    private static function productNames(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        try {
            $rows = Db::name('products')->where('id', 'in', $ids)->field('id,name')->select();
        } catch (\Throwable $exception) {
            return [];
        }
        if (is_object($rows) && method_exists($rows, 'toArray')) {
            $rows = $rows->toArray();
        }
        if (!is_array($rows)) {
            return [];
        }

        $names = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $names[intval(isset($row['id']) ? $row['id'] : 0)] = (string) (isset($row['name']) ? $row['name'] : '');
            }
        }

        return $names;
    }

    private static function numbersFromCsv($value)
    {
        $parts = preg_split('/[\s,，]+/u', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_filter(array_map('intval', $parts))));
    }
}
