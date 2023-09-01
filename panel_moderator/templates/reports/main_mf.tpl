<div class="page-box">
    
    <div class="page-title">
        User Reports
    </div>

    <div class="page-content">
        {$first = true}
        {if isset($unviewed)}
            {$subSelect="unviewed"}
            {include file="file:{$absPath}/{$unviewed}" title="unviewed"}
        {/if}
        
        {$first = false}
        {if isset($my)}
            {$subSelect="my"}
            {include file="file:{$absPath}/{$my}" title="my"}
        {/if} 
        
        {$input_full_page = false}
        {if isset($searchBox)}
            {include file="file:{$absPath}/{$searchBox}" title="searchBox"}
        {/if}
        
        {if isset($inprogress)}
            {$subSelect="inprogress"}
            {include file="file:{$absPath}/{$inprogress}" title="inprogress"}
        {/if}   
    </div>
    
</div>