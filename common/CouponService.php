<?php

namespace addons\qingjiyun_coupon\common;

use think\Db;

class CouponService
{
    const STATUS_UNUSED = 'unused';
    const STATUS_USED = 'used';
    const STATUS_EXPIRED = 'expired';
    const CODE_PREFIX_SETTING = 'qingjiyun_coupon_code_prefix';
    const DEFAULT_CODE_PREFIX = 'qingjiyun_';

    public static function ensureSchema()
    {
        static $done = false;
        if ($done) {
            return;
        }

        try {
            $prefix = (string) config('database.prefix');
            if ($prefix === '') {
                $prefix = 'shd_';
            }
            $prefix = preg_replace('/[^a-zA-Z0-9_]/', '', $prefix);
            $table = $prefix . 'qingjiyun_coupon_template';
            if (empty(Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'new_user_only'"))) {
                Db::execute("ALTER TABLE `{$table}` ADD `new_user_only` tinyint(1) NOT NULL DEFAULT '0' AFTER `new_user_auto`");
            }
            if (empty(Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'new_user_days'"))) {
                Db::execute("ALTER TABLE `{$table}` ADD `new_user_days` int(10) NOT NULL DEFAULT '7' AFTER `new_user_only`");
            }
            if (empty(Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'require_realname'"))) {
                Db::execute("ALTER TABLE `{$table}` ADD `require_realname` tinyint(1) NOT NULL DEFAULT '0' AFTER `require_paid`");
            }
            if (empty(Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'once_per_client'"))) {
                Db::execute("ALTER TABLE `{$table}` ADD `once_per_client` tinyint(1) NOT NULL DEFAULT '1' AFTER `require_realname`");
            }
            Db::execute("UPDATE `{$table}` SET `new_user_only` = 1 WHERE `new_user_auto` = 1 AND `new_user_only` = 0");
            Db::execute("UPDATE `{$table}` SET `once_per_client` = 0 WHERE `new_user_only` = 1 AND `once_per_client` = 1");
        } catch (\Throwable $exception) {
            // Older installations will still try to run with the legacy schema.
        }

        $done = true;
    }

    public function issue($templateId, $uid, $source = 'manual', $sourceRef = '', $ip = '')
    {
        self::ensureSchema();

        $templateId = intval($templateId);
        $uid = intval($uid);
        if ($templateId <= 0 || $uid <= 0) {
            return $this->error('模板或用户无效');
        }

        $template = Db::name('qingjiyun_coupon_template')->where('id', $templateId)->find();
        if (!$template || intval($template['enabled']) !== 1) {
            return $this->error('券模板不存在或已停用');
        }

        $client = Db::name('clients')->where('id', $uid)->find();
        if (!$client) {
            return $this->error('用户不存在');
        }

        if (intval($template['quota']) > 0 && intval($template['issued_count']) >= intval($template['quota'])) {
            return $this->error('券模板配额已用完');
        }

        $conditionMessage = $this->claimConditionMessage($uid, $template, $client);
        if ($conditionMessage !== '') {
            return $this->error($conditionMessage);
        }

        $this->syncStatuses($uid);

        $oncePerClient = intval(isset($template['once_per_client']) ? $template['once_per_client'] : 1) === 1;
        $duplicateMessage = $this->duplicateIssueMessage($uid, $templateId, $source, $sourceRef, $oncePerClient);
        if ($duplicateMessage !== '') {
            return $this->error($duplicateMessage);
        }

        $now = time();
        $code = $this->newCode();

        Db::startTrans();
        try {
            $lockedTemplate = Db::name('qingjiyun_coupon_template')->where('id', $templateId)->lock(true)->find();
            if (!$lockedTemplate || intval($lockedTemplate['enabled']) !== 1) {
                Db::rollback();
                return $this->error('券模板不存在或已停用');
            }
            if (intval($lockedTemplate['quota']) > 0 && intval($lockedTemplate['issued_count']) >= intval($lockedTemplate['quota'])) {
                Db::rollback();
                return $this->error('券模板配额已用完');
            }
            $lockedConditionMessage = $this->claimConditionMessage($uid, $lockedTemplate, $client);
            if ($lockedConditionMessage !== '') {
                Db::rollback();
                return $this->error($lockedConditionMessage);
            }
            $lockedOncePerClient = intval(isset($lockedTemplate['once_per_client']) ? $lockedTemplate['once_per_client'] : 1) === 1;
            $lockedDuplicateMessage = $this->duplicateIssueMessage($uid, $templateId, $source, $sourceRef, $lockedOncePerClient);
            if ($lockedDuplicateMessage !== '') {
                Db::rollback();
                return $this->error($lockedDuplicateMessage);
            }

            $template = $lockedTemplate;
            $expiresAt = intval($template['valid_days']) > 0
                ? $now + intval($template['valid_days']) * 86400
                : 0;
            $promo = [
                'code' => $code,
                'type' => $template['type'],
                'recurring' => intval($template['recurring']),
                'value' => $template['value'],
                'cycles' => (string) $template['cycles'],
                'appliesto' => (string) $template['appliesto'],
                'requires' => (string) $template['requires'],
                'requires_exist' => intval($template['requires_exist']),
                'start_time' => $now,
                'expiration_time' => $expiresAt,
                'max_times' => 1,
                'used' => 0,
                'lifelong' => 0,
                'one_time' => 1,
                'only_new_client' => 0,
                'only_old_client' => 0,
                'once_per_client' => $lockedOncePerClient ? 1 : 0,
                'recurfor' => intval($template['recurfor']),
                'upgrades' => 0,
                'upgrade_config' => '',
                'notes' => '[qingjiyun_coupon template:' . $templateId . ' uid:' . $uid . '] ' . $template['title'],
            ];
            if ($this->promoCodeHasDiscountColumn()) {
                $promo['is_discount'] = 1;
            }
            $promoId = Db::name('promo_code')->insertGetId($promo);

            Db::name('qingjiyun_coupon_user')->insert([
                'uid' => $uid,
                'template_id' => $templateId,
                'promo_id' => $promoId,
                'code' => $code,
                'status' => self::STATUS_UNUSED,
                'source' => $source,
                'source_ref' => $sourceRef,
                'issued_at' => $now,
                'expires_at' => $expiresAt,
                'used_at' => 0,
                'order_id' => 0,
                'created_ip' => $ip,
            ]);
            Db::name('qingjiyun_coupon_template')->where('id', $templateId)->setInc('issued_count');
            Db::commit();

            return ['ok' => true, 'message' => '发放成功', 'data' => ['code' => $code, 'promo_id' => $promoId]];
        } catch (\Exception $exception) {
            Db::rollback();
            return $this->error('发放失败：' . $exception->getMessage());
        }
    }

    public function issueMany($templateId, array $uids, $source = 'manual')
    {
        $result = ['success' => 0, 'failed' => 0, 'messages' => []];
        $uids = array_values(array_unique(array_filter(array_map('intval', $uids))));
        foreach ($uids as $uid) {
            $issued = $this->issue($templateId, $uid, $source, '', $this->currentIp());
            if ($issued['ok']) {
                $result['success']++;
            } else {
                $result['failed']++;
                $result['messages'][] = 'UID ' . $uid . '：' . $issued['message'];
            }
        }

        return $result;
    }

    public function codePrefix()
    {
        try {
            $value = Db::name('configuration')->where('setting', self::CODE_PREFIX_SETTING)->value('value');
        } catch (\Throwable $exception) {
            $value = '';
        }

        $prefix = self::normalizeCodePrefix($value);
        return $prefix !== '' ? $prefix : self::DEFAULT_CODE_PREFIX;
    }

    public function saveCodePrefix($prefix)
    {
        $prefix = self::normalizeCodePrefix($prefix);
        if ($prefix === '') {
            return $this->error('券码前缀不能为空，且只能包含字母、数字、下划线或中横线');
        }

        $data = ['value' => $prefix];
        $exists = Db::name('configuration')->where('setting', self::CODE_PREFIX_SETTING)->find();
        if ($exists) {
            Db::name('configuration')->where('setting', self::CODE_PREFIX_SETTING)->update($data);
        } else {
            $data['setting'] = self::CODE_PREFIX_SETTING;
            Db::name('configuration')->insert($data);
        }

        return ['ok' => true, 'message' => '券码前缀已保存，后续新发券将使用该前缀', 'data' => ['prefix' => $prefix]];
    }

    public static function normalizeCodePrefix($prefix)
    {
        $prefix = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $prefix);
        return substr($prefix, 0, 24);
    }

    public function syncStatuses($uid = 0)
    {
        $query = Db::name('qingjiyun_coupon_user')->where('status', self::STATUS_UNUSED);
        if (intval($uid) > 0) {
            $query->where('uid', intval($uid));
        }
        $query->where('expires_at', '>', 0)->where('expires_at', '<', time())
            ->update(['status' => self::STATUS_EXPIRED]);

        $active = Db::name('qingjiyun_coupon_user')->where('status', self::STATUS_UNUSED);
        if (intval($uid) > 0) {
            $active->where('uid', intval($uid));
        }
        foreach ($active->select() as $coupon) {
            $native = Db::name('promo_code')->where('id', $coupon['promo_id'])->field('used,expiration_time')->find();
            if (!$native) {
                continue;
            }
            if (intval($native['used']) > 0) {
                Db::name('qingjiyun_coupon_user')->where('id', $coupon['id'])->update([
                    'status' => self::STATUS_USED,
                    'used_at' => time(),
                ]);
            }
        }
    }

    public function userCoupons($uid, $status = '')
    {
        self::ensureSchema();

        $this->syncStatuses($uid);
        $query = Db::name('qingjiyun_coupon_user')->alias('u')
            ->leftJoin('qingjiyun_coupon_template t', 'u.template_id = t.id')
            ->field('u.*,t.title,t.description,t.type,t.value,t.require_paid,t.require_realname,t.appliesto,t.cycles')
            ->where('u.uid', intval($uid));
        if ($status !== '') {
            $query->where('u.status', $status);
        }

        return $query->order('u.id', 'desc')->select();
    }

    public function centerTemplates($uid)
    {
        self::ensureSchema();

        $uid = intval($uid);
        if ($uid <= 0) {
            return [];
        }

        $this->syncStatuses($uid);
        $templates = $this->rowsToArray(
            Db::name('qingjiyun_coupon_template')->where('enabled', 1)->order('id', 'desc')->select()
        );
        if (empty($templates)) {
            return [];
        }

        $templateIds = [];
        $productIds = [];
        foreach ($templates as $template) {
            $templateIds[] = intval($template['id']);
            $productIds = array_merge($productIds, $this->numbersFromCsv(isset($template['appliesto']) ? $template['appliesto'] : ''));
            $productIds = array_merge($productIds, $this->numbersFromCsv(isset($template['requires']) ? $template['requires'] : ''));
        }
        $templateIds = array_values(array_unique(array_filter($templateIds)));
        $productNames = $this->productNames(array_values(array_unique(array_filter($productIds))));

        $recordsByTemplate = [];
        if (!empty($templateIds)) {
            $rows = $this->rowsToArray(
                Db::name('qingjiyun_coupon_user')
                    ->where('uid', $uid)
                    ->where('template_id', 'in', $templateIds)
                    ->order('id', 'desc')
                    ->select()
            );
            foreach ($rows as $row) {
                $templateId = intval($row['template_id']);
                if (!isset($recordsByTemplate[$templateId])) {
                    $recordsByTemplate[$templateId] = [];
                }
                $recordsByTemplate[$templateId][] = $row;
            }
        }

        $client = Db::name('clients')->where('id', $uid)->field('id,create_time')->find();
        $hasPaid = null;
        $realNameVerified = null;
        $items = [];
        foreach ($templates as $template) {
            $templateId = intval($template['id']);
            $templateRecords = isset($recordsByTemplate[$templateId]) ? $recordsByTemplate[$templateId] : [];
            $record = !empty($templateRecords) ? $templateRecords[0] : null;
            $quota = intval(isset($template['quota']) ? $template['quota'] : 0);
            $issuedCount = intval(isset($template['issued_count']) ? $template['issued_count'] : 0);
            $quotaLeft = $quota > 0 ? max(0, $quota - $issuedCount) : -1;
            $soldOut = $quota > 0 && $issuedCount >= $quota;
            $requirePaid = intval(isset($template['require_paid']) ? $template['require_paid'] : 0) === 1;
            $requireRealName = intval(isset($template['require_realname']) ? $template['require_realname'] : 0) === 1;
            $newUserOnly = intval(isset($template['new_user_only']) ? $template['new_user_only'] : 0) === 1;
            $newUserDays = intval(isset($template['new_user_days']) ? $template['new_user_days'] : 7);
            if ($newUserOnly && $newUserDays <= 0) {
                $newUserDays = 7;
            }
            $oncePerClient = intval(isset($template['once_per_client']) ? $template['once_per_client'] : 1) === 1;
            if ($requirePaid && $hasPaid === null) {
                $hasPaid = $this->hasPaidInvoice($uid);
            }
            if ($requireRealName && $realNameVerified === null) {
                $realNameVerified = $this->isRealNameVerified($uid);
            }

            $hasRecord = !empty($record);
            $hasUnusedRecord = false;
            foreach ($templateRecords as $templateRecord) {
                if ((string) (isset($templateRecord['status']) ? $templateRecord['status'] : '') === self::STATUS_UNUSED) {
                    $hasUnusedRecord = true;
                    break;
                }
            }
            $claimed = $hasRecord;
            $blockedByClaim = $oncePerClient ? $hasRecord : $hasUnusedRecord;
            $conditionMessage = $this->claimConditionMessage($uid, $template, $client, $hasPaid, $realNameVerified);
            $canClaim = !$blockedByClaim && !$soldOut && $conditionMessage === '';
            $claimReason = '';
            if ($hasRecord && $oncePerClient) {
                $claimReason = '您已领取过该优惠券';
            } elseif (!$oncePerClient && $hasUnusedRecord) {
                $claimReason = '请先使用已领取的优惠券';
            } elseif ($soldOut) {
                $claimReason = '优惠券已领完';
            } elseif ($conditionMessage !== '') {
                $claimReason = $conditionMessage;
            }

            $items[] = [
                'item_kind' => 'template',
                'item_key' => 'template_' . $templateId,
                'id' => $templateId,
                'template_id' => $templateId,
                'record_id' => 0,
                'title' => (string) (isset($template['title']) ? $template['title'] : ''),
                'description' => (string) (isset($template['description']) ? $template['description'] : ''),
                'type' => (string) (isset($template['type']) ? $template['type'] : ''),
                'value' => floatval(isset($template['value']) ? $template['value'] : 0),
                'cycles' => (string) (isset($template['cycles']) ? $template['cycles'] : ''),
                'cycle_text' => $this->cycleText(isset($template['cycles']) ? $template['cycles'] : ''),
                'appliesto' => (string) (isset($template['appliesto']) ? $template['appliesto'] : ''),
                'product_text' => $this->productText(isset($template['appliesto']) ? $template['appliesto'] : '', $productNames),
                'requires' => (string) (isset($template['requires']) ? $template['requires'] : ''),
                'requires_exist' => intval(isset($template['requires_exist']) ? $template['requires_exist'] : 0),
                'requires_text' => $this->requiresText($template, $productNames),
                'valid_days' => intval(isset($template['valid_days']) ? $template['valid_days'] : 0),
                'quota' => $quota,
                'issued_count' => $issuedCount,
                'quota_left' => $quotaLeft,
                'new_user_auto' => intval(isset($template['new_user_auto']) ? $template['new_user_auto'] : 0),
                'new_user_only' => $newUserOnly ? 1 : 0,
                'new_user_days' => $newUserDays,
                'require_paid' => $requirePaid ? 1 : 0,
                'require_realname' => $requireRealName ? 1 : 0,
                'once_per_client' => $oncePerClient ? 1 : 0,
                'recurring' => intval(isset($template['recurring']) ? $template['recurring'] : 0),
                'recurfor' => intval(isset($template['recurfor']) ? $template['recurfor'] : 0),
                'claimed' => $claimed,
                'claimed_status' => $hasRecord ? (string) $record['status'] : '',
                'code' => $hasRecord ? (string) $record['code'] : '',
                'issued_at' => $hasRecord ? intval($record['issued_at']) : 0,
                'expires_at' => $hasRecord ? intval($record['expires_at']) : 0,
                'can_claim' => $canClaim,
                'claim_reason' => $claimReason,
                'sold_out' => $soldOut,
                'tags' => $this->centerTags($template),
            ];

            foreach ($templateRecords as $claimedRecord) {
                $recordId = intval(isset($claimedRecord['id']) ? $claimedRecord['id'] : 0);
                $items[] = [
                    'item_kind' => 'record',
                    'item_key' => 'record_' . $recordId,
                    'id' => $recordId,
                    'template_id' => $templateId,
                    'record_id' => $recordId,
                    'title' => (string) (isset($template['title']) ? $template['title'] : ''),
                    'description' => (string) (isset($template['description']) ? $template['description'] : ''),
                    'type' => (string) (isset($template['type']) ? $template['type'] : ''),
                    'value' => floatval(isset($template['value']) ? $template['value'] : 0),
                    'cycles' => (string) (isset($template['cycles']) ? $template['cycles'] : ''),
                    'cycle_text' => $this->cycleText(isset($template['cycles']) ? $template['cycles'] : ''),
                    'appliesto' => (string) (isset($template['appliesto']) ? $template['appliesto'] : ''),
                    'product_text' => $this->productText(isset($template['appliesto']) ? $template['appliesto'] : '', $productNames),
                    'requires' => (string) (isset($template['requires']) ? $template['requires'] : ''),
                    'requires_exist' => intval(isset($template['requires_exist']) ? $template['requires_exist'] : 0),
                    'requires_text' => $this->requiresText($template, $productNames),
                    'valid_days' => intval(isset($template['valid_days']) ? $template['valid_days'] : 0),
                    'quota' => $quota,
                    'issued_count' => $issuedCount,
                    'quota_left' => $quotaLeft,
                    'new_user_auto' => intval(isset($template['new_user_auto']) ? $template['new_user_auto'] : 0),
                    'new_user_only' => $newUserOnly ? 1 : 0,
                    'new_user_days' => $newUserDays,
                    'require_paid' => $requirePaid ? 1 : 0,
                    'require_realname' => $requireRealName ? 1 : 0,
                    'once_per_client' => $oncePerClient ? 1 : 0,
                    'recurring' => intval(isset($template['recurring']) ? $template['recurring'] : 0),
                    'recurfor' => intval(isset($template['recurfor']) ? $template['recurfor'] : 0),
                    'claimed' => true,
                    'claimed_status' => (string) (isset($claimedRecord['status']) ? $claimedRecord['status'] : ''),
                    'code' => (string) (isset($claimedRecord['code']) ? $claimedRecord['code'] : ''),
                    'issued_at' => intval(isset($claimedRecord['issued_at']) ? $claimedRecord['issued_at'] : 0),
                    'expires_at' => intval(isset($claimedRecord['expires_at']) ? $claimedRecord['expires_at'] : 0),
                    'can_claim' => false,
                    'claim_reason' => '',
                    'sold_out' => $soldOut,
                    'tags' => $this->centerTags($template),
                ];
            }
        }

        return $items;
    }

    public function claimFromCenter($templateId, $uid)
    {
        self::ensureSchema();

        $templateId = intval($templateId);
        $uid = intval($uid);
        if ($templateId <= 0 || $uid <= 0) {
            return $this->error('模板或用户无效');
        }

        $template = Db::name('qingjiyun_coupon_template')->where('id', $templateId)->field('id,enabled,once_per_client')->find();
        if (!$template || intval($template['enabled']) !== 1) {
            return $this->error('券模板不存在或已停用');
        }
        $this->syncStatuses($uid);
        $oncePerClient = intval(isset($template['once_per_client']) ? $template['once_per_client'] : 1) === 1;
        $duplicateMessage = $this->duplicateIssueMessage($uid, $templateId, 'center', 'coupon_center', $oncePerClient);
        if ($duplicateMessage !== '') {
            return $this->error($duplicateMessage === '同一模板无需重复发放' ? '您已领取过该优惠券' : $duplicateMessage);
        }

        $result = $this->issue($templateId, $uid, 'center', 'coupon_center', $this->currentIp());
        if (!$result['ok'] && strpos($result['message'], '无需重复发放') !== false) {
            $result['message'] = '您已领取过该优惠券';
        }

        return $result;
    }

    public function validateOwnedCode($uid, $code)
    {
        self::ensureSchema();

        $coupon = Db::name('qingjiyun_coupon_user')->alias('u')
            ->leftJoin('qingjiyun_coupon_template t', 'u.template_id = t.id')
            ->field('u.*,t.title,t.require_paid,t.require_realname,t.enabled')
            ->where('u.uid', intval($uid))
            ->where('u.code', trim($code))
            ->find();
        if (!$coupon) {
            return $this->error('该优惠券不属于当前用户');
        }
        if ($coupon['status'] === self::STATUS_USED) {
            return $this->error('该优惠券已使用');
        }
        if ($coupon['status'] === self::STATUS_EXPIRED || (intval($coupon['expires_at']) > 0 && intval($coupon['expires_at']) < time())) {
            return $this->error('该优惠券已过期');
        }
        if (intval($coupon['enabled']) !== 1) {
            return $this->error('该优惠券模板已停用');
        }
        if (intval($coupon['require_paid']) === 1 && !$this->hasPaidInvoice($uid)) {
            return $this->error('该优惠券需先完成一次支付');
        }
        if (intval(isset($coupon['require_realname']) ? $coupon['require_realname'] : 0) === 1
            && !$this->isRealNameVerified($uid)) {
            return $this->error('该优惠券需先完成实名认证');
        }

        return ['ok' => true, 'message' => '优惠券可用', 'data' => $coupon];
    }

    public function orderedUsableCoupons($uid)
    {
        self::ensureSchema();

        $uid = intval($uid);
        $coupons = Db::name('qingjiyun_coupon_user')->alias('u')
            ->leftJoin('qingjiyun_coupon_template t', 'u.template_id = t.id')
            ->field('u.id,u.code,u.expires_at,t.title,t.type,t.value,t.require_paid,t.require_realname,t.enabled')
            ->where('u.uid', $uid)
            ->where('u.status', self::STATUS_UNUSED)
            ->select();

        $usable = [];
        foreach ($coupons as $coupon) {
            if (intval($coupon['enabled']) !== 1) {
                continue;
            }
            if (intval($coupon['expires_at']) > 0 && intval($coupon['expires_at']) < time()) {
                continue;
            }
            if (intval(isset($coupon['require_realname']) ? $coupon['require_realname'] : 0) === 1
                && !$this->isRealNameVerified($uid)) {
                continue;
            }
            $usable[] = $coupon;
        }

        usort($usable, function ($left, $right) {
            $leftScore = $this->sortScore($left);
            $rightScore = $this->sortScore($right);
            if ($leftScore === $rightScore) {
                return intval($right['id']) - intval($left['id']);
            }
            return $leftScore < $rightScore ? 1 : -1;
        });

        return $usable;
    }

    public function deleteIssuedCoupons(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return $this->error('请选择要删除的优惠券');
        }

        $deleted = 0;
        $protected = 0;
        Db::startTrans();
        try {
            $records = Db::name('qingjiyun_coupon_user')->where('id', 'in', $ids)->select();
            foreach ($records as $record) {
                $nativeUsed = intval(Db::name('promo_code')->where('id', intval($record['promo_id']))->value('used'));
                if ($record['status'] === self::STATUS_USED || $nativeUsed > 0) {
                    $protected++;
                    continue;
                }

                Db::name('promo_code')->where('id', intval($record['promo_id']))
                    ->where('code', $record['code'])->delete();
                Db::name('qingjiyun_coupon_user')->where('id', intval($record['id']))->delete();
                Db::name('qingjiyun_coupon_template')->where('id', intval($record['template_id']))
                    ->where('issued_count', '>', 0)->setDec('issued_count');
                $deleted++;
            }
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->error('删除失败：' . $exception->getMessage());
        }

        $message = '已删除 ' . $deleted . ' 张优惠券';
        if ($protected > 0) {
            $message .= '，' . $protected . ' 张已使用券已保留';
        }

        return ['ok' => true, 'message' => $message, 'data' => ['deleted' => $deleted, 'protected' => $protected]];
    }

    public function hasPaidInvoice($uid)
    {
        return Db::name('invoices')->where('uid', intval($uid))
            ->where('status', 'Paid')->where('total', '>', 0)->count() > 0;
    }

    public function claimConditionMessage($uid, $template, $client = null, $hasPaid = null, $realNameVerified = null)
    {
        $newUserMessage = $this->newUserBlockReason($uid, $template, $client);
        if ($newUserMessage !== '') {
            return $newUserMessage;
        }

        if (intval(isset($template['require_realname']) ? $template['require_realname'] : 0) === 1) {
            if ($realNameVerified === null) {
                $realNameVerified = $this->isRealNameVerified($uid);
            }
            if (!$realNameVerified) {
                return '请先完成实名认证后再领取';
            }
        }

        if (intval(isset($template['require_paid']) ? $template['require_paid'] : 0) === 1) {
            if ($hasPaid === null) {
                $hasPaid = $this->hasPaidInvoice($uid);
            }
            if (!$hasPaid) {
                return '需先完成一次支付';
            }
        }

        return '';
    }

    public function newUserBlockReason($uid, $template, $client = null)
    {
        if (intval(isset($template['new_user_only']) ? $template['new_user_only'] : 0) !== 1) {
            return '';
        }

        if ($client === null) {
            $client = Db::name('clients')->where('id', intval($uid))->field('id,create_time')->find();
        }
        if (!$client) {
            return '无法确认注册时间';
        }

        $createTime = $this->clientCreateTime($client);
        if ($createTime <= 0) {
            return '无法确认注册时间';
        }

        $days = intval(isset($template['new_user_days']) ? $template['new_user_days'] : 7);
        if ($days <= 0) {
            $days = 7;
        }
        if (time() > $createTime + $days * 86400) {
            return '仅限注册 ' . $days . ' 天内用户领取';
        }

        return '';
    }

    public function isRealNameVerified($uid)
    {
        $uid = intval($uid);
        if ($uid <= 0) {
            return false;
        }

        foreach (['certifi_person', 'certifi_company'] as $table) {
            try {
                if (Db::name($table)->where('auth_user_id', $uid)->where('status', 1)->find()) {
                    return true;
                }
            } catch (\Throwable $exception) {
                // Some installations may not enable the real-name module tables.
            }
        }

        return false;
    }

    private function duplicateIssueMessage($uid, $templateId, $source, $sourceRef, $oncePerClient)
    {
        $base = Db::name('qingjiyun_coupon_user')
            ->where('uid', intval($uid))
            ->where('template_id', intval($templateId));

        if ($source === 'signin') {
            $signinDuplicate = Db::name('qingjiyun_coupon_user')
                ->where('uid', intval($uid))
                ->where('template_id', intval($templateId))
                ->where('source_ref', (string) $sourceRef);
            if ($signinDuplicate->find()) {
                return '同一签到奖励无需重复发放';
            }
        }

        if ($oncePerClient) {
            return $base->find() ? '同一模板无需重复发放' : '';
        }

        $active = Db::name('qingjiyun_coupon_user')
            ->where('uid', intval($uid))
            ->where('template_id', intval($templateId))
            ->where('status', self::STATUS_UNUSED);

        return $active->find() ? '请先使用已领取的优惠券' : '';
    }

    private function sortScore($coupon)
    {
        $type = $coupon['type'];
        $value = floatval($coupon['value']);
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

    private function centerTags($template)
    {
        $tags = [];
        if (intval(isset($template['once_per_client']) ? $template['once_per_client'] : 1) === 1) {
            $tags[] = '限领一次';
        } else {
            $tags[] = '用完可再领';
        }
        if (intval(isset($template['new_user_only']) ? $template['new_user_only'] : 0) === 1) {
            $days = intval(isset($template['new_user_days']) ? $template['new_user_days'] : 7);
            $tags[] = '新用户可领';
            $tags[] = '注册 ' . max(1, $days) . ' 天内';
        }
        if (intval(isset($template['new_user_auto']) ? $template['new_user_auto'] : 0) === 1) {
            $tags[] = '未领 1 天后自动发';
        }
        if (intval(isset($template['require_paid']) ? $template['require_paid'] : 0) === 1) {
            $tags[] = '需完成支付';
        }
        if (intval(isset($template['require_realname']) ? $template['require_realname'] : 0) === 1) {
            $tags[] = '需实名认证';
        }
        if (intval(isset($template['quota']) ? $template['quota'] : 0) > 0) {
            $tags[] = '限量 ' . intval($template['quota']) . ' 张';
        }
        if (trim((string) (isset($template['requires']) ? $template['requires'] : '')) !== '') {
            $tags[] = '有前置产品';
        }

        return $tags;
    }

    private function cycleText($cycles)
    {
        $parts = preg_split('/[\s,，]+/u', strtolower((string) $cycles), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($parts)) {
            return '全部周期';
        }
        $map = [
            'hour' => '小时',
            'day' => '日付',
            'monthly' => '月付',
            'quarterly' => '季付',
            'semiannually' => '半年付',
            'annually' => '年付',
            'biennially' => '两年付',
            'triennially' => '三年付',
            'fourly' => '四年付',
            'fively' => '五年付',
        ];
        $labels = [];
        foreach ($parts as $part) {
            $labels[] = isset($map[$part]) ? $map[$part] : $part;
        }

        return implode('、', array_values(array_unique($labels)));
    }

    private function productText($appliesTo, array $productNames)
    {
        $ids = $this->numbersFromCsv($appliesTo);
        if (empty($ids)) {
            return '全部产品可用';
        }

        $names = [];
        foreach ($ids as $id) {
            $names[] = isset($productNames[$id]) ? $productNames[$id] : ('产品 #' . $id);
        }
        if (count($names) > 3) {
            return implode('、', array_slice($names, 0, 3)) . ' 等 ' . count($names) . ' 个产品';
        }

        return implode('、', $names);
    }

    private function requiresText($template, array $productNames)
    {
        $requires = isset($template['requires']) ? $template['requires'] : '';
        $ids = $this->numbersFromCsv($requires);
        if (empty($ids)) {
            return '';
        }

        $text = $this->productText($requires, $productNames);
        if (intval(isset($template['requires_exist']) ? $template['requires_exist'] : 0) === 1) {
            return '需已购：' . $text;
        }

        return '需同购：' . $text;
    }

    private function productNames(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $rows = $this->rowsToArray(Db::name('products')->where('id', 'in', $ids)->field('id,name')->select());
        $names = [];
        foreach ($rows as $row) {
            $names[intval($row['id'])] = (string) $row['name'];
        }

        return $names;
    }

    private function numbersFromCsv($value)
    {
        $parts = preg_split('/[\s,，]+/u', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_filter(array_map('intval', $parts))));
    }

    private function rowsToArray($rows)
    {
        if (is_object($rows) && method_exists($rows, 'toArray')) {
            return $rows->toArray();
        }

        return is_array($rows) ? $rows : [];
    }

    private function clientCreateTime($client)
    {
        $value = is_array($client) && isset($client['create_time']) ? $client['create_time'] : 0;
        if (is_numeric($value)) {
            return intval($value);
        }

        $timestamp = strtotime((string) $value);
        return $timestamp ? intval($timestamp) : 0;
    }

    private function newCode()
    {
        $prefix = $this->codePrefix();
        do {
            try {
                $suffix = bin2hex(random_bytes(8));
            } catch (\Exception $exception) {
                $suffix = substr(md5(uniqid((string) mt_rand(), true)), 0, 16);
            }
            $code = $prefix . $suffix;
        } while (Db::name('promo_code')->where('code', $code)->find());

        return $code;
    }

    private function promoCodeHasDiscountColumn()
    {
        static $hasColumn;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $prefix = (string) config('database.prefix');
        if ($prefix === '') {
            $prefix = 'shd_';
        }
        $prefix = preg_replace('/[^a-zA-Z0-9_]/', '', $prefix);
        $result = Db::query("SHOW COLUMNS FROM `{$prefix}promo_code` LIKE 'is_discount'");
        $hasColumn = !empty($result);

        return $hasColumn;
    }

    private function currentIp()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    }

    private function error($message)
    {
        return ['ok' => false, 'message' => $message, 'data' => []];
    }
}
