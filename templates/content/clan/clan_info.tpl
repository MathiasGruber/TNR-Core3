<table width="95%" class="table">
    <tr>
        <td colspan="3" class="subHeader">Clan Details </td>
    </tr>
    <tr>
        <td width="33%" style="padding-left:15px;text-align:left;">
            <b>Clan Name:</b> {$clan['name']} <br>
            <b>Clan Rank:</b> {$clan['rank']}<br>
            <b>Clan Element:</b> {$clan['element']}<br>
            <b>Kage Position:</b> {$clan['kage_vote']}<br>
            <b>Clan leader:</b> 
            {if isset($canClaim) && $canClaim == true}
                <a href="?id=14&act2=claimLeader">Claim</a>
            {else}
                {$clan['leaderName']}
            {/if}
        </td>
        <td width="33%" style="padding-left:15px;text-align:left;">
            <b>Activity Points:</b> {$clan['activity']}<br>
            <b>Clan Members:</b> {$clanUsers} <br>
            <b>Activity / Member:</b>: {$avgPoints}<br>
            <b>Your Activity:</b> {$userPoints}<br>
            <b>Your Clan Rank:</b> {$userClanRank}<br>
        </td>
        <td width="33%" style="padding-left:15px;">
            <img style="border:1px solid #000000;" src="{$signature}">
        <br></td>
    </tr>
</table>