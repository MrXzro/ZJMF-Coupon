<script src="/plugins/addons/qingjiyun_coupon/assets/libs/vue.global.prod.js"></script>
<script src="/plugins/addons/qingjiyun_coupon/assets/libs/naive.prod.js"></script>

<style>
#qjy-wallet-app[v-cloak] { display: none; }
#qjy-wallet-app { max-width: 1140px; margin: 0 auto; }
.qjy-wallet-hero { overflow: hidden; border: 0; color: #fff; background: linear-gradient(122deg, #135fd1 0%, #277bea 50%, #53b8f0 100%); }
.qjy-wallet-hero .n-card__content { position: relative; padding: 30px 38px; }
.qjy-wallet-hero .n-card__content::after { content: ""; position: absolute; width: 270px; height: 270px; top: -148px; right: -76px; border-radius: 50%; background: rgba(255,255,255,.12); }
.qjy-hero-layout { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; gap: 30px; }
.qjy-kicker { margin-bottom: 8px; color: rgba(255,255,255,.78); font-size: 12px; font-weight: 700; letter-spacing: 2px; }
#qjy-wallet-app .qjy-wallet-title { margin: 0 0 8px; color: #fff !important; font-size: 28px; font-weight: 700; }
.qjy-wallet-subtitle { margin: 0 0 20px; color: rgba(255,255,255,.86); line-height: 1.7; }
.qjy-signin-link { display: inline-flex; color: #fff; padding: 7px 15px; border: 1px solid rgba(255,255,255,.38); border-radius: 18px; background: rgba(255,255,255,.1); transition: background .2s; }
.qjy-signin-link:hover { color: #fff; text-decoration: none; background: rgba(255,255,255,.2); }
.qjy-stats { display: flex; gap: 12px; }
.qjy-stat { min-width: 100px; padding: 15px 13px; border-radius: 13px; text-align: center; background: rgba(255,255,255,.14); }
.qjy-stat-number { font-size: 30px; font-weight: 700; line-height: 1.1; }
.qjy-stat-label { margin-top: 5px; color: rgba(255,255,255,.82); font-size: 12px; }
.qjy-wallet-list { margin-top: 18px; }
.qjy-filter-bar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 18px; }
.qjy-filter-note { color: #64748b; font-size: 13px; }
.qjy-coupon-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.qjy-coupon { position: relative; display: flex; min-height: 110px; border: 1px solid #e8eef7; border-radius: 14px; overflow: hidden; background: #fff; transition: transform .2s, box-shadow .2s; }
.qjy-coupon:hover { transform: translateY(-2px); box-shadow: 0 9px 23px rgba(15,23,42,.07); }
.qjy-coupon-side { position: relative; width: 82px; flex-shrink: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 9px 5px; color: #fff; background: linear-gradient(145deg, #1769dc, #40a3ef); }
.qjy-coupon-side::before,
.qjy-coupon-side::after { content: ""; position: absolute; right: -9px; width: 18px; height: 18px; border-radius: 50%; background: #fff; }
.qjy-coupon-side::before { top: -9px; }
.qjy-coupon-side::after { bottom: -9px; }
.qjy-coupon.fixed .qjy-coupon-side { background: linear-gradient(145deg, #1769dc, #40a3ef); }
.qjy-coupon.percent .qjy-coupon-side { background: linear-gradient(145deg, #1769dc, #40a3ef); }
.qjy-coupon.override .qjy-coupon-side { background: linear-gradient(145deg, #7855e9, #b16df3); }
.qjy-coupon.free .qjy-coupon-side { background: linear-gradient(145deg, #0eaf71, #36d39a); }
.qjy-coupon.used,
.qjy-coupon.expired { background: #fbfcfe; }
.qjy-coupon.used .qjy-coupon-side,
.qjy-coupon.expired .qjy-coupon-side { background: linear-gradient(145deg, #94a3b8, #cbd5e1); }
.qjy-value { white-space: nowrap; font-size: 18px; font-weight: 700; line-height: 1.2; }
.qjy-value.free { font-size: 20px; }
.qjy-type { margin-top: 3px; color: rgba(255,255,255,.92); font-size: 12px; }
.qjy-coupon-main { display: flex; flex: 1; flex-direction: column; min-width: 0; padding: 9px 10px 8px 12px; }
.qjy-coupon-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
.qjy-coupon-title { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-right: 5px; color: #1e293b; font-size: 15px; font-weight: 600; }
.qjy-coupon-desc { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-height: 16px; margin-top: 2px; color: #64748b; font-size: 12px; }
.qjy-meta { display: flex; flex-wrap: wrap; gap: 3px 10px; margin-top: 3px; color: #64748b; font-size: 12px; }
.qjy-urgent { color: #f97316; font-weight: 500; }
.qjy-code-line { display: flex; align-items: center; gap: 6px; margin-top: auto; padding-top: 6px; }
.qjy-code { overflow: hidden; flex: 1; text-overflow: ellipsis; white-space: nowrap; padding: 4px 7px; border-radius: 6px; color: #475569; background: #f5f7fb; font-family: Consolas, Monaco, monospace; font-size: 12px; }
.qjy-empty { padding: 54px 0 34px; }
@media (max-width: 1199px) {
    .qjy-coupon-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767px) {
    .qjy-wallet-hero .n-card__content { padding: 25px 20px; }
    .qjy-hero-layout { flex-direction: column; align-items: stretch; }
    .qjy-stats { justify-content: space-between; }
    .qjy-stat { flex: 1; min-width: 0; }
    .qjy-filter-bar { flex-direction: column; align-items: stretch; }
    .qjy-coupon-grid { grid-template-columns: 1fr; }
    .qjy-coupon-side { width: 92px; }
    .qjy-value { font-size: 18px; }
    .qjy-coupon-main { padding-left: 12px; }
}
</style>

<div id="qjy-wallet-app" v-cloak>
    <n-config-provider :locale="zhCN" :date-locale="dateZhCN">
        <n-card class="qjy-wallet-hero">
            <div class="qjy-hero-layout">
                <div>
                    <div class="qjy-kicker">MY COUPONS</div>
                    <h2 class="qjy-wallet-title">我的优惠券</h2>
                    <p class="qjy-wallet-subtitle">券码仅限当前账户使用，可复制后在购物车结算时使用。</p>
                    <a class="qjy-signin-link" :href="signinUrl">去签到领取奖励</a>
                </div>
                <div class="qjy-stats">
                    <div class="qjy-stat">
                        <div class="qjy-stat-number">{{ counts.unused }}</div>
                        <div class="qjy-stat-label">可使用</div>
                    </div>
                    <div class="qjy-stat">
                        <div class="qjy-stat-number">{{ counts.used }}</div>
                        <div class="qjy-stat-label">已使用</div>
                    </div>
                    <div class="qjy-stat">
                        <div class="qjy-stat-number">{{ counts.total }}</div>
                        <div class="qjy-stat-label">累计领取</div>
                    </div>
                </div>
            </div>
        </n-card>

        <n-card class="qjy-wallet-list">
            <div class="qjy-filter-bar">
                <n-tabs v-model:value="filter" type="segment" size="large">
                    <n-tab name="all">全部 {{ counts.total }}</n-tab>
                    <n-tab name="unused">可使用 {{ counts.unused }}</n-tab>
                    <n-tab name="used">已使用 {{ counts.used }}</n-tab>
                    <n-tab name="expired">已过期 {{ counts.expired }}</n-tab>
                </n-tabs>
                <span class="qjy-filter-note">复制前会校验可用状态</span>
            </div>

            <div v-if="filteredCoupons.length" class="qjy-coupon-grid">
                <div v-for="coupon in filteredCoupons" :key="coupon.id" class="qjy-coupon"
                    :class="[coupon.type, coupon.status]">
                    <div class="qjy-coupon-side">
                        <div class="qjy-value" :class="{ free: coupon.type === 'free' }">{{ couponValue(coupon) }}</div>
                        <div class="qjy-type">{{ typeText(coupon.type) }}</div>
                    </div>
                    <div class="qjy-coupon-main">
                        <div class="qjy-coupon-head">
                            <div class="qjy-coupon-title" :title="coupon.title">{{ coupon.title || '优惠券' }}</div>
                            <n-tag size="small" :type="statusType(coupon.status)" :bordered="false">
                                {{ statusText(coupon.status) }}
                            </n-tag>
                        </div>
                        <div class="qjy-coupon-desc" :title="coupon.description">{{ coupon.description || '结算时使用本券享受专属优惠' }}</div>
                        <div class="qjy-meta">
                            <span>{{ expirationText(coupon) }}</span>
                            <span v-if="isExpiring(coupon)" class="qjy-urgent">{{ expiringText(coupon) }}</span>
                            <span v-if="Number(coupon.require_paid) === 1">需完成过支付</span>
                        </div>
                        <div class="qjy-code-line">
                            <span class="qjy-code" :title="coupon.code">{{ coupon.code }}</span>
                            <n-button v-if="coupon.status === 'unused'" size="small" type="primary" secondary
                                :loading="copyingId === coupon.id" @click="copyCoupon(coupon)">复制券码</n-button>
                        </div>
                    </div>
                </div>
            </div>
            <n-empty v-else class="qjy-empty" :description="emptyText">
                <template #extra>
                    <n-button v-if="filter === 'all'" type="primary" secondary tag="a" :href="signinUrl">去签到领券</n-button>
                </template>
            </n-empty>
        </n-card>
    </n-config-provider>
</div>

<script>
(function () {
    const { createApp, ref, computed } = Vue;
    const { createDiscreteApi, zhCN, dateZhCN } = naive;
    const { message } = createDiscreteApi(["message"]);
    const initialCoupons = {:json_encode($Coupons, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};
    const signinUrl = {:json_encode($SigninUrl, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};

    function asArray(value) {
        return Array.isArray(value) ? value : [];
    }

    function formatAmount(value) {
        var amount = Number(value) || 0;
        return amount % 1 === 0 ? String(amount) : amount.toFixed(2).replace(/0+$/, "").replace(/\.$/, "");
    }

    const app = createApp({
        setup() {
            const coupons = ref(asArray(initialCoupons));
            const filter = ref("all");
            const copyingId = ref(null);

            const counts = computed(function () {
                return coupons.value.reduce(function (result, coupon) {
                    result.total++;
                    if (coupon.status === "unused") {
                        result.unused++;
                    } else if (coupon.status === "used") {
                        result.used++;
                    } else if (coupon.status === "expired") {
                        result.expired++;
                    }
                    return result;
                }, { total: 0, unused: 0, used: 0, expired: 0 });
            });

            const filteredCoupons = computed(function () {
                if (filter.value === "all") {
                    return coupons.value;
                }
                return coupons.value.filter(function (coupon) { return coupon.status === filter.value; });
            });

            const emptyText = computed(function () {
                if (filter.value === "unused") return "暂时没有可使用的优惠券";
                if (filter.value === "used") return "还没有使用过的优惠券";
                if (filter.value === "expired") return "没有已过期的优惠券";
                return "券包还是空的，签到奖励会出现在这里";
            });

            function couponValue(coupon) {
                if (coupon.type === "fixed") return "减 " + formatAmount(coupon.value);
                if (coupon.type === "percent") return formatAmount(coupon.value) + "%";
                if (coupon.type === "override") return formatAmount(coupon.value) + " 元";
                return "免费";
            }

            function typeText(type) {
                if (type === "fixed") return "元优惠券";
                if (type === "percent") return "折扣券";
                if (type === "override") return "一口价券";
                return "免费券";
            }

            function statusText(status) {
                if (status === "unused") return "可使用";
                if (status === "used") return "已使用";
                return "已过期";
            }

            function statusType(status) {
                if (status === "unused") return "success";
                if (status === "used") return "default";
                return "warning";
            }

            function expirationText(coupon) {
                if (!Number(coupon.expires_at)) {
                    return "永久有效";
                }
                var date = new Date(Number(coupon.expires_at) * 1000);
                var pad = function (value) { return value < 10 ? "0" + value : String(value); };
                return "有效至 " + date.getFullYear() + "-" + pad(date.getMonth() + 1) + "-" + pad(date.getDate());
            }

            function remainingDays(coupon) {
                return Math.ceil((Number(coupon.expires_at) * 1000 - Date.now()) / 86400000);
            }

            function isExpiring(coupon) {
                var remaining = remainingDays(coupon);
                return coupon.status === "unused" && Number(coupon.expires_at) > 0 && remaining >= 0 && remaining <= 7;
            }

            function expiringText(coupon) {
                var remaining = remainingDays(coupon);
                return remaining === 0 ? "今日到期" : "剩余 " + remaining + " 天";
            }

            function writeClipboard(text) {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                }
                return new Promise(function (resolve, reject) {
                    var input = document.createElement("textarea");
                    input.value = text;
                    input.setAttribute("readonly", "readonly");
                    input.style.position = "fixed";
                    input.style.left = "-9999px";
                    document.body.appendChild(input);
                    input.select();
                    var copied = document.execCommand("copy");
                    document.body.removeChild(input);
                    copied ? resolve() : reject(new Error("复制失败"));
                });
            }

            async function copyCoupon(coupon) {
                copyingId.value = coupon.id;
                try {
                    await writeClipboard(coupon.code);
                    message.success("券码已复制，可在购物车结算时使用");
                } catch (error) {
                    message.error("复制券码失败，请稍后重试");
                } finally {
                    copyingId.value = null;
                }
            }

            return {
                zhCN: zhCN,
                dateZhCN: dateZhCN,
                signinUrl: signinUrl,
                filter: filter,
                copyingId: copyingId,
                counts: counts,
                filteredCoupons: filteredCoupons,
                emptyText: emptyText,
                couponValue: couponValue,
                typeText: typeText,
                statusText: statusText,
                statusType: statusType,
                expirationText: expirationText,
                isExpiring: isExpiring,
                expiringText: expiringText,
                copyCoupon: copyCoupon
            };
        }
    });

    app.use(naive);
    app.mount("#qjy-wallet-app");
}());
</script>
