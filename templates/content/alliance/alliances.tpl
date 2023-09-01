<div align="center">
    <table class="table" width="95%">
        <tr>
            <td colspan="{count($allianceData)+1}" class="subHeader">Alliances</td>
        </tr>
        <tr>
            <td class="tableColumns"><b>Village</b></td>
            {foreach $allianceData['Syndicate'] as $ally}
                <td class="tableColumns">
                    <b>{$ally['village']}</b>
                </td>
            {/foreach}
        </tr>
        {foreach $allianceData as $key => $ally}
            <tr>
                <td><b>{$key}</b></td>
                {foreach $ally as $entries}
                    <td width="100">
                        <b>{$entries['status']}</b>
                    </td>
                {/foreach}
            </tr>
        {/foreach}    
    </table>
</div>