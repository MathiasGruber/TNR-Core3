<div class="page-box">
    <div class="page-title">
        Diplomacy
    </div>
    <div class="page-content">
        <div>
            {if isset($reputation)}
                {$first=true}
                {$subSelect="reputation"}
                {include file="file:{$absPath}/{$reputation}" title="User Reputation"}
            {/if}
        </div>

        <div>
            <div class="page-sub-title">
                Members / Village
            </div>

            <div class="table-grid table-break-early-column-6">

                <div class="lazy table-break-early-legend row-header column-1 row-0">
                </div>

                <div class="lazy table-break-early-legend row-header column-2 row-0">
                    <b>
                        Konoki
                    </b>
                </div>

                <div class="lazy table-break-early-legend row-header column-3 row-0">
                    <b>
                        Silence
                    </b>
                </div>

                <div class="lazy table-break-early-legend row-header column-4 row-0">
                    <b>
                        Samui
                    </b>
                </div>

                <div class="lazy table-break-early-legend row-header column-5 row-0">
                    <b>
                        Shine
                    </b>
                </div>

                <div class="lazy table-break-early-legend row-header column-6 row-0">
                    <b>
                        Shroud
                    </b>
                </div>

                <div class="lazy table-break-early-legend row-header column-1 row-1">
                    <b>Total</b>
                </div>

                <div class="lazy table-cell table-alternate-1 column-2 row-1">
                    {$memberCount.konoki}
                </div>

                <div class="lazy table-cell table-alternate-1 column-3 row-1">
                    {$memberCount.silence}
                </div>

                <div class="lazy table-cell table-alternate-1 column-4 row-1">
                    {$memberCount.samui}
                </div>

                <div class="lazy table-cell table-alternate-1 column-5 row-1">
                    {$memberCount.shine}
                </div>

                <div class="lazy table-cell table-alternate-1 column-6 row-1">
                    {$memberCount.shroud}
                </div>



                <div class="lazy table-break-early-legend row-header column-1 row-2">
                    <b>Active</b>
                </div>

                <div class="lazy table-cell table-alternate-2 column-2 row-2">
                    {$memberCount.akonoki}
                </div>

                <div class="lazy table-cell table-alternate-2 column-3 row-2">
                    {$memberCount.asilence}
                </div>

                <div class="lazy table-cell table-alternate-2 column-4 row-2">
                    {$memberCount.asamui}
                </div>

                <div class="lazy table-cell table-alternate-2 column-5 row-2">
                    {$memberCount.ashine}
                </div>

                <div class="lazy table-cell table-alternate-2 column-6 row-2">
                    {$memberCount.ashroud}
                </div>

            </div>
        </div>

        <div>
            <br/>
            {include file="file:{$absPath}/templates/message_mf.tpl" title="User Reputation"}
        </div>
    </div>
</div>