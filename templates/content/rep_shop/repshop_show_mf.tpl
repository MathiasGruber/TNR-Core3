{if !$page_addon}<div class="page-box">{/if}
    {if !$page_addon}
        <div class="page-title">
            Buy Reputation Points
        </div>
    {/if}

    {if !$page_addon}<div class="page-content">{/if}

        {if !$page_addon}
            <div class="page-sub-title-top">
                Reputation Points <span class="toggle-button-info closed" data-target="#reputation-points-info"/>
            </div>
        {/if}

        <div class="toggle-target closed" id="reputation-points-info">
            These points can be used to buy e.g. bloodline items in the black market. <br><br/>
        </div>
        <div>
            {if $extraRepsPerc > 0}
                To celebrate we are offering a limited discount on reputation points! <br>
                This means you get 20% extra reputation points on all purchases.<br>
                <ul style="list-style:square;list-style-position: inside;">
                    <li>6 points &#8605; {floor($extraRepsPerc*6)+6} reputation points</li>
                    <li>12 points &#8605; {floor($extraRepsPerc*12)+12} reputation points</li>
                    <li>24 points &#8605; {floor($extraRepsPerc*24)+24} reputation points</li>
                    <li>48 points &#8605; {floor($extraRepsPerc*48)+48} reputation points</li>
                    <li>120 points &#8605; {floor($extraRepsPerc*120)+120} reputation points</li>
                    <li>200 points &#8605; {floor($extraRepsPerc*200)+200} reputation points</li>
                    <li>500 points &#8605; {floor($extraRepsPerc*500)+500} reputation points</li>
                </ul><br>
            {/if}
        </div>


        {if isset($paypalSandbox) && $paypalSandbox == true}
            <!-- For Development Only -->
            <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top" class="table-grid table-column-2 grid-gap">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="QQLHCWP8SBFPL">
                <input type="hidden" name="on0" value="Amount of points">
                
                <div>
                    <div>
                        Amount of points
                    </div>

                    <select name="os0">
                        <option value="1 Reputation Point">1{if $extraRepsPerc > 0}+{floor($extraRepsPerc*1)}{/if} Reputation Point $2.00 USD</option>
                        <option value="6 Reputation Points">6{if $extraRepsPerc > 0}+{floor($extraRepsPerc*6)}{/if} Reputation Points $8.00 USD</option>
                        <option value="12 Reputation Points">12{if $extraRepsPerc > 0}+{floor($extraRepsPerc*12)}{/if} Reputation Points $10.00 USD</option>
                        <option value="24 Reputation Points">24{if $extraRepsPerc > 0}+{floor($extraRepsPerc*24)}{/if} Reputation Points $15.00 USD</option>
                        <option value="48 Reputation Points">48{if $extraRepsPerc > 0}+{floor($extraRepsPerc*48)}{/if} Reputation Points $30.00 USD</option>
                        <option value="120 Reputation Points">120{if $extraRepsPerc > 0}+{floor($extraRepsPerc*120)}{/if} Reputation Points $70.00 USD</option>
                        <option value="200 Reputation Points">200{if $extraRepsPerc > 0}+{floor($extraRepsPerc*200)}{/if} Reputation Points $120.00 USD</option>
                        <option value="500 Reputation Points">500{if $extraRepsPerc > 0}+{floor($extraRepsPerc*500)}{/if} Reputation Points $300.00 USD</option>
                    </select> 
                </div>

                <input type="hidden" name="currency_code" value="USD">
                <input type="hidden" name="custom" value="{$customField}"/>
                
                <div>
                    <input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                </div>
            </form>
        {else}
            <!-- For Production Only -->
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" class="table-grid table-column-2 grid-gap">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="6NULJW73JTQ6G">
                <input type="hidden" name="on0" value="Amount of Reputation Points">
                
                <div>
                    <div>
                        Amount of Reputation Points
                    </div>
                        
                    <select name="os0">
                        <option value="1 Reputation Point">1 Reputation Point $2.00 USD</option>
                        <option value="6 Reputation Points">6{if $extraRepsPerc > 0}+{floor($extraRepsPerc*6)}{/if} Reputation Points $8.00 USD</option>
                        <option value="12 Reputation Points">12{if $extraRepsPerc > 0}+{floor($extraRepsPerc*12)}{/if} Reputation Points $10.00 USD</option>
                        <option value="24 Reputation Points">24{if $extraRepsPerc > 0}+{floor($extraRepsPerc*24)}{/if} Reputation Points $15.00 USD</option>
                        <option value="48 Reputation Points">48{if $extraRepsPerc > 0}+{floor($extraRepsPerc*48)}{/if} Reputation Points $30.00 USD</option>
                        <option value="120 Reputation Points">120{if $extraRepsPerc > 0}+{floor($extraRepsPerc*120)}{/if} Reputation Points $70.00 USD</option>
                        <option value="200 Reputation Points">200{if $extraRepsPerc > 0}+{floor($extraRepsPerc*200)}{/if} Reputation Points $120.00 USD</option>
                        <option value="500 Reputation Points">500{if $extraRepsPerc > 0}+{floor($extraRepsPerc*500)}{/if} Reputation Points $300.00 USD</option>
                    </select> 
                </div>
                
                <input type="hidden" name="currency_code" value="USD">
                <input type="hidden" name="custom" value="{$customField}"/>

                <div>
                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/da_DK/i/scr/pixel.gif" width="1" height="1">
                </div>

            </form>
        {/if}

        {if isset($supp_data) && $supp_data !== "0 rows"}
            <div class="page-grid page-column-3 grid-gap-none">
                <div>
                    <div class="page-sub-title-no-margin">
                        Normal
                    </div>

                    <div class="font-small table-alternate-1">
                        Normal Federal support is a monthly payment of <b>$5</b>
                    </div>

                    <div class="font-small table-alternate-1">
                        <br>
                        <i>Normal support grants the following benefits</i>:<br><br>
                        <b>1.</b> Blue username in the tavern<br>
                        <b>2.</b> PM inbox (+25 messages)<br>
                        <b>3.</b> Inventory space(+2 misc)<br>
                        <b>4.</b> Avatar dimension & size. <br>(150x150px and size 500kb)<br>
                        <b>5.</b> Logout timer. (+30 minutes)<br>
                        <b>6.</b> Nindo size. (+25%) <br>

                        {if ( ($supp_data[0]['subscr_id'] == '0') || ($supp_data[0]['federal_timer'] == '0') || ($supp_data[0]['subscr_id'] == '') )}
                            {if isset($paypalSandbox) && $paypalSandbox == true}
                                <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                    <input type="hidden" name="cmd" value="_s-xclick">
                                    <input type="hidden" name="hosted_button_id" value="KX6NBZ34ULRW2">
                                    <input type="hidden" name="custom" value="{$customField}"/>
                                    <input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                                </form>
                            {else}
                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                    <input type="hidden" name="cmd" value="_s-xclick">
                                    <input type="hidden" name="hosted_button_id" value="F7GAU9EEVRCL6">
                                    <input type="hidden" name="custom" value="{$customField}"/>
                                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.paypalobjects.com/da_DK/i/scr/pixel.gif" width="1" height="1">
                                </form>
                            {/if}
                        {else}
                            <a href="?id=61&act=unsubscribe">
                                <img src="https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif" border="0">
                            </a>   
                        {/if}
                    </div>
                </div>
                    
                <div>
                    <div class="page-sub-title-no-margin">
                        Silver
                    </div>

                    <div class="font-small table-alternate-2">
                        Silver Federal support is a monthly payment of <b>$10</b>
                    </div>

                    <div class="font-small table-alternate-2">
                        <br>
                        <i>All benefits of normal support and</i>:<br><br>
                        <b>1.</b> Silver username<br>
                        <b>2.</b> Increased Nindo (50% total)<br>
                        <b>3.</b> Inventory space (+5 misc)<br>
                        <b>4.</b> Avatar file size <br>(200x200px and size 750kb)<br>
                        <b>5.</b> Base diplomacy gained from Diplomacy (+25%) <br>
                        <b>6.</b> Two extra jutsu slots in battle<br>

                        {if ( ($supp_data[0]['subscr_id'] == '0') || ($supp_data[0]['federal_timer'] == '0') || ($supp_data[0]['subscr_id'] == '') )}
                            {if isset($paypalSandbox) && $paypalSandbox == true}
                                <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                    <input type="hidden" name="cmd" value="_s-xclick">
                                    <input type="hidden" name="hosted_button_id" value="9GKZY6EVF6A44">
                                    <input type="hidden" name="custom" value="{$customField}"/>
                                    <input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                                </form>
                            {else}
                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                    <input type="hidden" name="cmd" value="_s-xclick">
                                    <input type="hidden" name="hosted_button_id" value="Y2NUNYKPAXM5S">
                                    <input type="hidden" name="custom" value="{$customField}"/>
                                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.paypalobjects.com/da_DK/i/scr/pixel.gif" width="1" height="1">
                                </form>
                            {/if}
                        {else}
                                <a href="?id=61&act=unsubscribe">
                                    <img src="https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif" border="0">
                                </a>
                        {/if}
                    </div>
                </div>

                <div>
                    <div class="page-sub-title-no-margin">
                        Gold
                    </div>

                    <div class="font-small table-alternate-1">
                        Gold Federal support is a monthly payment of <b>$15</b>
                    </div>

                    <div class="font-small table-alternate-1">
                        <br/>
                        <i>All benefits of normal/silver support and</i>:<br><br>
                        <b>1.</b> Gold username<br>
                        <b>2.</b> Increased profession experience. (+25%)<br>
                        <b>3.</b> Increased nindo (100% total)<br>
                        <b>4.</b> Logout timer (+30 minutes)<br>
                        <b>5.</b> Avatar dimension & size<br> (250x250 and size 1000kb)<br>
                        <b>6.</b> Base diplomacy gained from Diplomacy (+25%) <br>

                        {if ( ($supp_data[0]['subscr_id'] == '0') || ($supp_data[0]['federal_timer'] == '0') || ($supp_data[0]['subscr_id'] == '') )}
                            {if isset($paypalSandbox) && $paypalSandbox == true}
                                <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                    <input type="hidden" name="cmd" value="_s-xclick">
                                    <input type="hidden" name="hosted_button_id" value="PVBFK8ZAT75GA">
                                    <input type="hidden" name="custom" value="{$customField}"/>
                                    <input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                                </form>
                            {else}
                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                    <input type="hidden" name="cmd" value="_s-xclick">
                                    <input type="hidden" name="hosted_button_id" value="B4R3EL8WTM2V4">
                                    <input type="hidden" name="custom" value="{$customField}"/>
                                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.paypalobjects.com/da_DK/i/scr/pixel.gif" width="1" height="1">
                                </form>
                            {/if}
                        {else}
                            <a href="?id=61&act=unsubscribe">
                                <img src="https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif" border="0">
                            </a>
                        {/if}
                    </div>
                </div>
            </div>
        {/if}

        {if isset($showHistory)}
            <a href="?id={$smarty.get.id}&act=records">
                Check Transaction History of this User
            </a>
        {/if}
    {if !$page_addon}</div>{/if}
{if !$page_addon}</div>{/if}
