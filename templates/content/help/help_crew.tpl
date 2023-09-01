<div align="center">   
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
    
    <table class="table" width="95%">
        <tr>
            <td class="subHeader">Game Owner & Original Developer</td>
        </tr>
        <tr>
            <td><b>Terriator</b> is the game owner. A short version of his story can be found <a href="http://www.theninja-forum.com/index.php?/topic/44488-me-and-tnr/">here</a>. He currently spends most of his time in a far off land, un touched by time. </td>
        </tr>
    </table>

    <br>

    <table class="table" width="95%">
        <tr>
            <td class="subHeader">Current Developer</td>
        </tr>
        <tr>
            <td><b>Koala</b> is the games current programmer.
        </tr>
    </table>
    
    <a href="?id={$smarty.get.id}">Return</a>
</div>