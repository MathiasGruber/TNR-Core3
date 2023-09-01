<form id="tableParserCheckboxForm" action="?id=11&amp;act=selected" method="post">

    <div class="page-box">
        <div class="page-title"> 
            User Inventory
        </div>
        <div class="page-content">
            <div class="page-sub-title-top toggle-button-drop closed" data-target="#equipped">
                Equipped <span class="toggle-button-info closed" data-target="#equipped-information"/>
            </div>

            <div class="toggle-target closed" id="equipped-information">
                Items listed here do not add to your encumberment. Note that Gatherer Tools, when equipped, increase your pack size. Tools belonging to an active profession cannot be moved or sold.
            </div>

            <div class="table-grid table-column-5 toggle-target closed" id="equipped">
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
                        Select <input type="checkbox" name="selectAllEquipped" class="selectAllEquipped"/>
                    </label>
                </div>

                {$equipment_array = ['Helmet',   'Chest',    'Belt',     'Gloves',
                                     'Pants',    'Shoes',    'Weapon 1', 'Weapon 2',
                                     'Weapon 3', 'Weapon 4', 'Tool'                  ]}

                {foreach $equipment_array as $key => $equipment_type}
                    
                    <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-1">
                        Name
                    </div>
                    
                    <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-1 row-{$key}">
                        <a class="showTableLink" href="?id=11&act=details&inv_id={$inventory_equipped[$key]["id"]}">
                            {$inventory_equipped[$key]["name"]}
                        </a>
                    </div>



                    <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-2">
                        Type
                    </div>

                     <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-2 row-{$key}">
                        {$equipment_type}
                     </div>
                    
                    
                    
                    <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-3">
                        Durability
                    </div>

                    <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-3 row-{$key}">
                        {if $inventory_equipped[$key]["type"] == "weapon" || $inventory_equipped[$key]["type"] == "armor"}
                            {if $inventory_equipped[$key]["durabilityPoints"] != $inventory_equipped[$key]["max_durability"] && $inventory_equipped[$key]["canRepair"] == "yes"}
                                <a class="showTableLink" href="?id=87&act=offer&iid={$inventory_equipped[$key]["iid"]}&inv_id={$inventory_equipped[$key]["inv_id"]}">
                                    ({round($inventory_equipped[$key]["durabilityPoints"])}/{$inventory_equipped[$key]["max_durability"]})
                                </a>
                            {else}
                                ({round($inventory_equipped[$key]["durabilityPoints"])}/{$inventory_equipped[$key]["max_durability"]})
                            {/if}
                        {else}
                            n/a
                        {/if}
                    </div>



                    <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-4">
                        Action
                    </div>

                    <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-4 row-{$key}">    
                        {$inventory_equipped[$key]["action"]}
                    </div>


                    
                    <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-5">
                        <label>
                            Select <input type="checkbox" name="selectAllEquipped" class="selectAllEquipped"/>
                        </label>
                    </div>

                    <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-5 row-{$key}">
                        {if isset($inventory_equipped[$key]['name'])}
                            <input name="inventoryIDs[{$key + 1}]" value="{$inventory_equipped[$key]["inv_id"]}" type="checkbox" class="selectGroupEquipped"/>
                        {/if}
                    </div>
                {/foreach}

            </div>

            <div class="page-sub-title toggle-button-drop closed" data-target="#equipment-stats">
                Equipment Stats
            </div>

            <div class="toggle-target closed" id="equipment-stats">
                <div class="page-grid page-column-2 text-left font-shrink-early">
                    <div class="stiff-grid stiff-column-2 page-grid-justify-stretch">
                        <div>
                            Armor: {round($armor,2)}
                        </div>
                        <div>
                            Expertise: {round($expertise,2)}
                        </div>
                    </div>
                    <div class="stiff-grid stiff-column-2 page-grid-justify-stretch">
                        <div>
                            Stability: {round($stability,2)}
                        </div>
                        <div>
                            Accuracy: {round($accuracy,2)}
                        </div>
                    </div>
                    <div class="stiff-grid stiff-column-2 page-grid-justify-stretch">
                        <div>
                            Chakra Power: {round($chakra_power,2)}
                        </div>
                        <div>
                            Critical Strike: {round($critical_strike,2)}
                        </div>
                    </div>
                    <div class="stiff-grid stiff-column-2 page-grid-justify-stretch">
                        <div>
                            Mastery: {round($mastery,2)}
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-sub-title">
                Pack ({$pack_count}/{$max_pack_size}) <span class="toggle-button-info closed" data-target="#pack-info"/>
            </div>

            <div class="toggle-target closed" id="pack-info">
                All items not equipped are here. Make sure this doesn't exceed your bag limits or you will be restricted in what you can do!
            </div>

            <div class="table-grid table-column-5">
                {if count($inventory_pack) != 0}
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
                            Select <input type="checkbox" name="selectAllPack" class="selectAllPack"/>
                        </label>
                    </div>
                {/if}

                {assign var=i value=0}
                {foreach $inventory_pack as $item }

                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-1">
                        Name
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-1 row-{$i}">
                        <a class="showTableLink" href="?id=11&act=details&inv_id={$item["id"]}">{$item['name']}</a>
                    </div>



                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-2">
                        Type
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-2 row-{$i}">
                        {if $item['type'] != 'armor'}
                            {ucfirst($item['type'])}
                        {else}
                            Armor:{ucfirst($item['armor_types'])}
                        {/if}
                    </div>



                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-3">
                        Durability
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-3 row-{$i}">
                        {if $item["type"] == "armor" || $item['type'] == 'weapon'}
                            {if $item["durabilityPoints"] != $item["max_durability"] && $item["canRepair"] == "yes"}
                                <a class="showTableLink" href="?id=87&act=offer&iid={$item["iid"]}&inv_id={$item["inv_id"]}">
                                    ({round($item["durabilityPoints"])}/{$item["max_durability"]})
                                </a>
                            {else}
                                ({round($item["durabilityPoints"])}/{$item["max_durability"]})
                            {/if}
                        {else}
                            n/a
                        {/if}
                    </div>
        


                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-4">
                        Action
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-4 row-{$i}">
                        {$item["action"]}
                    </div>



                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-5">
                        <label>
                            Select <input type="checkbox" name="selectAllPack" class="selectAllPack"/>
                        </label>
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-5 row-{$i}">
        			    <input class="selectGroupPack" name="inventoryIDs[{12+$i}]" value="{$item["inv_id"]}" type="checkbox"\>
                    </div>
        		
                    {$i = $i + 1}
                {/foreach}

            </div>

            <div class="page-grid page-column-3">
                <input class="page-button-fill" title="Sells the selected items." name="Sell Selected" value="Sell Selected" type="submit">
                <a class="page-button-fill" href="?id={$smarty.get.id}&act=merge_all_user">Merge All</a>
                {if $transfer_available}
                    <input class="page-button-fill" title="Transfers the selected items to your home." name="Transfer Selected" value="Transfer Selected" type="submit">
                {else}
                    <input class="page-button-fill no-hover" style="color:gray;" title="You must be in your village and asleep to transfer." name="Transfer Selected" value="Transfer Selected" type="submit" disabled>
                {/if}
            </div>
        </div>
    </div>


                
</form>

<script>
    $('.selectAllEquipped').click(function() {
        $('.selectGroupEquipped').prop('checked', $(this).is(':checked'));
    });

    $('.selectAllPack').click(function() {
        $('.selectGroupPack').prop('checked', $(this).is(':checked'));
    });
</script>