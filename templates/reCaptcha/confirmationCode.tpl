<div align='center'>
    <table width='90%' class='table' border='0' cellspacing='0' cellpadding='0'>
        <tr>
            <td align='center' style='border-top:none;' class='subHeader'>Anti-bot Captcha</td>
        </tr>
        <tr>
            <td style='padding:5px;' align='center'>
                {$msg}<br>
                <form method='post' action=''>
                    <center>{$reCaptcha}</center>
                    {$loginInfo}
                    <input name="Submit" type="submit" class="input_submit_btn" id="Submit" value="Submit" style="margin:5px;" />
                </form>
            </td>
        </tr>
        <tr>
            <td class="tableColumns tdBorder">
                In case of issues: If you're using an extension that blocks scripts, ads, or trackers in some way, then you need to make an exception on the site or the captcha page. Alternatively, you may need to turn off data compression in your browser for the captcha to function correctly.
            </td>
        </tr>
    </table>
</div>