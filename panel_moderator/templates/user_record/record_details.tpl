<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">{$data[0]["action"]} details </td>
        </tr>
        <tr>
            <td width="30%" style="font-weight:bold;padding-left:5px;">Username:</td>
            <td width="70%">{$data[0]["username"]}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;padding-left:5px;">Moderator:</td>
            <td>{$data[0]["moderator"]}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Type:</td>
            <td>{$data[0]["action"]}</td>
        </tr>
        <tr>
        <tr>
            <td style="font-weight:bold;">Date:</td>
            <td>{$data[0]['time']|date_format:"%Y-%m-%d %H:%M:%S"}</td>
        </tr>
            <td style="font-weight:bold;">Reason:</td>
            <td>{$data[0]["reason"]}</td>
        </tr>
        <tr>
            <td colspan="2" class="subHeader">Message:</td>
        </tr>
        <tr>
            <td colspan="2" style="border-bottom:1px solid #000000;">
                {$data[0]["message"]}
            </td>
        </tr>
        {if isset( $data[0]['override_reason'] ) && $data[0]['override_reason'] != ""}
            <tr>
                <td colspan="2" class="subHeader">Override reason:</td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom:1px solid #000000;">
                    {$data[0]["override_reason"]}
                </td>
            </tr>
            <tr>
                <td align="center" style="border-bottom:1px solid #000000;">
                    Override by:
                </td>
                <td align="center" style="border-bottom:1px solid #000000;">
                    {$data[0]["override_by"]}
                </td>
            </tr>
        {/if}
        <tr>
            <td colspan="2" class="subHeader">Options:</td>
        </tr>    
        <tr>
            <td colspan="2" >
                <a href="?id={$smarty.get.id}&uid={$data[0]["uid"]}">Return to user violations</a>
            </td>
        </tr>
        {if isset($confirmDeletion)}
            <tr>
                <td colspan="2">
                    <form name="form1" method="post" action="">
                    <input type="submit" name="Submit" value="Delete Record">
                    </form>
                </td>
            </tr>
        {/if}
    </table>
</div>