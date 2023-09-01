{if isset($moderators)}
    {$subSelect="moderators"}
    {include file="file:{$absPath}/{$moderators|replace:'.tpl':'_mobile.tpl'}" title="Moderators"}
{/if}

{if isset($contentTeam)}
    {$subSelect="contentTeam"}
    {include file="file:{$absPath}/{$contentTeam|replace:'.tpl':'_mobile.tpl'}" title="contentTeam"}
{/if}

<tr>
    <headerText><br>Public Relations</headerText>
</tr>
<tr>
    <td><text><b>Teni</b>'s mission is to make sure information about updates, game changes, staff restructuring etc. is delayed properly to the end-users, and also between sections of the staff. He furthermore manages the game facebook page. </text></td>
</tr>


<tr>
    <headerText><br>Game Owner and Coder</headerText>
</tr>
<tr>
    <td>
        <text><b>Terriator</b> is the game owner. He currently operates the game on the overall level, and spends most of the time coding to further improve the game. He is also involved with everything else that goes on in the different teams.</text> 
        <a href="http://www.theninja-forum.com/index.php?/topic/44488-me-and-tnr/">Game History</a>
    </td>
</tr>

<tr>
    <td>
        <a href="?id={$smarty.get.id}">Return</a>
    </td>
</tr>

