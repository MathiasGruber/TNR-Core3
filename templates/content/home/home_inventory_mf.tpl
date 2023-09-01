<form action="?id=23&amp;act=selection" method="post">
    <div class="lazy page-box">
        <div class="lazy page-title">
            {if $storage_box_mode}
              Storage Box
            {else if $syndicate_mode}
              Camp {count($item_array['anything'])}/{$storage['anything']}
            {else}
              Home Inventory
            {/if}
            <span class="toggle-button-info closed" data-target="home-inventory-info"/>
        </div>
        <div class="lazy page-content">
            <div class="toggle-target closed" id="home-inventory-info">
                {if $storage_box_mode}
		        	Welcome to your storage box!<br>
                    When you buy a new home you will be able to<br>find your storage box in your new home inventory!
                {else if $syndicate_mode}
		        	Your secret stash of goodies.
                {else}
		        	Welcome to your home inventory!<br>Your home inventory currently holds {$totals['current']} out of {$totals['max']} items
                {/if}
                <br/>
                <br/>
            </div>

            {$first = true}
            {foreach $item_array as $type_name => $type_collection}
    
	            {if count($type_collection) != 0}
      
                    {if !$syndicate_mode}
    	      	  	    <div class="page-sub-title{if $first}-top{/if} toggle-button-drop {if $collapse_home != 'no'}closed{/if}" data-target="#{$type_name}">
                            {ucfirst($type_name)} {count($type_collection)}/{$storage[$type_name]}
                        </div>
                        {$first = false}
                    {/if}

                    <div class="table-grid table-column-5 font-shrink-early toggle-target {if $collapse_home != 'no' && !$syndicate_mode}closed{/if}" id="{$type_name}">

		    	  		<div class="lazy table-legend row-header column-1">
                            Name
                        </div>
		    	  		<div class="lazy table-legend row-header column-2">
                            Type
                        </div>

		    	  		<div class="lazy table-legend row-header column-3">
                            Durability
                        </div>

                        <div class="lazy table-legend row-header column-4">
                            Action
                        </div>

                        <div class="lazy table-legend row-header column-5">
                            <label>
                                Select <input type="checkbox" name="selectAll{$type_name}" class="selectAll{$type_name}"/>
                            </label>
                        </div>

		    	  	    {foreach $type_collection as $row_key => $item}

                            <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-1">
                                Name
                            </div>

                            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} column-1 row-{$row_key}">
		    	    	        <a class="showTableLink" href="?id=23&act=details&inv_id={$item["id"]}">{$item["name"]}</a>
                            </div>
      


                            <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-2">
                                Type
                            </div>

                            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} column-2 row-{$row_key}">
                                {if $item['type'] != 'armor'}
                                    {ucfirst($item['type'])}
                                {else}
                                    Armor:{ucfirst($item['armor_types'])}
                                {/if}
                            </div>
      


                            <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-3">
                                Durability
                            </div>

                            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} column-3 row-{$row_key}">
                                {if $item["type"] == "armor" || $item["type"] == 'weapon'}
                                    ({round($item["durabilityPoints"])}/{$item["max_durability"]})
                                {else}
                                    n/a
                                {/if}
                            </div>



                            <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-4">
                                Action
                            </div>

                            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} column-4 row-{$row_key}">
                                  {$item['action']}
                            </div>



                            <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-5">
                                <label>
                                    Select <input type="checkbox" name="selectAll{$type_name}" class="selectAll{$type_name}"/>
                                </label>
                            </div>

                            <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} column-5 row-{$row_key}">
                                <input class="selectGroup{$type_name}" name="inventoryIDs[{$item['id']}]" value="{$item['id']}" type="checkbox">
                            </div>

		    	  		{/foreach}
                    </div>
		    	{/if}

            {/foreach}

            {if isset($storage_box) && is_array($storage_box) && count($storage_box) > 0 && !$syndicate_mode}
    
                <div class="page-sub-title toggle-button-drop {if $collapse_home != 'no'}closed{/if}" data-target="#storage-box">
                    Storage Box <span class="toggle-button-info closed" data-target="#storage-box-information"/>
                </div>

                <div class="toggle-target closed" id="storage-box-information">
					{if !$syndicate_mode && !$storage_box_mode}
                        The Storage box, a nifty place that will store all your goods. However you'll need to pay a fee to withdraw items.
					{else if $syndicate_mode}
                        The Storage box, a nifty place that will store all your goods. Protected from all outlaws, meaning only villagers can withdraw items here.
					{else}
                        The Storage box, a nifty place that will store all your goods. However you'll need to pay a fee to withdraw items. You will need a house to withdraw items.
					{/if}
                </div>

                <div class="table-grid table-column-6 font-shrink-early toggle-target {if $collapse_home != 'no'}closed{/if}" id="storage-box">

                    <div class="lazy table-legend row-header column-1">
                        Name
                    </div>

                    <div class="lazy table-legend row-header column-2">
                        Type
                    </div>

                    <div class="lazy table-legend row-header column-3">
                        Durability
                    </div>

                    <div class="lazy table-legend row-header column-4">
                        Action
                    </div>

                    <div class="lazy table-legend row-header column-5">
                        Take Out Cost
                    </div>

                    <div class="lazy table-legend row-header column-6">
                        <label>
                            Select <input type="checkbox" name="selectAllStorage" class="selectAllStorage"/>
                        </label>
                    </div>

                    {foreach $storage_box as $row_key => $item}

                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-1">
                            Name
                        </div>

                        <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-1">
                            {if $item['type'] != 'furniture'}
                                <a class="showTableLink" href="?id=23&act=details&inv_id={$item["id"]}">{$item["name"]}</a>
                            {else}
                                {$item["name"]}
                            {/if}
                        </div>



                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-2">
                            Type
                        </div>

                        <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-2">
                            {if $item['type'] != 'armor'}
                                {ucfirst($item['type'])}
                            {else}
                                Armor:{ucfirst($item['armor_types'])}
                            {/if}
                        </div>



                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-3">
                            Durability
                        </div>

                        <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-3">
                            {if $item["type"] == "armor" || $item["type"] == 'weapon'}
                                ({round($item["durabilityPoints"])}/{$item["max_durability"]})
                            {else}
                                n/a
                            {/if}
                        </div>



                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-4">
                            Action
                        </div>

                        <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-4">
                            {$item['action']}
                        </div>



                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-5">
                            Take Out Cost
                        </div>

                        <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-5">
                            {$item['take_out_cost']}
                        </div>



                        <div class="lazy table-legend-mobile table-alternate-{$row_key % 2 + 1} row-{$row_key} column-6">
                            <label>
                                Select <input type="checkbox" name="selectAllStorage" class="selectAllStorage"/>
                            </label>
                        </div>

                        <div class="lazy table-cell table-alternate-{$row_key % 2 + 1} row-{$row_key} column-6">
                            <input class ="selectGroupStorage" name="inventoryIDs[F{$item['id']}]" value="{$item['id']}" type="checkbox">
                        </div>

                    {/foreach}
                </div>
            {/if}

            <div class="page-grid grid-fill-columns  font-shrink-early">
                <input class="page-button-fill" title="Sells the selected items." name="Sell Selected" value="Sell Selected" type="submit">
                <a class="page-button-fill" href="?id={$smarty.get.id}&act=merge_all_home">Merge All</a>
                {if !$storage_box_mode}
                    <input class="page-button-fill" title="Transfers the selected items to your inventory." name="Transfer Selected" value="Transfer Selected" type="submit">
                {/if}
            </div>
        </div>
    </div>
</form>

<script>
    $('.selectAllanything').click(function() {
        $('.selectGroupanything').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllStorage').click(function() {
        $('.selectGroupStorage').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllarmor').click(function() {
        $('.selectGrouparmor').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllbook').click(function() {
        $('.selectGroupbook').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllfood').click(function() {
        $('.selectGroupfood').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllgem').click(function() {
        $('.selectGroupgem').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllleather').click(function() {
        $('.selectGroupleather').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllmetal').click(function() {
        $('.selectGroupmetal').prop('checked', $(this).is(':checked'));
    });

    $('.selectAlltool').click(function() {
        $('.selectGrouptool').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllweapon').click(function() {
        $('.selectGroupweapon').prop('checked', $(this).is(':checked'));
    });

</script>