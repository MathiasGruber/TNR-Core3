<doStretch></doStretch>

    
    <!-- SHOW HEADER -->
    <tr>
        <td>
            <headerText>Furniture: {$slots['used']} / {$slots['max']}</headerText>
        </td>
    </tr>
    
    <!-- Column Names -->
    <tr color="fadedblack">
        <td><text color="white"><b>Name</b></text></td>
        <td><text color="white"><b>Size</b></text></td>
        <td><text color="white"><b>Storage</b></text></td>
        <td><text color="white"><b>Type</b></text></td>
        <td><text color="white"><b>Price</b></text></td>
        <td><text color="white"><b>Owned</b></text></td>
        <td><text color="white"><b>Buy</b></text></td>
        <td><text color="white"><b>Sell</b></text></td>
    </tr>
    
    <hidden type="hidden" name="quantity" value="1"></hidden>
    
    <!-- Loop through furniture items -->
    {assign var=column_flag value=false}
    {foreach $furniture as $item}
      
        <!-- Ignore certain furniture -->
        {if ($item["event_furniture"] && $item["owned"] == 0) || ($item['event_furniture'] && $item['event_start'] < time() && $item['event_end'] > time()) }
            {continue}
        {/if}
        
        <tr color="{cycle values="dim,clear"}">
        
            <td><text>{$item["name"]}</text></td>
            <td><text>{$item["size"]}</text></td>
            <td><text>{$item["storage"]}</text></td>
            <td><text>{$item["storage_type"]}</text></td>
            {if $item["event_furniture"]}
                <td><text>{$item["sale_value"]}</text></td>
            {else}
                <td><text>{$item["price"]}</text></td>
            {/if}
            <td><text>{$item["owned"]} / {$item["max_owned"]}</text></td>
            
            <!-- Buy button -->
            {if $item["event_furniture"]}
              <td><text>N/A</text></td>
            {else}
                <td>
                    <submit updatePrefWidth="true" type="submit" name="buy_button" href="?id=23&amp;act=furniture" value="{$item['id']}">Buy</submit>
                </td>
            {/if}
            
            <!-- Sell button -->
            <td>
                <submit updatePrefWidth="true" type="submit" name="sell_button" href="?id=23&amp;act=furniture" value="{$item['id']}">Sell</submit>
            </td>
        </tr>
    {/foreach}
    
    <!-- Event Furniture -->
    {if $event_active}
        
        <!-- SHOW HEADER -->
        <tr>
            <td>
                <headerText>Event Furniture</headerText>
            </td>
        </tr>
        
        <!-- Column Names -->
        <tr color="fadedblack">
            <td><text color="white"><b>Name</b></text></td>
            <td><text color="white"><b>Size</b></text></td>
            <td><text color="white"><b>Storage</b></text></td>
            <td><text color="white"><b>Type</b></text></td>
            <td><text color="white"><b>Price</b></text></td>
            <td><text color="white"><b>Owned</b></text></td>
            <td><text color="white"><b>Buy</b></text></td>
            <td><text color="white"><b>Sell</b></text></td>
        </tr>
    
        <!-- Loop through the event furniture -->
        {foreach $furniture as $item}

            <!-- Skip non-event furniture -->
            {if !$item["event_furniture"]}
                {continue}
            {/if}
            
            <tr color="{cycle values="dim,clear"}">
        
                <td><text>{$item["name"]}</text></td>
                <td><text>{$item["size"]}</text></td>
                <td><text>{$item["storage"]}</text></td>
                <td><text>{$item["storage_type"]}</text></td>
                {if !is_numeric($item["price"])}
                    <td>
                        <text>{str_replace(':',' (',str_replace(';','), <br/>', $item["price"]))})</text>
                    </td>
                {else}
                    <td><text>{$item["price"]}</text></td>
                {/if}
                <td><text>{$item["owned"]} / {$item["max_owned"]}</text></td>

                <!-- Buy button -->
                <td>
                    <submit updatePrefWidth="true" type="submit" name="buy_button" href="?id=23&amp;act=furniture" value="{$item['id']}">Buy</submit>
                </td>

                <!-- Sell button -->
                <td>
                    <submit updatePrefWidth="true" type="submit" name="sell_button" href="?id=23&amp;act=furniture" value="{$item['id']}">Sell</submit>
                </td>
            </tr>
        {/foreach}
    {/if}
    
    <tr>
        <td>
            {if !$syndicate_mode}
                <a href="?id=23">Return</a>
            {else}
                <a href="?id=19">Return</a>
            {/if}
        </td>
    </tr>
