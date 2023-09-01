<div><br>
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">Outlaw Hospital</td>
        </tr>
        <tr>
            <td colspan="2">You find yourself in a small tent, unable to move. 
                {if $random_heal}
                    Luckily a doctor is available and quickly comes to your aid. Within minutes, you are feeling completely healed.
                {else}
                    Some strangers willing to help you out, for a price.
                    There seem to be only two options:
                {/if}
            </td>
        </tr>
        <tr>
            <td width="50%">
                {if !$random_heal}
                    {if $user[0]['cur_health'] < $user[0]['max_health']}
                        It will cost you {$cost} ryo to <br>make the doctor fully heal your body<br>
                        {if $user[0]['money'] >= $cost}
                            <a href="?id={$smarty.get.id}&act=bribe">Pay</a>
                        {else}
                            &nbsp;
                        {/if}
                    {else}
                        Your body is already fully healed. <br>
                        No doctors want to heal you anymore.
                    {/if}
                {/if}
            </td>
            <td width="50%" style="padding-left:5px;padding-right:5px;">
                {if $serverTime >= $user[0]['hospital_timer'] || $user[0]['cur_health'] > 0}
                    You can now sign out of the hospital<br>
                    <a href="?id={$smarty.get.id}&act=release">Sign out</a>
                {else}
                    <b>Wait for your body to regenerate</b><br>{$waiting_time}<br>
                {/if}
            </td>
        </tr>
    </table>
</div>
