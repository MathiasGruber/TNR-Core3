<div align="center">
    <form name="form1" method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td colspan="4" class="subHeader">Spend village funds</td>
            </tr>
            <tr>
                <td colspan="4" class="tableColumns">
                    Current funds: {$totalPoints}. Prices are based on {$totalTerritories} owned territories.
                </td>
            </tr>
          
            <tr style="font-weight:bold;">
                <td width="50%" style="padding-left:5px;text-align:left;"> Upgrade Name</td>
                <td width="7%"> Level</td>
                <td width="20%"> Upgrade Price</td>
                <td width="23%"> Downgrade Refund</td>
            </tr>
            {foreach $updates as $update => $value}
                <tr>
                    <td style="padding-left:5px;text-align:left;">{$value['name']}</td>
                    <td>{if $value['lvl']>0}  {$value['lvl']}  {else} 0 {/if}</td>
                    <td>
                        {if {$value['price']} gt 0}
                            {$value['price']} <input type="radio" name="radio" value="{$update}_up">
                        {else}
                            N/A <input type="radio" name="radio" value="{$update}_up" disabled>
                        {/if}
                    </td>
                    <td>
                        {if {$value['lvl']} gt 0 && {$value['down']} gt 0}
                            {$value['down']} <input type="radio" name="radio" value="{$update}_down">
                        {else}
                            N/A <input type="radio" name="radio" value="{$update}_down" disabled>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            <tr>
                <td colspan="4">
                    <input type="submit" name="Submit" id="button" value="Submit">
                </td>
            </tr>
        </table>
    </form>
    <a href="?id={$smarty.get.id}&act={$smarty.get.act}">Return</a>
</div>
