<tr>
    <td><headerText>Level Up</headerText></td>
</tr>
<tr>
    <td>
<text>You have reached lvl {$charInfo.level + 1} {$newLevel.rank} and gained:
<b>{$newLevel.health_gain}</b> Health, 
<b>{$newLevel.chakra_gain}</b> Chakra, and 
<b>{$newLevel.stamina_gain}</b> Stamina
</text>

<text><br>The following is the next order, which you need to complete in order to advance to the next level.</text>
    </td>
</tr>
{if isset($nextOrder)}
    {$subSelect="nextOrder"}  
    {include file="file:{$absPath}/{$nextOrder|replace:'.tpl':'_mobile.tpl'}" title="nextOrder"}
{/if}


