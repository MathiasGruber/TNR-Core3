<form action="?id=23&amp;act=selection" method="post">
  <div align="center" style="border: 1px solid black;position:relative;top:-10px;" width="95%">

    <table align="center" width="100%">
		  <tr>
        {if $storage_box_mode}
          <td align="center" class="subHeader">Storage Box</td>
        {else if $syndicate_mode}
          <td align="center" class="subHeader">Camp {count($item_array['anything'])}/{$storage['anything']}</td>
        {else}
          <td align="center" class="subHeader">Home Inventory</td>
        {/if}
      </tr>

		  <tr>
        {if $storage_box_mode}
			    <td align="center">Welcome to your storage box!<br>When you buy a new home you will be able to<br>find your storage box in your new home inventory!</td>
        {else if $syndicate_mode}
			    <td align="center">Your secret stash of goodies.</td>        
        {else}
			    <td align="center">Welcome to your home inventory!<br>Your home inventory currently holds {$totals['current']} out of {$totals['max']} items</td>
        {/if}
		  </tr>
    </table>

		{foreach $item_array as $type_name => $type_collection}
    
			{if count($type_collection) != 0}
      
			  <style>
          summary::-webkit-details-marker { display: none; }
        </style>
        <details {if $collapse_home == 'no'}open{/if}>
      
          {if !$syndicate_mode}
            <summary>
              <table align="center" width="100%">

  		  	    	<tr>
	  	  	    		<td align="center" class="subHeader">{ucfirst($type_name)} {count($type_collection)}/{$storage[$type_name]}</td>
		    	    	</tr>
                
              </table>
            </summary>
          {/if}

          <table class="table sortable" style="border-left:none;border-right:none;border-bottom:none" width="100%">
    
            <thead>
  		  		<tr>
			  		  <td class="tdTop" width="28%">Name</td>
			  		  <td class="tdTop" width="18%">Type</td>
			  		  <td class="tdTop" width="18%">Durability</td>
              <td class="tdTop sorttable_nosort" width="18%">Action</td>
              <td class="tdTop sorttable_nosort" width="18%"><label>Select <input type="checkbox" name="selectAll{$type_name}" class="selectAll{$type_name}"/></label></td>
			  	  </tr>
            </thead>

            <tbody>
			  	  {foreach $type_collection as $item}
  		  	  <tr>
			    	  <td class="showTableEntry" width="28%"><a class="showTableLink" href="?id=23&act=details&inv_id={$item["id"]}">{$item["name"]}</a></td>
              
			  		  <td class="showTableEntry" width="18%">
                {if $item['type'] != 'armor'}
                  {ucfirst($item['type'])}
                {else}
                  Armor:{ucfirst($item['armor_types'])}
                {/if}
              </td>
              
			  		  <td class="showTableEntry" width="18%">
                {if $item["type"] == "armor" || $item["type"] == 'weapon'}
                  ({round($item["durabilityPoints"])}/{$item["max_durability"]})
                {else}
                  n/a

                {/if}

              </td>

			  		  <td class="showTableEntry" width="18%">{$item['action']}</td>

			  		  <td class="showTableEntry" width="18%"><input class="selectGroup{$type_name}" name="inventoryIDs[{$item['id']}]" value="{$item['id']}" type="checkbox"></td>


			  		  </tr>
			  		{/foreach}
            </tbody>

          </table>
          <br>
        
        </details>
			{/if}
      

    {/foreach}

		{if isset($storage_box) && is_array($storage_box) && count($storage_box) > 0 && !$syndicate_mode}
    
      <style>
        summary::-webkit-details-marker { display: none; }
      </style>
      <details {if $collapse_home == 'no'}open{/if}>
    
        {if !$syndicate_mode && !$storage_box_mode}
          <summary>
            <table align="center" width="100%">

              <tr>
                <td align="center" class="subHeader">Storage Box</td>
              </tr>
              
              <tr>
                <td>The Storage box, a nifty place that will store all your goods. However you'll need to pay a fee to withdraw items.</td>
              </tr>

            </table>
          </summary>
        {else if $syndicate_mode}
          <summary>
            <table align="center" width="100%">

                <tr>
                  <td align="center" class="subHeader">Storage Box</td>
                </tr>
                
                <tr>
                  <td>The Storage box, a nifty place that will store all your goods. Protected from all outlaws, meaning only villagers can withdraw items here.</td>
                </tr>

            </table>
          </summary>
        {else}
          <summary>
            <table align="center" width="100%">

              <tr>
                <td align="center" class="subHeader">Storage Box</td>
              </tr>
              
              <tr>
                <td>The Storage box, a nifty place that will store all your goods. However you'll need to pay a fee to withdraw items. You will need a house to withdraw items.</td>
              </tr>

            </table>
          </summary>
        {/if}
    
        <table class="table sortable" style="border-left:none;border-right:none;border-bottom:none" width="100%">

          <thead>
          <tr>
            <td class="tdTop">Name</td>
            <td class="tdTop">Type</td>
            <td class="tdTop">Durability</td>
            <td class="tdTop sorttable_nosort">Action</td>
            <td class="tdTop">Take Out Cost</td>
            <td class="tdTop sorttable_nosort"><label>Select <input type="checkbox" name="selectAllStorage" class="selectAllStorage"/></label></td>
          </tr>
          </thead>

          <tbody>
          {foreach $storage_box as $item}
          <tr>

            <td class="showTableEntry">
              {if $item['type'] != 'furniture'}
              <a class="showTableLink" href="?id=23&act=details&inv_id={$item["id"]}">{$item["name"]}</a>
              {else}
              {$item["name"]}
              {/if}
            </td>

            <td class="showTableEntry">
              {if $item['type'] != 'armor'}
                {ucfirst($item['type'])}
              {else}
                Armor:{ucfirst($item['armor_types'])}
              {/if}
            </td>

            <td class="showTableEntry">
              {if $item["type"] == "armor" || $item["type"] == 'weapon'}
              ({round($item["durabilityPoints"])}/{$item["max_durability"]})
              {else}
              n/a
              {/if}
            </td>

            <td class="showTableEntry">{$item['action']}</td>

            <td class="showTableEntry">{$item['take_out_cost']}</td>

            <td class="showTableEntry">
              <input class ="selectGroupStorage" name="inventoryIDs[F{$item['id']}]" value="{$item['id']}" type="checkbox">
            </td>


          </tr>
            {/foreach}
          </tbody>
          </table>
      </details>
       
    {/if}
  
	</div>
  <div>
    <input class="input_submit_btn" title="Sells the selected items." name="Sell Selected" value="Sell Selected" type="submit">
    <a class="input_submit_btn" href="?id={$smarty.get.id}&act=merge_all_home" style="position:relative;top:-1px;left:9px;color:white;font-weight:500;">Merge All</a>
    {if !$storage_box_mode}
      &nbsp&nbsp&nbsp&nbsp&nbsp
      <input class="input_submit_btn" title="Transfers the selected items to your inventory." name="Transfer Selected" value="Transfer Selected" type="submit">
    {/if}
  </div>
  <br>
  <div align ="center">
    {if !$syndicate_mode}
      <a href="?id=23">Return</a>
    {else}
      <a href="?id=19">Return</a>
    {/if}
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