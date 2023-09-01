<div class="page-box">

    <div class="page-title">
        {$user_data['village']} Status
    </div>
    
    <div class="page-content">

        <div class="page-grid page-column-min-left-2">

            <div>
                Symbol<br/>
                <img style="border: 2px solid #000000;" src="./images/villages/{$user_data['name']}.gif" width="100" height="100" />
            </div>

            <div class="stiff-grid stiff-column-3 page-grid-justify-stretch">
                <div>

                </div>

                <div>
                    Total
                </div>

                <div>
                    Active
                </div>



                <div>
                    <b>Academy students:</b>
                </div>

                <div>
                    {$total['as_count']}
                </div>

                <div>
                    {$active['as_count']}
                </div>



                <div>
                    <b>Genin:</b>
                </div>

                <div>
                    {$total['genin_count']}
                </div>

                <div>
                    {$active['genin_count']}
                </div>



                <div>
                    <b>Chuunin:</b>
                </div>

                <div>
                    {$total['chuunin_count']}
                </div>

                <div>
                    {$active['chuunin_count']}
                </div>



                <div>
                    <b>Jounin:</b>
                </div>

                <div>
                    {$total['jounin_count']}
                </div>

                <div>
                    {$active['jounin_count']}
                </div>



                <div>
                    <b>Elite Jounin:</b>
                </div>

                <div>
                    {$total['sj_count']}
                </div>

                <div>
                    {$active['sj_count']}
                </div>

            </div>

        </div>

        <div class="table-grid table-column-4">

            <div class="lazy table-legend row-header column-1">Supplies</div>
            <div class="lazy table-legend row-header column-2">Bonuses</div>     
            <div class="lazy table-legend row-header column-3">Village</div>
            <div class="lazy table-legend row-header column-4">Other</div>



            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-1">
                Supplies
            </div>

            <div class="lazy column-1 row-1">
                <div class="table-alternate-1">
                    <b>Hospital:</b> <a href="?id=9&act=hospitalSupply">{$hospitalSupply}/{$active['as_count'] + $active['genin_count'] + $active['chuunin_count'] + $active['jounin_count'] + $active['sj_count']}</a>
                </div>
                <div class="table-alternate-2">
                    <b>Ramen:</b> <a href="?id=9&act=ramenSupply">{$ramenSupply}/{$active['as_count'] + $active['genin_count'] + $active['chuunin_count'] + $active['jounin_count'] + $active['sj_count']}</a>
                </div>
            </div>



            <div class="lazy table-legend-mobile table-alternate-2 row-1 column-2">
                Bonuses
            </div>

            <div class="lazy column-2 row-1">
                <div class="table-alternate-1">
                    <b>Hospital:</b> {$hospitalBonus}
                </div>
                <div class="table-alternate-2">
                    <b>Ramen:</b> {$ramenBonus}
                </div>
            </div>



            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-3">
                Village
            </div>

            <div class="lazy table-alternate-1 column-3 row-1">
                <div class="table-alternate-1">
                    <b>Village Funds:</b> {$user_data['points']}
                </div>
                <div class="table-alternate-2">
                    <br/>
                </div>
            </div>



            <div class="lazy table-legend-mobile table-alternate-2 row-1 column-4">
                Other
            </div>

            <div class="lazy table-alternate-1 column-4 row-1">
                <div class="table-alternate-1">
                    <b>{$leaderName|capitalize}:</b> {$user_data['leader']}
                </div>
                <div class="table-alternate-2">
                    <b>Avg. PvP Exp:</b> {$user_data['avg_pvp']}
                </div>
            </div>

        </div>

        {if isset($DamageBonus)}
            <div>
                <b>Damage bonus:</b> {$DamageBonus}
            </div>
        {/if}

    </div>

</div>
