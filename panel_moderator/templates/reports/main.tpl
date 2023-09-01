<div align="center">
    
    {if isset($unviewed)}
        {$subSelect="unviewed"}
        {include file="file:{$absPath}/{$unviewed}" title="unviewed"}
    {/if}
    
    {if isset($my)}
        {$subSelect="my"}
        {include file="file:{$absPath}/{$my}" title="my"}
    {/if} 
    
    {if isset($searchBox)}
        {include file="file:{$absPath}/{$searchBox}" title="searchBox"}
    {/if}
    
    {if isset($inprogress)}
        {$subSelect="inprogress"}
        {include file="file:{$absPath}/{$inprogress}" title="inprogress"}
    {/if}   
    
    
</div>