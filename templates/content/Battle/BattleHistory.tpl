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

        $('.Bpvp').removeAttr('style');
        $('.Bpvp').addClass('tdTop');
        $('.Bpvp').css({'border-bottom':'1px solid silver','border-top':'1px solid grey','border-right':'1px solid grey'});
        $('.Bpve').removeAttr('style');
        $('.Bpve').removeClass('tdTop');
        $('.Bpve').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
        $('.Ball').removeAttr('style');
        $('.Ball').removeClass('tdTop');
        $('.Ball').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
        $('.Barena').removeAttr('style');
        $('.Barena').removeClass('tdTop');
        $('.Barena').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
        $('.Bchallenges').removeAttr('style');
        $('.Bchallenges').removeClass('tdTop');
        $('.Bchallenges').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
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

        $('.Bpvp').removeAttr('style');
        $('.Bpvp').removeClass('tdTop');
        $('.Bpvp').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver'});
        $('.Bpve').removeAttr('style');
        $('.Bpve').addClass('tdTop');
        $('.Bpve').css({'border-bottom':'1px solid silver','border-top':'1px solid grey','border-right':'1px solid grey','border-left':'1px solid grey'});
        $('.Ball').removeAttr('style');
        $('.Ball').removeClass('tdTop');
        $('.Ball').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
        $('.Barena').removeAttr('style');
        $('.Barena').removeClass('tdTop');
        $('.Barena').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
        $('.Bchallenges').removeAttr('style');
        $('.Bchallenges').removeClass('tdTop');
        $('.Bchallenges').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
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
      
        $('.Bpvp').removeAttr('style');
        $('.Bpvp').removeClass('tdTop');
        $('.Bpvp').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver'});
        $('.Bpve').removeAttr('style');
        $('.Bpve').removeClass('tdTop');
        $('.Bpve').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-left':'1px solid silver'});
        $('.Ball').removeAttr('style');
        $('.Ball').addClass('tdTop');
        $('.Ball').css({'border-bottom':'1px solid silver','border-top':'1px solid grey','border-right':'1px solid grey','border-left':'1px solid grey'});
        $('.Barena').removeAttr('style');
        $('.Barena').removeClass('tdTop');
        $('.Barena').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
        $('.Bchallenges').removeAttr('style');
        $('.Bchallenges').removeClass('tdTop');
        $('.Bchallenges').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
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
      
        $('.Bpvp').removeAttr('style');
        $('.Bpvp').removeClass('tdTop');
        $('.Bpvp').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver'});
        $('.Bpve').removeAttr('style');
        $('.Bpve').removeClass('tdTop');
        $('.Bpve').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-left':'1px solid silver'});
        $('.Ball').removeAttr('style');
        $('.Ball').removeClass('tdTop');
        $('.Ball').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-left':'1px solid silver'});
        $('.Barena').removeAttr('style');
        $('.Barena').addClass('tdTop');
        $('.Barena').css({'border-bottom':'1px solid silver','border-top':'1px solid grey','border-right':'1px solid grey','border-left':'1px solid grey'});
        $('.Bchallenges').removeAttr('style');
        $('.Bchallenges').removeClass('tdTop');
        $('.Bchallenges').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-right':'1px solid silver'});
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
      
        $('.Bpvp').removeAttr('style');
        $('.Bpvp').removeClass('tdTop');
        $('.Bpvp').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver'});
        $('.Bpve').removeAttr('style');
        $('.Bpve').removeClass('tdTop');
        $('.Bpve').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-left':'1px solid silver'});
        $('.Ball').removeAttr('style');
        $('.Ball').removeClass('tdTop');
        $('.Ball').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-left':'1px solid silver'});
        $('.Barena').removeAttr('style');
        $('.Barena').removeClass('tdTop');
        $('.Barena').css({'border-bottom':'1px solid Grey','border-top':'1px solid silver','border-left':'1px solid silver'});
        $('.Bchallenges').removeAttr('style');
        $('.Bchallenges').addClass('tdTop');
        $('.Bchallenges').css({'border-bottom':'1px solid silver','border-top':'1px solid grey','border-left':'1px solid grey'});
      });

    });
  </script>
{/literal}

  <form name="report_form" id="report_form" method="post" action="">
	  <table class="table" width="95%">
	  	<tr>
	  		<td class="subHeader">
	  			Battle History
	  		</td>
	  	</tr>
	  	<tr>
	  		<td>
	  			<br>
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
	  		</td>
	  	</tr>
	  </table>
	  <table width="95%">
	  	<tr>
	  		{if $pvp == true}
	  			<td class="tdTop Bpvp" style="border:1px solid silver;border-top:1px solid grey;border-right:1px solid grey;" width="20%">
	  				<a class="pvp" href="{if $step_back_sort == true}../{/if}?id=113">PvP</a>
	  		{else}
	  			<td class="Bpvp" style="border:1px solid silver;border-bottom:1px solid grey;{if $pve == true}border-right:1px solid grey;{/if}" width="20%">
	  				<a class="pvp" href="{if $step_back_sort == true}../{/if}?id=113&sort=pvp">PvP</a>
	  		{/if}
	  		</td>

	  		{if $pve == true}
	  			<td class="tdTop Bpve" style="border:1px solid silver;border-left:1px solid grey;border-top:1px solid grey;border-right:1px solid grey;" width="20%">
	  				<a class="pve" href="{if $step_back_sort == true}../{/if}?id=113">PvE</a>
	  		{else}
	  			<td class="Bpve" style="border:1px solid silver;border-bottom:1px solid grey;{if $pve != true && $pvp != true && $arena != true && $challenges != true}border-right:1px solid grey;{/if}" width="20%">
	  				<a class="pve" href="{if $step_back_sort == true}../{/if}?id=113&sort=pve">PvE
	  		{/if}
	  		</td>
            
        {if $pve != true && $pvp != true && $arena != true && $challenges != true}
	  			<td class="tdTop Ball" style="border:1px solid silver;border-left:1px solid grey;border-top:1px solid grey;border-right:1px solid grey;" width="20%">
	  				<a class="all" href="{if $step_back_sort == true}../{/if}?id=113">All</a>
	  		{else}
	  			<td class="Ball" style="border:1px solid silver;border-bottom:1px solid grey;{if $arena == true}border-right:1px solid grey;{/if}" width="20%">
	  				<a class="all" href="{if $step_back_sort == true}../{/if}?id=113">All
	  		{/if}
	  		</td>

	  		{if $arena == true}
	  			<td class="tdTop Barena" style="border:1px solid silver;border-left:1px solid grey;border-top:1px solid grey;border-right:1px solid grey;" width="20%">
	  				<a class="arena" href="{if $step_back_sort == true}../{/if}?id=113">Arena</a>
	  		{else}
	  			<td class="Barena" style="border:1px solid silver;border-bottom:1px solid grey;{if $challenges == true}border-right:1px solid grey;{/if}" width="20%">
	  				<a class="arena" href="{if $step_back_sort == true}../{/if}?id=113&sort=arena">Arena
	  		{/if}
	  		</td>

	  		{if  $challenges == true}
	  			<td class="tdTop Bchallenges" style="border:1px solid silver;border-left:1px solid grey;border-top:1px solid grey;" width="20%">
	  				<a class="challenges" href="{if $step_back_sort == true}../{/if}?id=113">Challenges</a>
	  		{else}
	  			<td class="Bchallenges" style="border:1px solid silver;border-bottom:1px solid grey;" width="20%">
	  				<a class="challenges" href="{if $step_back_sort == true}../{/if}?id=113&sort=challenges">Challenges
	  		{/if}
	  		</td>
	  	</tr>
	  </table>
	  <table class="sortable table" width="95%">
	  	<tr>
	  		</td>
	  		<td class="tdTop">
	  			Status
	  		</td>
	  		<td class="tdTop">
	  			Battle Type
	  		</td>
	  		<td class="tdTop">
	  			Team-1
	  		</td>
	  		<td class="tdTop">
	  			Team-2
	  		</td>
	  		<td class="tdTop">
	  			Time 
	  		</td>
        <td class="tdTop">
          X
        </td>
	  	</tr>
	  	{foreach $result as $record}
	  		<tr class="T{$record['type']}">
	  			<td class="T{$record['type']}">
	  				<a {if $step_back == true}target="_blank"{/if} href="{if $step_back_battle_link == true}../{/if}?id=113&history_id={$record['id']}">
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
	  			</td>
	  			<td class="T{$record['type']}">
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
	  			</td>
	  			{foreach $record['teams'] as $team => $team_data}
	  				<td class="T{$record['type']}">
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
	  				</td>
	  			{/foreach}
	  			<td class="T{$record['type']}">
	  				{date('m-d H:i',$record['time'])}
	  			</td>
          <td class="T{$record['type']}">
            <input type="checkbox" name="report{$record['id']}" class="report_checkbox" value="{$record['id']}">
          </td>
	  		</tr>
	  	{/foreach}
	  </table>
    <br class="report_items"/>
    <textarea name="details" id="details" form="report_form" class="report_items details_box" placeholder="Please explain the reason for your report..." style="resize: vertical;width: 80%;"></textarea>
    <br class="report_items"/>
    <br class="report_items"/>
    <input type="submit" value=" Report " name="report_button" class="tdTop report_items report_button">
    <br class="report_items"/>
    <br class="report_items"/>
    </form>
