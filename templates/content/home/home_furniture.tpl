<div align="center" style="border: 1px solid black;position:relative;top:-10px;" width="95%">
	<table align="center" width="100%">
		<tr>
			<td colspan="2" align="center" width="100%" class="subHeader">Furniture: {$slots['used']} / {$slots['max']}</td>
		</tr>
  </table>
  
  <table class="table sortable" style="border-left:none;border-right:none;border-bottom:none;" width="100%">
    <thead>
      <tr>
			  <td class="tdTop" width = "30%">Name</td>
        <td class="tdTop" width = "8%">Size</td>  
        <td class="tdTop" width = "8%">Storage</td>
        <td class="tdTop" width = "14%">type</td>
        <td class="tdTop" width = "15%">price</td>
        <td class="tdTop" width = "10%">owned</td>
        <td class="tdTop" width = "10%">quantity</td>
        <td class="tdTop sorttable_nosort" width = "5%">buy</td>
        <td class="tdTop sorttable_nosort" width = "5%">sell</td>
		  </tr>
    </thead>

    <tbody>
      {assign var=column_flag value=false}
      {foreach $furniture as $item}
      
      {if ($item["event_furniture"] && $item["owned"] == 0) || ($item['event_furniture'] && $item['event_start'] < time() && $item['event_end'] > time()) }
      {continue}
      {/if}

      {if $column_flag}
      <tr class="row2">
          {$column_flag = false}
      
        {else}
          <tr class="row1">
          {$column_flag = true}
      
        {/if}
          
		  		<td width = "30%">{$item["name"]}</td>
		  		<td width = "8%">{$item["size"]}</td>
		  		<td width = "8%">{$item["storage"]}</td>
		  		<td width = "14%">{$item["storage_type"]}</td>

          {if $item["event_furniture"]}
            <td width = "15%">{$item["sale_value"]}</td>
          {else}
            <td width = "15%">{$item["price"]}</td>
          {/if}
            
		  		<td width = "10%">{$item["owned"]} / {$item["max_owned"]}</td>
      
		  		<form action="?id=23&amp;act=furniture" method="post">
		  			<td width = "10%"><input type="number" name="quantity" style="width: 2.7em;" value="1" min="1" max="5"/></td>
            {if $item["event_furniture"]}
              <td>n/a</td>
            {else}
		  			  <td width = "5%"><input type="submit" name="buy_button" style="text-indent: -9000px; text-transform: capitalize; width: 25px" value="{$item['id']}"/></td>
            {/if}
		  			<td width = "5%"><input type="submit" name="sell_button" style="text-indent: -9000px; text-transform: capitalize; width: 25px" value="{$item['id']}"/></td>
		  		</form>
      
		  	</tr>
      {/foreach}
    </tbody>
	</table>

  {if $event_active}
    <table align="center" width="100%">
		  <tr>
			  <td colspan="2" align="center" width="100%" class="subHeader">Event Furniture</td>
		  </tr>
    </table>

    <table class="table sortable" style="border-left:none;border-right:none;border-bottom:none;" width="100%">
      <thead>
        <tr>
          <td class="tdTop" width = "30%">Name</td>
          <td class="tdTop" width = "8%">Size</td>
          <td class="tdTop" width = "8%">Storage</td>
          <td class="tdTop" width = "14%">type</td>
          <td class="tdTop" width = "15%">price</td>
          <td class="tdTop" width = "10%">owned</td>
          <td class="tdTop" width = "10%">quantity</td>
          <td class="tdTop sorttable_nosort" width = "5%">buy</td>
          <td class="tdTop sorttable_nosort" width = "5%">sell</td>
        </tr>
      </thead>

      <tbody>
        {assign var=column_flag value=false}
        {foreach $furniture as $item}

          {if !$item["event_furniture"]}
          {continue}
          {/if}

          {if $column_flag}
            <tr class="row2">
            {$column_flag = false}

          {else}
            <tr class="row1">
              {$column_flag = true}

              {/if}

              <td width = "30%">{$item["name"]}</td>
              <td width = "8%">{$item["size"]}</td>
              <td width = "8%">{$item["storage"]}</td>
              <td width = "14%">{$item["storage_type"]}</td>

              {if !is_numeric($item["price"])}
                <td width = "15%">{str_replace(':',' (',str_replace(';','), <br>', $item["price"]))})</td>
              {else}
                <td width = "15%">{$item["price"]}</td>
              {/if}
              
              <td width = "10%">{$item["owned"]} / {$item["max_owned"]}</td>

              <form action="?id=23&amp;act=furniture" method="post">
                <td width = "10%">
                  <input type="number" name="quantity" style="width: 2.7em;" value="1" min="1" max="5"/>
                </td>
                <td width = "5%">
                  <input type="submit" name="buy_button" style="text-indent: -9000px; text-transform: capitalize; width: 25px" value="{$item['id']}"/>
                </td>
                <td width = "5%">
                  <input type="submit" name="sell_button" style="text-indent: -9000px; text-transform: capitalize; width: 25px" value="{$item['id']}"/>
                </td>
              </form>

            </tr>
        {/foreach}
      </tbody>
    </table>
  {/if}
  
</div>
<div align ="center">
  <a href="?id=23">Return</a>
</div>