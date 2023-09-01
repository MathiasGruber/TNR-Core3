{include file="file:{$absPath}/templates/content/report/report_header_mobile.tpl" title="Report header"}

<tr>
    <td>
        <headerText>Report User</headerText>
    </td>
</tr>

<tr>
    <td>
<text>
Username: {$message[0]["user"]}<br>
Report by: {$reportBy}<br><br>
Reason:
</text>
<select name="reason" id="reason">
    <option value="Harassment">Harassment</option>
    <option value="Foul language">Foul language</option>
    <option value="Spamming">Spamming</option>
    <option value="Selling / Buying accounts">Selling / Buying accounts</option>
    <option value="other">Other (enter below)</option>
</select>
<text>Other Reason:</text>
<input name="reason_text" type="textarea"></input>
    </td>
</tr>

{if isset($message[0]["type"]) && $message[0]["type"] != ""}
    <tr>
        <td><headerText>{$message[0]["type"]}</headerText></td>
    </tr>
    <tr>
        <td><text>{$message[0]['message']}</text></td>
    </tr>
{/if}

<tr>
    <td>
        <submit type="submit" name="Submit" value="Submit"></submit>
    </td>
</tr>