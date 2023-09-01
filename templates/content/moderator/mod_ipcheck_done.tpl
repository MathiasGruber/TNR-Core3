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
            <td class="subHeader" colspan="2">Users on Join IP</td>
        </tr>
        {if $join_IPs != '0 rows'}
            <tr>
                <td width="71" style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td width="127" style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
            </tr>
            {foreach from=$join_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="2">Users on Last IP</td>
        </tr>
        {if $last_IPs != '0 rows'}
            <tr>
                <td width="71" style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td width="127" style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
            </tr>
            {foreach from=$last_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="2">Join IP Found on User's Past IPs</td>
        </tr>
        {if $last_join_IPs != '0 rows'}
            <tr>
                <td width="71" style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td width="127" style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
            </tr>
            {foreach from=$last_join_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="2">Last IP Found on User's Past IPs</td>
        </tr>
        {if $last_past_IPs != '0 rows'}
            <tr>
                <td width="71" style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td width="127" style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
            </tr>
            {foreach from=$last_past_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <a href="?id={$smarty.get.id}">Return</a>
</div>