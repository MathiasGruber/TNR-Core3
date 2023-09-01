<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="3" class="subHeader">{$kageInfo.village}</td>
        </tr>
        <tr>
            <td width="20%">Username:</td>
            <td width="30%">{$kageInfo.username}</td>
            <td width="50%" rowspan="4" align="center" valign="middle" style="padding:5px;">
                <img src="{$kageInfo.avatar}"/></td>
        </tr>
        <tr>
            <td>User Rank:</td>
            <td>{$kageInfo.rank}</td>
        </tr>
        <tr>
            <td>Bloodline:</td>
            <td>{$kageInfo.bloodline}</td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="padding-left:5px;">
                {if $kageInfo.rank != "Guardian"}
                    <a href="?id=13&page=profile&name={$kageInfo.username}">View {$kageInfo.username}'s Profile</a><br>
                {else}
                    &nbsp;
                {/if}
                {if $kageInfo.challenge == "yes"} 
                    <a href="?id={$smarty.get.id}&act={$smarty.get.act}&doChallenge={$kageInfo.username}{if isset($pvpCode)}&code={$pvpCode}{/if}">Challenge the kage!</a><br> 
                {else}
                     {$kageInfo.challenge}
                {/if}
            </td>
        </tr>
    </table>
</div>