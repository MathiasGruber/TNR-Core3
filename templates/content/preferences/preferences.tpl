<div align="center">
    <form method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td class="subHeader">User Preferences</td>
            </tr>
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Receive PM's?</b><br>
                    Do you want to receive PMs from other users?<br>
                    Yes:
                    {if $user[0]['pm_block'] == '1'}
                        <input name="pm_block" type="radio" value="0" />
                    {else}
                        <input name="pm_block" type="radio" checked="checked" value="0" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                    {if $user[0]['pm_block'] == '1'}
                        <input name="pm_block" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="pm_block" type="radio" value="1" />
                    {/if}
                </td>                                      
            </tr>
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Sending PM's by Email</b><br>
                    Do you want to receive PMs by Email in addition to Inbox?<br>
                    Yes:
                    {if $user[0]['pm_by_email'] == '0'}
                        <input name="pm_by_email" type="radio" value="1" />
                    {else}
                        <input name="pm_by_email" type="radio" checked="checked" value="1" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                    {if $user[0]['pm_by_email'] == '0'}
                        <input name="pm_by_email" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="pm_by_email" type="radio" value="0" />
                    {/if}
                </td>
            </tr>
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Lock account?</b><br>
                    Enable this to have your account locked upon 3 unsuccessful logins. Recommended for security reasons!<br>
                    Yes:
                    {if $user[0]['lock'] == '1'}
                        <input name="account_lock" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="account_lock" type="radio" value="1" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                    {if $user[0]['lock'] == '0'}
                        <input name="account_lock" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="account_lock" type="radio" value="0" />
                    {/if}
                </td>                                      
            </tr>
            {if $user[0]['rank_id'] == '2'}
                <tr>
                    <td style="padding:10px;text-align:left;">
                        <b>Allow Sensei?</b><br>
                        Allow people to be your sensei. Disabling this will remove current sensei!<br>
                        Yes:
                        {if $user[0]['sensei'] != '_disabled'}
                            <input name="sensei_block" type="radio" checked="checked" value="yes" />
                        {else}
                            <input name="sensei_block" type="radio" value="yes" />
                        {/if}
                        &nbsp;&nbsp;&nbsp;No:
                        {if $user[0]['sensei'] != '_disabled'}
                            <input name="sensei_block" type="radio" value="no" />
                        {else}
                            <input name="sensei_block" type="radio" checked="checked" value="no" />
                        {/if}
                    </td>                                      
                </tr>
            {/if}
            {if $user[0]['rank_id'] >= '3'}
                <tr>
                    <td style="padding:10px;text-align:left;">
                        <b>Allow ANBU?</b><br>
                        Allow people to add you to their ANBU squad. Disabling this will kick you out of your current squad.<br>
                        Yes:
                        {if $user[0]['anbu'] != '_disabled'}
                            <input name="anbu_block" type="radio" checked="checked" value="yes" />
                        {else}
                            <input name="anbu_block" type="radio" value="yes" />
                        {/if}
                        &nbsp;&nbsp;&nbsp;No:
                        {if $user[0]['anbu'] != '_disabled'}
                            <input name="anbu_block" type="radio" value="no" />
                        {else}
                            <input name="anbu_block" type="radio" checked="checked" value="no" />
                        {/if}
                    </td>                                      
                </tr>
            {/if}
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Allow Clan?</b><br>
                    Allow people to add you to their clan.<br>
                    Yes:
                    {if $user[0]['clan'] != '_disabled'}
                        <input name="clan_block" type="radio" checked="checked" value="yes" />
                    {else}
                        <input name="clan_block" type="radio" value="yes" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                    {if $user[0]['clan'] != '_disabled'}
                        <input name="clan_block" type="radio" value="no" />
                    {else}
                        <input name="clan_block" type="radio" checked="checked" value="no" />
                    {/if}
                </td>                                      
            </tr>
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Allow Healing?</b><br>
                    Allow medical ninjas to heal your character.<br>
                    Yes:
                    {if $user[0]['enable_heal'] == '1'}
                        <input name="heal_block" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="heal_block" type="radio" value="1" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No: 
                    {if $user[0]['enable_heal'] == '0'}
                        <input name="heal_block" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="heal_block" type="radio" value="0" />                                    
                    {/if}
                </td>                                      
            </tr>
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Allow Marriage?</b><br>
                    Allow people to propose to your character.<br>
                    Yes:
                    {if $user[0]['enable_marriage'] == '1'}
                        <input name="marriage_block" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="marriage_block" type="radio" value="1" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No: 
                    {if $user[0]['enable_marriage'] == '0'}
                        <input name="marriage_block" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="marriage_block" type="radio" value="0" />
                    {/if}
                </td>                                      
            </tr>
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Level Up and Rank Up button</b><br>
                    Show Level Up and Rank Up button in profile.<br>
                    Yes:
                    {if $user[0]['show_level_up_button'] == '1'}
                        <input name="show_level_up_button" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="show_level_up_button" type="radio" value="1" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                    {if $user[0]['show_level_up_button'] == '0'}
                        <input name="show_level_up_button" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="show_level_up_button" type="radio" value="0" />
                    {/if}
                </td>
            </tr>
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Auto Update Chat</b><br>
                    Yes:
                    {if $user[0]['chat_autoupdate'] == '1'}
                        <input name="chat_autoupdate" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="chat_autoupdate" type="radio" value="1" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                    {if $user[0]['chat_autoupdate'] == '0'}
                        <input name="chat_autoupdate" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="chat_autoupdate" type="radio" value="0" />
                    {/if}
                </td>
            </tr>
          <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Silence Spar Challenges</b><br>
                    Yes:
                    {if $user[0]['silence_spar'] == 'yes'}
                        <input name="silence_spar" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="silence_spar" type="radio" value="yes" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                    {if $user[0]['silence_spar'] == 'no'}
                        <input name="silence_spar" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="silence_spar" type="radio" value="no" />
                    {/if}
                </td>
            </tr>
          <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Auto Collapse Home Inventory</b><br>
                    Yes:
                    {if $user[0]['collapse_home'] == 'yes'}
                        <input name="collapse_home" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="collapse_home" type="radio" value="yes" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                  {if $user[0]['collapse_home'] == 'no'}
                  <input name="collapse_home" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="collapse_home" type="radio" value="no" />
                    {/if}
                </td>
            </tr>

            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Set Questing Mode</b><br>
                    Alert:
                    {if $user[0]['QuestingMode'] == 'alert'}
                        <input name="questing_mode" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="questing_mode" type="radio" value="alert" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;Quiet:
                  {if $user[0]['QuestingMode'] == 'quiet'}
                  <input name="questing_mode" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="questing_mode" type="radio" value="quiet" />
                    {/if}
                </td>
            </tr>

            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Activate Quest Widget</b><br>
                    Yes:
                    {if $user[0]['quest_widget'] == 'yes'}
                        <input name="quest_widget" type="radio" checked="checked" value="1" />
                    {else}
                        <input name="quest_widget" type="radio" value="yes" />
                    {/if}
                    &nbsp;&nbsp;&nbsp;No:
                  {if $user[0]['quest_widget'] == 'no'}
                  <input name="quest_widget" type="radio" checked="checked" value="0" />
                    {else}
                        <input name="quest_widget" type="radio" value="no" /> 
                    {/if}
                </td>
            </tr>

            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Set Turn Log Length</b><br>
                    1: <input name="turn_log_length" type="radio" value="1" {if $user[0]['turn_log_length'] == 1} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    2: <input name="turn_log_length" type="radio" value="2" {if $user[0]['turn_log_length'] == 2} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    3: <input name="turn_log_length" type="radio" value="3" {if $user[0]['turn_log_length'] == 3} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    4: <input name="turn_log_length" type="radio" value="4" {if $user[0]['turn_log_length'] == 4} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    5: <input name="turn_log_length" type="radio" value="5" {if $user[0]['turn_log_length'] == 5} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    10: <input name="turn_log_length" type="radio" value="10" {if $user[0]['turn_log_length'] == 10} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    25: <input name="turn_log_length" type="radio" value="25" {if $user[0]['turn_log_length'] == 25} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    50: <input name="turn_log_length" type="radio" value="50" {if $user[0]['turn_log_length'] == 50} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    100: <input name="turn_log_length" type="radio" value="100" {if $user[0]['turn_log_length'] == 100} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    1000: <input name="turn_log_length" type="radio" value="1000" {if $user[0]['turn_log_length'] == 1000} checked="checked" {/if} />
                </td>
            </tr>
                
            <tr>
                <td style="padding:10px;text-align:left;">
                    <b>Set Travel Default Redirect</b><br>
                    Combat: <input name="travel_default_redirect" type="radio" value="Combat" {if $user[0]['travel_default_redirect'] == 'Combat'} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    Scout: <input name="travel_default_redirect" type="radio" value="Scout" {if $user[0]['travel_default_redirect'] == 'Scout'} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    Rob: <input name="travel_default_redirect" type="radio" value="Rob" {if $user[0]['travel_default_redirect'] == 'Rob'} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    Profile: <input name="travel_default_redirect" type="radio" value="Profile" {if $user[0]['travel_default_redirect'] == 'Profile'} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                    QuestJournal: <input name="travel_default_redirect" type="radio" value="QuestJournal" {if $user[0]['travel_default_redirect'] == 'QuestJournal'} checked="checked" {/if} />&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
            </tr>

            <tr>
                <td style="padding:5px;"><input type="submit" class="input_submit_btn" name="Submit" value="Submit" /></td>
            </tr>
        </table>
    </form>
    <a href="?id={$smarty.get.id}" class="returnLink">Return</a>
</div>