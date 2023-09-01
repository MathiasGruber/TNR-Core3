{if isset($allowPost) && $allowPost == "yes"}
    {include file="./postBox_mobile.tpl" title="Post Box"}
{elseif isset($allowPost) }  
    {assign "subHeader" "System Message"}
    {assign "msg" {$allowPost}}
    {assign "returnLink" ""}
    {assign "returnLabel" ""}
    {include file="../../message_mobile.tpl" title="System Messages"}   
{/if}

<tr>
    <td>
        <headerText>{$welcomeMessage}</headerText>
        {if isset($subMessage)}
            {$subMessage}
        {/if}
    </td>
</tr>

<tr>
    <td>
        <buttonList>
            <buttonListButton href="?id={$smarty.get.id}&amp;min={$mins[0]}">Newer</buttonListButton>
            <buttonListButton href="?id={$smarty.get.id}&amp;min=0">Refresh</buttonListButton>
            <buttonListButton href="?id={$smarty.get.id}&amp;min={$mins[2]}">Older</buttonListButton>
        </buttonList>
    </td>
</tr>

{include file="./messages_mobile.tpl" title="Tavern Messages"} 