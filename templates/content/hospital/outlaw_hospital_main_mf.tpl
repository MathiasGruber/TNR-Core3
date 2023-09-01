<div class="page-box">
    <div class="page-title">
        OutLaw Hospital
    </div>
    <div class="page-content">
        <div>
            You find yourself in a small tent, unable to move. 
            {if $random_heal}
                Luckily a doctor is available and quickly comes to your aid. Within minutes, you are feeling completely healed.
            {else}
                Some stranger is willing to help you out... for a price.
                <div class="page-sub-title">
                    There seems to only be two options.
                </div>
            {/if}
        </div>
        <div class="page-grid page-column-fr-2">
            <div>
                {if !$random_heal}
                    {if $user[0]['cur_health'] < $user[0]['max_health']}
                        It will cost you <b>{$cost}</b> ryo to <br>pay the doctor and make them fully heal your body.<br>
                        {if $user[0]['money'] >= $cost}
                            <a href="?id={$smarty.get.id}&act=bribe">Pay</a>
                        {else}
                            &nbsp;
                        {/if}
                    {else}
                        Your body is already fully healed. <br>
                        The doctors say you are free to sign out.
                    {/if}
                {/if}
            </div>
            <div>
                {if $serverTime >= $user[0]['hospital_timer'] || $user[0]['cur_health'] > 0}
                    You can now sign out of the hospital<br>
                    <a href="?id={$smarty.get.id}&act=release">Sign out</a>
                {else}
                    <b>Wait for your body to regenerate</b><br>{$waiting_time}<br>
                {/if}
            </div>
        </div>
    </div>
</div>

