{literal}
  <script>
    $(document).ready(function()
    {
      $('.report_items').hide();
    
      $('.report_checkbox').change(function()
      {
        if($('.report_checkbox').is(':checked'))
          $('.report_items').show();
  
        else
          $('.report_items').hide();
      });
      
      
      $(".report_button").submit(function(event){
         alert('here');
         if($('.details_box').val().length === 0)
         {
            event.preventDefault();
            alert('please give a reason for this report.');
         }
         else if($('.details_box').val().length <= 50)
         {
            event.preventDefault();
            alert('please give a detailed reason for this report.');
         }
      });
      
      $(".report_button").click(function(event)
      {
        if($('.details_box').val().length === 0)
        {
           event.preventDefault();
           alert('please give a reason for this report.');
        }
        else if($('.details_box').val().length <= 50)
          {
          event.preventDefault();
          alert('please give a detailed reason for this report.');
          }
      });

      $(".pvp").click(function(event)
      {
        $('.T00').hide();
        $('.T01').hide();
        $('.T02').hide();
        $('.T03').show();
        $('.T04').show();
        $('.T05').hide();
        $('.T06').hide();
        $('.T07').hide();
        $('.T08').hide();
        $('.T09').hide();
        $('.T10').hide();
        $('.T11').hide();
        $('.T12').hide();
        $('.T13').hide();
        event.preventDefault();

        $('.tab-open').removeClass('tab-open');
        $('.pve, .all, .arena, .challenges').addClass('tab-closed');
        $('.pvp').addClass('tab-open');
        $('.pvp').removeClass('tab-closed');
      });

      $(".pve").click(function(event)
      {
        $('.T00').hide();
        $('.T01').show();
        $('.T02').show();
        $('.T03').hide();
        $('.T04').hide();
        $('.T05').show();
        $('.T06').show();
        $('.T07').hide();
        $('.T08').hide();
        $('.T09').hide();
        $('.T10').hide();
        $('.T11').hide();
        $('.T12').hide();
        $('.T13').show();
        event.preventDefault();

        $('.tab-open').removeClass('tab-open');
        $('.pvp, .all, .arena, .challenges').addClass('tab-closed');
        $('.pve').addClass('tab-open');
        $('.pve').removeClass('tab-closed');
      });

      $(".all").click(function(event)
      {
        $('.T00').show();
        $('.T01').show();
        $('.T02').show();
        $('.T03').show();
        $('.T04').show();
        $('.T05').show();
        $('.T06').show();
        $('.T07').show();
        $('.T08').show();
        $('.T09').show();
        $('.T10').show();
        $('.T11').show();
        $('.T12').show();
        $('.T13').show();
        event.preventDefault();

        $('.tab-open').removeClass('tab-open');
        $('.pvp, .pve, .arena, .challenges').addClass('tab-closed');
        $('.all').addClass('tab-open');
        $('.all').removeClass('tab-closed');
      });

      $(".arena").click(function(event)
      {
        $('.T00').hide();
        $('.T01').hide();
        $('.T02').hide();
        $('.T03').hide();
        $('.T04').hide();
        $('.T05').hide();
        $('.T06').hide();
        $('.T07').hide();
        $('.T08').hide();
        $('.T09').show();
        $('.T10').show();
        $('.T11').show();
        $('.T12').hide();
        $('.T13').hide();
        event.preventDefault();

        $('.tab-open').removeClass('tab-open');
        $('.pvp, .pve, .all, .challenges').addClass('tab-closed');
        $('.arena').addClass('tab-open');
        $('.arena').removeClass('tab-closed');
      });

      $(".challenges").click(function(event)
      {
        $('.T00').hide();
        $('.T01').hide();
        $('.T02').hide();
        $('.T03').hide();
        $('.T04').hide();
        $('.T05').hide();
        $('.T06').hide();
        $('.T07').show();
        $('.T08').show();
        $('.T09').hide();
        $('.T10').hide();
        $('.T11').hide();
        $('.T12').show();
        $('.T13').hide();
        event.preventDefault();
      
        $('.tab-open').removeClass('tab-open');
        $('.pvp, .pve, .all, .arena').addClass('tab-closed');
        $('.challenges').addClass('tab-open');
        $('.challenges').removeClass('tab-closed');
      });

    });
  </script>
{/literal}


<div class="page-box">
    <div class="page-title">
        Battle History <span class="toggle-button-info closed" data-target="#battle-history-info"/>
    </div>
    <div class="page-content">
        <div class="toggle-target closed" id="battle-history-info">
            This is where you can view your past battles and view the actions you and your opponent made. 
	  		<br>
			Click on the status of your battle to view the battle log. 
	  		<br>
			Battle log history will be cleared after 96 hours.
	  		<br>
	  		<br>
			To report suspicious battle activity select the box on the right, 
	  		<br>
			and give a detailed message about the battle in the text box below.
	  		<br>
        </div>

        <div class="page-grid stiff-column-fr-5 tabs">
            {if $pvp == true}
	  				<a class="pvp tab-open" href="{if $step_back_sort == true}../{/if}?id=113">PvP</a>
	  		{else}
	  				<a class="pvp tab-closed" href="{if $step_back_sort == true}../{/if}?id=113&sort=pvp">PvP</a>
	  		{/if}

	  		{if $pve == true}
	  				<a class="pve tab-open" href="{if $step_back_sort == true}../{/if}?id=113">PvE</a>
	  		{else}
	  				<a class="pve tab-closed" href="{if $step_back_sort == true}../{/if}?id=113&sort=pve">PvE</a>
	  		{/if}
            
            {if $pve != true && $pvp != true && $arena != true && $challenges != true}
	  				<a class="all tab-open" href="{if $step_back_sort == true}../{/if}?id=113">All</a>
	  		{else}
	  				<a class="all tab-closed" href="{if $step_back_sort == true}../{/if}?id=113">All</a>
	  		{/if}

	  		{if $arena == true}
	  				<a class="arena tab-open" href="{if $step_back_sort == true}../{/if}?id=113">Arena</a>
	  		{else}
	  				<a class="arena tab-closed" href="{if $step_back_sort == true}../{/if}?id=113&sort=arena">Arena</a>
	  		{/if}

	  		{if  $challenges == true}
	  				<a class="challenges tab-open" href="{if $step_back_sort == true}../{/if}?id=113">Challenges</a>
	  		{else}
	  				<a class="challenges tab-closed" href="{if $step_back_sort == true}../{/if}?id=113&sort=challenges">Challenges</a>
	  		{/if}
        </div>

        {$count = 0}
        {foreach $result as $row_key => $record}
          {if count($record['teams']) > $count}
            {$count = count($record['teams'])}
          {/if}
        {/foreach}

        <form name="report_form" id="report_form" method="post" action="" class="table-grid table-column-{4 + $count} font-small tab-above">
            <div class="table-legend row-header column-1 table-legend-first font-large">
	  			Status
	  		</div>
	  		<div class="table-legend row-header column-2 font-large">
	  			Battle Type
	  		</div>

        {for $i=1 to $count}
	  		  <div class="table-legend row-header column-{2 + $i} font-large">
	  			  Team-{$i}
	  		  </div>
	  		{/for}

	  		<div class="table-legend row-header column-{3 + $i} font-large">
	  			Time 
	  		</div>
            <div class="table-legend row-header column-{4 + $i} table-legend-last font-large">
                X
            </div>

            {foreach $result as $row_key => $record}
                <div class="table-legend-mobile table-alternate-{$row_key % 2 + 1}  column-1 font-large row-{$row_key}">
    	  			Status
	  		    </div>

                <div class="T{$record['type']} table-cell table-alternate-{$row_key % 2 + 1} column-1 row-{$row_key}">
	  	    	    <a  {if $step_back == true}
                      target="_blank"
                    {/if} 
                        
                    href="{if $step_back_battle_link == true}../{/if}?id=113&history_id={$record['id']}"
                >
                {if $record['result'] == 'win'}
                  Victory
                {else if $record['result'] == 'flee'}
                  Fled
		    	    	{else if $record['result'] != '' && $record['result'] != 'loss'}
		    	    		victory: {$record['result']}
                {else}
                  Defeat
                {/if}
	  	    	    </a>
                </div>
                
                <div class="table-legend-mobile table-alternate-{$row_key % 2 + 1}  column-2 font-large row-{$row_key}">
    	  			Battle Type
	  		    </div>

	  	    	<div class="T{$record['type']}  table-cell table-alternate-{$row_key % 2 + 1} column-2 row-{$row_key}">
	  	    		{if $record['type'] == '01'}
	  	    			Travel
	  	    		{else if $record['type'] == '02'}
	  	    			Event
	  	    		{else if $record['type'] == '03'}
	  	    			Spar
	  	    		{else if $record['type'] == '04'}
	  	    			Pvp
	  	    		{else if $record['type'] == '05'}
	  	    			Small Crimes
	  	    		{else if $record['type'] == '06'}
	  	    			Mission
	  	    		{else if $record['type'] == '07'}
	  	    			Kage
	  	    		{else if $record['type'] == '08'}
	  	    			Clan
	  	    		{else if $record['type'] == '09'}
	  	    			Arena
	  	    		{else if $record['type'] == '10'}
	  	    			Mirror
	  	    		{else if $record['type'] == '11'}
	  	    			Torn
	  	    		{else if $record['type'] == '12'}
	  	    			Territory
		    		{else if $record['type'] == '13'}
	  	    			Quest
	  	    		{else}
	  	    			'{$record['type']}'
	  	    		{/if}
	  	    	</div>

                {$temp_counter = 0}
	  	    	{foreach $record['teams'] as $team => $team_data}
                    <div class="table-legend-mobile table-alternate-{$row_key % 2 + 1}  column-{3+$temp_counter} font-large row-{$row_key}">
	  			        Team-{$temp_counter + 1}
	  		        </div>

	  	    		<div class="T{$record['type']}  table-cell table-alternate-{$row_key % 2 + 1} column-{3+$temp_counter} row-{$row_key}">
                        {$temp_counter = $temp_counter + 1}

	  	    			{foreach $team_data as $key => $username}
	  	    				{if $key > 0}
	  	    					<br>
	  	    				{/if}

	  	    				{if $username[1] == 'human'}
	  	    					<a {if $step_back == true}target="_blank"{/if} href="{if $step_back_battle_link == true}../{/if}?id=13&page=profile&name={$username[0]}">{$username[0]}</a>
	  	    				{else}
	  	    					{$username[0]}
	  	    				{/if}
	  	    			{/foreach}
	  	    		</div>
	  	    	{/foreach}

            {if count($record['teams']) < $count}
              {for $i=count($record['teams']) to $count-1}
                <div class="table-legend-mobile table-alternate-{$row_key % 2 + 1}  column-{3+$i} font-large row-{$row_key}">
	  			        Team-{$i + 1}
	  		        </div>

	  	    		  <div class="T{$record['type']}  table-cell table-alternate-{$row_key % 2 + 1} column-{3+$i} row-{$row_key}">
                </div>
              {/for}
            {/if}

                <div class="table-legend-mobile table-alternate-{$row_key % 2 + 1}  column-5 font-large row-{$row_key}">
    	  			Time 
	  		    </div>

	  	    	<div class="T{$record['type']} table-cell table-alternate-{$row_key % 2 + 1} column-5 row-{$row_key}">
	  	    		{date('m-d H:i',$record['time'])}
	  	    	</div>

                <div class="table-legend-mobile table-alternate-{$row_key % 2 + 1}  column-6 font-large row-{$row_key}">
                    X
                </div>

                <div class="T{$record['type']} table-cell table-alternate-{$row_key % 2 + 1} column-6 row-{$row_key}">
                  <input type="checkbox" name="report{$record['id']}" class="report_checkbox" value="{$record['id']}">
                </div>

	  	    {/foreach}

            <textarea name="details" id="details" form="report_form" class="report_items details_box table-span-6 page-text-area-fill table-footer" placeholder="Please explain the reason for your report..."></textarea>
            <input type="submit" value=" Report " name="report_button" class="tdTop report_items report_button table-span-6 page-button-fill table-footer">
        </form>
    </div>
</div>