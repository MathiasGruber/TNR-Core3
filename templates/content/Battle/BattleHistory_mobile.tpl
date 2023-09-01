<!-- header -->
<tr>
	<td>
		<headerText>Battle History</headerText>
		<text>
      <br>
      This is where you can view your past battles and
      <br>
      view the actions you and your opponent made.
      <br>
      Click on the status of your battle to view
      <br>
      the battle log.
      <br>
      Battle log history will be cleared after 96 hours.
      <br>
      <br>
      To report suspicious battle activity select the box on
      <br>
      the right, and give a detailed message about the battle
      <br>
      in the text box below.
      <br>
    </text>
	</td>
</tr>

<tr>
	<td>
    <a href="?id=113&sort=pvp" color="fadedblack">pvp</a>
  </td>
  <td>
    <a href="?id=113&sort=pve" color="fadedblack">pve</a>
  </td>
	<td>
    <a href="?id=113" color="fadedblack">all</a>
  </td>
	<td>
    <a href="?id=113&sort=arena" color="fadedblack">arena</a>
	</td>
	<td>
    <a href="?id=113&sort=challenges" color="fadedblack">challenge</a>
  </td>
</tr>

<tr color="fadeddarkgrey">
  <td>
    <text>Status</text>
    <text>Type</text>
    <text>Time</text>
  </td>
  <td>
    <text>Team-1</text>
    <text>Team-2</text>
  </td>
</tr>

{foreach $result as $record}
  <tr>
    <td>
      <a href="?id=113&history_id={$record['id']}"{if $record['result'] == 'win'} color="blue"> Victory{else if $record['result'] == 'flee'} color="fadedblack"> Fled{else} color="red" style="text-shadow: 0px 0px 2px black, 1px 1px #323232;"> Defeat{/if}</a>
      {if $record['type'] == '01'}
	  		<text>Travel</text>
	  	{else if $record['type'] == '02'}
	  		<text>Event</text>
	  	{else if $record['type'] == '03'}
	  		<text>Spar</text>
	  	{else if $record['type'] == '04'}
	  		<text>Pvp</text>
	  	{else if $record['type'] == '05'}
	  		<text>Small Crimes</text>
	  	{else if $record['type'] == '06'}
	  		<text>Mission</text>
	  	{else if $record['type'] == '07'}
	  		<text>Kage</text>
	  	{else if $record['type'] == '08'}
	  		<text>Clan</text>
	  	{else if $record['type'] == '09'}
	  		<text>Arena</text>
	  	{else if $record['type'] == '10'}
	  		<text>Mirror</text>
	  	{else if $record['type'] == '11'}
	  		<text>Torn</text>
	  	{else if $record['type'] == '12'}
	  		<text>Territory</text>
			{else if $record['type'] == '13'}
	  		<text>Quest</text>
	  	{else}
	  		<text>'{$record['type']}'</text>
	  	{/if}
      <text>{date('m-d H:i',$record['time'])}</text>
    </td>
	  <td class="T{$record['type']}">
      {assign "count" "1"}
      {foreach $record['teams'] as $team => $team_data}
        <tr>
          <td>
	  		    {foreach $team_data as $key => $username}
	  		    	{if $username[1] == 'human'}
	  		    		<a color="dim" {if $step_back == true}target="_blank"{/if} href="{if $step_back == true}../{/if}?id=13&page=profile&name={$username[0]}">{$username[0]}</a>
	  		    	{else}
	  		    		<text>{$username[0]}</text>
	  		    	{/if}
	  		    {/foreach}
          </td>
        </tr>
        {if $count != count($record['teams'])}
          <tr color="fadedwhite">
            <td>
            </td>
          </tr>
        {/if}
        
        {$count++}
	    {/foreach}
	  </td>
  </tr>
  <tr color="fadedblack">
    <td>
    </td>
  </tr>
{/foreach}