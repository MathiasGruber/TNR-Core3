{if $squad != "0 rows"}
    {if $squad['leader_uid_last_activity'] > ($serverTime - 120)}
        {$status = '<font color="#008000">Online</font>'}
    {else}
        {$status = '<font color="#FF0000">Offline</font>'}
    {/if}
{/if}

<tr>
    <td>
        <headerText screenWidth="true">Squad Details</headerText>
        <text>
<b>Name:</b> {$squad['name']}<br>
<b>Rank:</b> {$squad['rank']}<br>
<b>Assault Points: </b> {$squad['pt_rage']}<br>
<b>Defense Points: </b> {$squad['pt_def']}<br>
        </text>
    </td>
</tr>
  
{if isset($anbuSquad)}
    {$subSelect="anbuSquad"}
    {include file="file:{$absPath}/{$anbuSquad|replace:'.tpl':'_mobile.tpl'}" title="Squad Members"}
{/if}


<tr>
    <td>
        <headerText screenWidth="true">Options</headerText>
    </td>
</tr>
{if ($squad['leader_uid']) == ($smarty.session.uid)}
    <tr>
        <td>
            <a href="?id={$smarty.get.id}&act=invite">Invite member</a>
        </td>
        <td>
            <a href="?id={$smarty.get.id}&act=orders">Squad Orders</a>
        </td>
        <td>
            <a href="?id={$smarty.get.id}&act=kick">Kick member</a>
        </td>
    </tr>
{/if}

<tr>
    <td>
        <a href="?id={$smarty.get.id}&act=resign">Resign</a>
    </td>
    <td>
        <a href="?id=95">ANBU Chat</a>
    </td>
    {if $squad['rank'] != 'Trainees'}
        <td>
            <a href="?id={$smarty.get.id}&act=shop">ANBU Shop</a>
        </td>
    {/if}
</tr>