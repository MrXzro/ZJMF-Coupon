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

                    <div class="alert alert-info">
                        券码前缀只影响后续新发放的优惠券；已经发出的历史券码不会被修改。建议使用字母、数字、下划线或中横线，例如 <code>qjy_</code>、<code>cloud-</code>。
                    </div>

                    <form id="settings-form" class="border rounded p-3" style="max-width:720px;">
                        <div class="form-group">
                            <label class="font-weight-bold">优惠券码前缀</label>
                            <input class="form-control" id="code-prefix" name="code_prefix" maxlength="24"
                                value="{$CodePrefix|htmlspecialchars}" placeholder="qjy_">
                            <small class="form-text text-muted">仅允许字母、数字、下划线和中横线，最长 24 位。</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">券码预览</label>
                            <div class="rounded bg-light p-2">
                                <code id="code-preview"></code>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">保存设置</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var form = document.getElementById('settings-form');
    var input = document.getElementById('code-prefix');
    var preview = document.getElementById('code-preview');

    function normalize(value) {
        return String(value || '').replace(/[^a-zA-Z0-9_-]/g, '').substring(0, 24);
    }

    function updatePreview() {
        var prefix = normalize(input.value) || 'qjy_';
        preview.textContent = prefix + 'a1b2c3d4e5f6a7b8';
    }

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

    input.addEventListener('input', updatePreview);
    updatePreview();

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var normalized = normalize(input.value);
        input.value = normalized;
        if (!normalized) {
            alert('请输入有效的券码前缀');
            updatePreview();
            return;
        }
        postForm('{$SaveSettingsUrl}', new FormData(form))
            .then(function (result) {
                alert(result.msg);
                if (result.status === 200 && result.data && result.data.prefix) {
                    input.value = result.data.prefix;
                    updatePreview();
                }
            })
            .catch(function (error) { alert(error.message); });
    });
}());
</script>
