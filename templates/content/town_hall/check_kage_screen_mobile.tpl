<tr>
    <td>
        <headerText>{$kageInfo.village}</headerText>
        <text>
<b>Username:</b> {$kageInfo.username}<br>
<b>User Rank:</b> {$kageInfo.rank}<br>
<b>Bloodline: </b> {$kageInfo.bloodline}
        </text>
    </td>
    <td>
        <img src="{$kageInfo.avatar}"></img>
    </td>
</tr>

<tr>
    <td>
        {if $kageInfo.rank != "Guardian"}
            <a href="?id=13&page=profile&name={$kageInfo.username}">View {$kageInfo.username}'s Profile</a>
        {/if}
        {if $kageInfo.challenge == "yes"} 
            <a href="?id={$smarty.get.id}&act={$smarty.get.act}&doChallenge={$kageInfo.username}{if isset($pvpCode)}&code={$pvpCode}{/if}">Challenge the kage!</a><br> 
        {else}
            <text>{$kageInfo.challenge}</text>
        {/if}   
    </td>
</tr>

<tr><td><button href="?id={$smarty.get.id}">Return</button></td></tr>