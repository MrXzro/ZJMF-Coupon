<?php

namespace addons\qingjiyun_coupon\controller\clientarea;

use addons\qingjiyun_coupon\common\CouponService;
use addons\qingjiyun_coupon\common\SigninService;
use addons\qingjiyun_coupon\common\ThemeCouponProvider;

class IndexController extends \app\home\controller\PluginHomeBaseController
{
    public function center()
    {
        $uid = $this->currentUid();
        if ($uid <= 0) {
            return $this->loginResponse('请先登录后访问领券中心');
        }

        $service = new CouponService();
        $this->assign('Title', '领券中心');
        $this->assign('Templates', $service->centerTemplates($uid));
        $this->assign('ClaimUrl', shd_addon_url('QingjiyunCoupon://Index/claim', [], true));
        $this->assign('WalletUrl', shd_addon_url('QingjiyunCoupon://Index/wallet', [], true));
        $this->assign('CartUrl', '/cart?action=viewcart');

        return $this->fetch('/center');
    }

    public function claim()
    {
        $uid = $this->currentUid();
        if ($uid <= 0) {
            return $this->loginResponse('请先登录后领取优惠券');
        }

        if (!request()->isPost()) {
            return json(['status' => 405, 'msg' => '请求方式错误', 'data' => []]);
        }

        try {
            $service = new CouponService();
            $templateId = intval(input('post.template_id', input('template_id', 0)));
            $result = $service->claimFromCenter($templateId, $uid);
            $templates = $service->centerTemplates($uid);
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '领取失败：' . $exception->getMessage(), 'data' => []]);
        }

        return json([
            'status' => $result['ok'] ? 200 : 400,
            'msg' => $result['message'],
            'data' => [
                'coupon' => $result['data'],
                'templates' => $templates,
            ],
        ]);
    }

    public function wallet()
    {
        $uid = $this->currentUid();
        if ($uid <= 0) {
            return $this->loginResponse('请先登录后访问我的优惠券');
        }

        $api = trim((string) input('qjy_api', ''));
        if ($api === 'validate') {
            return $this->validateCoupon($uid);
        }
        if ($api === 'coupons') {
            return json([
                'status' => 200,
                'msg' => 'success',
                'data' => ['coupons' => ThemeCouponProvider::usableCoupons($uid)],
            ]);
        }

        $coupons = (new CouponService())->userCoupons($uid);

        $this->assign('Title', '我的优惠券');
        $this->assign('Coupons', $coupons);
        $this->assign('ValidateUrl', shd_addon_url('QingjiyunCoupon://Index/wallet', ['qjy_api' => 'validate'], true));
        $this->assign('SigninUrl', shd_addon_url('QingjiyunCoupon://Index/signin', [], true));

        return $this->fetch('/wallet');
    }

    public function signin()
    {
        $uid = $this->currentUid();
        if ($uid <= 0) {
            return $this->loginResponse('请先登录后签到');
        }

        $this->assign('Title', '每日签到');
        $this->assign('Summary', (new SigninService())->summary($uid));
        $this->assign('CheckinUrl', shd_addon_url('QingjiyunCoupon://Index/checkin', [], true));
        $this->assign('WalletUrl', shd_addon_url('QingjiyunCoupon://Index/wallet', [], true));

        return $this->fetch('/signin');
    }

    public function validateCode()
    {
        $uid = $this->currentUid();
        if ($uid <= 0) {
            return $this->loginResponse('请先登录后校验优惠券');
        }

        return $this->validateCoupon($uid);
    }

    private function validateCoupon($uid)
    {
        try {
            $code = trim((string) input('post.code', input('code', '')));
            $result = (new CouponService())->validateOwnedCode($uid, $code);
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '优惠券校验失败：' . $exception->getMessage(), 'data' => []]);
        }

        return json([
            'status' => $result['ok'] ? 200 : 400,
            'msg' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function orderedCoupons()
    {
        return $this->couponList();
    }

    public function coupons()
    {
        return $this->couponList();
    }

    private function couponList()
    {
        $uid = $this->currentUid();
        if ($uid <= 0) {
            return $this->loginResponse('请先登录后查看可用优惠券');
        }

        try {
            return json([
                'status' => 200,
                'msg' => 'success',
                'data' => ['coupons' => ThemeCouponProvider::usableCoupons($uid)],
            ]);
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '获取可用优惠券失败：' . $exception->getMessage(), 'data' => ['coupons' => []]]);
        }
    }

    private function usableCoupons($coupons)
    {
        $usable = [];
        $now = time();
        foreach ($coupons as $coupon) {
            if ($coupon['status'] !== CouponService::STATUS_UNUSED) {
                continue;
            }
            if (intval($coupon['expires_at']) > 0 && intval($coupon['expires_at']) < $now) {
                continue;
            }
            $usable[] = $coupon;
        }

        usort($usable, function ($left, $right) {
            $leftScore = $this->couponScore($left);
            $rightScore = $this->couponScore($right);
            if ($leftScore === $rightScore) {
                return intval($right['id']) - intval($left['id']);
            }
            return $leftScore < $rightScore ? 1 : -1;
        });

        return $usable;
    }

    private function couponScore($coupon)
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

    public function checkin()
    {
        $uid = $this->currentUid();
        if ($uid <= 0) {
            return $this->loginResponse('请先登录后签到');
        }

        if (!request()->isPost()) {
            return json(['status' => 405, 'msg' => '请求方式错误']);
        }

        try {
            $result = (new SigninService())->checkIn($uid);
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '签到失败：' . $exception->getMessage(), 'data' => []]);
        }

        return json([
            'status' => $result['ok'] ? 200 : 400,
            'msg' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    private function currentUid()
    {
        return isset($this->request->uid) ? intval($this->request->uid) : 0;
    }

    private function loginResponse($message)
    {
        if ($this->wantsJson()) {
            return json(['status' => 401, 'msg' => $message, 'data' => []]);
        }

        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>请先登录</title><style>'
            . 'body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f5f9ff;color:#172554;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","Microsoft YaHei",Arial,sans-serif;}'
            . '.qjy-login-notice{width:min(420px,calc(100% - 32px));padding:34px 30px;border:1px solid #dbeafe;border-radius:18px;background:#fff;box-shadow:0 18px 48px rgba(37,99,235,.12);text-align:center;}'
            . '.qjy-login-icon{width:54px;height:54px;margin:0 auto 16px;border-radius:18px;background:linear-gradient(135deg,#1677ff,#53b8f0);color:#fff;font-size:28px;line-height:54px;font-weight:700;}'
            . 'h1{margin:0 0 10px;color:#0f172a;font-size:22px;}p{margin:0 0 22px;color:#64748b;font-size:14px;line-height:1.8;}'
            . '.qjy-login-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}'
            . 'a{display:inline-flex;align-items:center;justify-content:center;min-width:104px;height:38px;padding:0 16px;border-radius:10px;text-decoration:none;font-size:14px;}'
            . '.primary{color:#fff;background:#1677ff;}.secondary{color:#1677ff;background:#eef6ff;}'
            . '</style></head><body><div class="qjy-login-notice"><div class="qjy-login-icon">!</div><h1>需要先登录</h1><p>' . $safeMessage . '</p>'
            . '<div class="qjy-login-actions"><a class="primary" href="/login">去登录</a><a class="secondary" href="javascript:history.back()">返回上一页</a></div>'
            . '</div></body></html>';

        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function wantsJson()
    {
        if (request()->isAjax()) {
            return true;
        }

        $accept = strtolower((string) request()->header('accept', ''));
        return strpos($accept, 'application/json') !== false;
    }
}
