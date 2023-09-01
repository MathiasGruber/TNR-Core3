<div align="center">
    <form name="form1" method="post" action="">
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader" >Blue Notification Message</td>
        </tr>
        <tr>
            <td colspan="2" style="color:darkred;">
                Use this feature sporadically and responsibly, too much joking around with this feature will not be tolerated.
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <TEXTAREA name="message" style="width:80%;height:75px;" placeholder="message"></TEXTAREA>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="text" name="message_link" placeholder="message link '?id=2...'" />
            </td>
        </tr>
        <tr>
            <td width="50%" style="text-align:right;">
                <select name="mask">
                    <option value="All">All</option>
                    <option value="Konoki">Konoki</option>
                    <option value="Shroud">Shroud</option>
                    <option value="Shine">Shine</option>
                    <option value="Samui">Samui</option>
                    <option value="Silence">Silence</option>
                    <option value="Syndicate">Syndicate</option>
                </select>
            </td>
            <td width="50%" style="text-align:left;">
                <input type="submit" name="Submit" value="Submit" />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {* Show the log *}
                {if isset($log)}
                    {$subSelect="log"} 
                    {include file="file:{$absPath}/{$log}" title="AI log"}
                {/if}
            </td>
        </tr>
    </table></form>
</div>