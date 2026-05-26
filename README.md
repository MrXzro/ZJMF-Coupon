# 轻极云优惠券插件

基于魔方财务/ZJMF 原生 `promo_code` 结算引擎的营销优惠券插件。插件自身只维护模板、用户归属、签到和风控记录，不修改核心程序；购物车按钮通过当前启用主题加载插件前端脚本接入。

## 功能

- 可视化维护 `percent`、`fixed`、`override`、`free` 四种券模板。
- 后台“基础设置”可修改后续新发券的券码前缀。
- 按 UID、用户组或全部活跃用户批量发券，模板可配置每账号限领一次或允许重复领取。
- 前台领券中心展示当前可领取模板，用户可主动领取并跳转购物车使用。
- 新用户可领模板支持“注册 N 天内可领”、实名认证门槛、同 IP 24 小时限制和临时邮箱域名拦截；注册满 1 天仍未领取时可自动补发。
- 连续签到奖励规则、用户签到日历和用户券包。
- 领券记录支持筛选、单条删除和批量删除，已使用券保留订单审计记录。
- 商品配置页可预选优惠券，添加购物车后在结算页自动走原生优惠码接口应用。
- 购物车“一键使用优惠券”：未提前选券时可在弹窗选择单张券，或逐张调用原生优惠码接口试用，以结算总额最低的一张作为最终应用券。
- 检测页检查数据表、原生优惠字段、PHP 环境、宿主魔方授权配置和文件完整性。

## 安装

1. 将目录 `qingjiyun_coupon` 上传到 `/public/plugins/addons/`，或将发布压缩包交给魔方商城安装器。
2. 后台进入系统插件列表，安装“轻极云优惠券”。安装过程自动创建 5 张 `qingjiyun_coupon_*` 数据表。
3. 打开插件后台“插件检测”页面确认数据表、原生优惠字段和魔方系统授权码配置均正常。
4. 在后台“券模板”创建模板，再通过“批量发券”或“签到奖励”启用营销流程。

插件依赖宿主已有的 `configuration.system_license` 授权配置进行状态检测；当前工程没有外部插件授权服务地址，因此不会虚构远程激活请求。

## 下单页集成

在实际启用的购物车主题 `configureproduct.tpl` 与 `viewcart.tpl` 底部分别追加检测页生成的代码。当前仓库的 `/public/themes/cart/server/configureproduct.tpl` 和 `/public/themes/cart/server/viewcart.tpl` 已加入接入代码；部署本版本时请将这两张主题模板与插件目录一同覆盖。

商品配置页代码示例：

```html
{php}
    $qjyUid = isset(request()->uid) ? intval(request()->uid) : 0;
    $qjyCouponData = \addons\qingjiyun_coupon\common\ThemeCouponProvider::usableCoupons($qjyUid);
    $qjyCouponPrefix = (new \addons\qingjiyun_coupon\common\CouponService())->codePrefix();
{/php}
<script>window.QingjiyunCouponConfig={coupons:{:json_encode($qjyCouponData, JSON_UNESCAPED_UNICODE)},codePrefix:{:json_encode($qjyCouponPrefix, JSON_UNESCAPED_UNICODE)},loggedIn:{:json_encode($qjyUid > 0)}};</script>
<script src="/plugins/addons/qingjiyun_coupon/assets/configure-coupon.js?v=1.0.24"></script>
```

结算页代码示例：

```html
{php}
    $qjyUid = isset(request()->uid) ? intval(request()->uid) : 0;
    $qjyCouponData = \addons\qingjiyun_coupon\common\ThemeCouponProvider::usableCoupons($qjyUid);
    $qjyCouponPrefix = (new \addons\qingjiyun_coupon\common\CouponService())->codePrefix();
{/php}
<script>window.QingjiyunCouponConfig={coupons:{:json_encode($qjyCouponData, JSON_UNESCAPED_UNICODE)},codePrefix:{:json_encode($qjyCouponPrefix, JSON_UNESCAPED_UNICODE)},loggedIn:{:json_encode($qjyUid > 0)}};</script>
<script src="/plugins/addons/qingjiyun_coupon/assets/cart.js?v=1.0.24"></script>
```

`configure-coupon.js` 将已选券写入原配置表单并临时记录到下一次结算；`cart.js` 已适配当前主题的 `#promo input[name="promo"]` 结构和原生 `statuscart=promo`、`statuscart=removepromo` AJAX 接口，进入结算页后自动应用配置页选中的券。配置页与结算页直接内嵌当前用户可用券，不再请求前台 `/addons` JSON 接口。

核心购物车控制器为加密交付，仓库中无法确认服务端优惠码钩子。若另有自定义主题或自定义提交入口，请沿用检测页生成的内嵌券数据片段，再交给原生优惠码接口结算。

## 数据与卸载

插件创建以下五张表：

- `qingjiyun_coupon_template`
- `qingjiyun_coupon_user`
- `qingjiyun_coupon_signin_rule`
- `qingjiyun_coupon_signin_log`
- `qingjiyun_coupon_risk_log`

每次发放同时在原生 `promo_code` 创建一条一次性券码，默认前缀为 `qingjiyun_`，可在后台基础设置中修改。卸载插件时将删除上述五张表，并按插件备注清理本插件创建的原生优惠码。
