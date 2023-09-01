<doStretch></doStretch>
<tr>
    <td>
        <headerText>Spend Village Funds</headerText>
        <text>Current funds: {$totalPoints}. Prices are based on {$totalTerritories} owned territories.</text>
    </td>
</tr>

<tr color="darkgrey">
    <td><text color="white">Upgrade (level)</text></td>
    <td><text color="white">Upgrade (price)</text></td>
    <td><text color="white">Downgrade (price)</text></td>
</tr>

{foreach $updates as $update => $value}
    <tr>
        <td><text>{$value['name']} {if $value['lvl']>0}({$value['lvl']}){/if}</text></td>
        <td>
            {if {$value['price']} gt 0}
                <submit type="submit" name="Submit" extraKeyValue="radio:{$update}_up" value="Submit" updatePrefWidth="true">Purchase ({$value['price']})</submit>
            {else}
                <submit type="submit" name="Submit" value="Submit" updatePrefWidth="true" disabled="true">Unavailable</submit>
            {/if}
        </td>
        <td>
            {if {$value['lvl']} gt 0 && {$value['down']} gt 0}
                <submit type="submit" name="Submit" extraKeyValue="radio:{$update}_down" value="Submit" updatePrefWidth="true">Purchase ({$value['price']})</submit>
            {else}
                <submit type="submit" name="Submit" value="Submit" updatePrefWidth="true" disabled="true">Unavailable</submit>
            {/if}
        </td>
    </tr>
{/foreach}
            
<tr>
    <td>
        <a href="?id={$smarty.get.id}&act={$smarty.get.act}">Return</a>
    </td>
</tr>

    

