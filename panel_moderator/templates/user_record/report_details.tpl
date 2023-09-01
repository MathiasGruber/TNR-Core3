<div >
    <table width="95%" class="table">
        <tr>
            <td colspan="4" class="subHeader">User Report</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight:bold;">User:</td>
            <td width="25%">{$name}</td>
            <td width="25%" style="font-weight:bold;">Reported by:</td>
            <td width="25%">{$rname}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Report date:</td>
            <td >{$report[0]['time']|date_format:"%Y-%m-%d %H:%M:%S"}</td>
            <td style="font-weight:bold;">Type:</td>
            <td >{$report[0]['type']}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Message date:</td>
            <td >{$report[0]['mt']|date_format:"%Y-%m-%d %H:%M:%S"}</td>
            <td style="font-weight:bold;"></td>
            <td ></td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Status:</td>
            <td >{$report[0]['status']}</td>
            <td style="font-weight:bold;">Processed by:</td>
            <td >{$processed}</td>
        </tr>
        <tr>
            <td align="left" style="font-weight:bold;">Reason:</td>
            <td >{$report[0]['reason']}</td>
            <td colspan="2" >
              {if $can_take_control}
              <a href='?id={$smarty.get.id}&act=take_control&eid={$smarty.get.eid}'>Take Control!</a>
              {/if}
              
              {if $can_take_control}
              <a href='?id={$smarty.get.id}&act=delete&eid={$smarty.get.eid}'>&nbsp&nbsp&nbsp&nbsp&nbspDelete!</a>
              {/if}
            </td>
        </tr>
        {if isset($message) && $message != ""}
            <tr><td colspan="4" class="subHeader">Reported Message</td></tr>
            <tr><td colspan="4" style='padding:10px;'>{$message}</td></tr>
        {/if} 
    </table>
</div>
<br>

<div >
    <table width="95%" border="0" class="table" >
        <tr>
            <td width="100%" class="subHeader">Options</td>
        </tr>
        <tr>
            <td style="padding:2px;">
                <a href="?id={$smarty.get.id}&amp;uid={$report[0]['uid']}">Check user's record</a>
            </td>
        </tr>
    </table>
</div>
{if isset($handleReport) && $handleReport == true }
    <div >
        <form method="post" action="?id={$smarty.get.id}&act=reportDetails&eid={$smarty.get.eid}"><br>
            <table width="95%" class="table">
                <tr>
                    <td style="border-top:none;" class="subHeader" >Alter Report Status</td>
                </tr>
                <tr>
                    <td style="padding:10px;color:darkred;">
                        Altering the report status will designate you as the processing moderator, any questions regarding this matter, and it's processing can and will be directed at you.<br>
                        <br>Once you have set the status to anything other than &quot;unviewed&quot; no other moderator or admin can alter the report's status, unless you set it back to &quot;unviewed&quot;<br>
                        <br>you cannot reset &quot;ungrounded&quot; or &quot;handled&quot; reports to unviewed.</td>
                </tr>
                <tr>
                    <td style="padding:5px;">
                        <select name="status">
                            <option>unviewed</option>
                            <option>in progress</option>
                            <option>ungrounded</option>
                            <option>handled</option>
                        </select></td>
                </tr>
                <tr>
                    <td style="padding:5px;">
                        <input type="submit" name="Submit" value="Submit"></td>
                </tr>
            </table><br>
        </form>
    </div>
{else}
    <div>The report has already been handled or ungrounded.</div>
{/if}
