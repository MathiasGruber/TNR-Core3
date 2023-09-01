{if isset($menu)}
    {* Menu for Content Admins *}
    <b>Moderator Modules</b>
    <ul>   
    {foreach $menu as $entry}
        {if $entry[1][0] == "moderator"}
            <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
        {/if}        
    {/foreach}
    </ul>
    
    {* Menus for Head Moderators *}  
    {if $user_rank == "Admin" ||  $user_rank == "Supermod" }
        
        <b>Head Moderator Modules</b><br>
        <ul>    
        {foreach $menu as $entry}
            {if $entry[1][0] == "supermod"}
                <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
            {/if}        
        {/foreach}
        </ul>
        
        
    {/if}
{else}
    Currently accepted IPs are:<br>    
{/if}                 