<div align="center">
    {if isset($updates)}
        {$subSelect="updates"}  
        {include file="file:{$absPath}/{$updates}" title="Logbook updates"}
    {/if}
    
    {if isset($entries)}
        {$subSelect="entries"}  
        {include file="file:{$absPath}/{$entries}" title="Logbook Entries"}
    {/if}
</div>