<tr>
    <td>
        <headerText screenWidth="true">Inbox System</headerText>
    </td>
</tr>

<tr>
{if $message[0]['sender'] != 'System'}
    <td><a href="?id={$smarty.get.id}&amp;act=reply&amp;pmid={$smarty.get.pmid}">Reply</a></td>
{/if}
    <td><a href="?id={$smarty.get.id}&amp;act=delete&amp;pmid={$smarty.get.pmid}">Delete message</a></td>
</tr>

<tr>
    <td>
        <headerText screenWidth="true">Message Information</headerText>
    </td>
</tr>

<tr>
    <td>
<text>
Sender: {$message[0]['sender_color']}<br>
Sent at: {{$message[0]['parsed_time']}} ({$message[0]['parsed_pm_time']})<br>
Subject: {$message[0]['subject']}
</text>
    </td>
</tr>

<tr>
    <td>
        <headerText screenWidth="true">Message</headerText>
    </td>
</tr>
<tr>
    <td>
        <text>{$message[0]['message']}</text>
    </td>
</tr>

<tr>
    <td>
        <a href="?id=53&amp;act=pm&amp;pmid={$message[0]['time']}&amp;uid={$message[0]['sender_uid']}">Report User</a>
    </td>
    <td>
        <a href="?id=13&amp;page=profile&amp;name={$message[0]['sender']}">View Profile</a>
    </td>
</tr>

<tr>
    <td>
        <text>TheNinja-RPG staff will <b>NEVER</b> ask for your password!</text>
    </td>
</tr>
<tr>
    <td>
        <a href="?id={$smarty.get.id}">Return</a>
    </td>
</tr>