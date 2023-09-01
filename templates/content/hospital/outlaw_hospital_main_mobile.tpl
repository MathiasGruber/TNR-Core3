<tr>
<td>
    <headerText>Outlaw Hospital</headerText>
</td>
</tr>
<tr>
    <td>
<text>You find yourself in a small tent, unable to move. 
{if $random_heal}
Luckily a doctor is available and quickly comes to your aid. Within minutes, you are feeling completely healed.
{else}
Some strangers willing to help you out, for a price. There seem to be only two options:
{/if}</text>
    </td>
</tr>
<tr>
    <td>
{if !$random_heal}
{if $user[0]['cur_health'] < $user[0]['max_health']}
<text>It will cost you {$cost} ryo to make the doctor fully heal your body</text>
{if $user[0]['money'] >= $cost}
    <text><br></text>
    <a href="?id={$smarty.get.id}&amp;act=bribe">Pay</a>
{/if}
{else}
<text>Your body is already fully healed. No doctors want to heal you anymore.</text>
{/if}
{/if}
        
    </td>
    <td>
{if $serverTime >= $user[0]['hospital_timer'] || $user[0]['cur_health'] > 0}
<text>You can now sign out of the hospital</text>
<a href="?id={$smarty.get.id}&amp;act=release">Sign out</a>
{else}
<countdown time="{$timeLeft}" reload="true" prepend="Wait for your body to regenerate: " postpend=" seconds"></countdown>
{/if}
    </td>
</tr>