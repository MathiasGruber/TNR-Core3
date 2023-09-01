<tr>
    <td>
        <headerText>User Preferences</headerText>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Receive PM's?</b><br>Do you want to receive PMs from other users?</text>
    </td>    
    <td childExpand="false">        
        <switch name="pm_block" value="{if $user[0]['pm_block'] == '1'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Sending PM's by Email</b><br>Do you want to receive PMs by Email in addition to Inbox?</text>
    </td>    
    <td childExpand="false">        
        <switch name="pm_by_email" value="{if $user[0]['pm_by_email'] == '1'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Lock account?</b><br>Enable this to have your account locked upon 3 unsuccessful logins. Recommended for security reasons!</text>
    </td>    
    <td childExpand="false">        
        <switch name="account_lock" value="{if $user[0]['lock'] == '1'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>


{if $user[0]['rank_id'] == '2'}    
    <tr childExpand="false">
        <td>
            <text><b>Allow Sensei?</b><br>Allow people to be your sensei. Disabling this will remove current sensei!</text>
        </td>    
        <td childExpand="false">        
            <switch name="sensei_block" value="{if $user[0]['sensei'] != '_disabled'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
        </td>
    </tr>
{/if}

{if $user[0]['rank_id'] >= '3'}
    <tr childExpand="false">
        <td>
            <text><b>Allow ANBU?</b><br>Allow people to add you to their ANBU squad. Disabling this will kick you out of your current squad.</text>
        </td>    
        <td childExpand="false">        
            <switch name="anbu_block" value="{if $user[0]['anbu'] != '_disabled'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
        </td>
    </tr>
{/if}

<tr childExpand="false">
    <td>
        <text><b>Allow Clan?</b><br>Allow people to add you to their clan.</text>
    </td>    
    <td childExpand="false">        
        <switch name="clan_block" value="{if $user[0]['clan'] != '_disabled'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Allow Healing?</b><br>Allow medical ninjas to heal your character.</text>
    </td>    
    <td childExpand="false">        
        <switch name="heal_block" value="{if $user[0]['enable_heal'] == '1'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Allow Marriage?</b><br>Allow people to propose to your character.</text>
    </td>    
    <td childExpand="false">        
        <switch name="marriage_block" value="{if $user[0]['enable_marriage'] == '1'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Level Up and Rank Up button</b><br>Show Level Up and Rank Up button in profile.</text>
    </td>    
    <td childExpand="false">        
        <switch name="show_level_up_button" value="{if $user[0]['show_level_up_button'] == '1'}1{else}0{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Silence Spar Challenges</b><br>Silence Spar Challenges.</text>
    </td>    
    <td childExpand="false">        
        <switch name="silence_spar" value="{if $user[0]['silence_spar'] == '1'}yes{else}no{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Auto Collapse Home Inventory</b><br>Auto Collapse Home Inventory.</text>
    </td>    
    <td childExpand="false">        
        <switch name="collapse_home" value="{if $user[0]['collapse_home'] == '1'}yes{else}no{/if}" oneValue="Yes" zeroValue="No"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Set Questing Mode</b><br>Set Questing Mode</text>
    </td>    
    <td childExpand="false">        
        <switch name="questing_mode" value="{if $user[0]['QuestingMode'] == 'alert' }alert{else}quiet{/if}" oneValue="alert" zeroValue="quiet"></switch>
    </td>
</tr>

<tr childExpand="false">
    <td>
        <text><b>Activate Quest Widget</b><br>Set Questing Mode</text>
    </td>    
    <td childExpand="false">        
        <switch name="quest_widget" value="{if $user[0]['quest_widget'] == 'yes' }yes{else}no{/if}" oneValue="yes" zeroValue="no"></switch>
    </td>
</tr>

<hidden name="chat_autoupdate" value="{$user[0]['chat_autoupdate']}"></hidden>
<hidden name="turn_log_length" value="{$user[0]['turn_log_length']}"></hidden>
<hidden name="travel_default_redirect" value="{$user[0]['travel_default_redirect']}"></hidden>

<tr childExpand="false">
    <td>
        <submit type="submit" name="Submit" value="Submit" />
        <a href="?id={$smarty.get.id}" class="returnLink">Return</a>
    </td>
</tr>
