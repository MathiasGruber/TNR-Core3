<doStretch></doStretch>

    
    <!-- SHOW HEADER -->
    <tr>
        <td>
            {if $storage_box_mode}
                <headerText>Storage Box</headerText>
            {else if $syndicate_mode}
                <headerText>Camp {count($item_array['anything'])}/{$storage['anything']}</headerText>
            {else}
                <headerText>Home Inventory</headerText>
            {/if}
        </td>
    </tr>
    
    <!-- SHOW TEXT -->
    <tr>
        <td>
            {if $storage_box_mode}
                <text color="dark">Welcome to your storage box! When you buy a new home you will be able to find your storage box in your new home inventory!</text>
            {else if $syndicate_mode}
                <text color="dark">Your secret stash of goodies.</text>        
            {else}
                <text color="dark">Welcome to your home inventory! Your home inventory currently holds {$totals['current']} out of {$totals['max']} items</text>
            {/if}
        </td>
    </tr>
    
    <!-- LOOP THROUGH ITEMS -->
    {foreach $item_array as $type_name => $type_collection}
	{if count($type_collection) != 0}
            
            <!-- Show title if not in syndicate mode -->
            {if !$syndicate_mode}
                <tr>
                    <td>
                        <headerText>{ucfirst($type_name)} {count($type_collection)}/{$storage[$type_name]}</headerText>
                    </td>
                </tr>
            {/if}
            
            <!-- Column Names -->
            <tr color="fadedblack">
                <td><text color="white"><b>Name</b></text></td>
                <td><text color="white"><b>Type</b></text></td>
                <td><text color="white"><b>Durability</b></text></td>
                <td><text color="white"><b>Action</b></text></td>
                <td><text color="white"><b>Select</b></text></td>
                <td><text color="white"><b>Details</b></text></td>
            </tr>
            
            <!-- Loop through items -->
            {foreach $type_collection as $item}
                <tr color="{cycle values="dim,clear"}">
                    
                    <td>
                        {if !empty($item["name"])}
                            <text>{$item["name"]}</text>
                        {else}
                            <text>None</text>
                        {/if}
                    </td>
                    
                    <td>
                        {if $item['type'] != 'armor'}
                            <text>{ucfirst($item['type'])}</text>
                        {else}
                            <text>Armor:{ucfirst($item['armor_types'])}</text>
                        {/if}
                    </td>
                    
                    <td>
                        {if $item["type"] == "armor" || $item["type"] == 'weapon'}
                            <text>({round($item["durabilityPoints"])}/{$item["max_durability"]})</text>
                        {else}
                            <text>n/a</text>
                        {/if}
                    </td>
                    
                    <td>
                        {$item['action']}
                    </td>
                    
                    <td>
                        <text>
                            <input name="inventoryIDs[{$item['id']}]" value="{$item['id']}" type="checkbox"></input>
                        </text>
                    </td>
                    
                    <td>
                        <a href="?id=23&act=details&inv_id={$item["id"]}">Details</a>
                    </td>
                </tr>
            {/foreach}
        {/if}
    {/foreach}

    {if isset($storage_box) && is_array($storage_box) && count($storage_box) > 0 && !$syndicate_mode}
        
        <tr>
            <td>
                <headerText>Storage Box</headerText>
            </td>
        </tr>
           
        {if $syndicate_mode}
            <tr>
                <td>
                    <text color="dark">The Storage box, a nifty place that will store all your goods. Protected from all outlaws, meaning only villagers can withdraw items here.</text>
                </td>
            </tr>
        {else}
            <tr>
                <td>
                    <text color="dark">The Storage box, a nifty place that will store all your goods. However you'll need to pay a fee to withdraw items.</text>
                </td>
            </tr>
        {/if}
        
        <!-- Column Names -->
        <tr color="fadedblack">
            <td><text color="white"><b>Name</b></text></td>
            <td><text color="white"><b>Type</b></text></td>
            <td><text color="white"><b>Durability</b></text></td>
            <td><text color="white"><b>Action</b></text></td>
            <td><text color="white"><b>Take out cost</b></text></td>
            <td><text color="white"><b>Select</b></text></td>
            <td><text color="white"><b>Details</b></text></td>
        </tr>
    
        <!-- Go through items in storage box -->
        {foreach $storage_box as $item}
            <tr color="{cycle values="dim,clear"}">
                
                    <td>
                        {if !empty($item["name"])}
                            <text>{$item["name"]}</text>
                        {else}
                            <text>None</text>
                        {/if}
                    </td>
                    
                    <td>
                        {if $item['type'] != 'armor'}
                            <text>{ucfirst($item['type'])}</text>
                        {else}
                            <text>Armor:{ucfirst($item['armor_types'])}</text>
                        {/if}
                    </td>
                    
                    <td>
                        {if $item["type"] == "armor" || $item["type"] == 'weapon'}
                            <text>({round($item["durabilityPoints"])}/{$item["max_durability"]})</text>
                        {else}
                            <text>N/A</text>
                        {/if}
                    </td>
                    
                    <td>
                        {$item['action']}
                    </td>
                    
                    <td>
                        <text>{$item['take_out_cost']}</text>
                    </td>
                    
                    <td>
                        <text>
                            <input name="inventoryIDs[{$item['id']}]" value="{$item['id']}" type="checkbox"></input>
                        </text>
                    </td>
                    
                    <td>
                        {if $item['type'] != 'furniture'}
                            <a href="?id=23&act=details&inv_id={$item["id"]}">Details</a>
                        {else}
                            <text>N/A</text>
                        {/if}
                    </td>
            
            </tr>
        {/foreach}  
    {/if}
    
    <tr>
        <td>
            <submit name="Sell Selected" value="Sell Selected" type="submit" href="?id=23&amp;act=selection">Sell Selected Items</submit>
        </td>
    </tr>
    
    <tr>
        {if !$storage_box_mode}
            <td>
                <submit name="Transfer Selected" value="Transfer Selected" type="submit" href="?id=23&amp;act=selection">Transfer Selected to Inventory</submit>
            </td>
        {/if}
    </tr>
    
    <tr>
        <td>
            {if !$syndicate_mode}
                <a href="?id=23">Return</a>
            {else}
                <a href="?id=19">Return</a>
            {/if}
        </td>
    </tr>

