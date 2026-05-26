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
            return $this->error('请登录后访问领券中心');
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
        if (!request()->isPost()) {
            return json(['status' => 405, 'msg' => '请求方式错误', 'data' => []]);
        }

        $uid = $this->currentUid();
        if ($uid <= 0) {
            return json(['status' => 401, 'msg' => '请先登录', 'data' => []]);
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
            return $this->error('请登录后访问券包');
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
            return $this->error('请登录后签到');
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
            return json(['status' => 401, 'msg' => '请先登录']);
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
            return json(['status' => 401, 'msg' => '请先登录', 'data' => []]);
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
        if (!request()->isPost()) {
            return json(['status' => 405, 'msg' => '请求方式错误']);
        }

        $uid = $this->currentUid();
        if ($uid <= 0) {
            return json(['status' => 401, 'msg' => '请先登录']);
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
}
