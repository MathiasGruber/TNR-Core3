<div align="center">
    {if isset($modLogData)}
        {$subSelect="modLogData"}
        {include file="file:{$absPath}/{$modLogData}" title="modLogData"}
    {/if}
    
    {if isset($userReports)}
        {$subSelect="userReports"}
        {include file="file:{$absPath}/{$userReports}" title="userReports"}
    {/if}
    
    {if isset($changes)}
        {$subSelect="changes"}
        {include file="file:{$absPath}/{$changes}" title="Name changes"}
    {/if}
    
    {if isset($extraNotices) && extraNotices != ""}
        <table width="95%" class="table">
            <tr>
                <td colspan="4" class="subHeader">User Information</td>
            </tr>
            <tr>
                <td colspan="4" class="tdBorder">{$extraNotices} </td>
            </tr>
             <tr>
                <td colspan="4"><a href="?id={$smarty.get.id}&act=ryoLog&uid={$userid}">Ryo log</a></td>
            </tr>
        </table>
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
                            {if isset($canDeleteStuff) && $canDeleteStuff == true}
                                <br><a href='?id={$smarty.get.id}&act=userNotes&postID={$tavern[$i]['id']}&perform=deletepost'>Delete Comment</a>
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
                    <form id="form1" name="form1" method="post" action="?id={$smarty.get.id}&act=userNotes&uid={$userid}">
                    <textarea name="message" cols="35" rows="3" id="message"></textarea><br>
                    <input type="submit" name="POSTCOMMENT" value="Upload Comment" />
                    </form>
                </td>
            </tr>
        </table>
    {/if}                  
</div>