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
                    <div class="alert alert-info">用户断签后连续天数会清零；达到指定连续天数时自动发放配置的模板。</div>
                    <form id="rule-form" class="form-inline border rounded p-3 mb-4">
                        <label class="mr-2">连续</label>
                        <input class="form-control mr-2" name="milestone" type="number" min="1" required placeholder="天数">
                        <label class="mr-2">天赠送</label>
                        <select class="form-control mr-2" name="template_id" required>
                            <option value="">选择模板</option>
                            {foreach $Templates as $item}<option value="{$item.id}">{$item.title|htmlspecialchars}</option>{/foreach}
                        </select>
                        <button class="btn btn-primary" type="submit">保存规则</button>
                    </form>
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light"><tr><th>连续签到天数</th><th>奖励模板</th><th>状态</th><th>操作</th></tr></thead>
                        <tbody>
                        {foreach $Rules as $rule}
                        <tr>
                            <td>第 {$rule.milestone} 天</td>
                            <td>{$rule.title|htmlspecialchars}</td>
                            <td>{if $rule.enabled}<span class="badge badge-success">启用</span>{else/}停用{/if}</td>
                            <td><button class="btn btn-sm btn-outline-danger delete-rule" data-id="{$rule.id}">删除</button></td>
                        </tr>
                        {/foreach}
                        {if empty($Rules)}<tr><td colspan="4" class="text-center text-muted">暂无签到奖励规则</td></tr>{/if}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
(function () {
    var form = document.getElementById('rule-form');
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
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        fetch('{$SaveRuleUrl}', {method: 'POST', body: new FormData(form), credentials: 'same-origin'})
            .then(readJsonResponse)
            .then(function (result) { alert(result.msg); if (result.status === 200) { location.reload(); } })
            .catch(function (error) { alert(error.message || '保存请求失败，请稍后重试'); });
    });
    Array.prototype.forEach.call(document.querySelectorAll('.delete-rule'), function (button) {
        button.addEventListener('click', function () {
            if (!confirm('确认删除此签到规则吗？')) { return; }
            var data = new FormData();
            data.append('id', button.getAttribute('data-id'));
            fetch('{$DeleteRuleUrl}', {method: 'POST', body: data, credentials: 'same-origin'})
                .then(readJsonResponse)
                .then(function (result) { alert(result.msg); if (result.status === 200) { location.reload(); } })
                .catch(function (error) { alert(error.message || '删除请求失败，请稍后重试'); });
        });
    });
}());
</script>
