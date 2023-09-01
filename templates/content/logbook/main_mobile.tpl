{if isset($updates)}
    {$subSelect="updates"}  
    {include file="file:{$absPath}/{$updates|replace:'.tpl':'_mobile.tpl'}" title="Logbook updates"}
{/if}

{if isset($entries)}
    {$subSelect="entries"}  
    {include file="file:{$absPath}/{$entries|replace:'.tpl':'_mobile.tpl'}" title="Logbook Entries"}
{/if}