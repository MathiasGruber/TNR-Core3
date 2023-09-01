<script type="text/javascript">
$(document).ready(function() {
    var Report_IDs = new Array("Unviewed", "My", "In_Progress");
    
    for(var i = 0; i < Report_IDs.length; i++) {
        $('#'+Report_IDs[i].toLowerCase()+'_reports').hide();
        $('#'+Report_IDs[i]).hover(function() {
            $(this).css('background-image', 
                'url(http://www.theninja-rpg.com/files/layout_default/images/ribbon_links.png)');
            return false;
        }, function() {
            $(this).css('background-image', 
                'url(http://www.theninja-rpg.com/files/layout_default/images/bg_subheader.png)');
            return false;
        });
        $('#'+Report_IDs[i]).click(function() {
            $('#'+$(this).attr('id').toLowerCase()+'_reports').fadeToggle();
            return false;
        });
    }
});
</script>

<div align="center"><br>
    <table width="95%" class="table">
        <tr>
            <td height="30px" colspan="4" class="subHeader" id="Unviewed"><font size="3">Unviewed Reports</font></td>
        </tr>
        <tr id="unviewed_reports" style="display:none;">
            {if $unviewed != "0 rows"}
                <td colspan="4" style="padding: 0px; margin: 0px;">
                    <table width="100%" style="padding: 0px; margin: 0px; border-collapse: collapse;">
                        <div align="center">
                            <tr>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">User</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Reporter</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Type</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Show Details</td>
                            </tr>
                            {for $i = 0 to ($unviewed|@count)-1} 
                                <tr class="row{($i % 2) + 1}">
                                    <td width="25%" align="center">{$unviewed[$i]['nname']}</td>
                                    <td width="25%" align="center">{$unviewed[$i]['rname']}</td>
                                    <td width="25%" align="center">{$unviewed[$i]['type']}</td>
                                    <td  width="25%"align="center">
                                        <a href="?id={$smarty.get.id}&act=reports&page=details&uid={$unviewed[$i]['uid']}&time={$unviewed[$i]['time']}&rid={$unviewed[$i]['rid']}">Details</a>
                                    </td>
                                </tr>
                            {/for}
                        </div>
                    </table>
                </td>
            {else}
                <td colspan="4" style="padding:0px;margin:0px;">
                    <table width="100%" style="padding: 0px; margin: 0px; border-collapse: collapse;">
                        <br><div align="center"><font size="2"><b>There are no reports to show.</b></font></div><br>
                    </table>
                </td>
            {/if}
        </tr>
    </table><br>
    <table width="95%" class="table">
        <tr>
        <td height="30px" colspan="4" class="subHeader" id="My"><font size="3">Your Reports</font></td>
        </tr>
        <tr id="my_reports" style="display:none;">
            {if $my != "0 rows"}
                <td colspan="4" style="padding: 0px; margin: 0px;">
                    <table width="100%" style="padding: 0px; margin: 0px; border-collapse: collapse;">
                        <div align="center">
                            <tr>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">User</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Reporter</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Type</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Show Details</td>
                            </tr>
                            {for $i = 0 to ($my|@count)-1} 
                                <tr class="row{($i % 2) + 1}">
                                    <td width="25%" align="center">{$my[$i]['nname']}</td>
                                    <td width="25%" align="center">{$my[$i]['rname']}</td>
                                    <td width="25%" align="center">{$my[$i]['type']}</td>
                                    <td width="25%" align="center">
                                        <a href="?id={$smarty.get.id}&act=reports&page=details&uid={$my[$i]['uid']}&time={$my[$i]['time']}&rid={$my[$i]['rid']}">Details</a>
                                    </td>
                                </tr>
                            {/for}
                        </div>
                    </table>
                </td>
            {else}
                <td colspan="4" style="padding:0px;margin:0px;">
                    <table width="100%" style="padding: 0px; margin: 0px; border-collapse: collapse;">
                        <br><div align="center"><font size="2"><b>There are no reports to show.</b></font></div><br>
                    </table>
                </td>
            {/if}
        </tr>
    </table><br>
    <table width="95%" class="table">
        <tr>
            <td height="30px" colspan="4" class="subHeader" id="In_Progress"><font size="3">Ongoing Reports</font></td>
        </tr>
        <tr id="in_progress_reports" style="display:none;">
            {if $in_progress != "0 rows"}
                <td colspan="4" style="padding: 0px; margin: 0px;">
                    <table width="100%" style="padding: 0px; margin: 0px; border-collapse: collapse;">
                        <div align="center">
                            <tr>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">User</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Reporter</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Type</td>
                                <td width="25%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Show Details</td>
                            </tr>
                            {for $i = 0 to ($in_progress|@count)-1} 
                                <tr class="row{($i % 2) + 1}">
                                    <td width="25%" align="center">{$in_progress[$i]['nname']}</td>
                                    <td width="25%" align="center">{$in_progress[$i]['rname']}</td>
                                    <td width="25%" align="center">{$in_progress[$i]['type']}</td>
                                    <td width="25%" align="center">
                                        <a href="?id={$smarty.get.id}&act=reports&page=details&uid={$in_progress[$i]['uid']}&time={$in_progress[$i]['time']}&rid={$in_progress[$i]['rid']}">Details</a>
                                    </td>
                                </tr>
                            {/for}				
                        </div>
                    </table>
                </td>
            {else}
                <td colspan="4" style="padding:0px;margin:0px;">
                    <table width="100%" style="padding: 0px; margin: 0px; border-collapse: collapse;">
                        <br><div align="center"><font size="2"><b>There are no reports to show.</b></font></div><br>
                    </table>
                </td>
            {/if}
        </tr>
    </table><br>
    <a href="?id={$smarty.get.id}"><font size="2">Return</font></a><br>
</div>