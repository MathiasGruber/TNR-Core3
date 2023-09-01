{if isset($menu)}
    {* Menu for Content Admins *}
    <b>Event Modules</b>
    <ul>   
    {foreach $menu as $entry}
        {if $entry[1][0] == "event"}
            <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
        {/if}        
    {/foreach}
    </ul>
{else}
    Currently accepted IPs are:<br>    
{/if}                 