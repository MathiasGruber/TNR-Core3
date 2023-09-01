<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">Outbox System</td>
        </tr>
        <tr>
            <td colspan="2">
                <a class="showTableTopLink" href="?id={$smarty.get.id}&act=delete&pmid={$smarty.get.pmid}">Delete message</a>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="tableColumns">Message Information</td>
        </tr>
        <tr>
            <td width="15%">Receiver:</td>
            <td width="85%">{$message[0]['receiver_color']}</td>
        </tr>
        <tr>
            <td>Received at:</td>
            <td>{{$message[0]['parsed_time']}} ({$message[0]['parsed_pm_time']})</td>
        </tr>
        <tr>
            <td style="padding-left:5px;">Subject:</td>
            <td>{$message[0]['subject']}</td>
        </tr>
        <tr>
            <td colspan="2" class="tableColumns">Message</td>
        </tr>
        <tr>
            <td colspan="2" style="padding:10px;">{$message[0]['message']}</td>
        </tr>
        <tr>
            <td colspan="2" style="padding-bottom:5px;">
                <a href="?id=13&page=profile&name={$message[0]['receiver']}">
                    <img src="./images/profile.png" alt="View profile" style="border:none;"></a></td>
        </tr>
    </table>
    <font size="1">TheNinja-RPG staff will <b>NEVER</b> ask for your password!</font><br>
    <a href="?id={$smarty.get.id}" class="returnLink">Return</a>
</div>