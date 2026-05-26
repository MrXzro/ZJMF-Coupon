<script src="/plugins/addons/qingjiyun_coupon/assets/libs/vue.global.prod.js"></script>
<script src="/plugins/addons/qingjiyun_coupon/assets/libs/naive.prod.js"></script>
<script src="/plugins/addons/qingjiyun_coupon/assets/libs/axios.min.js"></script>

<style>
#qjy-center-app[v-cloak] { display: none; }
#qjy-center-app { max-width: 1140px; margin: 0 auto; color: #172554; }
.qjy-center-hero { position: relative; overflow: hidden; border: 0; color: #fff; background: linear-gradient(122deg, #135fd1 0%, #277bea 50%, #53b8f0 100%); }
.qjy-center-hero .n-card__content { position: relative; z-index: 1; padding: 30px 38px; }
.qjy-center-hero .n-card__content::after { content: ""; position: absolute; width: 270px; height: 270px; top: -148px; right: -76px; border-radius: 50%; background: rgba(255,255,255,.12); }
.qjy-center-hero-inner { position: relative; z-index: 1; max-width: 670px; }
.qjy-center-kicker { margin-bottom: 8px; color: rgba(255,255,255,.78); font-size: 12px; font-weight: 700; letter-spacing: 2px; }
.qjy-center-title { margin: 0 0 8px; color: #fff !important; font-size: 30px; font-weight: 700; }
.qjy-center-subtitle { margin: 0 0 20px; max-width: 620px; color: rgba(255,255,255,.86); font-size: 14px; line-height: 1.7; }
.qjy-center-features { display: flex; flex-wrap: wrap; gap: 12px; }
.qjy-feature { display: flex; align-items: center; gap: 10px; min-width: 150px; padding: 10px 12px; border-radius: 13px; background: rgba(255,255,255,.14); box-shadow: inset 0 1px 0 rgba(255,255,255,.16); }
.qjy-feature-icon { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; flex-shrink: 0; border-radius: 10px; color: #1677ff; background: #fff; font-size: 15px; font-weight: 800; }
.qjy-feature-title { color: #fff; font-size: 14px; font-weight: 700; }
.qjy-feature-note { margin-top: 2px; color: rgba(255,255,255,.78); font-size: 12px; }
.qjy-center-layout { display: grid; grid-template-columns: 218px minmax(0, 1fr); gap: 18px; margin-top: 18px; }
.qjy-side-grid { min-width: 0; }
.qjy-side-card { padding: 16px; border: 1px solid #e8eef7; border-radius: 14px; background: #fff; box-shadow: 0 9px 23px rgba(15,23,42,.05); }
.qjy-side-card + .qjy-side-card { margin-top: 14px; }
.qjy-side-title { margin: 0 0 3px; color: #1e293b; font-size: 15px; font-weight: 700; }
.qjy-side-desc { margin-bottom: 12px; color: #64748b; font-size: 12px; }
.qjy-side-btn { display: flex; align-items: center; justify-content: space-between; width: 100%; min-height: 40px; margin-top: 8px; padding: 0 11px; border: 1px solid #e8eef7; border-radius: 10px; color: #475569; background: #fff; cursor: pointer; transition: all .18s; }
.qjy-side-btn:hover { color: #1677ff; border-color: #cfe4ff; background: #f7fbff; }
.qjy-side-btn.active { color: #1677ff; border-color: #b9dcff; background: #eef6ff; box-shadow: inset 0 0 0 1px rgba(22,119,255,.08); }
.qjy-side-left { display: inline-flex; align-items: center; gap: 8px; min-width: 0; }
.qjy-side-icon { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; flex-shrink: 0; border-radius: 8px; color: #1677ff; background: #eaf4ff; font-size: 12px; font-weight: 800; }
.qjy-side-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; font-weight: 700; }
.qjy-side-count { flex-shrink: 0; color: #64748b; font-size: 12px; }
.qjy-main-card { min-height: 420px; padding: 22px 24px 26px; border: 1px solid #e8eef7; border-radius: 14px; background: #fff; box-shadow: 0 9px 23px rgba(15,23,42,.05); }
.qjy-main-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding-bottom: 16px; border-bottom: 1px solid #edf2f7; }
.qjy-main-title { margin: 0; color: #1e293b; font-size: 20px; font-weight: 700; }
.qjy-main-title small { color: #1677ff; font-size: 13px; }
.qjy-main-note { margin-top: 5px; color: #64748b; font-size: 13px; }
.qjy-main-count { color: #64748b; font-size: 13px; white-space: nowrap; }
.qjy-main-count strong { color: #1677ff; }
.qjy-coupon-list { display: flex; flex-direction: column; gap: 12px; padding-top: 18px; }
.qjy-center-ticket { display: grid; grid-template-columns: 94px minmax(0, 1fr) 132px; min-height: 118px; overflow: hidden; border: 1px solid #e8eef7; border-radius: 14px; background: #fff; transition: transform .18s, box-shadow .18s; }
.qjy-center-ticket:hover { transform: translateY(-2px); box-shadow: 0 9px 23px rgba(15,23,42,.07); }
.qjy-center-ticket.claimed,
.qjy-center-ticket.soldout { background: #fbfcfe; }
.qjy-ticket-face { position: relative; display: flex; flex-direction: column; justify-content: center; align-items: center; color: #fff; background: linear-gradient(145deg, #1769dc, #40a3ef); }
.qjy-ticket-face::before,
.qjy-ticket-face::after { content: ""; position: absolute; right: -9px; width: 18px; height: 18px; border-radius: 50%; background: #fff; }
.qjy-ticket-face::before { top: -9px; }
.qjy-ticket-face::after { bottom: -9px; }
.qjy-center-ticket.fixed .qjy-ticket-face,
.qjy-center-ticket.percent .qjy-ticket-face { background: linear-gradient(145deg, #1769dc, #40a3ef); }
.qjy-center-ticket.override .qjy-ticket-face { background: linear-gradient(145deg, #7855e9, #b16df3); }
.qjy-center-ticket.free .qjy-ticket-face { background: linear-gradient(145deg, #0eaf71, #36d39a); }
.qjy-center-ticket.claimed .qjy-ticket-face,
.qjy-center-ticket.soldout .qjy-ticket-face { opacity: .92; }
.qjy-ticket-mark { display: none; }
.qjy-ticket-value { white-space: nowrap; font-size: 18px; font-weight: 700; line-height: 1.2; }
.qjy-ticket-type { margin-top: 4px; color: rgba(255,255,255,.92); font-size: 12px; }
.qjy-ticket-body { min-width: 0; padding: 13px 16px 12px; }
.qjy-ticket-title { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin: 0 0 4px; color: #1e293b; font-size: 15px; font-weight: 700; }
.qjy-ticket-desc { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; min-height: 18px; margin: 0 0 8px; color: #64748b; font-size: 12px; line-height: 1.55; }
.qjy-ticket-tags { display: flex; flex-wrap: wrap; gap: 5px; }
.qjy-ticket-tag { display: inline-flex; align-items: center; max-width: 100%; height: 22px; padding: 0 8px; border-radius: 999px; color: #1677ff; background: #edf6ff; font-size: 12px; }
.qjy-ticket-rules { margin-top: 10px; padding: 10px 12px; border-radius: 10px; color: #64748b; background: #f8fafc; font-size: 12px; line-height: 1.75; }
.qjy-ticket-actions { display: flex; flex-direction: column; justify-content: center; align-items: stretch; gap: 8px; padding: 14px; border-left: 1px dashed #e3eaf5; background: linear-gradient(180deg, #fff, #fbfdff); }
.qjy-ticket-status { text-align: center; color: #16a064; font-size: 12px; font-weight: 700; }
.qjy-ticket-meta { min-height: 18px; text-align: center; color: #64748b; font-size: 12px; line-height: 1.45; }
.qjy-ticket-actions .n-button { min-width: 0; }
.qjy-ticket-actions .n-button__content { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.qjy-rule-link { align-self: center; border: 0; color: #1677ff; background: transparent; font-size: 12px; cursor: pointer; }
.qjy-empty { padding: 54px 0; }
@media (max-width: 991px) {
    .qjy-center-layout { grid-template-columns: 1fr; }
    .qjy-side-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .qjy-side-card + .qjy-side-card { margin-top: 0; }
}
@media (max-width: 767px) {
    #qjy-center-app { max-width: none; }
    .qjy-center-hero .n-card__content { padding: 26px 20px; }
    .qjy-center-title { font-size: 25px; }
    .qjy-center-subtitle { margin-bottom: 16px; }
    .qjy-center-features { display: grid; grid-template-columns: 1fr; gap: 8px; }
    .qjy-feature { min-width: 0; padding: 9px 10px; }
    .qjy-center-layout { margin-top: 14px; gap: 14px; }
    .qjy-side-grid { grid-template-columns: 1fr; }
    .qjy-main-card { padding: 18px 14px 22px; border-radius: 14px; }
    .qjy-main-head { display: block; }
    .qjy-main-count { margin-top: 8px; }
    .qjy-center-ticket { display: block; min-height: 0; }
    .qjy-ticket-face { flex-direction: row; justify-content: flex-start; gap: 10px; height: 64px; padding: 0 16px; }
    .qjy-ticket-face::before, .qjy-ticket-face::after { display: none; }
    .qjy-ticket-value { font-size: 18px; }
    .qjy-ticket-type { margin-top: 0; padding: 2px 9px; border-radius: 999px; background: rgba(255,255,255,.16); }
    .qjy-ticket-body { padding: 13px 14px 11px; }
    .qjy-ticket-actions { padding: 12px 14px 14px; border-left: 0; border-top: 1px dashed #e3eaf5; }
    .qjy-ticket-status, .qjy-ticket-meta { text-align: left; }
    .qjy-rule-link { align-self: flex-start; }
}
</style>

<div id="qjy-center-app" v-cloak>
    <n-config-provider :locale="zhCN" :date-locale="dateZhCN">
        <n-card class="qjy-center-hero">
            <div class="qjy-center-hero-inner">
                <div class="qjy-center-kicker">优惠券领取中心</div>
                <h1 class="qjy-center-title">领券中心</h1>
                <p class="qjy-center-subtitle">专属云产品优惠券集中发放，支持满减、折扣与特价活动。领取后可直接用于下单抵扣，并可在个人券包查看领取与使用状态。</p>
                <div class="qjy-center-features">
                    <div class="qjy-feature">
                        <span class="qjy-feature-icon">多</span>
                        <div>
                            <div class="qjy-feature-title">多种优惠券</div>
                            <div class="qjy-feature-note">满足不同云产品场景</div>
                        </div>
                    </div>
                    <div class="qjy-feature">
                        <span class="qjy-feature-icon">官</span>
                        <div>
                            <div class="qjy-feature-title">官方权益</div>
                            <div class="qjy-feature-note">领取记录长期留存</div>
                        </div>
                    </div>
                    <div class="qjy-feature">
                        <span class="qjy-feature-icon">限</span>
                        <div>
                            <div class="qjy-feature-title">限时有效</div>
                            <div class="qjy-feature-note">可领取活动实时更新</div>
                        </div>
                    </div>
                </div>
            </div>
        </n-card>

        <div class="qjy-center-layout">
            <aside class="qjy-side-grid">
                <div class="qjy-side-card">
                    <h3 class="qjy-side-title">页面切换</h3>
                    <div class="qjy-side-desc">分类与记录均可在此快速查看</div>
                    <button class="qjy-side-btn" :class="{ active: page === 'available' }" type="button" @click="page = 'available'">
                        <span class="qjy-side-left"><span class="qjy-side-icon">领</span><span class="qjy-side-label">可领取优惠券</span></span>
                        <span class="qjy-side-count">{{ availableCount }}</span>
                    </button>
                    <button class="qjy-side-btn" :class="{ active: page === 'claimed' }" type="button" @click="page = 'claimed'">
                        <span class="qjy-side-left"><span class="qjy-side-icon">包</span><span class="qjy-side-label">我的已领取</span></span>
                        <span class="qjy-side-count">{{ claimedCount }}</span>
                    </button>
                </div>

                <div class="qjy-side-card">
                    <h3 class="qjy-side-title">优惠券分类</h3>
                    <div class="qjy-side-desc">更快筛选你的实惠权益</div>
                    <button v-for="item in categories" :key="item.key" class="qjy-side-btn"
                        :class="{ active: category === item.key }" type="button" @click="category = item.key">
                        <span class="qjy-side-left"><span class="qjy-side-icon">{{ item.icon }}</span><span class="qjy-side-label">{{ item.label }}</span></span>
                        <span class="qjy-side-count">{{ categoryCount(item.key) }}</span>
                    </button>
                </div>
            </aside>

            <main class="qjy-main-card">
                <div class="qjy-main-head">
                    <div>
                        <h2 class="qjy-main-title">{{ pageTitle }} <small>({{ filteredCoupons.length }})</small></h2>
                        <div class="qjy-main-note">{{ page === 'available' ? '展示当前账号可主动领取的优惠券，领取后会自动进入我的优惠券。' : '这里展示您已经领取过的优惠券，可复制券码或前往购物车使用。' }}</div>
                    </div>
                    <div class="qjy-main-count">可领取 <strong>{{ availableCount }}</strong> 个</div>
                </div>

                <div v-if="filteredCoupons.length" class="qjy-coupon-list">
                    <article v-for="coupon in filteredCoupons" :key="coupon.item_key || coupon.id" class="qjy-center-ticket" :class="[coupon.type, { claimed: coupon.claimed, soldout: coupon.sold_out }]">
                        <div class="qjy-ticket-face">
                            <div class="qjy-ticket-mark">券</div>
                            <div class="qjy-ticket-value">{{ couponValue(coupon) }}</div>
                            <div class="qjy-ticket-type">{{ typeText(coupon.type) }}</div>
                        </div>
                        <div class="qjy-ticket-body">
                            <h3 class="qjy-ticket-title">{{ coupon.title || '优惠券' }}</h3>
                            <p class="qjy-ticket-desc">{{ coupon.description || '领取后可在购物车结算时使用本券享受专属优惠。' }}</p>
                            <div class="qjy-ticket-tags">
                                <span class="qjy-ticket-tag" v-for="tag in coupon.tags" :key="tag">{{ tag }}</span>
                                <span class="qjy-ticket-tag">{{ coupon.product_text }}</span>
                                <span class="qjy-ticket-tag">{{ coupon.cycle_text }}</span>
                                <span v-if="coupon.requires_text" class="qjy-ticket-tag">{{ coupon.requires_text }}</span>
                            </div>
                            <n-collapse-transition :show="!!openedRules[coupon.item_key || coupon.id]">
                                <div class="qjy-ticket-rules">
                                    <div>有效期：{{ expireText(coupon) }}</div>
                                    <div>使用范围：{{ coupon.product_text }}，{{ coupon.cycle_text }}</div>
                                    <div v-if="coupon.requires_text">前置条件：{{ coupon.requires_text }}</div>
                                    <div>领取限制：{{ limitText(coupon) }}{{ coupon.quota > 0 ? '，剩余 ' + coupon.quota_left + ' 张' : '' }}</div>
                                    <div v-if="coupon.code">券码：{{ coupon.code }}</div>
                                </div>
                            </n-collapse-transition>
                        </div>
                        <div class="qjy-ticket-actions">
                            <div>
                                <div class="qjy-ticket-status">{{ ticketStatus(coupon) }}</div>
                                <div class="qjy-ticket-meta">{{ ticketMeta(coupon) }}</div>
                            </div>
                            <n-button v-if="coupon.can_claim" type="primary" :loading="claimingId === coupon.id || coupon.claiming" @click="claimCoupon(coupon)">
                                {{ coupon.claiming ? '领取中' : (coupon.claimed ? '再领一张' : '立即领取') }}
                            </n-button>
                            <n-button v-else-if="coupon.claimed" type="primary" secondary tag="a" :href="cartUrl">前往购物车</n-button>
                            <n-button v-else type="primary" disabled :title="coupon.claim_reason || '暂不可领'">{{ claimButtonText(coupon) }}</n-button>
                            <button class="qjy-rule-link" type="button" @click="toggleRule(coupon.item_key || coupon.id)">{{ openedRules[coupon.item_key || coupon.id] ? '收起规则' : '查看规则' }}</button>
                        </div>
                    </article>
                </div>

                <n-empty v-else class="qjy-empty" :description="emptyText">
                    <template #extra>
                        <n-button v-if="page === 'claimed'" type="primary" secondary @click="page = 'available'">去领券</n-button>
                        <n-button v-else type="primary" secondary tag="a" :href="walletUrl">查看我的优惠券</n-button>
                    </template>
                </n-empty>
            </main>
        </div>
    </n-config-provider>
</div>

<script>
(function () {
    const { createApp, ref, computed } = Vue;
    const { createDiscreteApi, zhCN, dateZhCN } = naive;
    const { message } = createDiscreteApi(["message"]);
    const initialTemplates = {:json_encode($Templates, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};
    const claimUrl = {:json_encode($ClaimUrl, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};
    const walletUrl = {:json_encode($WalletUrl, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};
    const cartUrl = {:json_encode($CartUrl, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};

    function asArray(value) {
        return Array.isArray(value) ? value : [];
    }

    function formatAmount(value) {
        var amount = Number(value) || 0;
        return amount % 1 === 0 ? String(amount) : amount.toFixed(2).replace(/0+$/, "").replace(/\.$/, "");
    }

    const app = createApp({
        setup() {
            const coupons = ref(asArray(initialTemplates));
            const page = ref("available");
            const category = ref("all");
            const claimingId = ref(0);
            const openedRules = ref({});
            const categories = [
                { key: "all", label: "全部优惠券", icon: "全" },
                { key: "new", label: "新用户可领", icon: "新" },
                { key: "once", label: "限领一次", icon: "限" },
                { key: "percent", label: "折扣券", icon: "折" },
                { key: "fixed", label: "满减券", icon: "减" }
            ];

            const availableCount = computed(function () {
                return coupons.value.filter(function (coupon) {
                    return coupon.item_kind !== "record" && !!coupon.can_claim;
                }).length;
            });

            const claimedCount = computed(function () {
                return coupons.value.filter(function (coupon) { return coupon.item_kind === "record"; }).length;
            });

            const pageCoupons = computed(function () {
                return coupons.value.filter(function (coupon) {
                    if (page.value === "claimed") {
                        return coupon.item_kind === "record";
                    }
                    return coupon.item_kind !== "record" && (!coupon.claimed || !!coupon.can_claim);
                });
            });

            const filteredCoupons = computed(function () {
                return pageCoupons.value.filter(function (coupon) {
                    return categoryMatches(coupon, category.value);
                });
            });

            const pageTitle = computed(function () {
                return page.value === "claimed" ? "我的已领取" : "全部优惠券";
            });

            const emptyText = computed(function () {
                if (page.value === "claimed") {
                    return "您还没有领取过优惠券";
                }
                return "当前分类暂无可领取优惠券";
            });

            function categoryMatches(coupon, key) {
                if (key === "all") return true;
                if (key === "new") return Number(coupon.new_user_only) === 1 || Number(coupon.new_user_auto) === 1;
                if (key === "once") return Number(coupon.once_per_client) === 1;
                if (key === "percent") return coupon.type === "percent";
                if (key === "fixed") return coupon.type === "fixed";
                return true;
            }

            function categoryCount(key) {
                return pageCoupons.value.filter(function (coupon) {
                    return categoryMatches(coupon, key);
                }).length;
            }

            function couponValue(coupon) {
                if (coupon.type === "fixed") return "减 " + formatAmount(coupon.value);
                if (coupon.type === "percent") return formatAmount(coupon.value) + "%";
                if (coupon.type === "override") return formatAmount(coupon.value) + " 元";
                return "免费";
            }

            function typeText(type) {
                if (type === "fixed") return "满减券";
                if (type === "percent") return "折扣券";
                if (type === "override") return "一口价";
                return "免费券";
            }

            function statusText(status) {
                if (status === "unused") return "已领取";
                if (status === "used") return "已使用";
                if (status === "expired") return "已过期";
                return "已领取";
            }

            function ticketStatus(coupon) {
                if (coupon.claimed && coupon.can_claim && Number(coupon.once_per_client) !== 1) {
                    return "已领取，可再领";
                }
                if (coupon.claimed) {
                    return statusText(coupon.claimed_status);
                }
                return coupon.can_claim ? "可领取" : "暂不可领";
            }

            function ticketMeta(coupon) {
                if (coupon.claimed) {
                    return expireText(coupon);
                }
                return coupon.claim_reason || expireText(coupon);
            }

            function claimButtonText(coupon) {
                var reason = String(coupon.claim_reason || "");
                if (coupon.sold_out || reason.indexOf("领完") !== -1) return "已领完";
                if (reason.indexOf("实名") !== -1) return "需实名";
                if (reason.indexOf("支付") !== -1) return "需支付";
                if (reason.indexOf("注册") !== -1 || reason.indexOf("新用户") !== -1) return "新人专享";
                if (reason.indexOf("已领取") !== -1 || reason.indexOf("领取过") !== -1) return "已领取";
                if (reason.indexOf("使用") !== -1) return "先使用";
                return "暂不可领";
            }

            function limitText(coupon) {
                var parts = [];
                if (Number(coupon.new_user_only) === 1) {
                    parts.push("注册 " + (Number(coupon.new_user_days) || 7) + " 天内新用户可领");
                }
                parts.push(coupon.once_per_client ? "每个账号同一模板限领一次" : "可重复领取，但需先使用完已领取的同模板券");
                if (Number(coupon.require_realname) === 1) {
                    parts.push("需完成实名认证");
                }
                if (Number(coupon.require_paid) === 1) {
                    parts.push("需完成过支付");
                }
                if (Number(coupon.new_user_auto) === 1) {
                    parts.push("注册满 1 天未领取自动发放");
                }
                return parts.join("，");
            }

            function expireText(coupon) {
                if (coupon.claimed && Number(coupon.expires_at) > 0) {
                    return "有效期至 " + formatDate(coupon.expires_at);
                }
                if (coupon.claimed) {
                    return "永久有效";
                }
                if (Number(coupon.valid_days) > 0) {
                    return "领取后 " + Number(coupon.valid_days) + " 天内有效";
                }
                return "永久有效";
            }

            function formatDate(timestamp) {
                var date = new Date(Number(timestamp) * 1000);
                var pad = function (value) { return value < 10 ? "0" + value : String(value); };
                return date.getFullYear() + "-" + pad(date.getMonth() + 1) + "-" + pad(date.getDate());
            }

            function toggleRule(id) {
                var next = Object.assign({}, openedRules.value);
                next[id] = !next[id];
                openedRules.value = next;
            }

            async function claimCoupon(coupon) {
                if (!coupon.can_claim || coupon.claiming || claimingId.value) {
                    return;
                }
                claimingId.value = coupon.id;
                var previousCanClaim = coupon.can_claim;
                var previousClaimReason = coupon.claim_reason;
                coupon.claiming = true;
                coupon.can_claim = false;
                coupon.claim_reason = "领取中";
                try {
                    var data = new FormData();
                    data.append("template_id", coupon.template_id || coupon.id);
                    var response = await axios.post(claimUrl, data, {
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    });
                    var result = response.data || {};
                    if (result.data && Array.isArray(result.data.templates)) {
                        coupons.value = result.data.templates;
                    }
                    if (result.status !== 200) {
                        if (!result.data || !Array.isArray(result.data.templates)) {
                            coupon.can_claim = previousCanClaim;
                            coupon.claim_reason = previousClaimReason;
                        }
                        message.warning(result.msg || "领取失败");
                        return;
                    }
                    message.success(result.msg || "领取成功");
                    page.value = "claimed";
                    category.value = "all";
                } catch (error) {
                    var result = error.response && error.response.data ? error.response.data : {};
                    if (result.data && Array.isArray(result.data.templates)) {
                        coupons.value = result.data.templates;
                    } else {
                        coupon.can_claim = previousCanClaim;
                        coupon.claim_reason = previousClaimReason;
                    }
                    message.error(result.msg || "领取请求失败，请稍后重试");
                } finally {
                    coupon.claiming = false;
                    claimingId.value = 0;
                }
            }

            return {
                zhCN: zhCN,
                dateZhCN: dateZhCN,
                walletUrl: walletUrl,
                cartUrl: cartUrl,
                page: page,
                category: category,
                categories: categories,
                coupons: coupons,
                availableCount: availableCount,
                claimedCount: claimedCount,
                filteredCoupons: filteredCoupons,
                pageTitle: pageTitle,
                emptyText: emptyText,
                claimingId: claimingId,
                openedRules: openedRules,
                categoryCount: categoryCount,
                couponValue: couponValue,
                typeText: typeText,
                statusText: statusText,
                ticketStatus: ticketStatus,
                ticketMeta: ticketMeta,
                claimButtonText: claimButtonText,
                limitText: limitText,
                expireText: expireText,
                toggleRule: toggleRule,
                claimCoupon: claimCoupon
            };
        }
    });

    app.use(naive);
    app.mount("#qjy-center-app");
}());
</script>
