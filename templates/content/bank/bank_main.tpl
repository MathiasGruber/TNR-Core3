{if $user[0]['village'] != 'Syndicate'}
    {$bank = "Here at the "|cat:$bank_name|cat:" we keep your money safe from outlaws"}
{else}
    {$bank = "Hiding your money underground will secure them from any theft.<br><br>Money from any previous bank account has already been dug down"}
{/if}
<div align="center">
    <table class="table" width="95%">
        <tr>
            <td colspan="3" class="subHeader">{$bank_name}</td>
        </tr>
        <tr>
            <td width="33%" rowspan="2">
                {$bank}
            </td>
            <td width="33%"><b>Balance</b>: <br>{$user[0]['bank']}</td>
            <td width="33%" rowspan="2">
                <form method="POST" action="" enctype="application/x-www-form-urlencoded">
                    <b>Amount:</b> <br><input type="text" name="amount"/><br><br>
                    <input type="radio" name="action" value="deposit" /> Deposit 
                    <input type="radio" name="action" value="withdraw" /> Withdraw<br><br>
                    <input class="input_submit_btn" type="submit" name="Submit" value="Execute" />
                </form>
            </td>
        </tr>
        <tr>
            <td width="25%"><b>In pocket</b>: <br>{$user[0]['money']}</td>
        </tr>
    </table><br>
</div>
{if $user[0]['rank_id'] >= 2}
    {include file='./bank_send_screen.tpl'}
{/if}