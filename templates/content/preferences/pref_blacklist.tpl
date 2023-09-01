<div align="center">
    <form method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td colspan="2" class="subHeader">Settings</td>
            </tr>
            <tr>
                <td width="32%" >PM setting:</td>
                <td width="68%" style="padding-top:5px;"><select name="setting" id="select">';
                        <option {if $settings[0]['pm_setting'] == 'white_only'}selected{/if} 
                          value="white_only">receive PM's only from people on my whitelist</option>
                        <option {if $settings[0]['pm_setting'] == 'block_black'}selected{/if} 
                          value="block_black">Block PM's and tavern messages from people on my blacklist only</option>
                        <option {if $settings[0]['pm_setting'] == 'block_black_tavern'}selected{/if} 
                          value="block_black_tavern">Block tavern messages from people on my blacklist only</option>
                        <option {if $settings[0]['pm_setting'] == 'block_black_pm'}selected{/if} 
                          value="block_black_pm">Block PM's messages from people on my blacklist only</option>
                        <option {if $settings[0]['pm_setting'] == 'off'}selected{/if} 
                          value="off">Ignore the black / whitelist completely</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="32%" >Call For Help (CFH) setting:</td>
                <td width="68%" style="padding-top:5px;">
                    <select name="CFHsetting" id="select">
                        {if $settings[0]['CFHsetting'] == 'CFHblock_black'}
                            <option selected value="CFHblock_black">Don't allow help from people on my blacklist</option>
                            <option value="CFHoff">Allow battle help from everyone</option>
                            <option value="CFHwhite_only">Allow battle help from people on my whitelist</option>
                        {elseif $settings[0]['CFHsetting'] == 'CFHwhite_only'}
                            <option value="CFHblock_black">Don't allow help from people on my blacklist</option>
                            <option value="CFHoff">Allow battle help from everyone</option>
                            <option selected value="CFHwhite_only">Allow battle help from people on my whitelist</option>
                        {elseif (($settings[0]['CFHsetting'] == 'CFHoff') || ($settings[0]['CFHsetting'] == 'off'))}
                            <option value="CFHblock_black">Don't allow help from people on my blacklist</option>
                            <option selected value="CFHoff">Allow battle help from everyone</option>
                            <option value="CFHwhite_only">Allow battle help from people on my whitelist</option>';
                        {/if}
                    </select>
                </td>
            </tr>        
            <tr>
                <td colspan="2" style="padding:3px;"><input class="input_submit_btn" type="submit" name="Submit" value="Save setting"></td>
            </tr>
        </table>
    </form>
    <form method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td colspan="2" class="subHeader">Black / Whitelist</td>
            </tr>
            <tr>
                <td width="50%" style="padding:10px;">
                    <b>Blacklist:</b><br>
                    <select name="blacklist[]" size="5" multiple id="blacklist" style="width:200px;">
                        <option selected value="none"><i>(none)</i></option>
                        {if $blacklisted != '0 rows'}
                            {for $i = 0 to ($blacklisted|@count)-1}
                                {if $blacklisted[$i] != ''}
                                    <option value="{$blacklisted[$i]['id']}">{$blacklisted[$i]['username']}</option>
                                {/if}
                            {/for}
                        {/if}
                    </select>
                </td>
                <td width="50%" style="padding:10px;">
                    <b>Whitelist:</b><br>
                    <select name="whitelist[]" size="5" multiple id="blacklist2" style="width:200px;">
                        <option selected value="none"><i>(none)</i></option>
                        {if $whitelisted != '0 rows'}
                            {for $i = 0 to ($whitelisted|@count)-1}
                                {if $whitelisted[$i] != ''}
                                    <option value="{$whitelisted[$i]['id']}">{$whitelisted[$i]['username']}</option>
                                {/if}
                            {/for}
                        {/if}
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" ><input class="input_submit_btn" type="submit" name="Submit" id="Submit" value="Remove selected"></td>
            </tr>
        </table>
    </form>
    <form method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td class="subHeader" >Add user</td>
            </tr>
            <tr>
                <td>Username: 
                    <input type="text" name="username" id="textfield"></td>
            </tr>
            <tr>
                <td>Add user to the: 
                    <label>
                        <input type="radio" name="listtype" value="black" id="listtype_0"> Blacklist
                    </label>
                    <label>
                        <input type="radio" name="listtype" value="white" id="listtype_1"> Whitelist
                    </label>
                </td>
            </tr>
            <tr>
                <td><input class="input_submit_btn" type="submit" name="Submit" value="Add user"></td>
            </tr>
        </table>
    </form>
    <a href="?id={$smarty.get.id}" class="returnLink">Return</a>
</div>