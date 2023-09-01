<div align="center" id="div_month_report">
    <form name="MonthReportForm" method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td colspan="2" class="subHeader">Monthly Village Report</td>
            </tr>
            <tr>
                <td width="30%" style="text-align:right;">Moderator: </td>
                <td width="70%" style="text-align:left;">{$moderator_name}</td>
            </tr>
            <tr>
                <td style="text-align:right;">Month of Report:</td>
                <td style="text-align:left;">
                    {if ($smarty.now-1209600)|date_format:'%B' == $smarty.now|date_format:'%B'}
                        <input type="radio" name="report_month_time" value="{$smarty.now|date_format:'%B'}" checked>
                            {$smarty.now|date_format:'%B'}
                    {else}		
                        <input type="radio" name="report_month_time" value="{($smarty.now-1209600)|date_format:'%B'}" checked>
                            {($smarty.now-1209600)|date_format:'%B'}&#160;
                    {/if}
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">Year of Report:</td>
                <td style="text-align:left;">
                    {if ($smarty.now-1209600)|date_format:'%Y' == $smarty.now|date_format:'%Y'}
                        <input type="radio" name="report_year_time" value="{$smarty.now|date_format:'%Y'}" checked>
                            {$smarty.now|date_format:'%Y'}
                    {else}		
                        <input type="radio" name="report_year_time" value="{($smarty.now-1209600)|date_format:'%Y'}" checked>
                            {($smarty.now-1209600)|date_format:'%Y'}&#160;
                    {/if}
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">Village:</td>
                <td style="text-align:left;">
                    <input type="radio" name="report_village" value="Konoki">Konoki&#160;&#160;&#160;
                    <input type="radio" name="report_village" value="Shine">Shine&#160;&#160;&#160;
                    <input type="radio" name="report_village" value="Samui" checked>Samui&#160;&#160;&#160;
                    <input type="radio" name="report_village" value="Silence">Silence&#160;&#160;&#160;
                    <input type="radio" name="report_village" value="Shroud">Shroud
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">Village Rating:</td>
                <td style="text-align:left;">
                    <input type="radio" name="report_vil_rate" value="1">1&#160;&#160;&#160;
                    <input type="radio" name="report_vil_rate" value="2" checked>2&#160;&#160;&#160;
                    <input type="radio" name="report_vil_rate" value="3">3
                </td>
            </tr>
            <tr>
                <td colspan="2" class="subHeader">Report Details</td>
            </tr>
            <tr>
                <td colspan="2">
                    <textarea name="month_report_message" id="month_report_message" rows="8" cols="35"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center" >
                    <input type="submit" name="month_report" id="month_report_submit" value="Submit Report">
                </td>
            </tr>
        </table>
    </form>
</div>