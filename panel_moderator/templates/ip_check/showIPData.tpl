<div align="center">
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="2">Users with Join IP matching {$ip_addr}</td>
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
            <td class="subHeader" colspan="2">Users with Last IP matching {$ip_addr}</td>
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
            <td class="subHeader" colspan="2">Users with Past IPs matching {$ip_addr}</td>
        </tr>
        {if $past_IPs != '0 rows'}
            <tr>
                <td width="71" style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td width="127" style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
            </tr>
            {foreach from=$past_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <a href="?id={$smarty.get.id}">Return</a>
</div>