<?php

namespace addons\qingjiyun_coupon\common;

use think\Db;

class SigninService
{
    public function checkIn($uid)
    {
        $uid = intval($uid);
        if ($uid <= 0) {
            return ['ok' => false, 'message' => '请先登录', 'data' => []];
        }

        $today = date('Y-m-d');
        if (Db::name('qingjiyun_coupon_signin_log')->where('uid', $uid)->where('signin_date', $today)->find()) {
            return ['ok' => false, 'message' => '今天已经签到过了', 'data' => $this->summary($uid)];
        }

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $previous = Db::name('qingjiyun_coupon_signin_log')->where('uid', $uid)
            ->where('signin_date', $yesterday)->find();
        $streak = $previous ? intval($previous['streak']) + 1 : 1;
        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';

        try {
            Db::name('qingjiyun_coupon_signin_log')->insert([
                'uid' => $uid,
                'signin_date' => $today,
                'streak' => $streak,
                'rewards' => '[]',
                'create_time' => time(),
                'ip' => $ip,
            ]);
        } catch (\Exception $exception) {
            return ['ok' => false, 'message' => '今天已经签到过了', 'data' => $this->summary($uid)];
        }

        $cycleStart = date('Y-m-d', strtotime($today . ' -' . ($streak - 1) . ' days'));
        $rewards = [];
        $rules = Db::name('qingjiyun_coupon_signin_rule')->alias('r')
            ->leftJoin('qingjiyun_coupon_template t', 'r.template_id = t.id')
            ->field('r.*,t.title')
            ->where('r.enabled', 1)->where('r.milestone', $streak)->select();
        $couponService = new CouponService();
        foreach ($rules as $rule) {
            $reference = 'signin_rule_' . $rule['id'] . '_' . $cycleStart;
            $issued = $couponService->issue($rule['template_id'], $uid, 'signin', $reference, $ip);
            if ($issued['ok']) {
                $rewards[] = ['rule_id' => $rule['id'], 'title' => $rule['title'], 'code' => $issued['data']['code']];
            }
        }

        Db::name('qingjiyun_coupon_signin_log')->where('uid', $uid)->where('signin_date', $today)
            ->update(['rewards' => json_encode($rewards, JSON_UNESCAPED_UNICODE)]);

        $message = empty($rewards) ? '签到成功，已连续签到 ' . $streak . ' 天' : '签到成功，奖励已发放到券包';

        return ['ok' => true, 'message' => $message, 'data' => $this->summary($uid)];
    }

    public function summary($uid)
    {
        $uid = intval($uid);
        $today = date('Y-m-d');
        $last = Db::name('qingjiyun_coupon_signin_log')->where('uid', $uid)
            ->order('signin_date', 'desc')->find();
        $streak = $last && ($last['signin_date'] === $today || $last['signin_date'] === date('Y-m-d', strtotime('-1 day')))
            ? intval($last['streak']) : 0;
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $days = Db::name('qingjiyun_coupon_signin_log')->where('uid', $uid)
            ->where('signin_date', 'between', [$monthStart, $monthEnd])->column('signin_date');
        $todayLog = Db::name('qingjiyun_coupon_signin_log')->where('uid', $uid)
            ->where('signin_date', $today)->field('rewards')->find();
        $todayRewards = [];
        if ($todayLog && !empty($todayLog['rewards'])) {
            $decodedRewards = json_decode($todayLog['rewards'], true);
            if (is_array($decodedRewards)) {
                $todayRewards = $decodedRewards;
            }
        }
        $rules = Db::name('qingjiyun_coupon_signin_rule')->alias('r')
            ->leftJoin('qingjiyun_coupon_template t', 'r.template_id = t.id')
            ->field('r.milestone,t.title')->where('r.enabled', 1)->order('r.milestone', 'asc')->select();

        return [
            'today' => $today,
            'streak' => $streak,
            'signed_today' => in_array($today, $days, true),
            'days' => $days,
            'today_rewards' => $todayRewards,
            'rules' => $rules,
        ];
    }
}
