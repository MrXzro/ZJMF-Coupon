<section class="admin-main">
    <div class="container-fluid">
        <div class="page-container">
            <div class="card">
                <div class="card-body">
                    <div class="card-title row">
                        <div style="padding:0 15px;">{$Title}</div>
                        <div class="col-lg-8 col-md-12">
                            {foreach $PluginsAdminMenu as $v}
                            <span class="ml-2"><a class="h5" href="{$v.url}" {if $v['custom']}target="_blank"{/if}>{$v.name}</a></span>
                            {/foreach}
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md"><div class="card bg-light"><div class="card-body"><div class="text-muted">券模板</div><h3>{$Stats.templates}</h3></div></div></div>
                        <div class="col-md"><div class="card bg-light"><div class="card-body"><div class="text-muted">累计发放</div><h3>{$Stats.issued}</h3></div></div></div>
                        <div class="col-md"><div class="card bg-light"><div class="card-body"><div class="text-muted">待使用</div><h3>{$Stats.unused}</h3></div></div></div>
                        <div class="col-md"><div class="card bg-light"><div class="card-body"><div class="text-muted">已使用</div><h3>{$Stats.used}</h3></div></div></div>
                        <div class="col-md"><div class="card bg-light"><div class="card-body"><div class="text-muted">今日签到</div><h3>{$Stats.signin_today}</h3></div></div></div>
                    </div>
                    <h5 class="mt-4">最近发放记录</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light"><tr><th>用户</th><th>模板</th><th>券码</th><th>来源</th><th>状态</th><th>发放时间</th></tr></thead>
                            <tbody>
                            {foreach $Latest as $item}
                            <tr>
                                <td>{if !empty($item.username)}{$item.username|htmlspecialchars}{else/}UID {$item.uid}{/if}</td>
                                <td>{$item.title|htmlspecialchars}</td>
                                <td><code>{$item.code|htmlspecialchars}</code></td>
                                <td>{$item.source|htmlspecialchars}</td>
                                <td>{$item.status|htmlspecialchars}</td>
                                <td>{:date('Y-m-d H:i', $item['issued_at'])}</td>
                            </tr>
                            {/foreach}
                            {if empty($Latest)}<tr><td colspan="6" class="text-center text-muted">暂无记录</td></tr>{/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
