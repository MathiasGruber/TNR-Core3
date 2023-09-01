<tr>
    <td>
        <headerText>Spend Clan Funds</headerText>
        <text>Current Funds: {$totalPoints} points</text>
    </td>
</tr>

<tr color="darkgrey">
    <td><text color="white">Upgrade Name</text></td>
    <td><text color="white">Upgrade Price</text></td>
    <td><text color="white">Action</text></td>
</tr>
{foreach $updates as $update => $value}
    <tr color="{cycle values="grey,white"}">
        <td><text>{$value['name']}
        {if isset($value['timer'])}
            <br><i>Running out in: {$value['timer']}</i>
        {/if}</text>
        </td>
        <td>
            {if {$value['price']} gt 0}
                <text>{$value['price']} points</text>
            {else}
                <text>N/A</text>
            {/if}
        </td>
        <td>
            <submit type="submit" name="Submit" extraKeyValue="radio:{$update}" value="Submit" updatePrefWidth="true">Purchase</submit>
        </td>
    </tr>
{/foreach}

<tr><td><a href="?id={$smarty.get.id}">Return</a></td></tr>