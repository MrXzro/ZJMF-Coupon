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
                    <div class="form-inline mb-3">
                        <select class="form-control mr-2" id="record-status">
                            <option value="" {if $Status==''}selected{/if}>全部状态</option>
                            <option value="unused" {if $Status=='unused'}selected{/if}>待使用</option>
                            <option value="used" {if $Status=='used'}selected{/if}>已使用</option>
                            <option value="expired" {if $Status=='expired'}selected{/if}>已过期</option>
                        </select>
                        <input class="form-control mr-2" id="record-keyword" value="{$Keyword|htmlspecialchars}" placeholder="券码 / 用户名 / UID">
                        <button type="button" id="record-search" class="btn btn-primary">搜索</button>
                        <button type="button" id="record-delete-selected" class="btn btn-outline-danger ml-2">批量删除</button>
                        <span class="text-muted ml-3">已使用券为订单凭证，不支持删除</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light"><tr><th style="width:40px;"><input type="checkbox" id="record-select-all"></th><th>UID / 用户</th><th>模板</th><th>优惠券码</th><th>来源</th><th>状态</th><th>有效期</th><th>订单</th><th>操作</th></tr></thead>
                            <tbody>
                            {foreach $List as $item}
                            <tr>
                                <td>{if $item.status!='used'}<input type="checkbox" class="record-check" value="{$item.id}">{else/}-{/if}</td>
                                <td>{$item.uid}<br><small>{$item.username|htmlspecialchars} {$item.email|htmlspecialchars}</small></td>
                                <td>{$item.title|htmlspecialchars}<br><small>{$item.type} / {$item.value}</small></td>
                                <td><code>{$item.code|htmlspecialchars}</code></td>
                                <td>{$item.source|htmlspecialchars}</td>
                                <td>{$item.status|htmlspecialchars}</td>
                                <td>{if $item.expires_at > 0}{:date('Y-m-d H:i', $item['expires_at'])}{else/}永久{/if}</td>
                                <td>{if $item.order_id > 0}#{$item.order_id}{else/}-{/if}</td>
                                <td>{if $item.status!='used'}<button type="button" class="btn btn-sm btn-outline-danger record-delete-one" data-id="{$item.id}">删除</button>{else/}<span class="text-muted">保留</span>{/if}</td>
                            </tr>
                            {/foreach}
                            {if empty($List)}<tr><td colspan="9" class="text-center text-muted">暂无发放记录</td></tr>{/if}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{$List->render()}</div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.getElementById('record-search').addEventListener('click', function () {
    var status = encodeURIComponent(document.getElementById('record-status').value);
    var keyword = encodeURIComponent(document.getElementById('record-keyword').value);
    location.href = '{$RecordsUrl}&status=' + status + '&keyword=' + keyword;
});
document.getElementById('record-select-all').addEventListener('change', function () {
    var checked = this.checked;
    Array.prototype.forEach.call(document.querySelectorAll('.record-check'), function (input) {
        input.checked = checked;
    });
});
function deleteRecords(ids) {
    if (!ids.length) {
        alert('请选择要删除的优惠券');
        return;
    }
    if (!confirm('确认删除选中的 ' + ids.length + ' 张优惠券吗？对应的原生优惠码也会失效。')) {
        return;
    }
    var body = ids.map(function (id) {
        return 'ids[]=' + encodeURIComponent(id);
    }).join('&');
    fetch('{$DeleteRecordsUrl}', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: body
    }).then(readJsonResponse).then(function (result) {
        alert(result.msg || '操作完成');
        if (result.status === 200) {
            location.reload();
        }
    }).catch(function (error) {
        alert(error.message || '删除请求失败，请稍后重试');
    });
}
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
document.getElementById('record-delete-selected').addEventListener('click', function () {
    var ids = [];
    Array.prototype.forEach.call(document.querySelectorAll('.record-check:checked'), function (input) {
        ids.push(input.value);
    });
    deleteRecords(ids);
});
Array.prototype.forEach.call(document.querySelectorAll('.record-delete-one'), function (button) {
    button.addEventListener('click', function () {
        deleteRecords([button.getAttribute('data-id')]);
    });
});
</script>
