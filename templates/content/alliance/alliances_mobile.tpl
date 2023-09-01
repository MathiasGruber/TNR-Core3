<doStretch></doStretch>

<tr>
    <td>
        <headerText>Alliances</headerText>
    </td>
</tr>
<tr color="fadedblack">
    <td><text color="white"><b>Village</b></text></td>
    {foreach $allianceData['Syndicate'] as $ally}
        <td><text color="white"><b>{$ally['village']}</b></text></td>
    {/foreach}    
</tr>

{foreach $allianceData as $key => $ally}
    <tr color="{cycle values="dim,clear"}">
        <td><text><b>{$key}</b></text></td>
        {foreach $ally as $entries}
            <td><text>{$entries['status']}</text></td>
        {/foreach}
    </tr>
{/foreach}