{literal}
	<script>
		function summary_script()
		{
			$('[class^="round"]').hide();
			$('[class^="details_round"]').hide();
			$('[class^="round' + ($('.turn_counter').text() - 1)+'"]').show();
			$('[class^="details_round' + ($('.turn_counter').text() - 1)+'_'+( $('.owner').attr('value') )+'"]').show();

			$('[class^="header_round"]').click( function()
			{
				$('[class^="'+'round' + $(this).text().match(/\d+/)+'_"]').toggle();
		    });

		    $('[class^="round"]').click( function()
			{
				if( $(this).attr('class').split(' ')[0].length > 8 )
				{
					$(('[class="details_'+$(this).attr('class').split(' ')[0]+'"]')).toggle();
				}
			});
		}

		$( document ).ready(function() {
			summary_script();
		});
	</script>
{/literal}

<div class="page-box" id="summary_page">

	<div class="page-title">
		Battle summary
	</div>

	<form action="" id="battle_form" method="post">
		<div id="summary" summary="yes" mf="yes"></div>

		<div class="page-content">

			<div class="bold font-giant">
				{if $owner['win_lose']}
					Victory
				{else if $owner['flee'] === true}
					Fled
				{else}
					Defeat
				{/if}
				<hr>
			</div>
			
			{if $hide_changes !== true}

				<div class="page-box {if $no_positive_changes === false && $no_negative_changes === false}page-column-fr-2{/if}">
					
					{if $no_positive_changes === false}
						<div class="page-grid grid-gap-none page-rows-min">
							<div class="solid-box bold table-cell">
								positive changes
							</div>

							<div class="plain-box stiff-grid stiff-column-3 page-grid-justify-stretch font-shrink-early">
								
								{if $changes['territory_battle_result'] == $owner['team'] && $changes['territory_battle_result'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have won the {$changes['territory_battle_rank']} territory battle.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['territory_challenge_result'] == $owner['team'] && $changes['territory_challenge_result'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										With the conclusion of this battle you have secured {$owner['team']}`s control of {$changes['territory_challenge_location']}.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['torn'] === true}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You broke your old high score!
										<br>
										Old Torn High Score: {$changes['torn_record']}
										<br>
										New Torn High Score: {$changes['torn_attempt']}
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['kage_replaced'] === false && $owner['team'] == 'kage'}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have maintained your position as {if $village != 'Syndicate'}kage{else}warlord{/if}.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['kage_replaced'] === true && $owner['team'] == 'challenger'}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have taken leadership by force.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['clan_replaced'] === false && $owner['team'] == 'leader'}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have maintained your position as the leader of your clan.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['clan_replaced'] === true && $owner['team'] == 'challenger'}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have taken leadership by force.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['pvp_experience'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have gained {$changes['pvp_experience']} pvp experience.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['pvp_streak'] != NULL && $changes['pvp_streak'] !== false }
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										{if $changes['pvp_streak'] != 1}
											You have won {$changes['pvp_streak']} consecutive pvp battles.
										{else}
											You have started your pvp battle streak.
										{/if}
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['health_gain'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										Your health pool has increased in size by {$changes['health_gain']}.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['gen_pool_gain'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										Your secondary pools and general stats have increased in size by {$changes['gen_pool_gain']}.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['ryo_gain'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have earned {$changes['ryo_gain']} ryo.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['clan'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										Clan points have been awarded.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['anbu'] != NULL}
									{if $changes['anbu'] == 'def'}
										<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

										<div>
											Defense anbu points have been awarded.
										</div>

										<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
									{else}
										<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

										<div>
											Assault anbu points have been awarded.
										</div>

										<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
									{/if}
								{/if}
								
								{if $changes['village_points'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have earned {$changes['village_points']} {if $village != 'Syndicate'}village{else}Syndicate{/if} {if $changes['village_points'] == 1}fund{else}funds{/if} for {if $village != 'Syndicate'}your village.{else}the Syndicate.{/if}
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
  
								{foreach $changes['jutsus']['level'] as $jutsu_name => $jutsu_level}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										Your jutsu, '{$jutsu_name}', is now level {$jutsu_level}.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/foreach}
  
								{foreach $changes['jutsus']['exp'] as $jutsu_name => $jutsu_exp}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										Your jutsu, '{$jutsu_name}', has gained {$jutsu_exp} exp.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/foreach}
  
								{if $changes['exp'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have earned {$changes['exp']} experience.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['bounty'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have collected a bounty of {$changes['bounty']} ryo.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['bounty_exp'] != NULL}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have earned {$changes['bounty_exp']} {if $village != 'Syndicate'}bounty hunter{else}mercenary{/if} experience.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['money'] != NULL && $changes['money'] > 0}
									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>

									<div>
										You have stolen {$changes['money']} ryo.
									</div>

									<div class="font-giant" style="color:blue">&nbsp;-&nbsp;</div>
								{/if}

							</div>
						</div>
					{/if}

					{if $no_negative_changes === false}
						<div class="page-grid grid-gap-none page-rows-min">
							<div class="solid-box bold table-cell">
								negative changes
							</div>

							<div class="plain-box stiff-grid stiff-column-3 page-grid-justify-stretch font-shrink-early">
								{if $changes['territory_battle_result'] != $owner['team'] && $changes['territory_battle_result']!= NULL}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have lost the {$changes['territory_battle_rank']} territory battle.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['territory_challenge_result'] != $owner['team'] && $changes['territory_challenge_result'] != NULL}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										With the conclusion of this battle {$changes['territory_challenge_result']}`s control of {$changes['territory_challenge_location']} has been secured.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['torn'] === false}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You failed to break your old high score!
										<br>
										Current Torn High Score: {$changes['torn_record']}
										<br>
										Attempt: {$changes['torn_attempt']}
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['kage_replaced'] === true && $owner['team'] == 'kage'}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have been removed from your position as {if $village != 'Syndicate'}kage{else}warlord{/if}.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['kage_replaced'] === false && $owner['team'] == 'challenger'}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have failed to take leadership.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								
								{if $changes['clan_replaced'] === true && $owner['team'] == 'leader'}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have been removed from your position as leader of the clan.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['clan_replaced'] === false && $owner['team'] == 'challenger'}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have failed to take leadership.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								
								{if $changes['jailed'] == true}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have been jailed.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['turn_outlaw'] == true}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have been exiled from your village.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
        
								{if $changes['heal_time'] != NULL}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have been hospitalized.
										<br/>
										You will be done healing in {$changes['heal_time']} {if $changes['heal_time'] == 1}minute{else}minutes{/if}.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
        
								{if $changes['diplomacy'] != NULL}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have lost {$changes['diplomacy']['amount']} diplomacy with {if $changes['diplomacy']['village'] != 'Syndicate'}{$changes['diplomacy']['village']} village{else} the Syndicate{/if}.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['loyalty'] != NULL}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have lost {$changes['loyalty']} loyalty with your village.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
  
								{if $changes['pvp_streak'] === false}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										Your pvp streak has been broken.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
  
								{foreach $changes['remove'] as $id => $name}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										Your item, '{$name}', has broken.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/foreach}
  
								{foreach $changes['durability'] as $name => $amount}
									{if $amount < 50}
										<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

										<div>
											Your item, '{$name}', was damaged.
											<br>
											It has {round($amount,2)} durability points remaining.
										</div>

										<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
									{/if}
								{/foreach}
								
								{foreach $changes['stack'] as $name => $amount}
									{if $amount <= 5}
										<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

										<div>
											you have {$amount} {$name} left.
										</div>

										<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
									{/if}
								{/foreach}
								
								{if $changes['money'] != NULL && $changes['money'] < 0}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										You have had {($changes['money'] * -1)} stolen from you.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
								
								{if $changes['bounty_collected'] != NULL}
									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>

									<div>
										Your bounty was collected by a {$changes['bounty_collected']}.
									</div>

									<div class="font-giant" style="color:orange">&nbsp;-&nbsp;</div>
								{/if}
							</div>
						</div>
					{/if}

				</div>

			{/if}

			<div class="page-grid {if $hide_extra_link != true}page-column-fr-2{/if}">
				<a class="page-button-fill" href="?id=113">Go To Battle History.</a>

				{if $hide_extra_link != true}
					<a class="page-button-fill" href="?id={$return_id}">Return to {$return_name}.</a>
				{/if}
			</div>

			<div class="page-box dark-solid-box" id="battle_log">
                
            	<div class="solid-box bold table-cell">
                    Battle Log
                </div>


            	{foreach array_combine( array_reverse( array_keys( $battle_log )), array_reverse( array_values( $battle_log ) ) ) as $round_number => $round_users}
            	    <div class="header_round{$round_number+1} table-cell bold plain-box">
            	    	Round: {$round_number + 1}
            	    </div>

            	    <div class="round{$round_number+1}_">

            	    	{foreach $round_users as $username => $userdata}

            	    	    <div class="round{$round_number+1}_{str_replace(' ','-',$username)} {if $username == $owner['name']}bracket-teal{else if $owner['team'] == $userdata['team']}bracket-blue{else}bracket-orange  {/if}">
            	    	    	{if $userdata['show_count'] == 'yes'}
            	    	    	    {assign var=the_users_name value=$username}
            	    	    	{else}
            	    	    	    {if strpos($username,'#') !== false}
            	    	    	        {assign var=the_users_name value=substr($username,0,strpos($username,'#') - 1)}
            	    	    	    {else}
            	    	    	        {assign var=the_users_name value= $username}
            	    	    	    {/if}
            	    	    	{/if}

            	    	    	{if $round_users[$userdata['target']]['show_count'] == 'yes'}
            	    	    	    {assign var=the_targets_name value=$userdata['target']}
            	    	    	{else}
            	    	    	    {if strpos($userdata['target'],'#') !== false}
            	    	    	        {assign var=the_targets_name value=substr($userdata['target'],0,strpos($userdata['target'],'#') - 1)}
            	    	    	    {else}
            	    	    	        {assign var=the_targets_name value=$userdata['target']}
            	    	    	    {/if}
            	    	    	{/if}

            	    	    	{if $userdata['order'] != ''}
            	    	    	    {$userdata['order']}- {$the_users_name}
            	    	    	{else}
            	    	    	    ?- {$the_users_name}
            	    	    	{/if}

            	    	    	{if $userdata['failure'] == 'failure'}
            	    	    	    was defeated before they could take their action.
            	    	    	{else if $userdata['type'] == 'respondent'}
            	    	    	    <strong style="font-size:16px;"> has responded to </strong><br>
            	    	    	    <strong style="font-size:16px;"> the call for help and has </strong><br>
            	    	    	    <strong style="font-size:16px;"> attacked </strong> {$the_targets_name} <strong style="font-size:16px;">!</strong>
            	    	    	{else if $userdata['name'] == 'Basic Attack'}
            	    	    	    <strong style="font-size:16px;"> attacked </strong> {$the_targets_name} <strong style="font-size:16px;">!</strong>
            	    	    	{else if $userdata['type'] == 'jutsu'}
            	    	    	    <strong style="font-size:16px;"> attacked </strong> {$the_targets_name} <strong style="font-size:16px;"> with </strong> <br>
            	    	    	    <strong style="font-size:16px;"> the jutsu {$userdata['name']}!</strong>
            	    	    	{else if $userdata['type'] == 'weapon'}
            	    	    	    <strong style="font-size:16px;"> attacked </strong> {$the_targets_name} <strong style="font-size:16px;"> with </strong> <br>
            	    	    	    <strong style="font-size:16px;"> their {$userdata['name']}!</strong>
            	    	    	{else if $userdata['type'] == 'item'}
            	    	    	    <strong style="font-size:16px;">used {$userdata['name']}</strong><br>
            	    	    	    <strong style="font-size:16px;"> on </strong> {$the_targets_name} <strong style="font-size:16px;">!</strong>
            	    	    	{else if $userdata['type'] == 'flee' }                                 
            	    	    	    tried to flee from the battle!
            	    	    	{else if $userdata['type'] == 'call_for_help'}
            	    	    	    called for help!
            	    	    	{else if $userdata['type'] == 'stunned'}
            	    	    	    was stunned this turn.
            	    	    	{else}
            	    	    	    was ?.
            	    	    	{/if}
            	    	    </div>

            	    	    <!-- ///////////////////////////////////////////// extra information \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->
            	    	    <div class="details_round{$round_number+1}_{str_replace(' ','-',$username)}"></div>
            	    	    <div class="details_round{$round_number+1}_{str_replace(' ','-',$username)}">
            	    	    	<div class="bracket-grey">
            	    	    		<details>
            	    	    			<summary>Effects: </summary>
            	    	    			{foreach $userdata['effects'] as $target => $effects}
            	    	    			    {foreach $effects as $effect => $messages}
            	    	    			        {$target}-> {$effect}:
            	    	    			        {foreach $messages as $key => $message}
            	    	    			            {if $key != 0}, {/if}
            	    	    			            {$message}
            	    	    			        {/foreach}
            	    	    			        <br/>
            	    	    			    {/foreach}
            	    	    			{/foreach}
            	    	    		</details>
                                </div>

            	    	    	{if isset($userdata['killed']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] != $round_users[ $userdata['killed'] ]['team'] }
            	    	    			    bracket-green">
            	    	    			{else}
            	    	    			    bracket-red">
            	    	    			{/if}

            	    	    			{$the_users_name} <strong style="font-size:16px;">defeated</strong>

            	    	    			{if $round_users[$userdata['killed']]['show_count'] == 'yes'}
            	    	    			    {$userdata['killed']}
            	    	    			{else}
            	    	    			    {if strpos($userdata['killed'],'#') !== false}
            	    	    			        {substr($userdata['killed'],0,strpos($userdata['killed'],'#') - 1)}
            	    	    			    {else}
            	    	    			        {$userdata['killed']}
            	    	    			    {/if}
            	    	    			{/if}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['died']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] == $round_users[ $userdata['died'] ]['team'] }
            	    	    			    bracket-green">
            	    	    			{else}
            	    	    			    bracket-red">
            	    	    			{/if}

            	    	    			{$the_users_name} <strong style="font-size:16px;">was defeated at the hands of</strong>

            	    	    			{if $round_users[$userdata['died']]['show_count'] == 'yes'}
            	    	    			    {$userdata['died']}
            	    	    			{else}
            	    	    			    {if strpos($userdata['died'],'#') !== false}
            	    	    			        {substr($userdata['died'],0,strpos($userdata['died'],'#') - 1)}
            	    	    			    {else}
            	    	    			        {$userdata['died']}
            	    	    			    {/if}
            	    	    			{/if}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['jutsu_description']) && $userdata['failure'] != 'failure'}
            	    	    		<div class="bracket-brown">
            	    	    			<strong><span style="font-size:13px;font-family: 'Merienda', cursive;-webkit-font-smoothing: subpixel-antialiased;">{$userdata['jutsu_description']}</span></strong>
            	    	    		</div>
            	    	    	{/if}
            	    	    	{if isset($userdata['damage_delt'])}
            	    	    	    {foreach $userdata['damage_delt'] as $damage_delt}
            	    	    	    	<!-- for later these are the colors for good: 179917 bad: a61919 nuetral: 7f7f7f brown: 653E1D-->
            	    	    	    	<div class="
            	    	    	    		{if $owner['team'] == $round_users[ $username ]['team'] }
            	    	    	    		    bracket-green">
            	    	    	    		{else}
            	    	    	    		    bracket-red">
            	    	    	    		{/if}
            	    	    	    		<strong>{if $damage_delt['oneHitKill'] === true}one hit kill{else}{$damage_delt['type']} damage dealt{/if}{if $damage_delt['aoe']} from aoe{/if}:</strong> {if $damage_delt['crit'] == true}<span style="color:darkorange;" title="critical">{$damage_delt['amount']}</span>{else}{$damage_delt['amount']}{/if}
            	    	    	    	</div>
            	    	    	    {/foreach}
            	    	    	{elseif isset($userdata['fled']) && $userdata['failure'] != 'failure'}
            	    	    	    {if $userdata['fled'] == true}
            	    	    	    	<div class="
            	    	    	    		{if $owner['team'] == $round_users[ $username ]['team'] }
            	    	    	    		    bracket-green">
            	    	    	    		{else}
            	    	    	    		    bracket-red">
            	    	    	    		{/if}
            	    	    	    		The Attempt was Successful.
            	    	    	    	</div>
            	    	    	    {else}
            	    	    	    	<div class="
            	    	    	    		{if $owner['team'] != $round_users[ $username ]['team'] }
            	    	    	    		    bracket-green">
            	    	    	    		{else}
            	    	    	    		    bracket-red">
            	    	    	    		{/if}
            	    	    	    		The Attempt Failed.
            	    	    	    	</div>
            	    	    	    {/if}
            	    	    	{/if}

            	    	    	{if isset($userdata['damage_over_time_delt'])}
            	    	    	    {foreach $userdata['damage_over_time_delt'] as $damage_over_time_delt}
            	    	    	    	<div class="
            	    	    	    		{if $owner['team'] == $round_users[ $username ]['team'] }
            	    	    	    		    bracket-green">
            	    	    	    		{else}
            	    	    	    		    bracket-red">
            	    	    	    		{/if}
            	    	    	    		<strong>{$damage_over_time_delt['type']} damage over time dealt{if $damage_over_time_delt['aoe']} from aoe{/if}:</strong> {if $damage_over_time_delt['crit'] == true}<span style="color:darkorange;" title="critical">{$damage_over_time_delt['amount']}</span>{else}{$damage_over_time_delt['amount']}{/if}
            	    	    	    	</div>
            	    	    	    {/foreach}
            	    	    	{/if}

            	    	    	{if isset($userdata['recoil']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] == $round_users[ $username ]['team'] }
            	    	    			    bracket-red">
            	    	    			{else}
            	    	    			    bracket-green">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>recoil damage:</strong>{$userdata['recoil']}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['absorb'])}
            	    	    		<div class="
            	    	    			{if $owner['team'] == $round_users[ $username ]['team'] }
            	    	    			    bracket-red">
            	    	    			{else}
            	    	    			    bracket-green">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>damage absorbed:</strong>{$userdata['absorb']}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['leach']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] != $round_users[ $username ]['team'] }
            	    	    			    bracket-red">
            	    	    			{else}
                		    				bracket-green">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>health leached:</strong> {$userdata['leach']}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['heal_delt']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] != $round_users[ $username ]['team'] }
                		    				bracket-red">
            	    	    			{else}
                		    				bracket-green">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>health restored:</strong> {$userdata['heal_delt']}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['heal_over_time_delt']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] != $round_users[ $username ]['team'] }
                		    				bracket-red">
            	    	    			{else}
                		    				bracket-green">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>health restored over time:</strong> {$userdata['heal_over_time_delt']}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['reflect']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] == $round_users[ $username ]['team'] }
                		    				bracket-red">
            	    	    			{else}
                		    				bracket-green">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>damage reflected:</strong> {$userdata['reflect']}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if is_numeric($userdata['rob'])  && $userdata['failure'] != 'failure'}
            	    	    		<div class="
            	    	    			{if $owner['team'] == $round_users[ $username ]['team'] }
                		    				bracket-green">
            	    	    			{else}
                		    				bracket-red">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>stole: </strong>{$userdata['rob']}
            	    	    		</div>
            	    	    	{elseif $userdata['rob'] == 'fail' && $userdata['failure'] != 'failure'}
            	    	    		<div class="
            	    	    			{if $owner['team'] != $round_users[ $username ]['team'] }
                		    				bracket-green">
            	    	    			{else}
                		    				bracket-red">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>Failed to rob anything.</strong>
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['disable'])}
            	    	    		<div class="
            	    	    			{if $owner['team'] == $round_users[ $username ]['team'] }
                		    				bracket-green">
            	    	    			{else}
                		    				bracket-red">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>disabled: </strong>{$userdata['disable']}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['stagger']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] == $round_users[ $username ]['team'] }
                		    				bracket-green">
            	    	    			{else}
                		    				bracket-red">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>staggered: </strong>{$userdata['stagger']}
            	    	    		</div>
            	    	    	{/if}

            	    	    	{if isset($userdata['stun']) }
            	    	    		<div class="
            	    	    			{if $owner['team'] == $round_users[ $username ]['team'] }
                		    				bracket-green">
            	    	    			{else}
                		    				bracket-red">
            	    	    			{/if}
            	    	    			
            	    	    			<strong>stunned: </strong>{$userdata['stun']}
            	    	    		</div>
            	    	    	{/if}
                            </div>
            	    	{/foreach}
            	    </div>
            	{/foreach}



            </div>

			{if in_array($_SESSION['uid'], [2015883, 2486, 1986872, 2001381])}
				<div>
					<pre>
						{var_dump($damage_multiplier)}
					</pre>

					<br/>
					<br/>

					{$flee_1}    

					<br/>

					{$flee_2}    

					<br/>

					{$flee_3}  
				</div>
			{/if}

		</div>
	</form>

</div>