{if isset($menu)}
    {* Menu for Content Admins *}
    <b>Content Modules</b>
    <ul>   
    {foreach $menu as $entry}
        {if $entry[1][0] == "content"}
            <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
        {/if}        
    {/foreach}
    </ul>
    
    {* Menus for Admins *}  
    {if $user_rank == "Admin" || $user_rank == "Supermod" }
        <b>Maintenance Modules</b><br>
        <ul>    
        {foreach $menu as $entry}
            {if $entry[1][0] == "maintain"}
                <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
            {/if}        
        {/foreach}
        </ul>
        
        <b>Admin Modules</b><br>
        <ul>    
        {foreach $menu as $entry}
            {if $entry[1][0] == "admin"}
                <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
            {/if}        
        {/foreach}
        </ul>

        <b>Event Modules</b><br>
        <ul>    
        {foreach $menu as $entry}
            {if $entry[1][0] == "event"}
                <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
            {/if}        
        {/foreach}
        </ul>
        
        <b>Log Modules</b><br>
        <ul>    
        {foreach $menu as $entry}
            {if $entry[1][0] == "log"}
                <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
            {/if}        
        {/foreach}
        </ul>
        
        <b>Finance Modules</b><br>
        <ul>    
        {foreach $menu as $entry}
            {if $entry[1][0] == "finance"}
                <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
            {/if}        
        {/foreach}
        </ul> 
        
        <b>TNR Mobile Modules</b><br>
        <ul>    
        {foreach $menu as $entry}
            {if $entry[1][0] == "tnr_mobile"}
                <li><a href="?id={$entry[2]}">{$entry[1][1]|replace:'_':' '} </a></li>
            {/if}        
        {/foreach}
        </ul> 
    {/if}
    
    {if $user_rank == "Admin"}
        <br>
        <div align="center">
            <form id="form1" name="form1" method="post" action="?id={$userAdminID}&act=search">
            <table class="table">
              <tr>
                <td class="subHeader">Quick Search</td>
              </tr>
              <tr>
                <td><input type="text" class="textfield" name="username" id="textfield" /></td>
              </tr>
              <tr>
                <td><input name="Submit" type="submit" class="button" value="Search Username" /></td>
                </tr>
            </table>
            </form>
        </div>
    {/if}
{else}
    Currently accepted IPs are:<br>    
{/if}                 