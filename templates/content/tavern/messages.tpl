{if isset($data)}
    <tr>
        <td width="20%" class="tableColumns tdBorder"><b>Username</b></td>
        <td width="80%" class="tableColumns tdBorder"><b>Message</b></td>
    </tr>
    {foreach $data as $entry}  
        {strip}
        <tr>
          <td id="user_info_{$entry.uid}" width="20%" class="tdBorder" style="vertical-align:middle;">
            {$entry.color_user}
            <br>
            {$entry.rank}
            <br>
            {$entry.time|date_format:"%l:%M %p"}<br>

			{if !$mod_panel}
				<a href="?id=53&amp;act=tavern&amp;mt={$entry.time}&amp;uid={$entry.uid}&tavern={$tavernTable}">
			{else}
				<a href="../?id=53&amp;act=tavern&amp;mt={$entry.time}&amp;uid={$entry.uid}&tavern={$tavernTable}">
			{/if}

                <img src="./images/report.gif" alt="Report message" style="border:none;">
            </a>&nbsp;

			{if !$mod_panel}
				<a href="?id=13&amp;page=profile&amp;name={$entry.user}">
			{else}
				<a href="../?id=13&amp;page=profile&amp;name={$entry.user}">
			{/if}

                <img src="./images/profile.png" alt="View profile" style="border:none;">
            </a>&nbsp;

			{if !$mod_panel}
				<a href="?id=3&amp;act=newMessage&amp;toUser={$entry.user}">
			{else}
				<a href="../?id=3&amp;act=newMessage&amp;toUser={$entry.user}">
			{/if}

                <img src="./images/email.png" alt="Send PM" style="border:none;">
            </a>&nbsp;
            {if isset($isAdmin) && $isAdmin == true}
                <form style="display:inline;" action="" method="post" class="deletePost">
                    <input type="hidden" name="identifier" value="{$entry.time}:{$entry.uid}">
                    <input type="image" src="./images/trash.gif" alt="Delete message" />
                </form>
            {/if}                
          </td>
          <td width="80%" class="tavernMessageBox">
            {$entry.message}
          </td>
        </tr>
        {/strip}
    {/foreach}
{else}
    <tr><td colspan="2">No Messages</td></tr>
{/if}
{if isset($autoUpdateChat)}
    <script>
        var autoUpdateChat = {$autoUpdateChat};
    </script>
{/if}