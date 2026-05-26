<?php

namespace addons\qingjiyun_coupon\common;

use think\Db;

class RiskService
{
    const NEW_USER_WINDOW = 300;
    const IP_LIMIT_WINDOW = 86400;

    private $temporaryDomains = [
        '10minutemail.com', '10minutemail.net', '20minutemail.com', '33mail.com',
        'dispostable.com', 'emailondeck.com', 'fakeinbox.com', 'fakemail.net',
        'getairmail.com', 'getnada.com', 'guerrillamail.com', 'guerrillamail.net',
        'inboxkitten.com', 'maildrop.cc', 'mailinator.com', 'mailnesia.com',
        'mailnull.com', 'mintemail.com', 'mohmal.com', 'mytemp.email',
        'nada.email', 'sharklasers.com', 'spam4.me', 'spamgourmet.com',
        'temp-mail.org', 'tempail.com', 'tempemail.net', 'tempmail.com',
        'tempmailaddress.com', 'throwawaymail.com', 'trashmail.com', 'trashmail.net',
        'yopmail.com', 'yopmail.fr', 'yopmail.net',
    ];

    public function grantNewUserCoupons($uid)
    {
        $uid = intval($uid);
        $client = Db::name('clients')->where('id', $uid)->field('id,email,create_time')->find();
        if (!$client) {
            return;
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        $domain = strtolower(substr(strrchr((string) $client['email'], '@') ?: '', 1));
        if (time() - intval($client['create_time']) > self::NEW_USER_WINDOW) {
            $this->record($uid, $ip, $domain, false, '超过注册后 5 分钟新人窗口');
            return;
        }
        if (in_array($domain, $this->temporaryDomains, true)) {
            $this->record($uid, $ip, $domain, false, '临时邮箱域名被拦截');
            return;
        }
        if ($ip !== '' && Db::name('qingjiyun_coupon_risk_log')
                ->where('action', 'new_user')
                ->where('ip', $ip)
                ->where('allowed', 1)
                ->where('create_time', '>', time() - self::IP_LIMIT_WINDOW)
                ->count() > 0) {
            $this->record($uid, $ip, $domain, false, '同 IP 24 小时内已领取新人券');
            return;
        }

        $templates = Db::name('qingjiyun_coupon_template')
            ->where('enabled', 1)->where('new_user_auto', 1)->select();
        $success = 0;
        $messages = [];
        $couponService = new CouponService();
        foreach ($templates as $template) {
            $result = $couponService->issue($template['id'], $uid, 'new_user', 'first_login', $ip);
            if ($result['ok']) {
                $success++;
            } else {
                $messages[] = $result['message'];
            }
        }

        if ($success > 0) {
            $this->record($uid, $ip, $domain, true, '新人券发放成功，共 ' . $success . ' 张');
        } elseif (!empty($templates)) {
            $this->record($uid, $ip, $domain, false, implode('；', array_unique($messages)));
        }
    }

    private function record($uid, $ip, $domain, $allowed, $reason)
    {
        $reason = (string) $reason;
        $reason = function_exists('mb_substr')
            ? mb_substr($reason, 0, 255, 'UTF-8')
            : substr($reason, 0, 255);
        Db::name('qingjiyun_coupon_risk_log')->insert([
            'uid' => intval($uid),
            'action' => 'new_user',
            'allowed' => $allowed ? 1 : 0,
            'ip' => $ip,
            'email_domain' => $domain,
            'reason' => $reason,
            'create_time' => time(),
        ]);
    }
}
