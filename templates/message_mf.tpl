<div class="{if $full_page}page-box{else}page-content-addon{/if}">
    <div class="{if $full_page}page-title{else}page-sub-title-no-margin{/if}"> 
        {$subHeader}
    </div>
    <div class="{if $full_page}page-content{/if}">
        <div>
            {$msg}
        </div>
        {if substr( $returnLabel, 0, 6 ) != 'Return'}
            <div>
                <a href="?{$returnLink}" class="{$returnLinkClass}">{$returnLabel}</a>
            </div>
        {/if}
    </div>
</div>
