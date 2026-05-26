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
                    <h5 class="mt-4">安装检查</h5>
                    <div class="row">
                        {foreach $Checks as $check}
                        <div class="col-lg-4 col-md-6 mb-2">
                            <div class="border rounded p-2">
                                {if $check.ok}<span class="badge badge-success">正常</span>{else/}<span class="badge badge-danger">异常</span>{/if}
                                {$check.name|htmlspecialchars}
                            </div>
                        </div>
                        {/foreach}
                    </div>
                    <hr>
                    <h5>购物车集成</h5>
                    <p>仓库中的 <code>/public/themes/cart/server/configureproduct.tpl</code> 和 <code>/public/themes/cart/server/viewcart.tpl</code> 已接入。配置页可预选优惠券，进入结算页后自动应用；未预选时，结算页显示蓝色“一键使用优惠券”入口。</p>
                    <label class="font-weight-bold">结算页 viewcart.tpl</label>
                    <textarea id="integration-snippet" class="form-control" rows="12" readonly>{$Snippet|htmlspecialchars}</textarea>
                    <button type="button" data-copy-target="integration-snippet" class="copy-snippet btn btn-primary mt-2">复制结算页代码</button>
                    <label class="font-weight-bold d-block mt-4">商品配置页 configureproduct.tpl</label>
                    <textarea id="configure-integration-snippet" class="form-control" rows="14" readonly>{$ConfigureSnippet|htmlspecialchars}</textarea>
                    <button type="button" data-copy-target="configure-integration-snippet" class="copy-snippet btn btn-primary mt-2">复制配置页代码</button>
                    <div class="alert alert-warning mt-4 mb-0">
                        魔方系统授权状态按宿主的 <code>system_license</code> 配置检测。配置页和结算页会直接内嵌当前用户可用券，不再请求前台 <code>/addons</code> JSON 接口；真正应用优惠码时仍复用魔方原生购物车结算。
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
Array.prototype.forEach.call(document.querySelectorAll('.copy-snippet'), function (button) {
    button.addEventListener('click', function () {
        var content = document.getElementById(button.getAttribute('data-copy-target'));
        content.select();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(content.value);
        } else {
            document.execCommand('copy');
        }
        alert('集成代码已复制');
    });
});
</script>
