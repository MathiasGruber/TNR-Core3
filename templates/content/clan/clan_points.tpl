<div align="center">
    <form name="form1" method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td colspan="2" class="subHeader">Spend Clan Funds</td>
            </tr>
            <tr>
                <td colspan="2" class="tableColumns">
                    Current funds: {$totalPoints}
                </td>
            </tr>
            <tr style="font-weight:bold;">
                <td width="60%" style="padding-left:5px;text-align:left;"> Upgrade Name </td>
                <td width="20%"> Upgrade Price</td>
            </tr>
            {foreach $updates as $update => $value}
                <tr>
                    <td style="padding-left:5px;text-align:left;">{$value['name']}
                    {if isset($value['timer'])}
                        <br><i>Running out in: {$value['timer']}</i>
                    {/if}
                    </td>
                    <td>
                        {if {$value['price']} gt 0}
                            {$value['price']} <input type="radio" name="radio" value="{$update}">
                        {else}
                            N/A <input type="radio" name="radio" value="{$update}" disabled>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            <tr>
                <td colspan="2">
                    <input type="submit" name="Submit" id="button" value="Submit">
                </td>
            </tr>
        </table>
    </form>
    <a href="?id={$smarty.get.id}">Return</a>
</div>
