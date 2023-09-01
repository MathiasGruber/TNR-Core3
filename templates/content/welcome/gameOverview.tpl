<table width="100%" style="margin:5px;border-spacing:0px;border-collapse: collapse;" >
    <tr>
        <td valign="top" width="50%">
            <table class="table" width="95%">
                <tr>
                    <td class="subHeader">Welcome</td>
                </tr>
                <tr>
                    <td class="tableColumns tdBorder">
                        Welcome back {$user_name}. These are the latests global messages posted in the game.
                    </td>
                </tr>
                {if isset($blueMessages)}
                    {foreach $blueMessages as $item}
                        <tr class="{cycle values="row1,row2"}">
                            <td style="text-align:left;padding-left:15px;">
                                <b>{$item['time']|date_format:"%d-%m-%y"}:</b> {$item['message']}
                            </td>
                        </tr>
                    {/foreach}
                {/if}  
            </table>
            
            {if isset($votingLinks)}
                <table class="table" width="95%">
                    <tr>
                        <td class="subHeader">Voting Sites</td>
                    </tr>
                    <tr>
                        <td class="tableColumns tdBorder">
                            Vote for TNR on these sites to increase your chakra and stamina pools.
                        </td>
                    </tr>
                    {foreach $votingLinks as $item}
                        <tr class="{cycle values="row1,row2"}">
                            <td>
                                <a href="{$item['link']}">{$item['title']}</a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
            
            <table class="table" width="95%">
                <tr>
                    <td class="subHeader">Reputation Points</td>
                </tr>
                <tr>
                    <td class="tableColumns tdBorder">
                        Reputation points can be used to buy e.g. bloodline items, profession materials, higher regen and more in the black market.
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href="?id=61">
                        <img alt="" src="data:image/png;base64,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" />
                        </a>
                    </td>
                </tr>
            </table>
        </td>
        <td  width="50%">
            
            <table class="table" width="95%">
                <tr>
                    <td class="subHeader">Current Events</td>
                </tr>
                {if isset($globalEvents)}
                    {foreach $globalEvents as $item}
                        {if $item['active'] == "yes"}
                            <tr class="{cycle values="row1,row2"}">
                                <td style="text-align:left;padding-left:15px;">
                                    <b>{$item['userVisualTitle']}:</b> {$item['userVisual']}
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                {/if}     
            </table>
            
            <table class="table" width="95%">
                <tr>
                    <td class="subHeader">Latest Updates</td>
                </tr>
                {if isset($contentChanges) && is_array($contentChanges)}
                    {foreach $contentChanges as $item}
                        <tr class="{cycle values="row1,row2"}">
                            <td style="text-align:left;padding-left:15px;">
                                <b>{$item['time']|date_format:"%d-%m-%y"}:</b> {$item['info']}
                            </td>
                        </tr>
                    {/foreach}
                {/if}     
            </table>
            
            <table class="table" width="95%">
                <tr>
                    <td class="subHeader">Latest News</td>
                </tr>
                <tr>
                    <td style="text-align:left;padding:15px;">
                       <b>{$newsItem[0].title|stripslashes}</b><br>
                       {$newsItem[0].content|stripslashes}<br>
                       <span style="font-size:13px;">
                           <i>{$newsItem[0].time|date_format:"%D"}, Posted by: {$newsItem[0].posted_by}</i>
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>