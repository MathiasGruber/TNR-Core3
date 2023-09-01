<div class="page-box">
    <div class="page-title lazy">
        {$subHeader} {if $subTitle_info != ''}<span class="toggle-button-info closed" data-target="#subTitle_info"/>{/if}
    </div>    
    <div class="page-content page-column-{$nCols} {if $nCols > 2}font-shrink-early {/if}lazy" style='padding-top:10px'>
        
        {if isset($subTitle_info)}
            <div class="page-sub-title-top span-{$nCols} toggle-target closed" id="subTitle_info">
                {$subTitle_info}
            </div>
        {/if}

        {if isset($subTitle)}
            <div class="page-sub-title{if !isset($subTitle_info)}-top{/if} span-{$nCols}">
                {$subTitle}
            </div>
        {/if}
        {if isset($linkMenu)}            
            {for $foo=0 to $nRows-1}
                {for $boo=0 to $nCols-1}
                    
                    {if $foo*$nCols+$boo < count($linkMenu) }
                        {if isset($buttonLayout) && $buttonLayout == "SUBMIT"}
                            <form action="{$linkMenu[{$foo*$nCols+$boo}]["href"]}" 
                                  method="post"
                            >
                                <input class="page-button-fill" style="line-height:30px;" type="submit" value="{$linkMenu[{$foo*$nCols+$boo}]["name"]}" />
                            </form>
                        {else}
                            <a class="page-button-fill"
                               href="{$linkMenu[{$foo*$nCols+$boo}]["href"]}"
                            >
                                {$linkMenu[{$foo*$nCols+$boo}]["name"]}
                            </a>
                        {/if}
                    {/if}
                {/for}
            {/for}
        {else}
            <div>
                No menu data found
            </div>
        {/if}
    </div>
</div>