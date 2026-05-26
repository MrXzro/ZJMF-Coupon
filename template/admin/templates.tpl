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
                    <div class="alert alert-info">创建模板后即可批量发放或配置自动发放；所有券码结算仍由魔方原生优惠码引擎完成。</div>
                    <form id="template-form" class="border rounded p-3 mb-4">
                        <input type="hidden" name="id" value="{if !empty($Edit)}{$Edit.id}{/if}">
                        <div class="form-row">
                            <div class="form-group col-md-4"><label>模板名称 *</label><input class="form-control" name="title" required value="{if !empty($Edit)}{$Edit.title|htmlspecialchars}{/if}"></div>
                            <div class="form-group col-md-2">
                                <label>类型 *</label>
                                <select class="form-control" name="type">
                                    <option value="fixed" {if !empty($Edit) && $Edit.type=='fixed'}selected{/if}>固定减免</option>
                                    <option value="percent" {if !empty($Edit) && $Edit.type=='percent'}selected{/if}>百分比</option>
                                    <option value="override" {if !empty($Edit) && $Edit.type=='override'}selected{/if}>一口价</option>
                                    <option value="free" {if !empty($Edit) && $Edit.type=='free'}selected{/if}>免费</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2"><label>优惠值 *</label><input class="form-control" type="number" min="0" step="0.01" name="value" value="{if !empty($Edit)}{$Edit.value}{else/}0{/if}"></div>
                            <div class="form-group col-md-2"><label>有效天数</label><input class="form-control" type="number" min="0" name="valid_days" value="{if !empty($Edit)}{$Edit.valid_days}{else/}30{/if}"><small>0 为永久</small></div>
                            <div class="form-group col-md-2"><label>总配额</label><input class="form-control" type="number" min="0" name="quota" value="{if !empty($Edit)}{$Edit.quota}{else/}0{/if}"><small>0 为不限</small></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4"><label>说明</label><input class="form-control" name="description" value="{if !empty($Edit)}{$Edit.description|htmlspecialchars}{/if}"></div>
                            <div class="form-group col-md-4">
                                <label>适用产品 <small class="text-muted">按 Ctrl/Cmd 多选；不选为不限</small></label>
                                <select class="form-control" name="appliesto[]" multiple size="4">
                                    {foreach $Products as $product}
                                    <option value="{$product.id}" {if !empty($Edit) && in_array($product.id, explode(',', $Edit['appliesto']))}selected{/if}>#{$product.id} {$product.name|htmlspecialchars}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>前置产品 <small class="text-muted">多选；不选为不限</small></label>
                                <select class="form-control" name="requires[]" multiple size="4">
                                    {foreach $Products as $product}
                                    <option value="{$product.id}" {if !empty($Edit) && in_array($product.id, explode(',', $Edit['requires']))}selected{/if}>#{$product.id} {$product.name|htmlspecialchars}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>适用周期 <small class="text-muted">按 Ctrl/Cmd 多选；不选为不限</small></label>
                                <select class="form-control" name="cycles[]" multiple size="5">
                                    {foreach $CycleOptions as $cycle}
                                    <option value="{$cycle.value}" {if !empty($Edit) && in_array($cycle.value, explode(',', $Edit['cycles']))}selected{/if}>{$cycle.label}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="form-group col-md-2"><label>循环优惠次数</label><input class="form-control" type="number" min="0" name="recurfor" value="{if !empty($Edit)}{$Edit.recurfor}{else/}0{/if}"></div>
                            <div class="form-group col-md-2"><label>新人可领天数</label><input class="form-control" type="number" min="1" name="new_user_days" value="{if !empty($Edit)}{$Edit.new_user_days}{else/}7{/if}"><small>注册 N 天内可领，自动发放建议 2 天以上</small></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="mr-3"><input type="checkbox" name="recurring" value="1" {if !empty($Edit) && $Edit.recurring}checked{/if}> 循环优惠</label>
                                <label class="mr-3"><input type="checkbox" name="requires_exist" value="1" {if !empty($Edit) && $Edit.requires_exist}checked{/if}> 要求已有前置产品</label>
                                <label class="mr-3"><input type="checkbox" name="new_user_only" value="1" {if !empty($Edit) && $Edit.new_user_only}checked{/if}> 新用户可领</label>
                                <label class="mr-3"><input type="checkbox" name="once_per_client" value="1" {if empty($Edit) || $Edit.once_per_client}checked{/if}> 每账号限领一次</label>
                                <label class="mr-3"><input type="checkbox" name="new_user_auto" value="1" {if !empty($Edit) && $Edit.new_user_auto}checked{/if}> 注册满 1 天未领自动发放</label>
                                <label class="mr-3"><input type="checkbox" name="require_paid" value="1" {if !empty($Edit) && $Edit.require_paid}checked{/if}> 要求完成支付</label>
                                <label class="mr-3"><input type="checkbox" name="require_realname" value="1" {if !empty($Edit) && $Edit.require_realname}checked{/if}> 需实名认证</label>
                                <label><input type="checkbox" name="enabled" value="1" {if empty($Edit) || $Edit.enabled}checked{/if}> 启用模板</label>
                                <small class="d-block text-muted mt-2">新用户可领与每账号限领一次互斥；未勾选限领一次时，同模板未使用券存在时不能继续领取，用完后可再领。</small>
                                <small class="d-block text-muted">自动发放仅对新用户可领模板生效：注册满 1 天仍未领取时，由登录钩子或系统 cron 自动补发。</small>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">{if !empty($Edit)}保存修改{else/}创建模板{/if}</button>
                        {if !empty($Edit)}<a class="btn btn-outline-secondary" href="{$TemplatesUrl}">取消编辑</a>{/if}
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light"><tr><th>模板</th><th>类型/值</th><th>有效期</th><th>已发/配额</th><th>自动规则</th><th>状态</th><th>操作</th></tr></thead>
                            <tbody>
                            {foreach $Templates as $item}
                            <tr>
                                <td>{$item.title|htmlspecialchars}<br><small class="text-muted">#{$item.id}</small></td>
                                <td>{$item.type|htmlspecialchars} / {$item.value}</td>
                                <td>{if $item.valid_days > 0}{$item.valid_days} 天{else/}永久{/if}</td>
                                <td>{$item.issued_count} / {if $item.quota > 0}{$item.quota}{else/}不限{/if}</td>
                                <td>
                                    {if $item.new_user_only}<span class="badge badge-info">新人 {$item.new_user_days} 天内</span>{/if}
                                    {if $item.once_per_client}<span class="badge badge-primary">限领一次</span>{else/}<span class="badge badge-light">用完可再领</span>{/if}
                                    {if $item.new_user_auto}<span class="badge badge-warning">1 天后自动发</span>{/if}
                                    {if $item.require_paid}<span class="badge badge-secondary">需支付</span>{/if}
                                    {if $item.require_realname}<span class="badge badge-success">需实名</span>{/if}
                                </td>
                                <td>{if $item.enabled}<span class="badge badge-success">启用</span>{else/}<span class="badge badge-secondary">停用</span>{/if}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="{$TemplatesUrl}&edit_id={$item.id}">编辑</a>
                                    <button class="btn btn-sm btn-outline-secondary toggle-btn" data-id="{$item.id}" data-enabled="{if $item.enabled}0{else/}1{/if}">{if $item.enabled}停用{else/}启用{/if}</button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="{$item.id}" data-title="{$item.title|htmlspecialchars}">删除</button>
                                </td>
                            </tr>
                            {/foreach}
                            {if empty($Templates)}<tr><td colspan="7" class="text-center text-muted">尚未创建模板</td></tr>{/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
(function () {
    var form = document.getElementById('template-form');
    var newUserOnly = form.querySelector('input[name="new_user_only"]');
    var oncePerClient = form.querySelector('input[name="once_per_client"]');
    var newUserAuto = form.querySelector('input[name="new_user_auto"]');
    function syncNewUserOptions(changed) {
        if (!newUserOnly || !oncePerClient) {
            return;
        }
        if (changed === oncePerClient && oncePerClient.checked) {
            newUserOnly.checked = false;
            if (newUserAuto) {
                newUserAuto.checked = false;
            }
        }
        if (newUserAuto && newUserAuto.checked) {
            newUserOnly.checked = true;
        }
        if (newUserOnly.checked) {
            oncePerClient.checked = false;
            oncePerClient.disabled = true;
        } else {
            oncePerClient.disabled = false;
            if (newUserAuto) {
                newUserAuto.checked = false;
            }
        }
    }
    if (newUserOnly) {
        newUserOnly.addEventListener('change', function () { syncNewUserOptions(newUserOnly); });
    }
    if (oncePerClient) {
        oncePerClient.addEventListener('change', function () { syncNewUserOptions(oncePerClient); });
    }
    if (newUserAuto) {
        newUserAuto.addEventListener('change', function () { syncNewUserOptions(newUserAuto); });
    }
    syncNewUserOptions(null);
    function postForm(url, data) {
        return fetch(url, {method: 'POST', body: data, credentials: 'same-origin'})
            .then(function (response) {
                return response.text().then(function (text) {
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        var clean = text.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                        throw new Error('请求失败 (HTTP ' + response.status + ')' + (clean ? '：' + clean.substring(0, 160) : ''));
                    }
                });
            });
    }
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        postForm('{$SaveUrl}', new FormData(form))
            .then(function (result) { alert(result.msg); if (result.status === 200) { location.href = '{$TemplatesUrl}'; } })
            .catch(function (error) { alert(error.message); });
    });
    Array.prototype.forEach.call(document.querySelectorAll('.toggle-btn'), function (button) {
        button.addEventListener('click', function () {
            var data = new FormData();
            data.append('id', button.getAttribute('data-id'));
            data.append('enabled', button.getAttribute('data-enabled'));
            postForm('{$ToggleUrl}', data)
                .then(function (result) { alert(result.msg); if (result.status === 200) { location.reload(); } })
                .catch(function (error) { alert(error.message); });
        });
    });
    Array.prototype.forEach.call(document.querySelectorAll('.delete-btn'), function (button) {
        button.addEventListener('click', function () {
            var title = button.getAttribute('data-title') || '该模板';
            if (!confirm('确定删除“' + title + '”吗？已有发放记录的模板会被系统阻止删除，避免影响用户券包。')) {
                return;
            }
            var data = new FormData();
            data.append('id', button.getAttribute('data-id'));
            postForm('{$DeleteUrl}', data)
                .then(function (result) { alert(result.msg); if (result.status === 200) { location.reload(); } })
                .catch(function (error) { alert(error.message); });
        });
    });
}());
</script>
