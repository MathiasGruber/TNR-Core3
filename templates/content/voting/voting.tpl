<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">Voting Links!</td>
        </tr>
        <tr> 
            <td colspan="2" style="border-bottom:1px solid black;">
                By voting on each topsite:<br>
                <i> Your chakra bar will be {$chakra}% restored<br>
                    Your stamina bar will be {$stamina}% restored. </i><br><br>
                You can only vote/visit each site 1 time / day.
            </td>
        </tr>
        <tr>
            <td  class="tableColumns"><b>Voting Site</b></td>
            <td  class="tableColumns"><b>What to do</b></td>
        </tr>
        {if $timer[0]['AWG'] < ({$smarty.now} - 86400)}
            <tr class="row2">
                <td><a href="http://apexwebgaming.com/in/982/{$smarty.session.uid}" target="_blank">Apex Web Gaming</a></td>
                <td><i>Press the link to the left and vote for TNR</i></td>
            </tr>
        {/if}
        {if $timer[0]['TWG'] < ({$smarty.now} - 86400)}
            <tr class="row2">
                <td><a href="http://www.topwebgames.com/in.asp?id=3575&amp;alwaysreward=1&amp;vuser={$smarty.session.uid}" target="_blank">Top Web Games</a></td>
                <td><i>Press the link to the left and vote for TNR</i></td>
            </tr>
        {/if}
        {if $timer[0]['DOG'] < ({$smarty.now} - 86400)}
            <tr class="row1">
                <td><a href="http://www.directoryofgames.com/main.php?view=topgames&amp;action=vote&amp;v_tgame=1487&amp;votedef={$smarty.session.uid}" target="_blank">DirectoryOfGames</a></td>
                <td><i>Press the link to the left and vote for TNR</i></td>
            </tr>
        {/if}
        {if $timer[0]['GALAXY'] < ({$smarty.now} - 86400)}
            <tr class="row2">
                <td><a href="?id=71&amp;act=GALAXY" target="_blank">Galaxy News</a></td>
                <td><i>Press the link to the left and vote for TNR</i></td>
            </tr>
        {/if}
        {if $timer[0]['OGLAB'] < ({$smarty.now} - 86400)}
            <tr class="row1">
                <td><a href="?id=71&amp;act=OGLAB" target="_blank">OG Labs</a></td>
                <td><i>Press the link to the left and vote for TNR</i></td>
            </tr>
        {/if}
    </table>
</div>