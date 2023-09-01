<table class="table" width="95%">
    <tr>
        <td class="subHeader">Level Up</td>
    </tr>
    <tr>
        <td>
            <br>
            You have reached lvl {$charInfo.level + 1} {$newLevel.rank} and have gained:<br>
            <b>{$newLevel.health_gain}</b> Health, 
            <b>{$newLevel.chakra_gain}</b> Chakra, and 
            <b>{$newLevel.stamina_gain}</b> Stamina
            <br><br>
            {if isset($nextOrderDescription)}
                <blockquote>
                    <span>
                        <b>Your Order for Next Level</b><br>
                        {$nextOrderDescription|nl2br}
                    </span>
                </blockquote>
            {/if}
            <br>
            <a href="?id={$smarty.get.id}">Return</a>
        </td>
    </tr>
</table>    
        
        
