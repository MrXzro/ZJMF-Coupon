<section class="admin-main">
    <div class="container-fluid">
        <div class="page-container">
            <div class="card">
                <div class="card-body">
                    <div class="card-title row">
                        <div style="padding:0 15px;">{$Title}</div>
                        <div class="col-lg-8 col-md-12">
                            {foreach $PluginsAdminMenu as $v}<span class="ml-2"><a class="h5" href="{$v.url}">{$v.name}</a></span>{/foreach}
                        </div>
                    </div>
                    <div class="alert alert-warning">同一个模板不会重复发放给同一用户。选择“全部活跃用户”前请确认模板配额和有效期。</div>
                    <form id="issue-form" class="border rounded p-4 col-xl-8">
                        <div class="form-group">
                            <label>券模板 *</label>
                            <select class="form-control" name="template_id" required>
                                <option value="">请选择</option>
                                {foreach $Templates as $item}<option value="{$item.id}">{$item.title|htmlspecialchars} ({$item.type} / {$item.value})</option>{/foreach}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>发放对象 *</label>
                            <div>
                                <label class="mr-4"><input type="radio" name="target" value="uids" checked> UID 列表</label>
                                <label class="mr-4"><input type="radio" name="target" value="group"> 用户组</label>
                                <label><input type="radio" name="target" value="all"> 全部活跃用户</label>
                            </div>
                        </div>
                        <div class="form-group target-panel" data-target="uids">
                            <label>用户 UID</label>
                            <textarea class="form-control" name="uids" rows="4" placeholder="可用逗号、空格或换行分隔，例如：1001, 1002"></textarea>
                        </div>
                        <div class="form-group target-panel d-none" data-target="group">
                            <label>用户组</label>
                            <select class="form-control" name="group_id">
                                {foreach $Groups as $group}<option value="{$group.id}">{$group.group_name|htmlspecialchars}</option>{/foreach}
                            </select>
                        </div>
                        <div class="form-group target-panel d-none" data-target="all">
                            <p class="text-danger mb-0">将对所有状态为激活的用户尝试发放，每位用户仍会执行重复与支付门槛校验。</p>
                        </div>
                        <button class="btn btn-primary" type="submit">确认发券</button>
                    </form>
                    <pre id="issue-result" class="mt-3 d-none"></pre>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
(function () {
    var form = document.getElementById('issue-form');
    var resultBox = document.getElementById('issue-result');
    function readJsonResponse(response) {
        return response.text().then(function (text) {
            var content = text.trim();
            if (!content) {
                throw new Error('请求失败 (HTTP ' + response.status + ')：服务器未返回内容');
            }
            try {
                return JSON.parse(content);
            } catch (error) {
                var clean = content.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                throw new Error('请求失败 (HTTP ' + response.status + ')：' + (clean.substring(0, 160) || '返回内容格式错误'));
            }
        });
    }
    function showPanel() {
        var selected = form.querySelector('input[name="target"]:checked').value;
        Array.prototype.forEach.call(form.querySelectorAll('.target-panel'), function (panel) {
            panel.classList.toggle('d-none', panel.getAttribute('data-target') !== selected);
        });
    }
    Array.prototype.forEach.call(form.querySelectorAll('input[name="target"]'), function (radio) {
        radio.addEventListener('change', showPanel);
    });
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!confirm('确认执行本次发券吗？')) { return; }
        fetch('{$IssueUrl}', {method: 'POST', body: new FormData(form), credentials: 'same-origin'})
            .then(readJsonResponse)
            .then(function (result) {
                resultBox.classList.remove('d-none');
                resultBox.textContent = result.msg + (result.details && result.details.length ? '\n' + result.details.join('\n') : '');
            })
            .catch(function (error) {
                resultBox.classList.remove('d-none');
                resultBox.textContent = error.message || '发券请求失败，请稍后重试';
            });
    });
}());
</script>
