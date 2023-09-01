<div class="page-box">
    <form name="form1" method="post" action="">
        <div class="page-title">
            Blue Notification Message
        </div>
        <div class="page-content">
            <table>
                <tr>
                    <td colspan="2" style="color:darkred;" class="shadow">
                        Use this feature sporadically and responsibly, too much joking around with this feature will not be tolerated.
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <textarea name="message" rows="5" placeholder="message" class="page-text-area-fill"></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="text" name="message_link" placeholder="message link '?id=2...'" class="page-text-input-fill"/>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:right;">
                        <select name="mask" class="page-drop-down-fill">
                            <option value="All">All</option>
                            <option value="Konoki">Konoki</option>
                            <option value="Shroud">Shroud</option>
                            <option value="Shine">Shine</option>
                            <option value="Samui">Samui</option>
                            <option value="Silence">Silence</option>
                            <option value="Syndicate">Syndicate</option>
                        </select>
                    </td>
                    <td style="text-align:left;">
                        <input type="submit" name="Submit" value="Submit" class="page-button-fill"/>
                    </td>
                </tr>
            </table>

            {* Show the log *}
            {if isset($log)}
                {$subSelect="log"} 
                {include file="file:{$absPath}/{$log}" title="AI log"}
            {/if}
        </div>
    </form>
</div>