<script src="/plugins/addons/qingjiyun_coupon/assets/libs/vue.global.prod.js"></script>
<script src="/plugins/addons/qingjiyun_coupon/assets/libs/naive.prod.js"></script>
<script src="/plugins/addons/qingjiyun_coupon/assets/libs/axios.min.js"></script>

<style>
#qjy-signin-app[v-cloak] { display: none; }
#qjy-signin-app { max-width: 1140px; margin: 0 auto; }
#qjy-signin-app .qjy-hero { position: relative; overflow: hidden; border: 0; background: linear-gradient(128deg, #1667d9 0%, #318bf0 48%, #54c0f1 100%); color: #fff; }
#qjy-signin-app .qjy-hero::before,
#qjy-signin-app .qjy-hero::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.12); }
#qjy-signin-app .qjy-hero::before { width: 270px; height: 270px; right: -95px; top: -142px; }
#qjy-signin-app .qjy-hero::after { width: 190px; height: 190px; right: 178px; bottom: -140px; }
#qjy-signin-app .qjy-hero .n-card__content { position: relative; z-index: 1; padding: 34px 42px; }
.qjy-hero-body { display: flex; align-items: center; justify-content: space-between; gap: 28px; }
.qjy-kicker { margin-bottom: 10px; color: rgba(255,255,255,.82); font-size: 12px; font-weight: 700; letter-spacing: 2px; }
#qjy-signin-app .qjy-title { margin: 0 0 10px; color: #fff !important; font-size: 30px; font-weight: 700; }
.qjy-subtitle { max-width: 470px; margin: 0 0 22px; color: rgba(255,255,255,.85); font-size: 14px; line-height: 1.8; }
.qjy-wallet-link { display: inline-flex; color: #fff; padding: 7px 15px; border: 1px solid rgba(255,255,255,.35); border-radius: 18px; background: rgba(255,255,255,.09); transition: background .2s; }
.qjy-wallet-link:hover { color: #fff; text-decoration: none; background: rgba(255,255,255,.18); }
.qjy-streak-panel { min-width: 218px; padding: 17px 22px 20px; text-align: center; border-radius: 18px; background: rgba(255,255,255,.15); box-shadow: inset 0 1px 0 rgba(255,255,255,.2); }
.qjy-streak-number { color: #fff; font-size: 47px; font-weight: 700; line-height: 1; }
.qjy-streak-label { margin: 6px 0 17px; color: rgba(255,255,255,.88); }
.qjy-check-button { width: 170px; font-weight: 600; }
.qjy-feedback { margin-top: 16px; }
.qjy-panels { margin-top: 18px; }
.qjy-card { height: 100%; }
.qjy-section-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.qjy-section-title { margin: 0; color: #1e293b; font-size: 17px; font-weight: 600; }
.qjy-month { color: #64748b; font-size: 13px; }
.qjy-weekdays,
.qjy-calendar { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 9px; }
.qjy-weekdays { margin-bottom: 10px; text-align: center; color: #94a3b8; font-size: 12px; }
.qjy-day { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 66px; border: 1px solid #eef2f7; border-radius: 11px; color: #475569; background: #f8fafc; transition: transform .2s, box-shadow .2s; }
.qjy-day:not(.qjy-empty):hover { transform: translateY(-2px); box-shadow: 0 7px 17px rgba(15,23,42,.07); }
.qjy-day.qjy-empty { border-color: transparent; background: transparent; }
.qjy-day.qjy-signed { border-color: #d3f2e1; color: #13a05f; background: #ecfdf3; font-weight: 600; }
.qjy-day.qjy-today { border-color: #3b82f6; box-shadow: inset 0 0 0 1px #3b82f6; }
.qjy-day-status { height: 16px; margin-top: 4px; color: #16a064; font-size: 11px; }
.qjy-side-stack { display: flex; flex-direction: column; gap: 16px; }
.qjy-next-card { padding: 18px 20px; border-radius: 12px; background: linear-gradient(120deg, #f7f9ff, #eef6ff); }
.qjy-next-title { margin-bottom: 4px; color: #1e293b; font-size: 15px; font-weight: 600; }
.qjy-next-note { margin-bottom: 13px; color: #64748b; font-size: 13px; }
.qjy-earned { margin-bottom: 2px; }
.qjy-earned-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding-top: 7px; }
.qjy-reward-title { color: #1f2937; font-size: 14px; font-weight: 500; }
.qjy-rule-copy { display: flex; align-items: center; gap: 8px; }
.qjy-rule-current { color: #1677ff; }
.qjy-celebrate { animation: qjy-rise .45s ease-out; }
@keyframes qjy-rise {
    0% { transform: translateY(3px); opacity: .75; }
    60% { transform: translateY(-3px); }
    100% { transform: translateY(0); opacity: 1; }
}
@media (max-width: 767px) {
    #qjy-signin-app .qjy-hero .n-card__content { padding: 26px 20px; }
    .qjy-hero-body { flex-direction: column; align-items: stretch; }
    .qjy-title { font-size: 25px; }
    .qjy-streak-panel { min-width: auto; }
    .qjy-day { min-height: 53px; }
    .qjy-weekdays, .qjy-calendar { gap: 5px; }
}
</style>

<div id="qjy-signin-app" v-cloak>
    <n-config-provider :locale="zhCN" :date-locale="dateZhCN">
        <n-card class="qjy-hero">
            <div class="qjy-hero-body">
                <div>
                    <div class="qjy-kicker">DAILY REWARDS</div>
                    <h2 class="qjy-title">每日签到领优惠券</h2>
                    <p class="qjy-subtitle">连续签到可解锁更多优惠，奖励达成后自动发放至您的券包。</p>
                    <a class="qjy-wallet-link" :href="walletUrl">查看我的优惠券</a>
                </div>
                <div class="qjy-streak-panel" :class="{ 'qjy-celebrate': celebrating }">
                    <div class="qjy-streak-number">{{ summary.streak }}</div>
                    <div class="qjy-streak-label">已连续签到天数</div>
                    <n-button class="qjy-check-button" round size="large" type="primary" color="#ffffff"
                        text-color="#1677ff" :loading="checking" :disabled="summary.signed_today" @click="checkIn">
                        {{ summary.signed_today ? '今日已签到' : '立即签到' }}
                    </n-button>
                </div>
            </div>
        </n-card>

        <n-alert v-if="feedback" class="qjy-feedback" type="success" closable @close="feedback = ''">
            {{ feedback }}
        </n-alert>

        <n-grid class="qjy-panels" x-gap="18" y-gap="18" cols="1 m:5" responsive="screen">
            <n-gi span="1 m:3">
                <n-card class="qjy-card">
                    <div class="qjy-section-head">
                        <h3 class="qjy-section-title">本月签到</h3>
                        <span class="qjy-month">{{ monthLabel }}</span>
                    </div>
                    <div class="qjy-weekdays">
                        <span v-for="week in weekdays" :key="week">{{ week }}</span>
                    </div>
                    <div class="qjy-calendar">
                        <div v-for="(day, index) in calendarDays" :key="day.date || ('empty-' + index)"
                            class="qjy-day" :class="{ 'qjy-empty': day.empty, 'qjy-signed': day.signed, 'qjy-today': day.today }">
                            <template v-if="!day.empty">
                                <span>{{ day.number }}</span>
                                <span class="qjy-day-status">{{ day.signed ? '已签' : '' }}</span>
                            </template>
                        </div>
                    </div>
                </n-card>
            </n-gi>
            <n-gi span="1 m:2">
                <div class="qjy-side-stack">
                    <n-card>
                        <div class="qjy-next-card">
                            <div class="qjy-next-title">{{ progressTitle }}</div>
                            <div class="qjy-next-note">{{ progressNote }}</div>
                            <n-progress type="line" :percentage="progress" :height="10" :show-indicator="false"
                                :color="nextReward ? '#1677ff' : '#18a058'" />
                        </div>
                    </n-card>

                    <n-alert v-if="todayRewards.length" class="qjy-earned" type="success" title="今日奖励已到账">
                        <div v-for="reward in todayRewards" :key="reward.code" class="qjy-earned-row">
                            <span class="qjy-reward-title">{{ reward.title }}</span>
                            <n-tag type="success" size="small">{{ reward.code }}</n-tag>
                        </div>
                    </n-alert>

                    <n-card title="连续奖励">
                        <n-timeline v-if="rules.length">
                            <n-timeline-item v-for="rule in rules" :key="rule.milestone"
                                :type="rule.achieved ? 'success' : (rule.next ? 'info' : 'default')">
                                <div class="qjy-rule-copy" :class="{ 'qjy-rule-current': rule.next }">
                                    <strong>连续 {{ rule.milestone }} 天</strong>
                                    <span>{{ rule.title }}</span>
                                </div>
                            </n-timeline-item>
                        </n-timeline>
                        <n-empty v-else description="暂无连续签到奖励" size="small" />
                    </n-card>
                </div>
            </n-gi>
        </n-grid>
    </n-config-provider>
</div>

<script>
(function () {
    const { createApp, ref, computed } = Vue;
    const { createDiscreteApi, zhCN, dateZhCN } = naive;
    const { message } = createDiscreteApi(["message"]);
    const initialSummary = {:json_encode($Summary, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};
    const checkinUrl = {:json_encode($CheckinUrl, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};
    const walletUrl = {:json_encode($WalletUrl, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)};

    function friendlyMessage(text) {
        var messageText = String(text || "");
        if (/重新登录|请先登录|登录后|未登录/.test(messageText)) {
            return "请先登录后签到";
        }
        return messageText;
    }

    function normalizeSummary(data) {
        return Object.assign({
            today: "",
            streak: 0,
            signed_today: false,
            days: [],
            rules: [],
            today_rewards: []
        }, data || {});
    }

    const app = createApp({
        setup() {
            const summary = ref(normalizeSummary(initialSummary));
            const checking = ref(false);
            const celebrating = ref(false);
            const feedback = ref("");
            const weekdays = ["日", "一", "二", "三", "四", "五", "六"];

            const today = computed(function () {
                var parts = String(summary.value.today || "").split("-").map(Number);
                return parts.length === 3 && parts[0] ? new Date(parts[0], parts[1] - 1, parts[2]) : new Date();
            });

            const monthLabel = computed(function () {
                return today.value.getFullYear() + " 年 " + (today.value.getMonth() + 1) + " 月";
            });

            const calendarDays = computed(function () {
                var date = today.value;
                var year = date.getFullYear();
                var month = date.getMonth();
                var firstWeekday = new Date(year, month, 1).getDay();
                var count = new Date(year, month + 1, 0).getDate();
                var signed = {};
                (Array.isArray(summary.value.days) ? summary.value.days : []).forEach(function (value) {
                    signed[value] = true;
                });
                var items = [];
                var pad = function (value) { return value < 10 ? "0" + value : String(value); };
                for (var blank = 0; blank < firstWeekday; blank++) {
                    items.push({ empty: true });
                }
                for (var number = 1; number <= count; number++) {
                    var value = year + "-" + pad(month + 1) + "-" + pad(number);
                    items.push({
                        empty: false,
                        number: number,
                        date: value,
                        signed: !!signed[value],
                        today: value === summary.value.today
                    });
                }
                return items;
            });

            const rules = computed(function () {
                var streak = Number(summary.value.streak) || 0;
                var reachedNext = false;
                return (Array.isArray(summary.value.rules) ? summary.value.rules : []).map(function (rule) {
                    var item = {
                        milestone: Number(rule.milestone) || 0,
                        title: rule.title || "优惠券奖励"
                    };
                    item.achieved = item.milestone <= streak;
                    item.next = !item.achieved && !reachedNext;
                    if (item.next) {
                        reachedNext = true;
                    }
                    return item;
                });
            });

            const nextReward = computed(function () {
                return rules.value.filter(function (rule) { return rule.next; })[0] || null;
            });

            const progress = computed(function () {
                if (!nextReward.value) {
                    return rules.value.length ? 100 : 0;
                }
                return Math.min(100, Math.round((Number(summary.value.streak) || 0) * 100 / nextReward.value.milestone));
            });

            const progressTitle = computed(function () {
                return nextReward.value ? "下一份奖励：" + nextReward.value.title : (rules.value.length ? "全部奖励已解锁" : "持续签到积累优惠");
            });

            const progressNote = computed(function () {
                if (!nextReward.value) {
                    return rules.value.length ? "您的连续签到奖励已全部达成" : "管理员配置奖励后将在这里显示";
                }
                var remaining = Math.max(0, nextReward.value.milestone - (Number(summary.value.streak) || 0));
                return "再签到 " + remaining + " 天即可领取";
            });

            const todayRewards = computed(function () {
                return Array.isArray(summary.value.today_rewards) ? summary.value.today_rewards : [];
            });

            async function checkIn() {
                checking.value = true;
                try {
                    var response = await axios.post(checkinUrl, {}, {
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    });
                    var result = response.data || {};
                    if (result.data) {
                        summary.value = normalizeSummary(result.data);
                    }
                    if (result.status !== 200) {
                        message.warning(friendlyMessage(result.msg) || "签到未完成");
                        return;
                    }
                    feedback.value = friendlyMessage(result.msg) || "签到成功";
                    message.success(feedback.value);
                    celebrating.value = false;
                    setTimeout(function () {
                        celebrating.value = true;
                        setTimeout(function () { celebrating.value = false; }, 500);
                    }, 0);
                } catch (error) {
                    var result = error.response && error.response.data ? error.response.data : {};
                    if (result.data) {
                        summary.value = normalizeSummary(result.data);
                    }
                    message.error(friendlyMessage(result.msg || error.message) || "签到请求失败，请稍后重试");
                } finally {
                    checking.value = false;
                }
            }

            return {
                zhCN: zhCN,
                dateZhCN: dateZhCN,
                walletUrl: walletUrl,
                summary: summary,
                checking: checking,
                celebrating: celebrating,
                feedback: feedback,
                weekdays: weekdays,
                monthLabel: monthLabel,
                calendarDays: calendarDays,
                rules: rules,
                nextReward: nextReward,
                progress: progress,
                progressTitle: progressTitle,
                progressNote: progressNote,
                todayRewards: todayRewards,
                checkIn: checkIn
            };
        }
    });

    app.use(naive);
    app.mount("#qjy-signin-app");
}());
</script>
