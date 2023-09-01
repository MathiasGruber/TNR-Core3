{if $squad != "0 rows"}
    {if $squad['leader_uid_last_activity'] > ($serverTime - 120)}
        {$status = '<font color="#008000">Online</font>'}
    {else}
        {$status = '<font color="#FF0000">Offline</font>'}
    {/if}
{/if}
<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="4" class="subHeader">Squad Details</td>
        </tr>
        <tr>
            <td colspan="3" align="left" style="padding-left:15px;">Name:</td>
            <td width="46%">{$squad['name']}</td>
        </tr>
        <tr>
            <td colspan="3" align="left" style="padding-left:15px;">Rank:</td>
            <td width="46%">{$squad['rank']}</td>
        </tr>
        <tr>
            <td colspan="3" align="left" style="padding-left:15px;">Assault Points </td>
            <td align="left">{$squad['pt_rage']}</td>
        </tr>
        <tr>
            <td colspan="3" align="left" style="padding-left:15px;">Defense Points </td>
            <td align="left">{$squad['pt_def']}</td>
        </tr>
    </table>
</div>

{if isset($anbuSquad)}
    <div align="center">
        {$subSelect="anbuSquad"}
        {include file="file:{$absPath}/{$anbuSquad}" title="Squad Members"}
    </div>
{/if}

<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="3" class="subHeader">Options</td>
        </tr>
        {if ($squad['leader_uid']) == ($smarty.session.uid)}
            <tr>
                <td width="33%" style="text-align:center;">
                    <a href="?id={$smarty.get.id}&act=invite">Invite member</a>
                </td>
                <td width="33%" style="text-align:center;">
                    <a href="?id={$smarty.get.id}&act=orders">Squad Orders</a>
                </td>
                <td width="33%" style="text-align:center;">
                    <a href="?id={$smarty.get.id}&act=kick">Kick member</a>
                </td>
            </tr>
        {/if}
        <tr>
            <td width="33%" style="text-align:center;">
                <a href="?id={$smarty.get.id}&act=resign">Resign</a>
            </td>
            <td width="33%" style="text-align:center;">
                <a href="?id=95">ANBU Chat</a>
            </td>
            {if $squad['rank'] != 'Trainees'}
                <td width="33%" style="text-align:center;">
                    <a href="?id={$smarty.get.id}&act=shop">ANBU Shop</a>
                </td>
            {else}
                <td width="33%" style="text-align:center;">&nbsp;</td>  
            {/if}
        </tr>
    </table>
</div>