<div align="center">
    <table width="95%" class="table">
        <tr>
          <td colspan="3" class="subHeader">Admin notes</td>
        </tr>
        <tr>
          <td colspan="3" style="color:darkred;">Here you can post notes to fellow members of the administration.</td>
        </tr>
        <tr>
          <td width="33%" style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=new">New note </a></td>
          <td width="33%" style="border-top:1px solid #000000;">&nbsp;</td>
          <td width="33%" style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=clear">Clear notes </a></td>
        </tr>
        <tr>
          <td colspan="3">
            {if isset($notes)}
                {$subSelect="notes"}
                {include file="file:{$absPath}/{$notes}" title="Admin Notes"}
            {/if}
          </td>
        </tr>
        <tr>
            <td colspan="3" class="subHeader">Current Team Members</td>
        </tr>
        <tr>
            <td colspan="3">
                {if isset($admins) && $admins != ""}
                    {foreach $admins as $entry}
                        <font size="+2">{$entry["username"]}</font> &nbsp;&nbsp;&nbsp;
                    {/foreach}
                {/if}                      
            </td>
        </tr>
        <tr>
          <td colspan="3">
            {if isset($log)}
                {$subSelect="log"}  
                {include file="file:{$absPath}/{$log}" title="Latest Admin Updates"}
            {/if}
          </td>
        </tr>
    </table>
</div>