(function () {
    'use strict';

    if (window.__QingjiyunCouponConfigureMounted) {
        return;
    }

    var config = window.QingjiyunCouponConfig || {};
    var embeddedCoupons = Array.isArray(config.coupons) ? config.coupons : null;
    var form = document.getElementById('addCartForm');
    var totalBox = document.querySelector('.configoption_total');
    if (!form || !totalBox || (!config.listUrl && embeddedCoupons === null)) {
        return;
    }
    window.__QingjiyunCouponConfigureMounted = true;

    var pendingKey = 'qingjiyun_coupon_pending';
    var coupons = embeddedCoupons !== null ? embeddedCoupons.slice() : [];
    var selected = readSelected();
    var modal;
    var listBox;
    var countText;
    var loading = false;

    insertStyle();
    buildModal();
    setFormCoupon(selected ? selected.code : '');
    observeSummary();
    renderEntry();
    loadCoupons();

    function insertStyle() {
        var style = document.createElement('style');
        style.textContent = [
            '.allocation-card .allocation-footer .ordersummarybottom-title{min-width:0;}',
            '.qjy-config-entry{display:inline-flex;align-items:center;gap:9px;min-width:0;max-width:min(760px,52vw);margin-left:18px;color:#64748b;font-size:13px;vertical-align:middle;overflow:hidden;}',
            '.qjy-config-entry strong{color:#f97316;font-size:16px;}',
            '.qjy-config-action{height:29px;padding:0 14px;border:0;border-radius:16px;color:#216cf5;background:#eaf2ff;font-size:13px;cursor:pointer;transition:all .18s;}',
            '.qjy-config-action:hover{color:#fff;background:#216cf5;}',
            '.qjy-config-remove{color:#64748b;background:#f3f6fb;}',
            '.qjy-config-remove:hover{color:#216cf5;background:#eaf2ff;}',
            '.qjy-config-line{display:inline-flex;align-items:center;gap:8px;min-width:0;}',
            '.qjy-config-info{flex:1 1 auto;min-width:0;color:#64748b;overflow:hidden;}',
            '.qjy-config-actions{flex-shrink:0;}',
            '.qjy-config-badge{display:inline-flex;align-items:center;flex:0 1 auto;min-width:0;max-width:260px;height:27px;padding:0 11px;border-radius:14px;color:#216cf5;background:#edf4ff;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}',
            '.qjy-config-discount{flex:0 0 auto;color:#f97316;font-weight:600;white-space:nowrap;}',
            '.qjy-config-condition{display:block;flex:1 1 auto;min-width:0;color:#64748b;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}',
            '.qjy-config-lock{overflow:hidden;}',
            '.qjy-config-layer{display:none;position:fixed;z-index:10030;inset:0;align-items:center;justify-content:center;background:rgba(15,23,42,.54);padding:20px;}',
            '.qjy-config-layer.show{display:flex;}',
            '.qjy-config-dialog{overflow:hidden;width:560px;max-width:100%;border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(15,23,42,.28);}',
            '.qjy-config-head{display:flex;align-items:flex-start;justify-content:space-between;padding:21px 23px 17px;border-bottom:1px solid #edf1f7;}',
            '.qjy-config-head h3{margin:0 0 4px;color:#182230;font-size:19px;font-weight:700;}',
            '.qjy-config-count{color:#98a2b3;font-size:13px;}',
            '.qjy-config-close{width:32px;height:32px;border:0;border-radius:50%;color:#98a2b3;background:#f3f4f6;cursor:pointer;font-size:17px;}',
            '.qjy-config-list{max-height:354px;overflow-y:auto;padding:18px 23px;}',
            '.qjy-config-loading,.qjy-config-empty{padding:42px 10px;text-align:center;color:#98a2b3;}',
            '.qjy-config-ticket{display:flex;overflow:hidden;min-height:101px;margin-bottom:12px;border:1px solid #e2ebfc;border-radius:12px;background:#fff;cursor:pointer;transition:box-shadow .2s,transform .2s;}',
            '.qjy-config-ticket:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(33,108,245,.13);}',
            '.qjy-config-value{position:relative;display:flex;width:131px;flex-shrink:0;flex-direction:column;justify-content:center;align-items:center;color:#fff;background:linear-gradient(135deg,#1464e8,#4893fb);}',
            '.qjy-config-value:after{content:"";position:absolute;right:-8px;top:45px;width:16px;height:16px;border-radius:50%;background:#fff;}',
            '.qjy-config-amount{font-size:27px;font-weight:700;line-height:1.15;}',
            '.qjy-config-type{margin-top:4px;font-size:12px;}',
            '.qjy-config-detail{display:flex;flex:1;min-width:0;flex-direction:column;justify-content:center;padding:13px 17px 12px 23px;}',
            '.qjy-config-title{color:#182230;font-size:15px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}',
            '.qjy-config-tag{display:inline-flex;align-self:flex-start;margin-top:6px;padding:2px 8px;border-radius:10px;color:#216cf5;background:#eef5ff;font-size:12px;}',
            '.qjy-config-expire{margin-top:7px;color:#98a2b3;font-size:12px;}',
            '.qjy-config-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 23px;border-top:1px solid #edf1f7;}',
            '.qjy-config-tip{color:#98a2b3;font-size:12px;}',
            '.qjy-config-buttons{display:flex;gap:9px;}',
            '.qjy-config-cancel,.qjy-config-best{height:38px;border-radius:8px;padding:0 17px;cursor:pointer;font-size:13px;}',
            '.qjy-config-cancel{border:1px solid #e5e7eb;color:#475467;background:#fff;}',
            '.qjy-config-best{border:1px solid #216cf5;color:#fff;background:#216cf5;}',
            '.qjy-config-best:hover{border-color:#155bdd;background:#155bdd;}',
            '@media(max-width:700px){.allocation-card .allocation-footer .ordersummarybottom-title{flex-wrap:wrap;align-items:flex-start}.qjy-config-entry{display:flex;align-items:center;gap:6px;flex:0 0 100%;width:100%;max-width:100%;margin:4px 0 0;font-size:12px;line-height:1.35;flex-wrap:nowrap;overflow:hidden}.qjy-config-line{display:flex;align-items:center;gap:6px;min-width:0}.qjy-config-info{flex:1 1 auto;width:auto;min-width:0;overflow:hidden}.qjy-config-actions{display:flex;flex:0 0 auto;gap:4px;justify-content:flex-end}.qjy-config-entry strong{font-size:14px}.qjy-config-action{height:24px;padding:0 8px;font-size:12px}.qjy-config-badge{height:24px;max-width:42vw;padding:0 9px;font-size:12px}.qjy-config-discount{font-size:12px;white-space:nowrap}.qjy-config-condition{flex:1 1 auto;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.qjy-config-entry:not(.qjy-config-selected) .qjy-config-info{flex:0 1 auto}.qjy-config-entry:not(.qjy-config-selected) .qjy-config-actions{margin-left:2px}.qjy-config-layer{padding:12px}.qjy-config-dialog{width:100%}.qjy-config-ticket{min-height:88px;margin-bottom:10px}.qjy-config-value{width:96px}.qjy-config-value:after{top:38px}.qjy-config-amount{font-size:23px}.qjy-config-detail{padding:10px 13px 10px 18px}.qjy-config-foot{display:block}.qjy-config-buttons{justify-content:flex-end;margin-top:12px}}'
        ].join('');
        document.head.appendChild(style);
    }

    function buildModal() {
        modal = document.createElement('div');
        modal.className = 'qjy-config-layer';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');

        var dialog = document.createElement('div');
        dialog.className = 'qjy-config-dialog';
        var header = document.createElement('div');
        header.className = 'qjy-config-head';
        var titleWrap = document.createElement('div');
        var title = document.createElement('h3');
        title.textContent = '选择优惠券';
        countText = document.createElement('div');
        countText.className = 'qjy-config-count';
        titleWrap.appendChild(title);
        titleWrap.appendChild(countText);
        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'qjy-config-close';
        closeButton.textContent = 'x';
        closeButton.addEventListener('click', closeModal);
        header.appendChild(titleWrap);
        header.appendChild(closeButton);
        listBox = document.createElement('div');
        listBox.className = 'qjy-config-list';

        var footer = document.createElement('div');
        footer.className = 'qjy-config-foot';
        var tip = document.createElement('div');
        tip.className = 'qjy-config-tip';
        tip.textContent = '选中后将在结算页自动应用';
        var buttons = document.createElement('div');
        buttons.className = 'qjy-config-buttons';
        var cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'qjy-config-cancel';
        cancel.textContent = '取消';
        cancel.addEventListener('click', closeModal);
        var best = document.createElement('button');
        best.type = 'button';
        best.className = 'qjy-config-best';
        best.textContent = '选用推荐券';
        best.addEventListener('click', function () {
            if (coupons.length) {
                chooseCoupon(coupons[0]);
            }
        });
        buttons.appendChild(cancel);
        buttons.appendChild(best);
        footer.appendChild(tip);
        footer.appendChild(buttons);
        dialog.appendChild(header);
        dialog.appendChild(listBox);
        dialog.appendChild(footer);
        modal.appendChild(dialog);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        document.body.appendChild(modal);
    }

    function observeSummary() {
        var observer = new MutationObserver(function () {
            renderEntry();
        });
        observer.observe(totalBox, {childList: true});
    }

    function renderEntry() {
        var host = totalBox.querySelector('.ordersummarybottom-title');
        if (!host) {
            applyEstimatedTotal();
            return;
        }
        var entry = host.querySelector('.qjy-config-entry');
        if (!entry) {
            entry = document.createElement('div');
            entry.className = 'qjy-config-entry';
            host.appendChild(entry);
        }
        entry.innerHTML = '';
        entry.className = 'qjy-config-entry' + (selected ? ' qjy-config-selected' : '');
        if (!coupons.length && !selected) {
            entry.style.display = 'none';
            toggleNativePlaceholder(host, false);
            restoreEstimatedTotal();
            return;
        }
        entry.style.display = '';
        toggleNativePlaceholder(host, true);

        if (selected) {
            var selectedInfo = entryLine('qjy-config-info');
            selectedInfo.title = [selected.title || selected.code, previewText(selected), conditionText(selected)].filter(Boolean).join(' ');
            var badge = document.createElement('span');
            badge.className = 'qjy-config-badge';
            badge.textContent = selected.title || selected.code;
            badge.title = selected.title || selected.code;
            selectedInfo.appendChild(badge);
            var preview = document.createElement('span');
            preview.className = 'qjy-config-discount';
            preview.textContent = previewText(selected);
            selectedInfo.appendChild(preview);
            var selectedConditionText = conditionText(selected);
            if (selectedConditionText) {
                var selectedCondition = document.createElement('span');
                selectedCondition.className = 'qjy-config-condition';
                selectedCondition.textContent = selectedConditionText;
                selectedCondition.title = selectedConditionText;
                selectedInfo.appendChild(selectedCondition);
            }
            entry.appendChild(selectedInfo);
            var selectedActions = entryLine('qjy-config-actions');
            selectedActions.appendChild(actionButton('更换', openModal));
            selectedActions.appendChild(actionButton('取消', cancelSelected, 'qjy-config-remove'));
            entry.appendChild(selectedActions);
            applyEstimatedTotal();
            return;
        }

        var info = entryLine('qjy-config-info');
        appendText(info, coupons[0].type === 'fixed' ? '最高立减' : '推荐优惠');
        var strong = document.createElement('strong');
        strong.textContent = valueText(coupons[0]);
        info.appendChild(strong);
        var condition = conditionText(coupons[0]);
        info.title = [(coupons[0].type === 'fixed' ? '最高立减' : '推荐优惠') + valueText(coupons[0]), condition].filter(Boolean).join(' ');
        if (condition) {
            var conditionNode = document.createElement('span');
            conditionNode.className = 'qjy-config-condition';
            conditionNode.textContent = condition;
            conditionNode.title = condition;
            info.appendChild(conditionNode);
        }
        entry.appendChild(info);
        var actions = entryLine('qjy-config-actions');
        actions.appendChild(actionButton('立即使用', openModal));
        entry.appendChild(actions);
        restoreEstimatedTotal();
    }

    function toggleNativePlaceholder(host, hide) {
        Array.prototype.forEach.call(host.querySelectorAll('.configure-total-row'), function (row) {
            if (row.querySelector('.promocodeicon') || row.textContent.indexOf('无折扣') !== -1) {
                row.style.display = hide ? 'none' : '';
            }
        });
    }

    function appendText(parent, text) {
        parent.appendChild(document.createTextNode(text));
    }

    function entryLine(extraClass) {
        var line = document.createElement('span');
        line.className = 'qjy-config-line ' + extraClass;
        return line;
    }

    function actionButton(text, callback, extraClass) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'qjy-config-action' + (extraClass ? ' ' + extraClass : '');
        button.textContent = text;
        button.addEventListener('click', callback);
        return button;
    }

    function openModal() {
        modal.classList.add('show');
        document.body.classList.add('qjy-config-lock');
        if (!coupons.length && !loading) {
            loadCoupons();
        } else {
            renderCoupons();
        }
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.classList.remove('qjy-config-lock');
    }

    function parseJson(response) {
        return response.text().then(function (text) {
            var content = text.trim();
            if (!content) {
                throw new Error('请求失败 (HTTP ' + response.status + ')：服务器未返回内容');
            }
            try {
                return JSON.parse(content);
            } catch (exception) {
                var clean = content.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                throw new Error('请求失败 (HTTP ' + response.status + ')：' + (clean.substring(0, 120) || '返回内容格式错误'));
            }
        });
    }

    function loadCoupons() {
        if (loading) {
            return;
        }
        loading = true;
        listBox.innerHTML = '<div class="qjy-config-loading">正在加载可用优惠券...</div>';
        if (embeddedCoupons !== null) {
            coupons = embeddedCoupons.slice();
            if (selected && !containsCoupon(selected.code)) {
                clearSelected();
            }
            renderEntry();
            renderCoupons();
            loading = false;
            return;
        }

        fetch(config.listUrl, {credentials: 'same-origin'}).then(parseJson).then(function (result) {
            if (result.status !== 200) {
                throw new Error(result.msg || '获取优惠券失败');
            }
            coupons = result.data && Array.isArray(result.data.coupons) ? result.data.coupons : [];
            if (selected && !containsCoupon(selected.code)) {
                clearSelected();
            }
            renderEntry();
            renderCoupons();
        }).catch(function (error) {
            coupons = [];
            listBox.innerHTML = '';
            var empty = document.createElement('div');
            empty.className = 'qjy-config-empty';
            empty.textContent = error.message || '获取优惠券失败';
            listBox.appendChild(empty);
            countText.textContent = '当前无法加载优惠券';
            renderEntry();
        }).then(function () {
            loading = false;
        });
    }

    function containsCoupon(code) {
        return coupons.some(function (coupon) {
            return coupon.code === code;
        });
    }

    function renderCoupons() {
        listBox.innerHTML = '';
        countText.textContent = '共 ' + coupons.length + ' 张可用，点击卡片选用';
        if (!coupons.length) {
            var empty = document.createElement('div');
            empty.className = 'qjy-config-empty';
            empty.textContent = '当前没有可使用的优惠券';
            listBox.appendChild(empty);
            return;
        }
        coupons.forEach(function (coupon) {
            var card = document.createElement('div');
            card.className = 'qjy-config-ticket';
            var side = document.createElement('div');
            side.className = 'qjy-config-value';
            var value = document.createElement('div');
            value.className = 'qjy-config-amount';
            value.textContent = valueText(coupon);
            var type = document.createElement('div');
            type.className = 'qjy-config-type';
            type.textContent = typeText(coupon);
            side.appendChild(value);
            side.appendChild(type);
            var detail = document.createElement('div');
            detail.className = 'qjy-config-detail';
            var title = document.createElement('div');
            title.className = 'qjy-config-title';
            title.textContent = coupon.title || '优惠券';
            var tag = document.createElement('div');
            tag.className = 'qjy-config-tag';
            tag.textContent = typeText(coupon);
            var expire = document.createElement('div');
            expire.className = 'qjy-config-expire';
            expire.textContent = expireText(coupon.expires_at);
            detail.appendChild(title);
            detail.appendChild(tag);
            var condition = conditionText(coupon);
            if (condition) {
                var conditionNode = document.createElement('div');
                conditionNode.className = 'qjy-config-expire';
                conditionNode.textContent = condition;
                detail.appendChild(conditionNode);
            }
            detail.appendChild(expire);
            card.appendChild(side);
            card.appendChild(detail);
            card.addEventListener('click', function () {
                chooseCoupon(coupon);
            });
            listBox.appendChild(card);
        });
    }

    function chooseCoupon(coupon) {
        selected = coupon;
        setFormCoupon(coupon.code);
        saveSelected(coupon);
        closeModal();
        renderEntry();
        if (window.toastr && typeof window.toastr.success === 'function') {
            window.toastr.success('已选择优惠券，进入结算页后将自动应用');
        }
        refreshSummary();
    }

    function cancelSelected() {
        clearSelected();
        renderEntry();
        refreshSummary();
        if (window.toastr && typeof window.toastr.success === 'function') {
            window.toastr.success('已取消使用优惠券');
        }
    }

    function setFormCoupon(code) {
        var input = form.querySelector('input[name="promocode"]');
        if (!input && code) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'promocode';
            form.appendChild(input);
        }
        if (input) {
            input.value = code || '';
        }
    }

    function refreshSummary() {
        if (typeof window.configoption_ajax === 'function') {
            window.configoption_ajax();
        } else {
            applyEstimatedTotal();
        }
    }

    function saveSelected(coupon) {
        try {
            window.sessionStorage.setItem(pendingKey, JSON.stringify(coupon));
        } catch (exception) {
            return;
        }
    }

    function readSelected() {
        try {
            var stored = JSON.parse(window.sessionStorage.getItem(pendingKey) || 'null');
            return stored && stored.code ? stored : null;
        } catch (exception) {
            return null;
        }
    }

    function clearSelected() {
        selected = null;
        setFormCoupon('');
        try {
            window.sessionStorage.removeItem(pendingKey);
        } catch (exception) {
            return;
        }
    }

    function applyEstimatedTotal() {
        if (!selected) {
            restoreEstimatedTotal();
            return;
        }
        var priceNode = totalBox.querySelector('.ordersummarybottom-price');
        if (!priceNode) {
            return;
        }
        var base = readBaseTotal(priceNode);
        if (base === null) {
            return;
        }
        var estimated = estimateTotal(base, selected);
        if (estimated === null) {
            restoreEstimatedTotal();
            return;
        }
        priceNode.textContent = formatMoney(estimated);
        priceNode.setAttribute('data-qjy-estimated', '1');
        patchBreakdownTotal(estimated);
    }

    function restoreEstimatedTotal() {
        var priceNode = totalBox.querySelector('.ordersummarybottom-price');
        if (!priceNode) {
            return;
        }
        var original = priceNode.getAttribute('data-qjy-original-total');
        if (original !== null && priceNode.getAttribute('data-qjy-estimated') === '1') {
            priceNode.textContent = formatMoney(Number(original));
        }
        priceNode.removeAttribute('data-qjy-estimated');

        var breakdown = totalBox.querySelector('.tb_totalPrice');
        var breakdownOriginal = breakdown ? breakdown.getAttribute('data-qjy-original-text') : null;
        if (breakdown && breakdownOriginal !== null) {
            breakdown.textContent = breakdownOriginal;
        }
    }

    function readBaseTotal(priceNode) {
        var stored = priceNode.getAttribute('data-qjy-original-total');
        if (stored !== null && stored !== '') {
            return Number(stored);
        }
        var parsed = parseMoney(priceNode.textContent);
        if (parsed === null) {
            return null;
        }
        priceNode.setAttribute('data-qjy-original-total', String(parsed));
        return parsed;
    }

    function estimateTotal(base, coupon) {
        var value = Number(coupon && coupon.value) || 0;
        if (coupon.type === 'fixed') {
            return Math.max(0, base - value);
        }
        if (coupon.type === 'percent') {
            return Math.max(0, base * Math.max(0, Math.min(100, value)) / 100);
        }
        if (coupon.type === 'override') {
            return Math.max(0, value);
        }
        if (coupon.type === 'free') {
            return 0;
        }

        return null;
    }

    function patchBreakdownTotal(estimated) {
        var breakdown = totalBox.querySelector('.tb_totalPrice');
        if (!breakdown) {
            return;
        }
        if (breakdown.getAttribute('data-qjy-original-text') === null) {
            breakdown.setAttribute('data-qjy-original-text', breakdown.textContent);
        }
        breakdown.textContent = currencyPrefix() + formatMoney(estimated);
    }

    function parseMoney(text) {
        var found = String(text || '').replace(/,/g, '').match(/-?\d+(?:\.\d+)?/);
        return found ? Number(found[0]) : null;
    }

    function formatMoney(value) {
        return (Number(value) || 0).toFixed(2);
    }

    function currencyPrefix() {
        var prefix = totalBox.querySelector('.ordersummarybottom-prefix');
        return prefix ? prefix.textContent.trim() : '';
    }

    function amount(value) {
        var number = Number(value) || 0;
        return number % 1 === 0 ? String(number) : number.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
    }

    function valueText(coupon) {
        if (coupon.type === 'fixed') return '￥' + amount(coupon.value);
        if (coupon.type === 'percent') return amount(coupon.value) + '%';
        if (coupon.type === 'override') return '￥' + amount(coupon.value);
        return '免费';
    }

    function typeText(coupon) {
        if (coupon.type === 'fixed') return '立减券';
        if (coupon.type === 'percent') return '折扣券';
        if (coupon.type === 'override') return '一口价';
        return '免费券';
    }

    function previewText(coupon) {
        if (coupon.type === 'fixed') return '预计优惠 ￥' + amount(coupon.value);
        if (coupon.type === 'percent') return '折扣 ' + amount(coupon.value) + '%';
        if (coupon.type === 'override') return '一口价 ￥' + amount(coupon.value);
        return '预计优惠 免费';
    }

    function conditionText(coupon) {
        return coupon && coupon.requires_text ? String(coupon.requires_text) : '';
    }

    function expireText(timestamp) {
        if (!Number(timestamp)) return '永久有效';
        var date = new Date(Number(timestamp) * 1000);
        var pad = function (value) { return value < 10 ? '0' + value : String(value); };
        return '有效期至 ' + date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }
}());
