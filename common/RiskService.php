<?php

namespace addons\qingjiyun_coupon\common;

use think\Db;

class RiskService
{
    const AUTO_DELAY = 86400;
    const IP_LIMIT_WINDOW = 86400;
    const CRON_BATCH_SIZE = 300;

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
        CouponService::ensureSchema();

        $uid = intval($uid);
        $client = Db::name('clients')->where('id', $uid)->field('id,email,create_time')->find();
        if (!$client) {
            return;
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        $domain = $this->emailDomain($client);
        if ($this->isTemporaryDomain($domain)) {
            $this->record($uid, $ip, $domain, false, '临时邮箱域名被拦截');
            return;
        }
        if ($ip !== '' && Db::name('qingjiyun_coupon_risk_log')
                ->where('action', 'new_user')
                ->where('ip', $ip)
                ->where('allowed', 1)
                ->where('create_time', '>', time() - self::IP_LIMIT_WINDOW)
                ->count() > 0) {
            $this->record($uid, $ip, $domain, false, '同 IP 24 小时内已发放新人券');
            return;
        }

        $templates = $this->newUserAutoTemplates();
        $success = 0;
        $messages = [];
        foreach ($templates as $template) {
            $result = $this->grantTemplateIfDue($client, $template, $ip);
            if ($result['ok']) {
                $success++;
            } elseif (empty($result['skip'])) {
                $messages[] = $result['message'];
            }
        }

        if ($success > 0) {
            $this->record($uid, $ip, $domain, true, '新人券自动发放成功，共 ' . $success . ' 张');
        } elseif (!empty($templates) && !empty($messages)) {
            $this->record($uid, $ip, $domain, false, implode('；', array_unique($messages)));
        }
    }

    public function grantDelayedNewUserCoupons($limit = self::CRON_BATCH_SIZE)
    {
        CouponService::ensureSchema();

        $templates = $this->newUserAutoTemplates();
        if (empty($templates)) {
            return;
        }

        $limit = max(1, intval($limit));
        foreach ($templates as $template) {
            foreach ($this->delayedClientsForTemplate($template, $limit) as $client) {
                $this->grantTemplateIfDue($client, $template, '');
            }
        }
    }

    private function newUserAutoTemplates()
    {
        return $this->rowsToArray(
            Db::name('qingjiyun_coupon_template')
                ->where('enabled', 1)
                ->where('new_user_only', 1)
                ->where('new_user_auto', 1)
                ->select()
        );
    }

    private function delayedClientsForTemplate($template, $limit)
    {
        $now = time();
        $query = Db::name('clients')
            ->field('id,email,create_time')
            ->where('create_time', '>', 0)
            ->where('create_time', '<=', $now - self::AUTO_DELAY);

        $days = intval(isset($template['new_user_days']) ? $template['new_user_days'] : 7);
        if ($days <= 0) {
            $days = 7;
        }
        $query->where('create_time', '>=', $now - $days * 86400);

        return $this->rowsToArray($query->order('id', 'desc')->limit($limit)->select());
    }

    private function grantTemplateIfDue($client, $template, $ip)
    {
        $uid = intval(isset($client['id']) ? $client['id'] : 0);
        $templateId = intval(isset($template['id']) ? $template['id'] : 0);
        if ($uid <= 0 || $templateId <= 0) {
            return $this->result(false, '用户或模板无效');
        }

        if ($this->hasAnyTemplateRecord($uid, $templateId)) {
            return $this->result(false, '用户已领取过该新人券', true);
        }

        $createTime = $this->clientCreateTime($client);
        if ($createTime <= 0) {
            return $this->result(false, '无法确认注册时间');
        }
        if (time() < $createTime + self::AUTO_DELAY) {
            return $this->result(false, '未到注册满 1 天自动发放时间', true);
        }

        $domain = $this->emailDomain($client);
        if ($this->isTemporaryDomain($domain)) {
            return $this->result(false, '临时邮箱域名被拦截');
        }

        $couponService = new CouponService();
        $conditionMessage = $couponService->claimConditionMessage($uid, $template, $client);
        if ($conditionMessage !== '') {
            return $this->result(false, $conditionMessage);
        }

        return $couponService->issue($templateId, $uid, 'new_user_auto', 'after_1_day', $ip);
    }

    private function hasAnyTemplateRecord($uid, $templateId)
    {
        return Db::name('qingjiyun_coupon_user')
            ->where('uid', intval($uid))
            ->where('template_id', intval($templateId))
            ->find() ? true : false;
    }

    private function emailDomain($client)
    {
        return strtolower(substr(strrchr((string) (isset($client['email']) ? $client['email'] : ''), '@') ?: '', 1));
    }

    private function isTemporaryDomain($domain)
    {
        return $domain !== '' && in_array($domain, $this->temporaryDomains, true);
    }

    private function clientCreateTime($client)
    {
        $value = isset($client['create_time']) ? $client['create_time'] : 0;
        if (is_numeric($value)) {
            return intval($value);
        }

        $timestamp = strtotime((string) $value);
        return $timestamp ? intval($timestamp) : 0;
    }

    private function rowsToArray($rows)
    {
        if (is_object($rows) && method_exists($rows, 'toArray')) {
            return $rows->toArray();
        }

        return is_array($rows) ? $rows : [];
    }

    private function result($ok, $message, $skip = false)
    {
        return ['ok' => $ok, 'message' => $message, 'skip' => $skip ? 1 : 0];
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
