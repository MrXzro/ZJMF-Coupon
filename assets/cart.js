(function () {
    'use strict';

    if (window.__QingjiyunCouponCartMounted) {
        return;
    }

    var config = window.QingjiyunCouponConfig || {};
    var embeddedCoupons = Array.isArray(config.coupons) ? config.coupons : null;
    var loginRequired = config.loggedIn === false;
    var pendingKey = 'qingjiyun_coupon_pending';
    var promoBox = document.getElementById('promo');
    if (!promoBox || (!config.listUrl && embeddedCoupons === null)) {
        if (document.getElementById('removepromo')) {
            clearPendingSelection();
        }
        return;
    }

    var input = promoBox.querySelector('input[name="promo"]');
    var nativeButton = promoBox.querySelector('button');
    if (!input || !nativeButton) {
        return;
    }
    window.__QingjiyunCouponCartMounted = true;

    var base = typeof window._url === 'string' ? window._url : '';
    var applyUrl = config.cartApplyUrl || base + '/cart?action=viewcart&statuscart=promo&ajax=true';
    var removeUrl = config.cartRemoveUrl || base + '/cart?action=viewcart&statuscart=removepromo&ajax=true';
    var prefix = String(config.codePrefix || 'qingjiyun_').toLowerCase();
    var availableCoupons = embeddedCoupons !== null ? embeddedCoupons.slice() : [];
    var loadingCoupons = false;
    var modal;
    var listBox;
    var countText;
    var footerStatus;
    var bestButton;

    insertStyle();
    insertLauncher();
    buildModal();
    applyPendingSelection();

    function insertStyle() {
        var style = document.createElement('style');
        style.textContent = [
            '.qjy-cart-launch{height:35px;margin-left:10px;padding:0 17px;border:1px solid #216cf5;border-radius:6px;color:#fff;background:#216cf5;font-size:13px;white-space:nowrap;cursor:pointer;transition:all .2s;}',
            '.qjy-cart-launch:hover{border-color:#155bdd;color:#fff;background:#155bdd;}',
            '.qjy-cart-status{display:block;margin-top:7px;color:#667085;font-size:12px;text-align:right;}',
            '.qjy-cart-status.error{color:#ef4444;}',
            '.qjy-cart-lock{overflow:hidden;}',
            '.qjy-coupon-layer{display:none;position:fixed;z-index:10030;inset:0;align-items:center;justify-content:center;background:rgba(15,23,42,.54);padding:20px;}',
            '.qjy-coupon-layer.show{display:flex;}',
            '.qjy-coupon-dialog{overflow:hidden;width:560px;max-width:100%;border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(15,23,42,.28);}',
            '.qjy-coupon-head{display:flex;align-items:flex-start;justify-content:space-between;padding:21px 23px 17px;border-bottom:1px solid #edf1f7;}',
            '.qjy-coupon-head h3{margin:0 0 4px;color:#182230;font-size:19px;font-weight:700;}',
            '.qjy-coupon-count{color:#98a2b3;font-size:13px;}',
            '.qjy-coupon-close{width:32px;height:32px;border:0;border-radius:50%;color:#98a2b3;background:#f3f4f6;cursor:pointer;font-size:17px;}',
            '.qjy-coupon-list{max-height:354px;overflow-y:auto;padding:18px 23px;}',
            '.qjy-coupon-loading,.qjy-coupon-empty{padding:42px 10px;text-align:center;color:#98a2b3;}',
            '.qjy-cart-ticket{display:flex;overflow:hidden;min-height:101px;margin-bottom:12px;border:1px solid #e2ebfc;border-radius:12px;background:#fff;cursor:pointer;transition:box-shadow .2s,transform .2s;}',
            '.qjy-cart-ticket:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(33,108,245,.13);}',
            '.qjy-ticket-value{position:relative;display:flex;width:131px;flex-shrink:0;flex-direction:column;justify-content:center;align-items:center;color:#fff;background:linear-gradient(135deg,#1464e8,#4893fb);}',
            '.qjy-ticket-value:after{content:"";position:absolute;right:-8px;top:45px;width:16px;height:16px;border-radius:50%;background:#fff;}',
            '.qjy-ticket-amount{font-size:27px;font-weight:700;line-height:1.15;}',
            '.qjy-ticket-type{font-size:12px;margin-top:4px;}',
            '.qjy-ticket-detail{display:flex;flex:1;min-width:0;flex-direction:column;justify-content:center;padding:13px 17px 12px 25px;}',
            '.qjy-ticket-title{color:#182230;font-size:15px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}',
            '.qjy-ticket-tag{display:inline-flex;align-self:flex-start;max-width:100%;margin-top:6px;padding:2px 8px;border-radius:10px;color:#216cf5;background:#eef5ff;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}',
            '.qjy-ticket-expire{margin-top:7px;color:#98a2b3;font-size:12px;}',
            '.qjy-coupon-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 23px;border-top:1px solid #edf1f7;}',
            '.qjy-coupon-tip{color:#98a2b3;font-size:12px;}',
            '.qjy-coupon-buttons{display:flex;gap:9px;}',
            '.qjy-coupon-cancel,.qjy-coupon-best{height:38px;border-radius:8px;padding:0 17px;cursor:pointer;font-size:13px;}',
            '.qjy-coupon-cancel{border:1px solid #e5e7eb;color:#475467;background:#fff;}',
            '.qjy-coupon-best{border:1px solid #216cf5;color:#fff;background:#216cf5;}',
            '.qjy-coupon-best:hover{border-color:#155bdd;background:#155bdd;}',
            '.qjy-coupon-best[disabled]{opacity:.62;cursor:wait;}',
            '@media(max-width:600px){.qjy-cart-launch{margin-left:6px;padding:0 10px}.qjy-coupon-layer{padding:12px}.qjy-coupon-dialog{width:100%}.qjy-ticket-value{width:105px}.qjy-coupon-foot{display:block}.qjy-coupon-buttons{justify-content:flex-end;margin-top:12px}}'
        ].join('');
        document.head.appendChild(style);
    }

    function insertLauncher() {
        var button = document.createElement('button');
        var status = document.createElement('small');
        button.type = 'button';
        button.className = 'qjy-cart-launch';
        button.textContent = '一键使用优惠券';
        status.className = 'qjy-cart-status';
        button.addEventListener('click', openModal);
        promoBox.appendChild(button);
        promoBox.parentNode.appendChild(status);
        footerStatus = status;
    }

    function buildModal() {
        modal = document.createElement('div');
        modal.className = 'qjy-coupon-layer';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');

        var dialog = document.createElement('div');
        dialog.className = 'qjy-coupon-dialog';
        var header = document.createElement('div');
        header.className = 'qjy-coupon-head';
        var titleWrap = document.createElement('div');
        var title = document.createElement('h3');
        countText = document.createElement('div');
        title.textContent = '我的优惠券';
        countText.className = 'qjy-coupon-count';
        titleWrap.appendChild(title);
        titleWrap.appendChild(countText);
        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'qjy-coupon-close';
        closeButton.textContent = 'x';
        closeButton.addEventListener('click', closeModal);
        header.appendChild(titleWrap);
        header.appendChild(closeButton);

        listBox = document.createElement('div');
        listBox.className = 'qjy-coupon-list';
        var footer = document.createElement('div');
        footer.className = 'qjy-coupon-foot';
        var tip = document.createElement('div');
        tip.className = 'qjy-coupon-tip';
        tip.textContent = '同一订单仅可使用一张优惠券';
        var buttons = document.createElement('div');
        buttons.className = 'qjy-coupon-buttons';
        var cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.className = 'qjy-coupon-cancel';
        cancelButton.textContent = '取消';
        cancelButton.addEventListener('click', closeModal);
        bestButton = document.createElement('button');
        bestButton.type = 'button';
        bestButton.className = 'qjy-coupon-best';
        bestButton.textContent = '一键最优';
        bestButton.addEventListener('click', applyBest);
        buttons.appendChild(cancelButton);
        buttons.appendChild(bestButton);
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

    function openModal() {
        modal.classList.add('show');
        document.body.classList.add('qjy-cart-lock');
        if (loginRequired) {
            renderLoginNotice();
            return;
        }
        loadCoupons();
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.classList.remove('qjy-cart-lock');
    }

    function encode(data) {
        return Object.keys(data).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
        }).join('&');
    }

    function parseJson(response) {
        return response.text().then(function (text) {
            var content = text.trim();
            if (!content) {
                throw new Error('请求失败 (HTTP ' + response.status + ')：服务器未返回内容');
            }
            var result;
            try {
                result = JSON.parse(content);
            } catch (exception) {
                var clean = content.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                if (isLoginMessage(clean) || response.status === 401 || response.status === 405) {
                    throw new Error(loginTip());
                }
                throw new Error('请求失败 (HTTP ' + response.status + ')：' + (clean.substring(0, 120) || '返回内容格式错误'));
            }
            if (result && (Number(result.status) === 401 || Number(result.status) === 405) && isLoginMessage(result.msg)) {
                throw new Error(loginTip());
            }
            return result;
        });
    }

    function isLoginMessage(text) {
        return /重新登录|请先登录|登录后|未登录/.test(String(text || ''));
    }

    function loginTip() {
        return '请先登录后使用优惠券';
    }

    function renderLoginNotice() {
        availableCoupons = [];
        countText.textContent = '登录后可查看可用优惠券';
        listBox.innerHTML = '';
        var empty = document.createElement('div');
        empty.className = 'qjy-coupon-empty';
        empty.textContent = loginTip();
        listBox.appendChild(empty);
        bestButton.disabled = true;
    }

    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: encode(data || {})
        }).then(parseJson);
    }

    function show(text, error, notify) {
        footerStatus.textContent = text;
        footerStatus.className = 'qjy-cart-status' + (error ? ' error' : '');
        if (notify !== false && window.toastr && typeof window.toastr[error ? 'error' : 'success'] === 'function') {
            window.toastr[error ? 'error' : 'success'](text);
        }
    }

    function clearPendingSelection() {
        try {
            window.sessionStorage.removeItem(pendingKey);
        } catch (exception) {
            return;
        }
    }

    function readPendingSelection() {
        try {
            var pending = JSON.parse(window.sessionStorage.getItem(pendingKey) || 'null');
            return pending && pending.code ? pending : null;
        } catch (exception) {
            clearPendingSelection();
            return null;
        }
    }

    function applyPendingSelection() {
        var pending = readPendingSelection();
        if (!pending) {
            return;
        }
        input.value = pending.code;
        show('正在自动应用已选择的优惠券...', false, false);
        validate(pending.code).then(function (checked) {
            if (!checked.ok) {
                clearPendingSelection();
                show(checked.message, true);
                return null;
            }
            return applyNative(pending.code);
        }).then(function (applied) {
            if (!applied) {
                return;
            }
            clearPendingSelection();
            if (!applied.ok) {
                show(applied.message, true);
                return;
            }
            show('优惠券已自动应用，正在刷新订单金额', false, false);
            window.location.reload();
        }).catch(function (error) {
            clearPendingSelection();
            show(error.message || '优惠券自动应用失败', true);
        });
    }

    function validate(code) {
        if (!config.validateUrl) {
            return Promise.resolve(validateLocal(code));
        }

        return post(config.validateUrl, {code: code}).then(function (result) {
            return {ok: result.status === 200, message: result.msg || ''};
        });
    }

    function validateLocal(code) {
        var coupon = findCoupon(code);
        if (!coupon) {
            return {ok: false, message: '该优惠券不属于当前用户或已不可用'};
        }

        return {ok: true, message: '优惠券可用'};
    }

    function findCoupon(code) {
        var normalized = String(code || '').trim();
        for (var i = 0; i < availableCoupons.length; i++) {
            if (String(availableCoupons[i].code || '').trim() === normalized) {
                return availableCoupons[i];
            }
        }

        return null;
    }

    function applyNative(code) {
        return post(applyUrl, {promo: code}).then(function (result) {
            return {ok: !!result.SuccessMsg, message: result.SuccessMsg || result.ErrorMsg || '优惠券不可用于当前订单'};
        });
    }

    function removeNative() {
        return post(removeUrl, {}).catch(function () { return {}; });
    }

    function amount(value) {
        var number = Number(value) || 0;
        return number % 1 === 0 ? String(number) : number.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
    }

    function valueText(coupon) {
        if (coupon.type === 'fixed') return '减 ' + amount(coupon.value);
        if (coupon.type === 'percent') return '%' + amount(coupon.value);
        if (coupon.type === 'override') return amount(coupon.value);
        return '免费';
    }

    function typeText(coupon) {
        if (coupon.type === 'fixed') return '元优惠券';
        if (coupon.type === 'percent') return '折扣';
        if (coupon.type === 'override') return '一口价';
        return '免费券';
    }

    function tagText(coupon) {
        if (coupon.type === 'fixed') return amount(coupon.value) + ' 元优惠券';
        if (coupon.type === 'percent') return amount(coupon.value) + '% 折扣券';
        if (coupon.type === 'override') return '一口价 ' + amount(coupon.value) + ' 元';
        return '免费券';
    }

    function expireText(timestamp) {
        if (!Number(timestamp)) return '永久有效';
        var date = new Date(Number(timestamp) * 1000);
        var pad = function (value) { return value < 10 ? '0' + value : String(value); };
        return '有效期至 ' + date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }

    function loadCoupons() {
        if (loadingCoupons) {
            return;
        }
        loadingCoupons = true;
        listBox.innerHTML = '<div class="qjy-coupon-loading">正在加载可用优惠券...</div>';
        countText.textContent = '';
        if (embeddedCoupons !== null) {
            availableCoupons = embeddedCoupons.slice();
            renderCoupons();
            loadingCoupons = false;
            return;
        }

        fetch(config.listUrl, {credentials: 'same-origin'}).then(parseJson).then(function (result) {
            if (result.status !== 200) {
                throw new Error(result.msg || '获取优惠券失败');
            }
            availableCoupons = result.data && Array.isArray(result.data.coupons) ? result.data.coupons : [];
            renderCoupons();
        }).catch(function (error) {
            availableCoupons = [];
            listBox.innerHTML = '';
            var empty = document.createElement('div');
            empty.className = 'qjy-coupon-empty';
            empty.textContent = error.message || '获取优惠券失败';
            listBox.appendChild(empty);
            countText.textContent = '当前无法加载优惠券';
        }).then(function () {
            loadingCoupons = false;
        });
    }

    function renderCoupons() {
        listBox.innerHTML = '';
        bestButton.disabled = false;
        bestButton.textContent = '一键最优';
        countText.textContent = '共 ' + availableCoupons.length + ' 张可用，点击卡片立即使用';
        if (!availableCoupons.length) {
            var empty = document.createElement('div');
            empty.className = 'qjy-coupon-empty';
            empty.textContent = '当前没有可使用的优惠券';
            listBox.appendChild(empty);
            return;
        }
        availableCoupons.forEach(function (coupon) {
            var card = document.createElement('div');
            card.className = 'qjy-cart-ticket';
            var side = document.createElement('div');
            side.className = 'qjy-ticket-value';
            var value = document.createElement('div');
            value.className = 'qjy-ticket-amount';
            value.textContent = valueText(coupon);
            var type = document.createElement('div');
            type.className = 'qjy-ticket-type';
            type.textContent = typeText(coupon);
            side.appendChild(value);
            side.appendChild(type);

            var detail = document.createElement('div');
            detail.className = 'qjy-ticket-detail';
            var title = document.createElement('div');
            title.className = 'qjy-ticket-title';
            title.textContent = coupon.title || '优惠券';
            var tag = document.createElement('div');
            tag.className = 'qjy-ticket-tag';
            tag.textContent = tagText(coupon);
            tag.title = tagText(coupon);
            var expire = document.createElement('div');
            expire.className = 'qjy-ticket-expire';
            expire.textContent = expireText(coupon.expires_at);
            detail.appendChild(title);
            detail.appendChild(tag);
            detail.appendChild(expire);
            card.appendChild(side);
            card.appendChild(detail);
            card.addEventListener('click', function () {
                applySelected(coupon);
            });
            listBox.appendChild(card);
        });
    }

    function applySelected(coupon) {
        show('正在应用：' + (coupon.title || '优惠券'), false, false);
        applyNative(coupon.code).then(function (applied) {
            if (!applied.ok) {
                show(applied.message, true);
                return;
            }
            show('优惠券已应用，正在刷新订单金额', false, false);
            window.location.reload();
        }).catch(function (error) {
            show(error.message || '优惠券应用失败', true);
        });
    }

    function readCurrentTotal() {
        return fetch(window.location.href, {credentials: 'same-origin'}).then(function (response) {
            return response.text();
        }).then(function (html) {
            var documentCopy = new DOMParser().parseFromString(html, 'text/html');
            var total = documentCopy.querySelector('.price-num');
            if (!total) return null;
            var found = total.textContent.replace(/,/g, '').match(/-?\d+(?:\.\d+)?/);
            return found ? parseFloat(found[0]) : null;
        }).catch(function () {
            return null;
        });
    }

    function applyBest() {
        if (loginRequired) {
            show(loginTip(), true);
            return;
        }
        if (!availableCoupons.length) {
            show('当前没有可使用的优惠券', true);
            return;
        }
        bestButton.disabled = true;
        bestButton.textContent = '计算中...';
        show('正在为您比较可用优惠券...', false, false);
        var best = null;
        var sequence = Promise.resolve();
        availableCoupons.forEach(function (coupon) {
            sequence = sequence.then(function () {
                return applyNative(coupon.code).then(function (applied) {
                    if (!applied.ok) return null;
                    return readCurrentTotal().then(function (total) {
                        if (!best || (total !== null && (best.total === null || total < best.total))) {
                            best = {code: coupon.code, title: coupon.title || '优惠券', total: total};
                        }
                        return removeNative();
                    });
                });
            });
        });
        sequence.then(function () {
            if (!best) {
                throw new Error('您的优惠券均不适用于当前订单');
            }
            return applyNative(best.code).then(function (applied) {
                if (!applied.ok) {
                    throw new Error(applied.message);
                }
                show('已为您应用：' + best.title, false, false);
                window.location.reload();
            });
        }).catch(function (error) {
            show(error.message || '优惠券应用失败', true);
            bestButton.disabled = false;
            bestButton.textContent = '一键最优';
        });
    }

    nativeButton.addEventListener('click', function (event) {
        var code = input.value.trim();
        if (code.toLowerCase().indexOf(prefix) !== 0) {
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        if (loginRequired) {
            show(loginTip(), true);
            return;
        }
        validate(code).then(function (checked) {
            if (!checked.ok) {
                show(checked.message, true);
                return;
            }
            applyNative(code).then(function (applied) {
                if (applied.ok) {
                    window.location.reload();
                } else {
                    show(applied.message, true);
                }
            });
        }).catch(function () {
            show('优惠券验证失败，请稍后重试', true);
        });
    }, true);
}());
