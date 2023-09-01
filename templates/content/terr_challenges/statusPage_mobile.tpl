<tr>
    <td>
        <headerText>Territory Challenges</headerText>
        <text stripTags="true">{$information}</text>   
    </td>
</tr>

<tr>
    {if isset($terrStatusMessage)}
        {$subSelect="terrStatusMessage"}
        {include file="file:{$absPath}/{$terrStatusMessage|replace:'.tpl':'_mobile.tpl'}" title="Admin Notes"}
    {/if}    
</tr>

{if isset($showLogs)}
    <tr>            
        <td><headerText>Territory Battle Logs</headerText></td>
    </tr>
    <tr>
        <td>
            Each territory battle is split into three parts; Chunins, Jounins and Elite Jounins.
        </td>
    </tr>
    <tr>
        <td>
            <a href="?id={$smarty.get.id}&amp;log=chuuninWinner">Chuunin</a>
        </td>
        <td>
            <a href="?id={$smarty.get.id}&amp;log=jouninWinner">Jounin</a>
        </td>
        <td>
            <a href="?id={$smarty.get.id}&amp;log=specialjouninWinner">Elite Jounin</a>
        </td>
    </tr>
    <tr>
        <td>
            {if isset($logInclude)}
                {include file="file:{$absPath}/{$logInclude|replace:'.tpl':'_mobile.tpl'}" title="{$logInclude}"}
            {elseif isset($logText)}
                <text>{$logText}</text>
            {/if}
        </td>
    </tr>
{/if}