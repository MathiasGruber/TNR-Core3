{if $user[0]['village'] != ''}
    {$bank = "Here at the bank we keep your money safe from outlaws"}
    {$bankname = 'Banked'}
    {$bankname2 = 'Bank'}
{else}
    {$bank = "Hiding your money underground will secure them from any theft.<br><br>Money from any previous bank account has already been dug down"}
    {$bankname = 'Underground'}
    {$bankname2 = 'Underground'}
{/if}

<tr>
    <td>
        <headerText>{$bankname2}</headerText>
        <text>{$bank}</text>
    </td>
</tr>

<tr>
    <td>
        <text><b>{$bankname}</b>: {$user[0]['bank']}<br><b>In pocket</b>: {$user[0]['money']}</text>
    </td>
</tr>

<tr color="dim">
    <td>
        <input type="text" name="amount">Enter amount ...</input>
    </td>
</tr>

<tr>
    <td>
        <submit type="Submit" name="Submit" value="Submit" extraKeyValue="action:deposit">Deposit</submit>
    </td>
    <td>
        <submit type="Submit" name="Submit" value="Submit" extraKeyValue="action:withdraw">Withdraw</submit>
    </td>
</tr>

{if $user[0]['rank_id'] >= 2}
    {include file='./bank_send_screen_mobile.tpl'}
{/if}