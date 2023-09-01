    <tr color="fadedblack">
        <td><text color="white"><b>Username</b></text></td>
        <td><text color="white"><b>Message</b></text></td>
    </tr>

    {if isset($data)}    
        {foreach $data as $entry}  
            <tr color="{cycle values="dim,clear"}">
              <td width="30%" contentFit="true">
<text bbcode="true">
{$entry.color_user}
<br>
{$entry.rank}
<br>
{$entry.time|date_format:"%l:%M %p"}
</text>   

<tr spacing="20">
<a href="?id=53&amp;act=tavern&amp;mt={$entry.time}&amp;uid={$entry.uid}&amp;tavern={$tavernTable}" icon="icon_report"></a>
<a href="?id=13&amp;page=profile&amp;name={$entry.user}" icon="icon_profile"></a>
<a href="?id=3&amp;act=newMessage&amp;toUser={$entry.user}" icon="icon_pm"></a>          
</tr>

              </td>
              <td width="70%">
                  <text>{$entry.message}</text>
              </td>
            </tr>
        {/foreach}
    {else}
        <tr><td><text>No Messages</text></td></tr>
    {/if}
