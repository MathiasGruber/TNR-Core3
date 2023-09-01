<div class="page-box">
    <div class="page-title">
        War Status
    </div>
    <div class="page-content">
        <div class="page-sub-title-top">
            Structures Levels <span class=" toggle-button-info closed" data-target="#structure-levels-info"/>
        </div>
        <div class="toggle-target closed" id="structure-levels-info">
            These are the current levels of the structures/buildings in your village. Updated every 5 min.
        </div>
        <div class="table-grid table-column-6">
            <div class="lazy table-legend row-header column-1">
				Extra Anbu
            </div>

            <div class="lazy table-legend row-header column-2">
				Hospital
            </div>

            <div class="lazy table-legend row-header column-3">
				Shop
            </div>

            <div class="lazy table-legend row-header column-4">
				Regen
            </div>

            <div class="lazy table-legend row-header column-5">
				Rob Defences
            </div>

            <div class="lazy table-legend row-header column-6">
				Wall Defences
            </div>



            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-1">
                Extra Anbu
            </div>
            <div class="lazy table-cell table-alternate-1 column-1 row-1">
                {$villageVars['anbu_bonus_level']}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-2">
                Hospital
            </div>
            <div class="lazy table-cell table-alternate-1 column-2 row-1">
                lvl. {$villageVars['hospital_level']}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-3">
                Shop
            </div>
            <div class="lazy table-cell table-alternate-1 column-3 row-1">
                lvl. {$villageVars['shop_level']}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-4">
                Regen
            </div>
            <div class="lazy table-cell table-alternate-1 column-4 row-1">
                lvl. {$villageVars['regen_level']}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-5">
                Rob Defences
            </div>
            <div class="lazy table-cell table-alternate-1 column-5 row-1">
                lvl. {$villageVars['wall_rob_level']}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-6">
                Wall Defences
            </div>
            <div class="lazy table-cell table-alternate-1 column-6 row-1">
                lvl. {$villageVars['wall_def_level']}
            </div>

        </div>

        <div class="page-sub-title">
            Village Standing <span class="toggle-button-info closed" data-target="#village-standing-info"/>
        </div>
        <div class="toggle-target closed" id="village-standing-info">
            Your village is currently in war with {$warringVillages} village(s).
        </div>
        <div class="table-grid table-column-6">
            {foreach $allianceData as $key => $ally}
                <div class="lazy table-legend row-header column-{$key}">
				    {$ally['village']}
                </div>
            {/foreach}

            {foreach $allianceData as $key => $ally}
                <div class="lazy table-legend-mobile table-alternate-1 row-1 column-{$key}">
                    {$ally['village']}
                </div>
                <div class="lazy table-cell table-alternate-1 column-{$key} row-1">
                    {$ally['status']}
                </div>
            {/foreach}
        </div>

        <div class="page-sub-title-no-margin">
            Your village is not currently in war with anyone.
        </div>
    </div>
</div>    