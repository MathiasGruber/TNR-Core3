<form id="tableParserCheckboxForm" action="?id=11&amp;act=selected" method="post">
    <div style="border: 1px solid black;position:relative;top:-10px;" width="95%">

        <table align="center" width="100%">
	        <tbody>
		        <tr>
			        <td align="center" class="subHeader">Equipped</td>
		        </tr>
                <tr>
                    <td>Items listed here do not add to your encumberment. Note that Gatherer Tools, when equipped, increase your pack size. Tools belonging to an active profession cannot be moved or sold.</td>
                </tr>
                <tr>
                    <td>
                        <details>
                            <summary>Equipment Details</summary>
                            <table align="center" width="100%">
                                <tr>
                                    <td>
                                        Armor: {$armor}
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                        Expertise: {$expertise}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Stability: {$stability}
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                        Accuracy: {$accuracy}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Chakra Power: {$chakra_power}
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                        Critical Strike: {$critical_strike}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Mastery: {$mastery}
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                            </table>
                        </details>
                    </td>
                </tr>
	        </tbody>
        </table > 

        <table class="sortable" style="border-left:none;border-right:none" width="100%">
        	<thead>
        		<tr>
        			<td class="tdTop" width="30%">Name</td>
                    <td class="tdTop" width="20%">Type</td>
                    <td class="tdTop" width="15%">Durability</td>
                    <td class="tdTop sorttable_nosort" width="18%">Action</td>
                    <td class="tdTop sorttable_nosort" width="17%"><label>Select <input type="checkbox" name="selectAllEquipped" class="selectAllEquipped"/></label></td>
        		</tr>
            </thead>
           
            
            <tbody>
                {$equipment_array = ['Helmet',   'Chest',    'Belt',     'Gloves',
                                     'Pants',    'Shoes',    'Weapon 1', 'Weapon 2',
                                     'Weapon 3', 'Weapon 4', 'Tool'                  ]}

                {foreach $equipment_array as $key => $equipment_type}
            		<tr>
        			    <td width="35%">
                            <a class="showTableLink" href="?id=11&act=details&inv_id={$inventory_equipped[$key]["id"]}">
                                {$inventory_equipped[$key]["name"]}
                            </a>
                        </td>

        			    <td width="20%">
                            {$equipment_type}
                        </td>

        			    <td width="15%">
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
        
                        </td>

        		    	<td width="18%">
                            {$inventory_equipped[$key]["action"]}
                        </td>

        		    	<td width="17%">
                            {if isset($inventory_equipped[$key]['name'])}
                                <input name="inventoryIDs[{$key + 1}]" value="{$inventory_equipped[$key]["inv_id"]}" type="checkbox" class="selectGroupEquipped"/>
                            {/if}
                        </td>
        		    </tr>
                {/foreach}
        	</tbody>
        </table >

        <table align="center" width="100%">
        	<tbody>
        	    <tr>
        			<td align="center" width="100%" class="subHeader" >
                        Pack ({$pack_count}/{$max_pack_size})
                    </td> 
        		</tr>
                <tr>
                    <td>
                        All items not equipped are here. Make sure this doesn't exceed your bag limits or you will be restricted in what you can do!
                    </td>
                </tr>
        	</tbody>
        </table >

        <table class="sortable" style="border-left:none;border-right:none;border-bottom:none" width="100%">
            {if count($inventory_pack) != 0}
        	    <thead>
        		    <tr>
        			    <td class="tdTop" width="30%">Name</td>
                        <td class="tdTop" width="20%">Type</td>
                        <td class="tdTop sorttable_nosort" width="15%">Durability</td>
                        <td class="tdTop sorttable_nosort" width="18%">Action</td>
                        <td class="tdTop sorttable_nosort" width="17%"><label>Select <input type="checkbox" name="selectAllPack" class="selectAllPack"/></label></td>
        		    </tr>
                <thead>
            {/if}
            
            <tbody>
                {assign var=i value=0}
                {foreach $inventory_pack as $item }
        
                    <tr>
                        <td width="30%"><a class="showTableLink" href="?id=11&act=details&inv_id={$item["id"]}">{$item['name']}</a></td>    
        
                        <td width="20%">
                            {if $item['type'] != 'armor'}
                                {ucfirst($item['type'])}
                            {else}
                                Armor:{ucfirst($item['armor_types'])}
                            {/if}
                        </td>
        
                        <td width="15%">
        
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
        
                        </td>
        
                        <td width="18%">{$item["action"]}</td>
        			    <td width="17%"><input class="selectGroupPack" name="inventoryIDs[{12+$i}]" value="{$item["inv_id"]}" type="checkbox"\></td>
        		    </tr>
        		
                    {$i = $i + 1}
                {/foreach}
            
        	</tbody>
        </table >

    </div>
    <div>
        <input class="input_submit_btn" title="Sells the selected items." name="Sell Selected" value="Sell Selected" type="submit">
        <a class="input_submit_btn" href="?id={$smarty.get.id}&act=merge_all_user" style="position:relative;top:-1px;left:9px;color:white;font-weight:500;">Merge All</a>
        {if $transfer_available}
            &nbsp&nbsp&nbsp&nbsp&nbsp
            <input class="input_submit_btn" title="Transfers the selected items to your home." name="Transfer Selected" value="Transfer Selected" type="submit">
        {else}
            &nbsp&nbsp&nbsp&nbsp&nbsp
            <input class="input_submit_btn" style="color:gray;" title="You must be in your village and asleep to transfer." name="Transfer Selected" value="Transfer Selected" type="submit" disabled>
        {/if}
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