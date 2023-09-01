<div class="page-box">

    <div class="page-title">
        Village Upgrades
    </div>

    <div class="page-content">
        <div class="page-sub-title-top">
            Current funds: {$totalPoints}. Prices are based on {$totalTerritories} owned territories.
        </div>

        <form name="form1" method="post" action="" class="table-grid table-column-4">
            <!--legend-->
            <div class="table-legend row-header column-1">
                Upgrade Name
            </div>
            <div class="table-legend row-header column-2">
                Level
            </div>
            <div class="table-legend row-header column-3">
                Buy
            </div>
            <div class="table-legend row-header column-4">
                Sell
            </div>
            <!--legend-->

            {$i = 0}
            {foreach $updates as $update => $value}

                <div class="table-legend-mobile row-header column-1 row-{$i} table-alternate-{($i % 2) + 1}">
                Upgrade Name
                </div>
            
                <div class="table-cell column-1 row-{$i} table-alternate-{($i % 2) + 1}">
                    {$value['name']}
                </div>



                <div class="table-legend-mobile row-header column-2 row-{$i} table-alternate-{($i % 2) + 1}}">
                    Level
                </div>

                <div class="table-cell column-2 row-{$i} table-alternate-{($i % 2) + 1}">
                    {if $value['lvl']>0}
                        {$value['lvl']}
                    {else}
                        0
                    {/if}
                </div>



                <div class="table-legend-mobile row-header column-3 row-{$i} table-alternate-{($i % 2) + 1}}">
                    Upgrade Price
                </div>

                <div class="table-cell column-3 row-{$i} table-alternate-{($i % 2) + 1} stiff-grid stiff-column-2 page-grid-justify-stretch">
                    {if {$value['price']} > 0}
                        <div class="text-left" style="width:100%">{$value['price']} :</div><div class="text-right"><input type="radio" name="radio" value="{$update}_up"></div>
                    {else}
                        <div class="text-left" style="width:100%">N/A :</div><div class="text-right"><input type="radio" name="radio" value="{$update}_up" disabled></div>
                    {/if}
                </div>



                <div class="table-legend-mobile row-header column-4 row-{$i} table-alternate-{($i % 2) + 1}} stiff-grid stiff-column-2 page-grid-justify-stretch">
                    Downgrade Refund
                </div>

                <div class="table-cell column-4 row-{$i} table-alternate-{($i % 2) + 1}">
                    {if {$value['lvl']} > 0 && {$value['down']} > 0}
                        <div class="text-left" style="width:100%">{$value['down']} :</div><div class="text-right"><input type="radio" name="radio" value="{$update}_down"></div>
                    {else}
                        <div class="text-left" style="width:100%">N/A :</div><div class="text-right"><input type="radio" name="radio" value="{$update}_down" disabled></div>
                    {/if}
                </div>

                {$i = $i + 1}
            {/foreach}


            <!--submit-->
            <input type="submit" name="Submit" id="button" value="Submit" class="span-4 page-button-fill">
            <!--submit-->
        </form>
    </div>
</div>


            

