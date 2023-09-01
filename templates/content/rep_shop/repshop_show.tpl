<div align="center">
    <table width="95%" class="table" >      
        <tr>
            <td class="subHeader">Reputation Points</td>
        </tr>
        <tr>
            <td class="tableColumns tdBorder">These points can be used to buy e.g. bloodline items in the black market. </td>
        </tr>
        {if $extraRepsPerc > 0}
            <tr>
                <td style="text-align:left;color:darkred;padding-left:15pt;" class="tableColumns tdBorder">
                    To celebrate we are offering a limited discount on reputation points! <br>
                    This means you get 20% extra reputation points on all purchases.<br><br>
                    <ul style="list-style:square;list-style-position: inside;">
                        <li>6 points &#8605; {floor($extraRepsPerc*6)+6} reputation points</li>
                        <li>12 points &#8605; {floor($extraRepsPerc*12)+12} reputation points</li>
                        <li>24 points &#8605; {floor($extraRepsPerc*24)+24} reputation points</li>
                        <li>48 points &#8605; {floor($extraRepsPerc*48)+48} reputation points</li>
                        <li>120 points &#8605; {floor($extraRepsPerc*120)+120} reputation points</li>
                        <li>200 points &#8605; {floor($extraRepsPerc*200)+200} reputation points</li>
                        <li>500 points &#8605; {floor($extraRepsPerc*500)+500} reputation points</li>
                    </ul><br>
                </td>
            </tr>
        {/if}
        <tr>
            <td style="padding:20px;">

                {if isset($paypalSandbox) && $paypalSandbox == true}
                    <!-- For Development Only -->
                    <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top" style="display:inline;">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="QQLHCWP8SBFPL">
                        <table>
                            <tr>
                                <td>
                                    <input type="hidden" name="on0" value="Amount of points">Amount of points
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

                                </td>
                                <td>
                                    <input type="hidden" name="currency_code" value="USD">
                                    <input type="hidden" name="custom" value="{$customField}"/>
                                    <input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                                </td>
                            </tr>
                        </table>
                    </form>
                {else}
                    <!-- For Production Only -->
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="6NULJW73JTQ6G">
                        <table>
                            <tr>
                                <td>
                                    <input type="hidden" name="on0" value="Amount of Reputation Points">Amount of Reputation Points
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
                                </td>
                                <td>
                                    <input type="hidden" name="currency_code" value="USD">
                                    <input type="hidden" name="custom" value="{$customField}"/>
                                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                    <img alt="" border="0" src="https://www.paypalobjects.com/da_DK/i/scr/pixel.gif" width="1" height="1">

                                </td>
                            </tr>
                        </table>
                    </form>
                {/if}
            </td>
        </tr>
    </table>
    {if isset($supp_data) && $supp_data !== "0 rows"}
        <br>
        <table width="95%" border="0" cellspacing="0" cellpadding="0" >      
            <tr>
                <td style="text-align:left;padding:0px;" width="30%" valign="top">
                    <table width="99%" class="table" align="right">      
                        <tr>
                            <td class="subHeader">Normal</td>
                        </tr>
                        <tr>
                            <td class="tableColumns tdBorder">Normal Federal support is a monthly payment of <b>$5</b></td>
                        </tr>
                        <tr height="180px">
                            <td style="text-align:left;">
                                <i>Normal support grants the following benefits</i>:<br><br>
                                <b>1.</b> Blue username in the tavern<br>
                                <b>2.</b> PM inbox (+25 messages)<br>
                                <b>3.</b> Inventory space(+2 misc)<br>
                                <b>4.</b> Avatar dimension & size. <br>(150x150px and size 500kb)<br>
                                <b>5.</b> Logout timer. (+30 minutes)<br>
                                <b>6.</b> Nindo size. (+25%) <br>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {if ( ($supp_data[0]['subscr_id'] == '0') || ($supp_data[0]['federal_timer'] == '0') || ($supp_data[0]['subscr_id'] == '') )}
                                    <br>
                            <center>
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
                            </center>
                        {else}
                            <br>
                            <center>
                                <A HREF="?id=61&act=unsubscribe">
                                    <IMG SRC="https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif" BORDER="0">
                                </A>   
                            </center>
                        {/if}
                </td>
            </tr>
        </table>
    </td>
    <td style="text-align:left;padding:0px;" width="30%" valign="top">
        <table width="99%" class="table" align="right">      
            <tr>
                <td class="subHeader">Silver</td>
            </tr>
            <tr>
                <td class="tableColumns tdBorder">Silver Federal support is a monthly payment of <b>$10</b></td>
            </tr>
            <tr height="180px">
                <td style="text-align:left;">
                    <i>All benefits of normal support and</i>:<br><br>
                    <b>1.</b> Silver username<br>
                    <b>2.</b> Increased Nindo (50% total)<br>
                    <b>3.</b> Inventory space (+5 misc)<br>
                    <b>4.</b> Avatar file size <br>(200x200px and size 750kb)<br>
                    <b>5.</b> Base diplomacy gained from Diplomacy (+25%) <br>
                    <b>6.</b> Two extra jutsu slots in battle<br>

                </td>
            </tr>
            <tr>
                <td>
                    {if ( ($supp_data[0]['subscr_id'] == '0') || ($supp_data[0]['federal_timer'] == '0') || ($supp_data[0]['subscr_id'] == '') )}
                        <br>
                <center>
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
                </center>
            {else}
                <br>
                <center>
                    <A HREF="?id=61&act=unsubscribe">
                        <IMG SRC="https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif" BORDER="0">
                    </A>
                </center>
            {/if}
    </td>
</tr>
</table>
</td>
<td style="text-align:right;padding:0px;"  width="30%" valign="top">
    <table width="99%" class="table" align="right">      
        <tr>
            <td class="subHeader">Gold</td>
        </tr>
        <tr>
            <td class="tableColumns tdBorder">Gold Federal support is a monthly payment of <b>$15</b></td>
        </tr>
        <tr height="180px">
            <td style="text-align:left;">
                <i>All benefits of normal/silver support and</i>:<br><br>
                <b>1.</b> Gold username<br>
                <b>2.</b> Increased profession experience. (+25%)<br>
                <b>3.</b> Increased nindo (100% total)<br>
                <b>4.</b> Logout timer (+30 minutes)<br>
                <b>5.</b> Avatar dimension & size<br> (250x250 and size 1000kb)<br>
                <b>6.</b> Base diplomacy gained from Diplomacy (+25%) <br>
            </td>
        </tr>
        <tr>
            <td>
                {if ( ($supp_data[0]['subscr_id'] == '0') || ($supp_data[0]['federal_timer'] == '0') || ($supp_data[0]['subscr_id'] == '') )}
                    <br>
            <center>
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
            </center>
        {else}
            <br>
            <center>
                <A HREF="?id=61&act=unsubscribe">
                    <IMG SRC="https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif" BORDER="0">
                </A>
            </center>
        {/if}
</td>
</tr>
</table>
</td>
</tr>
</table>
        
<!-- Update Federal Support -->
{if $supp_data[0]['subscr_id'] !== '0' && $supp_data[0]['federal_timer'] !== '0' && $supp_data[0]['subscr_id'] !== '' && $supp_data[0]['federal_level'] !== "Gold" && $smarty.session.uid == "1959568"}
    
    {if $supp_data[0]['federal_level'] == "Normal"}
        {assign var="columns" value="2"}
    {else}
        {assign var="columns" value="1"}
    {/if}
    
    <table width="95%" class="table" >      
        <tr>
            <td class="subHeader" colspan="{$columns}">Upgrade Federal Support</td>
        </tr>
        <tr>
            <td colspan="{$columns}" class="tableColumns tdBorder">
                Using this feature you can upgrade your current federal support level. This upgrade is a one-time payment, and will only apply to the current months federal support. If you want to continue with your higher level of federal support, it's recommended that you cancel your current subscription and purchase the level of federal support you desire once your current term is up.
            </td>
        </tr>
        <tr>
            {if $supp_data[0]['federal_level'] == "Normal"}
                <td>
                    Update your {$supp_data[0]['federal_level']} federal support to Silver federal support. Given the {$currentDaysLeft} days left on your {$supp_data[0]['federal_level']}, this will cost you ${$silverUpgradePrice}
                    <form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_xclick">
                    <input type="hidden" name="business" value="nano.mathias@gmail.com">
                    <input type="hidden" name="currency_code" value="USD">
                    <input type="hidden" name="custom" value="{$customField}"/>
                    <input type="hidden" name="item_name" value="{$supp_data[0]['federal_level']}ToSilver">
                    <input type="hidden" name="amount" value="{$silverUpgradePrice}">
                    <input type="image" src="http://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
                    </form>
                </td>
            {/if}
            {if $supp_data[0]['federal_level'] == "Normal" || $supp_data[0]['federal_level'] == "Silver"}
                <td>
                    Update your {$supp_data[0]['federal_level']} federal support to Gold federal support. Given the {$currentDaysLeft} days left on your {$supp_data[0]['federal_level']}, this will cost you ${$goldUpgradePrice}
                    <form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_xclick">
                    <input type="hidden" name="business" value="nano.mathias@gmail.com">
                    <input type="hidden" name="currency_code" value="USD">
                    <input type="hidden" name="custom" value="{$customField}"/>
                    <input type="hidden" name="item_name" value="{$supp_data[0]['federal_level']}ToGold">
                    <input type="hidden" name="amount" value="{$goldUpgradePrice}">
                    <input type="image" src="http://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
                    </form>
                </td>
            {/if}
        </tr>
    </table>
{/if}
        
{/if}

{if isset($showHistory)}
    <a href="?id={$smarty.get.id}&act=records">
        <font size="+1">
        Check Transaction History of this User
        </font>
    </a>
{/if}
</div>
