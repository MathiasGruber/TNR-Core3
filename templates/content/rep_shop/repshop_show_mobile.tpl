<tr>
    <td>
        <headerText>Reputation Points</headerText>
        <text>These points can be used to buy e.g. bloodline items in the black market.</text>
        <repshop custom="{$customField}"></repshop>
    </td>
</tr>


{if isset($showHistory)}
    <tr><td><a href="?id={$smarty.get.id}&act=records">Transaction History</a></td></tr>
{/if}
