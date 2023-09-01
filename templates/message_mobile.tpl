<tr>
    <td>
        <headerText>{$subHeader}</headerText>
        <text stripTags="true">{$msg}</text>   
    </td>
</tr>
{if isset($returnLink)}    
    {if $returnLink === true}
        <tr><td><button href="?id={$smarty.get.id}">{$returnLabel}</button></td></tr>
    {elseif $returnLink !== false}
        <tr><td><button href="{$returnLink}">{$returnLabel}</button></td></tr>
    {/if}    
{/if} 
        
        