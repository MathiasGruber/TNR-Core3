<div class="page-box">
    <div class="page-title">
        Alliances
    </div>
    <div class="page-content">
        <div class="table-grid table-break-early-column-{count($allianceData)+1}">
            <div class="lazy table-break-early-legend row-header column-0">
                <b>Village</b>
            </div>

            {foreach $allianceData['Syndicate'] as $key => $ally}
                <div class="lazy table-break-early-legend row-header column-{$key+1} row-0">
                    <b>{$ally['village']}</b>
                </div>
            {/foreach}

            {$row = 1}
            {foreach $allianceData as $key => $ally}
                <div class="lazy table-break-early-legend row-header column-0 row-{$key}">
                    <b>{$key}</b>
                </div>

                {$column = 1}
                {foreach $ally as $entries}
                    <div class="lazy table-break-early-legend-mobile table-alternate-{$row % 2 + 1} row-{$row} column-{$column}">
                        {$key} & {$allianceData['Syndicate'][$column-1]['village']}
                    </div>

                    <div class="lazy table-cell table-alternate-{$row % 2 + 1} column-{$column} row-{$row}">
                        <b>{$entries['status']}</b>
                    </div>
                    {$column = $column + 1}
                {/foreach}
                {$row = $row + 1}
            {/foreach} 
        </div>
    </div>
</div>