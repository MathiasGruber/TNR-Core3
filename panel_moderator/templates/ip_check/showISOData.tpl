<div align="center">
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="2">Extensive and Isolated IP Check</td>
        </tr>
        {if $ip_filter != '0 rows'}
            <tr>
                <td width="71" style="border-bottom:1px solid #000000;font-weight:bold;">IP Address</td>
                <td width="127" style="border-bottom:1px solid #000000;font-weight:bold;">User Matches</td>
            </tr>{$ctr = 0}
            {foreach from=$ip_filter key=k item=v}
                <tr class="row{({$ctr++} % 2) + 1}">
                    <td align="center">{$k}</td>
                    <td align="center">{$v['Matches']} Accounts</td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
    <a href="?id={$smarty.get.id}">Return</a>
</div>