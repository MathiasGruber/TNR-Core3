<div align="center">

    {if isset($notes)}
        <table width="95%" border="0" cellspacing="0" cellpadding="0" style="border:0px;">
            <tr>
                 <td style="text-align:left;padding:0px;" width="50%" valign="top">
                    <table width="95%" class="table">
                        <tr>
                            <td width="100%" class="subHeader">Announcements/Orders</td>
                        </tr>
                        <tr>
                            <td>{$announ_nindo}&nbsp;</td>
                        </tr>
                    </table>
                </td>
                <td style="text-align:right;padding:0px;" width="50%" valign="top">
                    {$subSelect="notes"}
                    {include file="file:{$absPath}/{$notes}" title="Admin Notes"}
                </td>
            </tr>
        </table>
    {else}
        <table width="95%" class="table">
            <tr>
                <td width="100%" class="subHeader">Announcements/Orders</td>
            </tr>
            <tr>
                <td>{$announ_nindo}&nbsp;</td>
            </tr>
        </table>
    {/if}





    <table width="95%" class="table">
        <tr>
            <td colspan="3" class="subHeader">Current Team Members</td>
        </tr>
        <tr>
            <td colspan="3">
                {if isset($admins) && $admins != ""}
                    {foreach $admins as $entry}
                        {if $entry["user_rank"] == "Admin"}
                            <font color="#AA1111" size="+1">{$entry["username"]}</font> &nbsp;&nbsp;&nbsp;
                        {elseif $entry["user_rank"] == "Supermod"}
                            <font color="#008080" size="+1">{$entry["username"]}</font> &nbsp;&nbsp;&nbsp;
                        {elseif $entry["user_rank"] == "Moderator"}
                            <font color="#006633" size="+1">{$entry["username"]}</font> &nbsp;&nbsp;&nbsp;
                        {/if}

                    {/foreach}
                {/if}                      
            </td>
        </tr>
    </table>

    {if isset($modLog)}
        {$subSelect="modLog"}
        {include file="file:{$absPath}/{$modLog}" title="Moderator log"}
    {/if}

</div>