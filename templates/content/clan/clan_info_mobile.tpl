<tr>
    <td>
        <headerText screenWidth="true">Clan Details</headerText>
    </td>
</tr>
<tr>
    <td>
        <text><b>Clan Name:</b> {$clan['name']}
            <br><b>Clan Rank:</b> {$clan['rank']}
            <br><b>Clan Element:</b> {$clan['element']}
            <br><b>Kage Position:</b> {$clan['kage_vote']}
            <br><b>Clan leader:</b> 
        </text>
        {if isset($canClaim) && $canClaim == true}
            <a href="?id=14&act2=claimLeader">Claim</a>
        {else}
            {$clan['leaderName']}
        {/if}
    </td>
    <td>
        <text><b>Activity Points:</b> {$clan['activity']}
            <br><b>Clan Members:</b> {$clanUsers}
            <br><b>Activity / Member:</b>: {$avgPoints}
            <br><b>Your Activity:</b> {$userPoints}
            <br><b>Your Clan Rank:</b> {$userClanRank}
        </text>
    </td>
    <td><img src="{$signature}"></img></td>
</tr>