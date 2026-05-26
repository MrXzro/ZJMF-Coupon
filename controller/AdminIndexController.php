<?php

namespace addons\qingjiyun_coupon\controller;

use addons\qingjiyun_coupon\common\CouponService;
use app\admin\controller\PluginAdminBaseController;
use think\Db;

class AdminIndexController extends PluginAdminBaseController
{
    public function index()
    {
        $stats = [
            'templates' => Db::name('qingjiyun_coupon_template')->count(),
            'issued' => Db::name('qingjiyun_coupon_user')->count(),
            'unused' => Db::name('qingjiyun_coupon_user')->where('status', CouponService::STATUS_UNUSED)->count(),
            'used' => Db::name('qingjiyun_coupon_user')->where('status', CouponService::STATUS_USED)->count(),
            'signin_today' => Db::name('qingjiyun_coupon_signin_log')->where('signin_date', date('Y-m-d'))->count(),
        ];
        $latest = Db::name('qingjiyun_coupon_user')->alias('u')
            ->leftJoin('qingjiyun_coupon_template t', 'u.template_id = t.id')
            ->leftJoin('clients c', 'u.uid = c.id')
            ->field('u.*,t.title,c.username')
            ->order('u.id', 'desc')->limit(10)->select();
        $this->assign('Title', '优惠券概览');
        $this->assign('Stats', $stats);
        $this->assign('Latest', $latest);

        return $this->fetch('/index');
    }

    public function settings()
    {
        $service = new CouponService();
        $this->assign('Title', '基础设置');
        $this->assign('CodePrefix', $service->codePrefix());
        $this->assign('SaveSettingsUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/saveSettings'));

        return $this->fetch('/settings');
    }

    public function saveSettings()
    {
        if (!request()->isPost()) {
            return json(['status' => 405, 'msg' => '请求方式错误', 'data' => []]);
        }

        try {
            $result = (new CouponService())->saveCodePrefix(input('post.code_prefix', ''));
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '设置保存失败：' . $exception->getMessage(), 'data' => []]);
        }

        return json([
            'status' => $result['ok'] ? 200 : 400,
            'msg' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function templates()
    {
        CouponService::ensureSchema();

        $templates = Db::name('qingjiyun_coupon_template')->order('id', 'desc')->select();
        $products = Db::name('products')->field('id,name')->order('id', 'desc')->select();
        $editId = intval(input('edit_id', 0));
        $edit = $editId > 0 ? Db::name('qingjiyun_coupon_template')->where('id', $editId)->find() : [];
        $this->assign('Title', '券模板管理');
        $this->assign('Templates', $templates);
        $this->assign('Products', $products);
        $this->assign('CycleOptions', $this->cycleOptions());
        $this->assign('Edit', $edit ?: []);
        $this->assign('TemplatesUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/templates'));
        $this->assign('SaveUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/saveTemplate'));
        $this->assign('ToggleUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/toggleTemplate'));

        return $this->fetch('/templates');
    }

    public function saveTemplate()
    {
        if (!request()->isPost()) {
            return json(['status' => 405, 'msg' => '请求方式错误']);
        }

        try {
            CouponService::ensureSchema();

            $input = $this->request->post();
            $title = trim((string) ($input['title'] ?? ''));
            $type = trim((string) ($input['type'] ?? 'fixed'));
            $value = floatval($input['value'] ?? 0);
            if ($title === '') {
                return json(['status' => 400, 'msg' => '请输入模板名称']);
            }
            if (!in_array($type, ['percent', 'fixed', 'override', 'free'], true)) {
                return json(['status' => 400, 'msg' => '优惠券类型无效']);
            }
            if ($type !== 'free' && $value <= 0) {
                return json(['status' => 400, 'msg' => '优惠值必须大于 0']);
            }
            if ($type === 'percent' && $value > 100) {
                return json(['status' => 400, 'msg' => '百分比折扣不能大于 100']);
            }

            $data = [
                'title' => $title,
                'description' => trim((string) ($input['description'] ?? '')),
                'type' => $type,
                'value' => $type === 'free' ? 0 : $value,
                'cycles' => $this->commaValues($input['cycles'] ?? ''),
                'appliesto' => $this->commaNumbers($input['appliesto'] ?? ''),
                'requires' => $this->commaNumbers($input['requires'] ?? ''),
                'requires_exist' => !empty($input['requires_exist']) ? 1 : 0,
                'recurring' => !empty($input['recurring']) ? 1 : 0,
                'recurfor' => max(0, intval($input['recurfor'] ?? 0)),
                'valid_days' => max(0, intval($input['valid_days'] ?? 30)),
                'quota' => max(0, intval($input['quota'] ?? 0)),
                'new_user_auto' => !empty($input['new_user_auto']) ? 1 : 0,
                'require_paid' => !empty($input['require_paid']) ? 1 : 0,
                'once_per_client' => !empty($input['once_per_client']) ? 1 : 0,
                'enabled' => !empty($input['enabled']) ? 1 : 0,
                'update_time' => time(),
            ];
            $id = intval($input['id'] ?? 0);
            if ($id > 0) {
                Db::name('qingjiyun_coupon_template')->where('id', $id)->update($data);
            } else {
                $data['create_time'] = time();
                Db::name('qingjiyun_coupon_template')->insert($data);
            }
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '模板保存失败：' . $exception->getMessage()]);
        }

        return json(['status' => 200, 'msg' => '模板已保存']);
    }

    public function toggleTemplate()
    {
        try {
            $id = intval(input('post.id', 0));
            $enabled = intval(input('post.enabled', 0)) === 1 ? 1 : 0;
            if ($id <= 0) {
                return json(['status' => 400, 'msg' => '模板无效']);
            }
            Db::name('qingjiyun_coupon_template')->where('id', $id)->update([
                'enabled' => $enabled,
                'update_time' => time(),
            ]);
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '状态更新失败：' . $exception->getMessage()]);
        }

        return json(['status' => 200, 'msg' => '状态已更新']);
    }

    public function issue()
    {
        $templates = Db::name('qingjiyun_coupon_template')->where('enabled', 1)->order('id', 'desc')->select();
        $groups = Db::name('client_groups')->field('id,group_name')->select();
        $this->assign('Title', '批量发券');
        $this->assign('Templates', $templates);
        $this->assign('Groups', $groups);
        $this->assign('IssueUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/send'));

        return $this->fetch('/issue');
    }

    public function send()
    {
        if (!request()->isPost()) {
            return json(['status' => 405, 'msg' => '请求方式错误']);
        }

        try {
            $data = $this->request->post();
            $templateId = intval($data['template_id'] ?? 0);
            $target = (string) ($data['target'] ?? 'uids');
            $uids = [];
            if ($target === 'all') {
                $uids = Db::name('clients')->where('status', 1)->column('id');
            } elseif ($target === 'group') {
                $groupId = intval($data['group_id'] ?? 0);
                $uids = Db::name('clients')->where('groupid', $groupId)->where('status', 1)->column('id');
            } else {
                $uids = preg_split('/[\s,，]+/u', (string) ($data['uids'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            }
            if (empty($uids)) {
                return json(['status' => 400, 'msg' => '没有匹配到发放用户']);
            }

            $result = (new CouponService())->issueMany($templateId, $uids, 'manual');
            $message = '成功 ' . $result['success'] . ' 人，跳过/失败 ' . $result['failed'] . ' 人';
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '发券失败：' . $exception->getMessage()]);
        }

        return json(['status' => 200, 'msg' => $message, 'details' => array_slice($result['messages'], 0, 30)]);
    }

    public function records()
    {
        (new CouponService())->syncStatuses();
        $status = trim((string) input('status', ''));
        $keyword = trim((string) input('keyword', ''));
        $query = Db::name('qingjiyun_coupon_user')->alias('u')
            ->leftJoin('qingjiyun_coupon_template t', 'u.template_id = t.id')
            ->leftJoin('clients c', 'u.uid = c.id')
            ->field('u.*,t.title,t.type,t.value,c.username,c.email');
        if ($status !== '') {
            $query->where('u.status', $status);
        }
        if ($keyword !== '') {
            $query->where(function ($scope) use ($keyword) {
                $scope->where('u.code', 'like', '%' . $keyword . '%')
                    ->whereOr('c.username', 'like', '%' . $keyword . '%')
                    ->whereOr('u.uid', intval($keyword));
            });
        }
        $list = $query->order('u.id', 'desc')->paginate(20, false, [
            'query' => ['status' => $status, 'keyword' => $keyword],
        ]);
        $this->assign('Title', '领券记录');
        $this->assign('List', $list);
        $this->assign('Status', $status);
        $this->assign('Keyword', $keyword);
        $this->assign('RecordsUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/records'));
        $this->assign('DeleteRecordsUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/deleteRecords'));

        return $this->fetch('/records');
    }

    public function deleteRecords()
    {
        if (!request()->isPost()) {
            return json(['status' => 405, 'msg' => '请求方式错误']);
        }

        try {
            $ids = input('post.ids/a', []);
            if (empty($ids)) {
                $ids = preg_split('/[\s,，]+/u', (string) input('post.ids', ''), -1, PREG_SPLIT_NO_EMPTY);
            }
            $result = (new CouponService())->deleteIssuedCoupons(is_array($ids) ? $ids : []);
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '删除失败：' . $exception->getMessage(), 'data' => []]);
        }

        return json([
            'status' => $result['ok'] ? 200 : 400,
            'msg' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function signin()
    {
        $rules = Db::name('qingjiyun_coupon_signin_rule')->alias('r')
            ->leftJoin('qingjiyun_coupon_template t', 'r.template_id = t.id')
            ->field('r.*,t.title')->order('r.milestone', 'asc')->select();
        $templates = Db::name('qingjiyun_coupon_template')->where('enabled', 1)->select();
        $this->assign('Title', '签到奖励');
        $this->assign('Rules', $rules);
        $this->assign('Templates', $templates);
        $this->assign('SaveRuleUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/saveRule'));
        $this->assign('DeleteRuleUrl', shd_addon_url('QingjiyunCoupon://AdminIndex/deleteRule'));

        return $this->fetch('/signin');
    }

    public function saveRule()
    {
        try {
            $milestone = intval(input('post.milestone', 0));
            $templateId = intval(input('post.template_id', 0));
            if ($milestone <= 0 || $templateId <= 0) {
                return json(['status' => 400, 'msg' => '签到天数和券模板不能为空']);
            }
            $existing = Db::name('qingjiyun_coupon_signin_rule')->where('milestone', $milestone)->find();
            $data = [
                'milestone' => $milestone,
                'template_id' => $templateId,
                'enabled' => 1,
                'update_time' => time(),
            ];
            if ($existing) {
                Db::name('qingjiyun_coupon_signin_rule')->where('id', $existing['id'])->update($data);
            } else {
                $data['create_time'] = time();
                Db::name('qingjiyun_coupon_signin_rule')->insert($data);
            }
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '规则保存失败：' . $exception->getMessage()]);
        }

        return json(['status' => 200, 'msg' => '签到奖励规则已保存']);
    }

    public function deleteRule()
    {
        try {
            Db::name('qingjiyun_coupon_signin_rule')->where('id', intval(input('post.id', 0)))->delete();
        } catch (\Throwable $exception) {
            return json(['status' => 500, 'msg' => '规则删除失败：' . $exception->getMessage()]);
        }

        return json(['status' => 200, 'msg' => '规则已删除']);
    }

    public function diagnostics()
    {
        CouponService::ensureSchema();

        $prefix = preg_replace('/[^a-zA-Z0-9_]/', '', (string) config('database.prefix'));
        if ($prefix === '') {
            $prefix = 'shd_';
        }
        $checks = [];
        foreach (['template', 'user', 'signin_rule', 'signin_log', 'risk_log'] as $table) {
            $name = $prefix . 'qingjiyun_coupon_' . $table;
            $checks[] = [
                'name' => '数据表 ' . $name,
                'ok' => !empty(Db::query("SHOW TABLES LIKE '{$name}'")),
            ];
        }
        $checks[] = [
            'name' => '模板字段 once_per_client',
            'ok' => !empty(Db::query("SHOW COLUMNS FROM `{$prefix}qingjiyun_coupon_template` LIKE 'once_per_client'")),
        ];
        foreach (['code', 'type', 'value', 'expiration_time', 'max_times', 'used'] as $column) {
            $checks[] = [
                'name' => '原生优惠码字段 ' . $column,
                'ok' => !empty(Db::query("SHOW COLUMNS FROM `{$prefix}promo_code` LIKE '{$column}'")),
            ];
        }
        $systemLicense = trim((string) Db::name('configuration')->where('setting', 'system_license')->value('value'));
        $checks[] = ['name' => '魔方系统授权码已配置', 'ok' => $systemLicense !== ''];
        $checks[] = ['name' => 'PHP >= 7.1', 'ok' => version_compare(PHP_VERSION, '7.1.0', '>=')];
        $checks[] = ['name' => 'PDO 扩展', 'ok' => extension_loaded('pdo')];
        $checks[] = ['name' => 'mbstring 扩展', 'ok' => extension_loaded('mbstring')];
        $checks[] = ['name' => '券码前缀：' . (new CouponService())->codePrefix(), 'ok' => true];
        $requiredFiles = [
            'QingjiyunCouponPlugin.php',
            'common/CouponService.php',
            'common/ThemeCouponProvider.php',
            'common/RiskService.php',
            'common/SigninService.php',
            'controller/clientarea/IndexController.php',
            'template/admin/settings.tpl',
            'template/clientarea/center.tpl',
            'assets/cart.js',
            'assets/configure-coupon.js',
        ];
        foreach ($requiredFiles as $file) {
            $checks[] = ['name' => '文件 ' . $file, 'ok' => file_exists(dirname(__DIR__) . '/' . $file)];
        }

        $providerSnippet = '{php}' . PHP_EOL .
            '    $qjyCouponData = [];' . PHP_EOL .
            '    $qjyCouponPrefix = "qingjiyun_";' . PHP_EOL .
            '    try {' . PHP_EOL .
            '        $qjyReq = request();' . PHP_EOL .
            '        $qjyUid = isset($qjyReq->uid) ? intval($qjyReq->uid) : 0;' . PHP_EOL .
            '        $qjyCouponData = \addons\qingjiyun_coupon\common\ThemeCouponProvider::usableCoupons($qjyUid);' . PHP_EOL .
            '        $qjyCouponPrefix = (new \addons\qingjiyun_coupon\common\CouponService())->codePrefix();' . PHP_EOL .
            '    } catch (\Throwable $exception) {' . PHP_EOL .
            '        $qjyCouponData = [];' . PHP_EOL .
            '        $qjyCouponPrefix = "qingjiyun_";' . PHP_EOL .
            '    }' . PHP_EOL .
            '{/php}' . PHP_EOL;
        $configSnippet = '<script>window.QingjiyunCouponConfig={coupons:{:json_encode($qjyCouponData, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)},codePrefix:{:json_encode($qjyCouponPrefix, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)}};</script>' . PHP_EOL;
        $snippet = $providerSnippet . $configSnippet .
            '<script src="/plugins/addons/qingjiyun_coupon/assets/cart.js?v=1.0.21"></script>';
        $configureSnippet = '{if $userinfo}' . PHP_EOL . $providerSnippet . $configSnippet .
            '<script src="/plugins/addons/qingjiyun_coupon/assets/configure-coupon.js?v=1.0.21"></script>' . PHP_EOL .
            '{/if}';
        $this->assign('Title', '插件检测与集成');
        $this->assign('Checks', $checks);
        $this->assign('Snippet', $snippet);
        $this->assign('ConfigureSnippet', $configureSnippet);

        return $this->fetch('/diagnostics');
    }

    private function commaNumbers($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $parts = preg_split('/[\s,，]+/u', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $numbers = array_filter(array_unique(array_map('intval', $parts)));

        return implode(',', $numbers);
    }

    private function commaValues($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $parts = preg_split('/[\s,，]+/u', strtolower((string) $value), -1, PREG_SPLIT_NO_EMPTY);
        $allowed = ['hour', 'day', 'monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially', 'fourly', 'fively'];

        return implode(',', array_values(array_intersect($allowed, array_unique($parts))));
    }

    private function cycleOptions()
    {
        return [
            ['value' => 'hour', 'label' => '小时'],
            ['value' => 'day', 'label' => '日付'],
            ['value' => 'monthly', 'label' => '月付'],
            ['value' => 'quarterly', 'label' => '季付'],
            ['value' => 'semiannually', 'label' => '半年付'],
            ['value' => 'annually', 'label' => '年付'],
            ['value' => 'biennially', 'label' => '两年付'],
            ['value' => 'triennially', 'label' => '三年付'],
            ['value' => 'fourly', 'label' => '四年付'],
            ['value' => 'fively', 'label' => '五年付'],
        ];
    }
}
