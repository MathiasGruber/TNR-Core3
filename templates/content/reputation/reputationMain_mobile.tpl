{if isset($reputation)}
    {$subSelect="reputation"}
    {include file="file:{$absPath}/{$reputation|replace:'.tpl':'_mobile.tpl'}" title="User Reputation"}
{/if}

<tr>
    <td>
        <headerText screenWidth="true">Members / Village</headerText>
    </td>
</tr>
<tr color="darkgrey">
    <td><text color="white"><b></b></text></td>
    <td><text color="white"><b>Konoki</b></text></td>
    <td><text color="white"><b>Silence</b></text></td>
    <td><text color="white"><b>Samui</b></text></td>
    <td><text color="white"><b>Shine</b></text></td>
    <td><text color="white"><b>Shroud</b></text></td>
</tr>
<tr color="grey">
    <td><text><b>Total</b></text></td>
    <td><text><b>{$memberCount.konoki}</b></text></td>
    <td><text><b>{$memberCount.silence}</b></text></td>
    <td><text><b>{$memberCount.samui}</b></text></td>
    <td><text><b>{$memberCount.shine}</b></text></td>
    <td><text><b>{$memberCount.shroud}</b></text></td>
</tr>
<tr color="white">
    <td><text><b>Active</b></text></td>
    <td><text><b>{$memberCount.akonoki}</b></text></td>
    <td><text><b>{$memberCount.asilence}</b></text></td>
    <td><text><b>{$memberCount.asamui}</b></text></td>
    <td><text><b>{$memberCount.ashine}</b></text></td>
    <td><text><b>{$memberCount.ashroud}</b></text></td>
</tr>

{include file="file:{$absPath}/templates/message_mobile.tpl" title="User Reputation"}
