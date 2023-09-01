{if $user[0]['village'] != ''}
    {$bank = "Here at the "|cat:$bank_name|cat:" we keep your money safe from outlaws"}
{else}
    {$bank = "Hiding your money underground will secure them from any theft.<br><br>Money from any previous bank account has already been dug down"}
{/if}

<div class="page-box">
    <div class="page-title">
        {$bank_name} <span class="toggle-button-info closed" data-target="#bank-info"/>
    </div>
    <div class="page-content">
        <div class="toggle-target closed" id="bank-info">
            {$bank}
            <br/>
            <br/>
        </div>

        <div class="page-sub-title-top">
            Overview
        </div>

        <div class="page-grid page-column-5">
            <div class="text-left">
                <b>Balance</b>:
            </div>
            <div>
                {$user[0]['bank']}
            </div>

            <div></div>

            <div class="text-left">
                <b>In pocket</b>:
            </div>
            <div>
                {$user[0]['money']}
            </div>
        </div>


        <form method="POST" action="" enctype="application/x-www-form-urlencoded" class="page-grid">
            <div class="page-sub-title">
                <input type="radio" name="action" value="deposit" />Deposit / Withdraw<input type="radio" name="action" value="withdraw" />
            </div>

            <div class="page-grid page-column-2">
                <div>
                    <input class="page-text-input-fill" type="text" name="amount" placeholder="amount"/>
                </div>

                <div>
                    <input class="page-button-fill" type="submit" name="Submit" value="Move" />
                </div>
            </div>
        </form>

        {if $user[0]['rank_id'] >= 2}
            {include file='./bank_send_screen_mf.tpl'}
        {/if}
    </div>
</div>
