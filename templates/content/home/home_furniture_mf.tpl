<div class="page-box">
    <div class="page-title"> 
        Furniture: {$slots['used']} / {$slots['max']}
    </div>
    <div class="page-content">
		<form action="?id=23&amp;act=furniture" method="post">
            <div class="table-grid table-column-9 font-shrink-early">

                <div class="lazy table-legend row-header column-1" >Name</div>
                <div class="lazy table-legend row-header column-2" >Size</div>  
                <div class="lazy table-legend row-header column-3" >Storage</div>
                <div class="lazy table-legend row-header column-4" >Type</div>
                <div class="lazy table-legend row-header column-5" >Price</div>
                <div class="lazy table-legend row-header column-6" >Owned</div>
                <div class="lazy table-legend row-header column-7" >Quantity</div>
                <div class="lazy table-legend row-header column-8" >Buy</div>
                <div class="lazy table-legend row-header column-9" >Sell</div>

                {assign var=column_flag value=false}
                {foreach $furniture as $row_key => $item}
                
                    {if ($item["event_furniture"] && $item["owned"] == 0) || ($item['event_furniture'] && $item['event_start'] < time() && $item['event_end'] > time()) }
                        {continue}
                    {/if}

                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-1">
                        Name
                    </div>

		            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-1" >
                        {$item["name"]}
                    </div>

                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-2">
                        Size
                    </div>

		            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-2">
                        {$item["size"]}
                    </div>

                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-3">
                        Storage
                    </div>

		            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-3">
                        {$item["storage"]}
                    </div>

                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-4">
                        Type
                    </div>

		            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-4">
                        {$item["storage_type"]}
                    </div>

                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-5">
                        Price
                    </div>

                    <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-5">
                        {if $item["event_furniture"]}
                            {$item["sale_value"]}
                        {else}
                            {$item["price"]}
                        {/if}
                    </div>
                  
                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-6">
                        Owned
                    </div>

		            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-6">
                        {$item["owned"]} / {$item["max_owned"]}
                    </div>
        
                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-7">
                        Quantity
                    </div>

		            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-7">
                        <input type="number" name="quantity" style="width: 2.7em;" value="1" min="1" max="5"/>
                    </div>

                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-8">
                        Buy
                    </div>

                    <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-8">
                        {if $item["event_furniture"]}
                            n/a
                        {else}
		                    <input type="submit" name="buy_button" style="text-indent: -9000px; text-transform: capitalize; width: 25px" value="{$item['id']}"/>
                        {/if}
                    </div>

                    <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-9">
                        Sell
                    </div>
                    
		            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-9">
                        <input type="submit" name="sell_button" style="text-indent: -9000px; text-transform: capitalize; width: 25px" value="{$item['id']}"/>
                    </div>

                {/foreach}
            </div>
		</form>

        {if $event_active}
            <div class="page-sub-title">
                Event Furniture
            </div>

            <form action="?id=23&amp;act=furniture" method="post">
                <div class="table-grid table-column-9 font-shrink-early">

                    <div class="lazy table-legend row-header column-1" >Name</div>
                    <div class="lazy table-legend row-header column-2" >Size</div>  
                    <div class="lazy table-legend row-header column-3" >Storage</div>
                    <div class="lazy table-legend row-header column-4" >Type</div>
                    <div class="lazy table-legend row-header column-5" >Price</div>
                    <div class="lazy table-legend row-header column-6" >Owned</div>
                    <div class="lazy table-legend row-header column-7" >Quantity</div>
                    <div class="lazy table-legend row-header column-8" >Buy</div>
                    <div class="lazy table-legend row-header column-9" >Sell</div>

                    {assign var=column_flag value=false}
                    {foreach $furniture as $row_key => $item}
                    
                        {if !$item["event_furniture"]}
                            {continue}
                        {/if}

                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-1">
                            Name
                        </div>

		                <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-1" >
                            {$item["name"]}
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-2">
                            Size
                        </div>

		                <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-2">
                            {$item["size"]}
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-3">
                            Storage
                        </div>

		                <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-3">
                            {$item["storage"]}
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-4">
                            Type
                        </div>

		                <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-4">
                            {$item["storage_type"]}
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-5">
                            Price
                        </div>

                        <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-5">
                            {if !is_numeric($item["price"])}
                                {str_replace(':',' (',str_replace(';','), <br>', $item["price"]))})
                            {else}
                                {$item["price"]}
                            {/if}
                        </div>
                      
                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-6">
                            Owned
                        </div>

		                <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-6">
                            {$item["owned"]} / {$item["max_owned"]}
                        </div>
            
                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-7">
                            Quantity
                        </div>

		                <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-7">
                            <input type="number" name="quantity" style="width: 2.7em;" value="1" min="1" max="5"/>
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-8">
                            Buy
                        </div>

                        <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-8">
		                    <input type="submit" name="buy_button" style="text-indent: -9000px; text-transform: capitalize; width: 25px" value="{$item['id']}"/>
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-9">
                            Sell
                        </div>
                        
		                <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-9">
                            <input type="submit" name="sell_button" style="text-indent: -9000px; text-transform: capitalize; width: 25px" value="{$item['id']}"/>
                        </div>

                    {/foreach}
                </div>
		    </form>
        {/if}
    </div>
</div>