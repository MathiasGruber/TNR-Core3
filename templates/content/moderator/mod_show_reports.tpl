<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="4" class="subHeader">{$report_type}</td>
        </tr>
        <tr>
            <td style="border-bottom:1px solid #000000;font-weight:bold;">User:</td>
            <td style="border-bottom:1px solid #000000;font-weight:bold;">Report by: </td>
            <td style="border-bottom:1px solid #000000;font-weight:bold;">Type:</td>
            <td style="border-bottom:1px solid #000000;font-weight:bold;">Show details:</td>
        </tr>
        {for $i = 0 to ($data|@count)-1} 
            <tr class="row{($i % 2) + 1}">
                <td width="25%" align="center">{$data[$i]['nname']}</td>
                <td width="25%" align="center">{$data[$i]['rname']}</td>
                <td width="25%" align="center">{$data[$i]['type']}</td>
                <td width="25%" align="center">
                    <a href="?id={$smarty.get.id}&act=reports&page=details&uid={$data[$i]['uid']}&time={$data[$i]['time']}&rid={$data[$i]['rid']}">Details</a>
                </td>
            </tr>
        {/for}
    </table>
</div>