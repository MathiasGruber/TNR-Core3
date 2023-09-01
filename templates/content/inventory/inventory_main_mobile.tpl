<bg color="clear">
    <headerText>Equipped</headerText>

    
    <!-- Text to show at top of page -->
    <tr>
      <td>
        <text color="dark">Items listed here do not add to your encumberment. Note that Gatherer Tools, when equipped, increase your pack size. Tools belonging to an active profession cannot be moved or sold.</text>
        <a color="dim" contentControl="equipment_details">Equipment Details</a>
        <contentElement name="equipment_details">
          <tr>
            <td>
              <text>Armor: {$armor}</text>
            </td>
            <td>
            </td>
            <td>
              <text>Expertise: {$expertise}</text>
            </td>
          </tr>
          <tr>
            <td>
              <text>Stability: {$stability}</text>
            </td>
            <td>
            </td>
            <td>
              <text>Accuracy: {$accuracy}</text>
            </td>
          </tr>
          <tr>
            <td>
              <text>Chakra Power: {$chakra_power}</text>
            </td>
            <td>
            </td>
            <td>
              <text>Critical Strike: {$critical_strike}</text>
            </td>
          </tr>
          <tr>
            <td>
              <text>Mastery: {$mastery}</text>
            </td>
            <td>
            </td>
            <td>
            </td>
          </tr>
        </contentElement>
      </td>
    </tr>

    <!-- Column Names -->
    <tr color="fadedblack">
      <td>
        <text color="white">
          <b>Name</b> / <b>Details</b>
        </text>
      </td>

      <td>
        <text color="white"> / <b>Durability</b>
        </text>
      </td>

      <td>
        <text color="white">
          <b>Select</b> / <b>Action</b>
        </text>
      </td>
    </tr>
    
  
    <!-- Inventory Content -->
    {assign var=names value=["Helm","Chest","Belt","Gloves","Pants","Shoes","Arms 1","Arms 2","Arms 3","Arms 4","Tool"]}
    {for $i=0 to 10}
        <tr color="{cycle values="dim,clear"}">
            <td>
                {if !empty($inventory_equipped[$i]["name"])}
                    <a overflow="true" color="clear" fontcolor="black" fontStyle="bold" href="?id=11&amp;act=details&amp;inv_id={$inventory_equipped[$i]["id"]}">{$inventory_equipped[$i]["name"]}</a>
                {else}
                    <text>None</text>
                {/if}
            </td>
            <td>
              <text>{$names[$i]}</text>
              <br>
              {if $inventory_equipped[$i]["type"] == "weapon" || $inventory_equipped[$i]["type"] == "armor"}
                {if $inventory_equipped[$i]["durabilityPoints"] != $inventory_equipped[$i]["max_durability"] && $inventory_equipped[$i]["canRepair"] == "yes"}
                  <a href="?id=87&amp;act=offer&amp;iid={$inventory_equipped[$i]["iid"]}&amp;inv_id={$inventory_equipped[$i]["inv_id"]}">
                    ({round($inventory_equipped[$i]["durabilityPoints"])}/{$inventory_equipped[$i]["max_durability"]})
                  </a>
                {else}
                  <text>({round($inventory_equipped[$i]["durabilityPoints"])}/{$inventory_equipped[$i]["max_durability"]})</text>
                {/if}
              {else}
                <text>n/a</text>
              {/if}
            </td>

          
            <td>
              {if isset($inventory_equipped[$i]['name'])}
                <text>
                  <input name="inventoryIDs[1]" value="{$inventory_equipped[$i]["inv_id"]}" type="checkbox"></input>
                </text>
              {else}
                <text></text>
              {/if}
              <br>
              {if strstr({$inventory_equipped[$i]["action"]}, "</a")}
                {$inventory_equipped[$i]["action"]}
              {else}
                <text>{$inventory_equipped[$i]["action"]}</text>
              {/if}
            </td>
            
        </tr>
    {/for}

    <headerText>Pack ({$pack_count}/{$max_pack_size})</headerText>

    <!-- Text to show at top of page -->
    <tr>
      <td>
        <text color="dark">All items not equipped are here. Make sure this doesn't exceed your bag limits or you will be restricted in what you can do!</text>
      </td>
    </tr>
    
    {if count($inventory_pack) != 0}
        <!-- Column Names -->
        <tr color="fadedblack">
            <td>
              <text color="white">
                <b>Name</b> / <b>details</b>
              </text>
            </td>
            <td>
              <text color="white">
                <b>Type</b> / <b>Durability</b>
              </text>
            </td>
            <td>
              <text color="white">
                <b>Select</b> / <b>Action</b>
              </text>
            </td>
        </tr>
        
        <!-- Items in pack -->
        {assign var=i value=0}
        {foreach $inventory_pack as $item }

            <tr color="{cycle values="dim,clear"}">
                <td>
                    {if !empty($item["name"])}
                        <a overflow="true" color="clear" fontcolor="black" fontStyle="bold" href="?id=11&amp;act=details&amp;inv_id={$item["id"]}">{$item["name"]}</a>
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
                  <br>
                  {if $item["type"] == "armor" || $item['type'] == 'weapon'}
                      {if $item["durabilityPoints"] != $item["max_durability"] && $item["canRepair"] == "yes"}
                          <a href="?id=87&amp;act=offer&amp;iid={$item["iid"]}&amp;inv_id={$item["inv_id"]}">
                              ({round($item["durabilityPoints"])}/{$item["max_durability"]})
                          </a>
                      {else}
                          <text>({round($item["durabilityPoints"])}/{$item["max_durability"]})</text>
                      {/if}
                  {else}
                      <text>n/a</text>
                  {/if}
                </td>
                <td>
                    <text><input name="inventoryIDs[{12+$i}]" value="{$item["inv_id"]}" type="checkbox"></input></text>
                    <br>
                    {if strstr({$item["action"]}, "</a")}
                        {$item["action"]}
                    {else}
                        <text>{$item["action"]}</text>
                    {/if}
                </td>
            </tr>
            {$i = $i + 1}
        {/foreach}
    {else}
        <td>
            <text>NO ITEMS IN PACK</text>
        </td>
    {/if}
    
    <submit name="Sell Selected" value="Sell Selected" type="submit" href="?id=11&amp;act=selected">Sell Selected Items</submit>
  
    {if $transfer_available}
      <submit name="Transfer Selected" value="Transfer Selected" type="submit" href="?id=11&amp;act=selected">Transfer Selected to Home</submit>
    {else}
      <text>You must be in your village and asleep to transfer items to your home.</text>
    {/if}
</bg>