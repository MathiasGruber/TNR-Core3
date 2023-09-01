<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="5" class="subHeader">Username: {$username} - Location: {$userlocation}</td>
        </tr>
        
        {if isset($warning) && $warning != ""}
            <tr>
                <td colspan="5" style="color:red;border-bottom:1px solid #000000;">{$warning}</td>
            </tr>
        {/if}
        
        {if $sheet != '0 rows'}
            <tr>
                <td style="font-weight:bold;border-bottom:1px solid #000000;">
                    Type:
                </td>
                <td style="font-weight:bold;border-bottom:1px solid #000000;">
                    Moderator
                </td>
                <td style="font-weight:bold;border-bottom:1px solid #000000;">
                    Duration
                </td>
                <td style="font-weight:bold;border-bottom:1px solid #000000;">
                    On:
                </td>
                <td style="border-bottom:1px solid #000000;">
                    &nbsp;
                </td>
            </tr>
            
            {for $i = 0 to ($sheet|@count)-1} 
                <tr class="{cycle values="row1,row2"}" >
                  <td width="15%">{$sheet[$i]['action']}</td>
                  <td width="16%">{$sheet[$i]['moderator']}</td>
                  <td width="23%">{$sheet[$i]['duration']}</td>
                  <td width="23%">{$sheet[$i]['time']|date_format:"%Y-%m-%d"}</td>
                  <td width="23%">
                    <a href="?id={$smarty.get.id}&act=details&action_id={$sheet[$i]['id']}&uid={$sheet[$i]['uid']}&time={$sheet[$i]['time']}">Details</a>
                    {if $mod_rank == "Supermod" || $mod_rank == "Admin"}
                        - <a href='?id={$smarty.get.id}&act=deleterecord&time={$sheet[$i]['time']}&uid={$userid}&action_id={$sheet[$i]['id']}'>Delete</a>
                    {/if}
                  </td>
                </tr>
            {/for}
        {else}    
             <tr><td colspan="5"><b>No violations in the moderator log</b></td></tr>
        {/if}
    </table>
    
    {if $reports != '0 rows'}
        <br>
        <table width="95%" class="table">
            <tr>
                <td colspan="4" class="subHeader">Reports filed against this user: </td>
            </tr>
            <tr>
                <td style="font-weight:bold;border-bottom:1px solid #000000;" width="25%">Filed by: </td>
                <td style="font-weight:bold;border-bottom:1px solid #000000;" width="25%">Status:</td>
                <td style="font-weight:bold;border-bottom:1px solid #000000;" width="25%">Type:</td>
                <td style="font-weight:bold;border-bottom:1px solid #000000;" width="25%">&nbsp;</td>
            </tr>
            {for $i = 0 to ($reports|@count)-1} 
                <tr class="{cycle values="row1,row2"}" >
                    <td><a href="?id=13&page=profile&uid={$reports[$i]["rid"]}">{$reports[$i]["rid"]}</a></td>
                    <td>{$reports[$i]["status"]}</td>
                    <td>{$reports[$i]["type"]}</td>
                    <td><a href="?id={$smarty.get.id}&act=reports&page=details&uid={$reports[$i]["uid"]}&time={$reports[$i]["time"]}&rid={$reports[$i]["rid"]}">Show details</a></td>                      
                </tr>
            {/for}
        </table>
    {/if}
    
    {if isset($changes)}
        {$subSelect="changes"}
        {include file="file:{$absPath}/{$changes}" title="Name changes"}
    {/if}
    
    {if isset($extraNotices) && extraNotices != ""}
        <br>
        <table width="95%" class="table">
            <tr>
                <td colspan="4" class="subHeader">Moderator discussion about user: </td>
            </tr>
            <tr>
                <td width="20%" style="border-bottom:1px solid #000000; "><b>Date</b></td>
                <td width="20%" style="border-bottom:1px solid #000000;"><b>Moderator</b></td>
                <td width="80%" style="border-bottom:1px solid #000000;"><b>Message</b></td>
            </tr>
            {if $tavern != '0 rows'}
                {for $i = 0 to ($tavern|@count)-1} 
                    <tr class="{cycle values="row1,row2"}" >
                        <td>
                            {$tavern[$i]['time']|date_format:"%Y-%m-%d"}
                            {if $mod_rank == "Supermod" || $mod_rank == "Admin"}
                                <br><a href='?id={$smarty.get.id}&act=check_user&postIDs={$tavern[$i]['id']}&uid={$userid}&perform=deletepost'>Delete Comment</a>
                            {/if}
                        </td>
                        <td>{$tavern[$i]["moderator"]}</td>
                        <td>{$tavern[$i]["message"]}</td>
                    </tr>                                
                {/for}
            {else}
                <tr>
                    <td colspan="4">No comments on this user</td>
                </tr>
            {/if}
            <tr>
                <td style="border-top:1px solid #000000;" colspan="3">
                    <form id="form1" name="form1" method="post" action="?id={$smarty.get.id}&act=postcomment&userIDs={$userid}">
                    <textarea name="message" cols="35" rows="3" id="message"></textarea><br>
                    <input type="submit" name="POSTCOMMENT" value="Upload Comment" />
                    </form>
                </td>
            </tr>
        </table>
    {/if}                  
</div>