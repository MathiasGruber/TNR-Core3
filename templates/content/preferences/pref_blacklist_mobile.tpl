<tr>
    <td>
        <headerText>Settings</headerText>
        <text>PM setting:</text>
        <select name="setting">
            {if $settings[0]['pm_setting'] == 'white_only'}
                <option action="selected" value="white_only">Receive PM's only from people on my whitelist</option>
                <option value="block_black">Block PM\'s from people on my blacklist only</option>
                <option value="off">Ignore the black / whitelist completely</option>
            {elseif $settings[0]['pm_setting'] == 'block_black'}
                <option value="white_only">Receive PM's only from people on my whitelist</option>
                <option action="selected" value="block_black">Block PM's from people on my blacklist only</option>
                <option value="off">Ignore the black / whitelist completely</option>
            {elseif $settings[0]['pm_setting'] == 'off'}
                <option value="white_only">Receive PM's only from people on my whitelist</option>
                <option value="block_black">Block PM's from people on my blacklist only</option>
                <option action="selected" value="off">Ignore the black / whitelist completely</option>
            {/if}
        </select>
        <text>Call For Help (CFH) setting:</text>
        <select name="CFHsetting">
            {if $settings[0]['CFHsetting'] == 'CFHblock_black'}
                <option  action="selected" value="CFHblock_black">Don't allow help from people on my blacklist</option>
                <option value="CFHoff">Allow battle help from everyone</option>
                <option value="CFHwhite_only">Allow battle help from people on my whitelist</option>
            {elseif $settings[0]['CFHsetting'] == 'CFHwhite_only'}
                <option value="CFHblock_black">Don't allow help from people on my blacklist</option>
                <option value="CFHoff">Allow battle help from everyone</option>
                <option action="selected" value="CFHwhite_only">Allow battle help from people on my whitelist</option>
            {elseif (($settings[0]['CFHsetting'] == 'CFHoff') || ($settings[0]['CFHsetting'] == 'off'))}
                <option value="CFHblock_black">Don't allow help from people on my blacklist</option>
                <option action="selected" value="CFHoff">Allow battle help from everyone</option>
                <option value="CFHwhite_only">Allow battle help from people on my whitelist</option>';
            {/if}
        </select>
        <submit type="submit" name="Submit" value="Save setting"></submit>
        
        <headerText>Black / Whitelist</headerText>
        <select name="blacklist[]">
            <option action="selected" value="none">(none)</option>
            {if $blacklisted != '0 rows'}
                {for $i = 0 to ($blacklisted|@count)-1}
                    {if $blacklisted[$i] != ''}
                        <option value="{$blacklisted[$i]['id']}">{$blacklisted[$i]['username']}</option>
                    {/if}
                {/for}
            {/if}
        </select>       
        <select name="whitelist[]">
            <option action="selected" value="none">(none)</option>
            {if $whitelisted != '0 rows'}
                {for $i = 0 to ($whitelisted|@count)-1}
                    {if $whitelisted[$i] != ''}
                        <option value="{$whitelisted[$i]['id']}">{$whitelisted[$i]['username']}</option>
                    {/if}
                {/for}
            {/if}
        </select>
        <submit type="submit" name="Submit" value="Remove selected"></submit>
        
        <headerText>Add User</headerText>
        <tr color="dim">
          <td>
            <input type="text" name="username" value="type username..."></input>
          </td>
        </tr>
        <tr>
            <td><submit type="submit" name="Submit" value="Add user" extraKeyValue="listtype:black">Blacklist</submit></td>
            <td><submit type="submit" name="Submit" value="Add user" extraKeyValue="listtype:white">Whitelist</submit></td>
        </tr>
        
        <a href="?id={$smarty.get.id}">Return</a>
    </td>
</tr>


    
