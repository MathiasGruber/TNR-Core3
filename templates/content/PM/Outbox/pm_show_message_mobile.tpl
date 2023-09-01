<tr>
    <td>
        <headerText screenWidth="true">Outbox System</headerText>
    </td>
</tr>

<tr>
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
Receiver: {$message[0]['receiver_color']}<br>
Received at: {{$message[0]['parsed_time']}} ({$message[0]['parsed_pm_time']})<br>
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
        <a href="?id=13&amp;page=profile&amp;name={$message[0]['receiver']}">View Profile</a>
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
