<div class="page-box">
    <div class="page-title">
        Crew List
    </div>
    <div class="page-content">
        {if isset($moderators)}
            {$subSelect="moderators"}
            {include file="file:{$absPath}/{$moderators}" title="Moderators"}
        {/if}

        <br>

        {if isset($eventTeam)}
            {$subSelect="eventTeam"}
            {include file="file:{$absPath}/{$eventTeam}" title="eventTeam"}
        {/if}

        <br>

        {if isset($balanceTeam)}
            {$subSelect="balanceTeam"}
            {include file="file:{$absPath}/{$balanceTeam}" title="balanceTeam"}
        {/if}

        <br>

        {if isset($prTeam)}
            {$subSelect="prTeam"}
            {include file="file:{$absPath}/{$prTeam}" title="prTeam"}
        {/if}

        <br>

        <div class="page-sub-title">
            Game Owner & Original Developer
        </div>

        <br>

        <div>
            <b>Terriator</b> is the game owner. A short version of his story can be found <a href="http://www.theninja-forum.com/index.php?/topic/44488-me-and-tnr/">here</a>. He currently spends most of his time in a far off land, untouched by time.
        </div>

        <br>

        <div class="page-sub-title">
            Current Developer
        </div>

        <br>

        <div>
            <b>Koala</b> is the games current programmer. 
        </div>
    </div>
</div>