<div align="center">
    {include file="file:{$absPath}/templates/content/report/report_header.tpl" title="Report header"}

    <form name="form1" method="post" action="">
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">Report user: </td>
        </tr><tr>
            <td width="20%" style="text-align:right;font-weight:bold;">Username:</td>
            <td width="80%" style="text-align:left;">{$message[0]["user"]}</td>
        </tr><tr>
            <td style="text-align:right;font-weight:bold;">Report by: </td>
            <td style="text-align:left;">{$reportBy}</td>
        </tr><tr>
            <td style="text-align:right;font-weight:bold;">Reason:</td>
            <td style="text-align:left;">
                <select name="reason" id="reason">
                    <option>Harassment</option><option>Foul language</option><option>Spamming</option>
                    <option>Selling / Buying accounts</option><option value="other">Other (enter below)</option>
                </select>
            </td>
        </tr><tr>
            <td>&nbsp;</td>
            <td style="text-align:left;">
                <input name="reason_text" type="text" id="reason_text" size="35">
            </td>
        </tr>
        {if isset($message[0]["type"]) && $message[0]["type"] != ""}
            <tr>
                <td colspan="2" class="subHeader">{$message[0]["type"]}</td>
            </tr>
            <tr>
                <td colspan="2" >{$message[0]['message']}</td>
            </tr>
        {/if}
        <tr>
            <td colspan="2">
                <input type="submit" name="Submit" value="Submit" />
            </td>
        </tr>
    </table>
    </form>
</div>