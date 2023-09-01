    <tr color="darkbrown">
        <td>
            <headerText>Latests Global Messages</headerText>
        </td>
    </tr>
    {if isset($blueMessages)}
        {foreach $blueMessages as $item}
            <tr color="{cycle values="dim,clear"}">
                <td>
                    <text><b>{$item['time']|date_format:"%d-%m-%y"}:</b> {$item['message']}</text>
                </td>
            </tr>
        {/foreach}
    {/if}
    <tr color="darkbrown">
        <td>
            <headerText>Current Events</headerText>
        </td>
    </tr>
    {if isset($globalEvents)}
        {foreach $globalEvents as $item}
            {if $item['active'] == "yes"}
                <tr color="{cycle values="dim,clear"}">
                    <td>
                        <text><b>{$item['userVisualTitle']}:</b> {$item['userVisual']}</text>
                    </td>
                </tr>
            {/if}
        {/foreach}
    {/if}  
    <tr color="darkbrown">
        <td><headerText>Latest News</headerText></td>
    </tr>
    <tr>
        <td>
<text><b>{$newsItem[0].title|stripslashes}</b><br>
{$newsItem[0].content|stripslashes}<br>
<i>{$newsItem[0].time|date_format:"%D"}, Posted by: {$newsItem[0].posted_by}</i>
</text>
        </td>
    </tr>
    <tr color="darkbrown">
        <td>
            <headerText>Latest Updates</headerText>
        </td>
    </tr>
    {if isset($contentChanges) && is_array($contentChanges)}
        {foreach $contentChanges as $item}
            <tr color="{cycle values="dim,clear"}">
                <td style="text-align:left;padding-left:15px;">
                    <text><b>{$item['time']|date_format:"%d-%m-%y"}:</b> {$item['info']}</text>
                </td>
            </tr>
        {/foreach}
    {/if}  