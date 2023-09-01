<tr>
    <td>
        <headerText screenWidth="true">{$subHeader}</headerText>
    </td>
</tr>


{if isset($subTitle)}
    <tr>
        <td><text color="dark">{$subTitle}</text></td>
    </tr>
{/if}

{if isset($linkMenu)}            
    {for $foo=0 to $nRows-1}
        <tr>
            {for $boo=0 to $nCols-1}
                <td>
                    {if $foo*$nCols+$boo < count($linkMenu) }
                        <submit color="blue" href="{$linkMenu[{$foo*$nCols+$boo}]["href"]}" value="{$linkMenu[{$foo*$nCols+$boo}]["name"]}" name="LinkSubmit" type="submit">{$linkMenu[{$foo*$nCols+$boo}]["name"]}</submit>
                    {/if}
                </td>
            {/for}
        </tr>
    {/for}
{else}
    <tr>
        <td>
            <text>No menu data found</text>
        </td>
    </tr>
{/if}

{if !isset($hideReturnLink)}
    {if isset($returnLink)}
        <tr><td>
        {if $returnLink === true}
            <a href="?id={$smarty.get.id}">Return</a>
        {else}
            <a href="{$returnLink}">Return</a>
        {/if}
        </td></tr>
    {/if}
{/if}