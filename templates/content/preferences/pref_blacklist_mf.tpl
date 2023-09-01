<div class="page-box">
    <div class="page-title">
        Blacklist Settings
    </div>
    <div class="page-content lazy">
        <form method="post" action="" class="page-grid page-column-2">
            <div>
                PM settings: 
            </div>
            <select class="lazy page-drop-down-fill" name="setting" id="select">';
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


            <div>
                Call For Help (CFH) setting:
            </div>
            <select class="lazy page-drop-down-fill" name="CFHsetting" id="select">
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

            <input class="lazy page-button-fill span-2" type="submit" name="Submit" value="Save setting">
        </form>
        <form method="post" action="" class="page-grid page-column-2">
            <div class="span-2 page-sub-title">
                Black / Whitelist
            </div>
            <div class="page-grid">
                <div>Blacklist:</div>
                <select name="blacklist[]" size="5" multiple id="blacklist" class="lazy page-drop-down-fill">
                    <option selected value="none"><i>(none)</i></option>
                    {if $blacklisted != '0 rows'}
                        {for $i = 0 to ($blacklisted|@count)-1}
                            {if $blacklisted[$i] != ''}
                                <option value="{$blacklisted[$i]['id']}">{$blacklisted[$i]['username']}</option>
                            {/if}
                        {/for}
                    {/if}
                </select>
            </div>
            <div class="page-grid">
                <div>Whitelist:</div>
                <select name="whitelist[]" size="5" multiple id="blacklist2" class="lazy page-drop-down-fill">
                    <option selected value="none"><i>(none)</i></option>
                    {if $whitelisted != '0 rows'}
                        {for $i = 0 to ($whitelisted|@count)-1}
                            {if $whitelisted[$i] != ''}
                                <option value="{$whitelisted[$i]['id']}">{$whitelisted[$i]['username']}</option>
                            {/if}
                        {/for}
                    {/if}
                </select>
            </div>
            <input class="lazy page-button-fill span-2" type="submit" name="Submit" id="Submit" value="Remove selected">
        </form>
        <form method="post" action="" class="page-grid">
            <div class="page-sub-title">Add user</div>
            <div>Username: 
                <input type="text" name="username" id="textfield"></div>
            <div>Add user to the: 
                <label>
                    <input type="radio" name="listtype" value="black" id="listtype_0"> Blacklist
                </label>
                <label>
                    <input type="radio" name="listtype" value="white" id="listtype_1"> Whitelist
                </label>
            </div>
            <input class="page-button-fill" type="submit" name="Submit" value="Add user">
        </form>
    </div>
</div>