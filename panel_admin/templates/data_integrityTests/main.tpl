<div align="center">
    {if isset($users)}
        {$subSelect="users"}  
        {include file="file:{$absPath}/{$users}" title="User Errors"}
    {/if}
    
    {if isset($usersBattles)}
        {$subSelect="usersBattles"}  
        {include file="file:{$absPath}/{$usersBattles}" title="usersBattles Errors"}
    {/if}
    
    {if isset($battleUsers)}
        {$subSelect="battleUsers"}  
        {include file="file:{$absPath}/{$battleUsers}" title="battleUsers Errors"}
    {/if}
</div>