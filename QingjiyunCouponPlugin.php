<?php

namespace addons\qingjiyun_coupon;

use addons\qingjiyun_coupon\common\CouponService;
use addons\qingjiyun_coupon\common\RiskService;
use app\admin\lib\Plugin;
use think\Db;

class QingjiyunCouponPlugin extends Plugin
{
    public $info = [
        'name' => 'QingjiyunCoupon',
        'title' => '轻极云优惠券',
        'description' => '原生优惠码驱动的领券、批量发券、新人券和签到奖励插件',
        'status' => 1,
        'author' => '轻极云',
        'version' => '1.0.21',
        'module' => 'addons',
        'lang' => [
            'chinese' => '轻极云优惠券',
            'chinese_tw' => '輕極雲優惠券',
            'english' => 'Qingjiyun Coupon',
        ],
    ];

    public function install()
    {
        $prefix = $this->tablePrefix();
        $tables = [
            "CREATE TABLE IF NOT EXISTS `{$prefix}qingjiyun_coupon_template` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(100) NOT NULL DEFAULT '',
                `description` varchar(255) NOT NULL DEFAULT '',
                `type` varchar(30) NOT NULL DEFAULT 'fixed',
                `value` decimal(10,2) NOT NULL DEFAULT '0.00',
                `cycles` text NOT NULL,
                `appliesto` text NOT NULL,
                `requires` text NOT NULL,
                `requires_exist` tinyint(1) NOT NULL DEFAULT '0',
                `recurring` tinyint(1) NOT NULL DEFAULT '0',
                `recurfor` int(10) NOT NULL DEFAULT '0',
                `valid_days` int(10) NOT NULL DEFAULT '30',
                `quota` int(10) NOT NULL DEFAULT '0',
                `issued_count` int(10) NOT NULL DEFAULT '0',
                `new_user_auto` tinyint(1) NOT NULL DEFAULT '0',
                `require_paid` tinyint(1) NOT NULL DEFAULT '0',
                `once_per_client` tinyint(1) NOT NULL DEFAULT '1',
                `enabled` tinyint(1) NOT NULL DEFAULT '1',
                `create_time` int(11) NOT NULL DEFAULT '0',
                `update_time` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `enabled` (`enabled`),
                KEY `new_user_auto` (`new_user_auto`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='轻极云优惠券模板'",
            "CREATE TABLE IF NOT EXISTS `{$prefix}qingjiyun_coupon_user` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `uid` int(10) NOT NULL DEFAULT '0',
                `template_id` int(10) NOT NULL DEFAULT '0',
                `promo_id` int(10) NOT NULL DEFAULT '0',
                `code` varchar(50) NOT NULL DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'unused',
                `source` varchar(30) NOT NULL DEFAULT 'manual',
                `source_ref` varchar(100) NOT NULL DEFAULT '',
                `issued_at` int(11) NOT NULL DEFAULT '0',
                `expires_at` int(11) NOT NULL DEFAULT '0',
                `used_at` int(11) NOT NULL DEFAULT '0',
                `order_id` int(10) NOT NULL DEFAULT '0',
                `created_ip` varchar(50) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`),
                UNIQUE KEY `code` (`code`),
                KEY `uid_status` (`uid`,`status`),
                KEY `template_uid` (`template_id`,`uid`),
                KEY `promo_id` (`promo_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='轻极云用户领券记录'",
            "CREATE TABLE IF NOT EXISTS `{$prefix}qingjiyun_coupon_signin_rule` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `milestone` int(10) NOT NULL DEFAULT '1',
                `template_id` int(10) NOT NULL DEFAULT '0',
                `enabled` tinyint(1) NOT NULL DEFAULT '1',
                `create_time` int(11) NOT NULL DEFAULT '0',
                `update_time` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `milestone` (`milestone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='轻极云签到奖励规则'",
            "CREATE TABLE IF NOT EXISTS `{$prefix}qingjiyun_coupon_signin_log` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `uid` int(10) NOT NULL DEFAULT '0',
                `signin_date` date NOT NULL,
                `streak` int(10) NOT NULL DEFAULT '1',
                `rewards` text NOT NULL,
                `create_time` int(11) NOT NULL DEFAULT '0',
                `ip` varchar(50) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uid_date` (`uid`,`signin_date`),
                KEY `uid_streak` (`uid`,`streak`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='轻极云用户签到记录'",
            "CREATE TABLE IF NOT EXISTS `{$prefix}qingjiyun_coupon_risk_log` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `uid` int(10) NOT NULL DEFAULT '0',
                `action` varchar(30) NOT NULL DEFAULT '',
                `allowed` tinyint(1) NOT NULL DEFAULT '0',
                `ip` varchar(50) NOT NULL DEFAULT '',
                `email_domain` varchar(100) NOT NULL DEFAULT '',
                `reason` varchar(255) NOT NULL DEFAULT '',
                `create_time` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `action_ip_time` (`action`,`ip`,`create_time`),
                KEY `uid_action` (`uid`,`action`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='轻极云优惠券风控记录'",
        ];

        foreach ($tables as $sql) {
            Db::execute($sql);
        }
        if (!Db::name('configuration')->where('setting', CouponService::CODE_PREFIX_SETTING)->find()) {
            Db::name('configuration')->insert([
                'setting' => CouponService::CODE_PREFIX_SETTING,
                'value' => CouponService::DEFAULT_CODE_PREFIX,
            ]);
        }

        return true;
    }

    public function uninstall()
    {
        Db::name('promo_code')->where('notes', 'like', '[qingjiyun_coupon template:%')->delete();
        Db::name('promo_code')->where('code', 'like', 'qingjiyun\_%')->delete();
        Db::name('configuration')->where('setting', CouponService::CODE_PREFIX_SETTING)->delete();

        $prefix = $this->tablePrefix();
        foreach (['risk_log', 'signin_log', 'signin_rule', 'user', 'template'] as $table) {
            Db::execute("DROP TABLE IF EXISTS `{$prefix}qingjiyun_coupon_{$table}`");
        }

        return true;
    }

    public function clientLogin()
    {
        $uid = intval(request()->uid ?? 0);
        if ($uid <= 0) {
            return;
        }

        try {
            (new RiskService())->grantNewUserCoupons($uid);
        } catch (\Throwable $exception) {
            // Login must remain available even when a marketing task cannot run.
        }
    }

    public function afterCron()
    {
        (new CouponService())->syncStatuses();
    }

    private function tablePrefix()
    {
        $prefix = (string) config('database.prefix');
        if ($prefix === '') {
            $prefix = 'shd_';
        }

        return preg_replace('/[^a-zA-Z0-9_]/', '', $prefix);
    }
}
