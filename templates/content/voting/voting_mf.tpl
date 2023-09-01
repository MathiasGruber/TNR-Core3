<div class="page-box">
    <div class="page-title">
        Voting Links!
    </div>

    <div class="page-content">
        <div>
            By voting on each topsite:<br>
            <i> Your chakra bar will be {$chakra}% restored<br>
                Your stamina bar will be {$stamina}% restored. </i><br><br>
            You can only vote/visit each site 1 time / day.
        </div>

        <div class="page-sub-title">
            Follow the links below and vote for TNR.
        </div>

        {if $timer[0]['AWG'] < ({$smarty.now} - 86400)}
            <a class="font-large" href="http://apexwebgaming.com/in/982/{$smarty.session.uid}" target="_blank">Apex Web Gaming</a>
        {/if}
        {if $timer[0]['TWG'] < ({$smarty.now} - 86400)}
            <a class="font-large" href="http://www.topwebgames.com/in.asp?id=3575&amp;alwaysreward=1&amp;vuser={$smarty.session.uid}" target="_blank">Top Web Games</a>
        {/if}
        {if $timer[0]['DOG'] < ({$smarty.now} - 86400)}
            <a class="font-large" href="http://www.directoryofgames.com/main.php?view=topgames&amp;action=vote&amp;v_tgame=1487&amp;votedef={$smarty.session.uid}" target="_blank">DirectoryOfGames</a>
        {/if}
        {if $timer[0]['GALAXY'] < ({$smarty.now} - 86400)}
            <a class="font-large" href="?id=71&amp;act=GALAXY" target="_blank">Galaxy News</a>
        {/if}
        {if $timer[0]['OGLAB'] < ({$smarty.now} - 86400)}
            <a class="font-large" href="?id=71&amp;act=OGLAB" target="_blank">OG Labs</a>
        {/if}

    </div>
</div>