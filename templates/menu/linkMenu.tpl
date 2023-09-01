<div align="center">
    <table width="95%" class="table">
        <tr>
          <td colspan="{$nCols}" class="subHeader">{$subHeader}</td>
        </tr>
        {if isset($subTitle) || isset($subTitle_info)}
            <tr>
              <td colspan="{$nCols}" align="center" style="padding:5px;">{if isset($subTitle)}{$subTitle}{else}{$subTitle_info}{/if}</td>
            </tr>
        {/if}
        {if isset($linkMenu)}            
            {for $foo=0 to $nRows-1}
                <tr>
                    {for $boo=0 to $nCols-1}
                        <td>
                            {if $foo*$nCols+$boo < count($linkMenu) }
                                {if isset($buttonLayout) && $buttonLayout == "SUBMIT"}
                                    <form action="{$linkMenu[{$foo*$nCols+$boo}]["href"]}" method="post">
                                        <input class="input_submit_btn" style="line-height:30px;" type="submit" value="{$linkMenu[{$foo*$nCols+$boo}]["name"]}" />
                                    </form>
                                {else}
                                    <a href="{$linkMenu[{$foo*$nCols+$boo}]["href"]}">{$linkMenu[{$foo*$nCols+$boo}]["name"]}</a>
                                {/if}
                            {/if}
                        </td>
                    {/for}
                </tr>
            {/for}
        {else}
            <tr>
                <td colspan="{$nCols}" >
                    No menu data found
                </td>
            </tr>
        {/if}
        <tr><td colspan="{$nCols}" style="height:10px;"></td></tr>
    </table>
    {if isset($returnLink)}
        {if $returnLink === true}
            <a href="?id={$smarty.get.id}">Return</a>
        {else}
            <a href="{$returnLink}">Return</a>
        {/if}
    {/if} 
</div>