<div align="center">
    {if isset($users)}
        {$subSelect="users"}
        {include file="file:{$absPath}/{$users}" title="Nearby Users"}
    {/if}
    
    {if isset($resources)}
        {$subSelect="resources"}
        {include file="file:{$absPath}/{$resources}" title="Nearby resources"}
    {/if}
</div>