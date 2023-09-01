<div align="center">
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="4">IP Check</td>
        </tr>
        <tr>
            <td width="71" style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
            <td width="127" style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
            <td width="125" style="border-bottom:1px solid #000000;font-weight:bold;">Last IP</td>
            <td width="125" style="border-bottom:1px solid #000000;font-weight:bold;">Join IP</td>
        </tr>
        <tr class="row1">
            <td align="center">{$user[0]['id']}</td>
            <td align="center">{$user[0]['username']}{if $user[0]['perm_ban'] == 1}*{/if}</td>
            <td align="center">{$user[0]['last_ip']}</td>
            <td align="center">{$user[0]['join_ip']}</td>
        </tr>
    </table>
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="3">Users on Join IP ({$user[0]['join_ip']})</td>
        </tr>
        {if $join_IPs != '0 rows'}
            <tr>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Check</td>
            </tr>
            {foreach from=$join_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                    <td><form id="hireModForm" action="?id=2" method="post" enctype="multipart/form-data" ><button class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Submit">check</button><input type="hidden" name="ip_select" value="Username"><input name="ip_search" type="hidden" value="{$v['username']}"></form></td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="3">Users on Last IP ({$user[0]['last_ip']})</td>
        </tr>
        {if $last_IPs != '0 rows'}
            <tr>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Check</td>
            </tr>
            {foreach from=$last_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                    <td><form id="hireModForm" action="?id=2" method="post" enctype="multipart/form-data" ><button class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Submit">check</button><input type="hidden" name="ip_select" value="Username"><input name="ip_search" type="hidden" value="{$v['username']}"></form></td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="3">IP Address {$user[0]['join_ip']} Found on User's Past IPs</td>
        </tr>
        {if $last_join_IPs != '0 rows'}
            <tr>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Check</td>
            </tr>
            {foreach from=$last_join_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                    <td><form id="hireModForm" action="?id=2" method="post" enctype="multipart/form-data" ><button class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Submit">check</button><input type="hidden" name="ip_select" value="Username"><input name="ip_search" type="hidden" value="{$v['username']}"></form></td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="3">IP Address {$user[0]['last_ip']} Found on User's Past IPs</td>
        </tr>
        {if $last_past_IPs != '0 rows'}
            <tr>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Check</td>
            </tr>
            {foreach from=$last_past_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                    <td><form id="hireModForm" action="?id=2" method="post" enctype="multipart/form-data" ><button class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Submit">check</button><input type="hidden" name="ip_select" value="Username"><input name="ip_search" type="hidden" value="{$v['username']}"></form></td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>

    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="6">IP Addresses {$super_search_ips} Found on User's join, last, or past IPs</td>
        </tr>
        {if $super_search != '0 rows'}
            <tr>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Join</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Last</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Past</td>
                <td style="border-bottom:1px solid #000000;font-weight:bold;">Check</td>
            </tr>
            {foreach from=$super_search key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                    <td align="center">{$v['join_ip']}</td>
                    <td align="center">{$v['last_ip']}</td>
                    <td align="center">{$v['past_ips']}</td>
                    <td><form id="hireModForm" action="?id=2" method="post" enctype="multipart/form-data" ><button class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Submit">check</button><input type="hidden" name="ip_select" value="Username"><input name="ip_search" type="hidden" value="{$v['username']}"></form></td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <a href="?id={$smarty.get.id}">Return</a>
</div>