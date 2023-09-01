{if isset($onStatus)}
    {if $onStatus == 1}
        <tr><td><headerText color="solidlightgreen"><b>.::Online::.</b></headerText></td></tr>
    {else}
        <tr><td><headerText color="solidlightred"><b>.::Offline::.</b></headerText></td></tr>
        {if isset($lastOnline)}
            <tr><td><text>Last Online: {$lastOnline}</text></td></tr>
        {/if}
    {/if}    
{/if}

<tr><td><headerText>Profile Overview</headerText></td></tr>
<tr>
    <td>
<text>
<b>Character status:</b><br>
Username: {$charInfo.username}<br>
Gender:  {$charInfo.gender} <br>
{$charInfo.marriedTo}
Level {$charInfo.level}, {$charInfo.rank}<br>
Village: {$charInfo.village} <br>
{if $charInfo.village != "Syndicate"}
ANBU: {$charInfo.anbu}<br>
Clan: {$charInfo.clan}<br>
{/if}
{if $charInfo.rank_id <= 2}
Sensei: {$charInfo.sensei}<br> 
{/if}
<br>
Experience: {$charInfo.experience}<br>
PvP Experience: {$charInfo.pvp_experience}<br>
Bloodline: {if !empty($charInfo.bloodlineMask)}{$charInfo.bloodlineMask}{else}{$charInfo.bloodline}{/if}<br>
DSR: {$charInfo.DSR}<br>
</text>
    </td>
    <td>

<img src="{$charInfo.avatar}" alt="User Avatar" />


    </td>
</tr>

<tr>
    <td>
<text>
<b>PVP Battles</b><br>
Battles won: {$charInfo.battles_won} <br>
Battles lost: {$charInfo.battles_lost} <br>
Battles fled: {$charInfo.battles_fled}<br>
Battle draws: {$charInfo.battles_draws}<br>
Battles fought: {math equation="won+lost+fled+draws" won=$charInfo.battles_won lost=$charInfo.battles_lost fled=$charInfo.battles_fled draws=$charInfo.battles_draws}<br>
Win Percentage: <font class="{$charInfo.color}">{$charInfo.percentage}%</font>
</text><text><b>AI Battles</b><br>
Battles won: {$charInfo.AIwon} <br>
Battles lost: {$charInfo.AIlost} <br>
Battles fled: {$charInfo.AIfled} <br>
Battle draws: {$charInfo.AIdraw} <br>
Arena Record: {$charInfo.torn_record}<br>
</text>
    </td>
    <td>
<text>
<b>Site Support:</b><br>
Federal support: {$charInfo.federal}<br>
Federal level: {$charInfo.federal_level}<br>
Rep points ever: {$charInfo.rep_ever}<br>
Rep points now: {$charInfo.rep_now}<br>
Pop points ever: {$charInfo.pop_ever}<br>
Pop points now: {$charInfo.pop_now}
</text>

<a href="?id=3&amp;act=newMessage&amp;toUser={$charInfo.username}">Send PM</a>
<a href="?id={$smarty.get.id}&amp;page=view_nindo&amp;uid={$charInfo.id}">View Nindo</a>
<a href="?id=53&amp;act=user&amp;uid={$charInfo.id}">Report User</a> 

    </td>
</tr>