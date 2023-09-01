<link href='//fonts.googleapis.com/css?family=Calligraffitti' rel='stylesheet'>
{literal}
  <script>
    window.addEventListener('DOMContentLoaded', function() {
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
    });
  </script>
{/literal}
<form action="" id="battle_form" method="post">
  <div id="summary" summary="yes"></div>
  <table style="margin-left:auto;margin-right:auto;border:none;position:relative;top:-10px;width:100%;max-width:960px;">
    {if $hide_top !== true}
  	  <tr>
  	  	<td>
  	  		<table class="table" align="left" style="width:100%;">
  	  			<tbody>
  	  				<tr><td class="subHeader" colspan="3">Battle Summary</td></tr>
  
              {if $owner['win_lose']}
              <tr>
                <td style="vertical-align:middle;font-family: 'Calligraffitti';font-size: 34px;border-right:1px solid;" width="10%">
  	  					  	<b>
  	  					  		Victory
  	  					  	</b>
  	  					  </td>
                </tr>
              {else if $owner['flee'] === true}
              <tr>
                  <td style="vertical-align:middle;font-family: 'Calligraffitti';font-size: 34px;border-right:1px solid;" width="10%">
  	  	  					<b>
  	  		  					Fled
  	  			  			</b>
  	  				  	</td>
                </tr>
              {else}
                <tr>
  	  					  <td style="vertical-align:middle;font-family: 'Calligraffitti';font-size: 34px;border-right:1px solid;" width="10%">
  	  						  <b>
  	  							  Defeat
  	  						  </b>
  	  					  </td>
                </tr>
              {/if}
  
              <tr>
  	  					<td style="border-right:1px solid;padding:0;">
                  
                  <table style="border:none;table-layout: fixed;width:100%">
                    {if $no_positive_changes === false}<col style="width:50%;" />{/if}
                    {if $no_negative_changes === false}<col style="width:50%;" />{/if}
                    
                    {if $hide_changes !== true}
                    
                      
                      <tr>
                        
                        {if $no_positive_changes === false}
                          <td class="tableColumns" style="font-size:24px;">
                            positive changes
                          </td>
                        {/if}
  
                        {if $no_negative_changes === false}
                          <td class="tableColumns" style="font-size:24px;">
                            negative changes
                          </td>
                        {/if}
                        
                      </tr>
                      <tr>
  
                        <!-- left column in summary contains all good things -->
                        {if $no_positive_changes === false}
                          <td>
  
                            {if $changes['territory_battle_result'] == $owner['team'] && $changes['territory_battle_result'] != NULL}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have won the {$changes['territory_battle_rank']} territory battle.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
  
                            {if $changes['territory_challenge_result'] == $owner['team'] && $changes['territory_challenge_result'] != NULL}
                              <span style="color:blue;font-size:24px;">-</span>
                              With the conclusion of this battle you have
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              secured {$owner['team']}`s control of {$changes['territory_challenge_location']}.
                              <br>
                              <br>
                            {/if}
  
                            {if $changes['torn'] === true}
                              <span style="color:blue;font-size:24px;">-</span>
                              You broke your old high score!
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              Old Torn High Score: {$changes['torn_record']}
                              <br>
                              New Torn High Score: {$changes['torn_attempt']}
                              <br>
                              <br>
                            {/if}
  
                            {if $changes['kage_replaced'] === false && $owner['team'] == 'kage'}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have maintained your position as {if $village != 'Syndicate'}kage{else}warlord{/if}.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['kage_replaced'] === true && $owner['team'] == 'challenger'}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have taken leadership by force.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['clan_replaced'] === false && $owner['team'] == 'leader'}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have maintained your position as the leader of your clan.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['clan_replaced'] === true && $owner['team'] == 'challenger'}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have taken leadership by force.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['pvp_experience'] != NULL}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have gained {$changes['pvp_experience']} pvp experience.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
  
                            {if $changes['pvp_streak'] != NULL && $changes['pvp_streak'] !== false }
                              <span style="color:blue;font-size:24px;">-</span>
                              {if $changes['pvp_streak'] != 1}
                                You have won {$changes['pvp_streak']} consecutive pvp battles.
                              {else}
                                You have started your pvp battle streak.
                              {/if}
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
  
                            {if $changes['health_gain'] != NULL}
                            <span style="color:blue;font-size:24px;">-</span>
                            Your health pool has increased in size
                            <span style="color:blue;font-size:24px;">-</span>
                            <br>
                            by {$changes['health_gain']}.
                            <br>
                            <br>
                            {/if}
  
                            {if $changes['gen_pool_gain'] != NULL}
                            <span style="color:blue;font-size:24px;">-</span>
                            Your secondary pools and general stats
                            <span style="color:blue;font-size:24px;">-</span>
                            <br>
                            have increased in size by {$changes['gen_pool_gain']}.
                            <br>
                            <br>
                            {/if}
  
                            {if $changes['ryo_gain'] != NULL}
                            <span style="color:blue;font-size:24px;">-</span>
                            You have earned {$changes['ryo_gain']} ryo.
                            <span style="color:blue;font-size:24px;">-</span>
                            <br>
                            <br>
                            {/if}
  
                            {if $changes['clan'] != NULL}
                            <span style="color:blue;font-size:24px;">-</span>
                              Clan points have been awarded.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
  
                            {if $changes['anbu'] != NULL}
                              {if $changes['anbu'] == 'def'}
                                <span style="color:blue;font-size:24px;">-</span>
                                Defense anbu points have been awarded.
                                <span style="color:blue;font-size:24px;">-</span>
                                <br>
                                <br>
                              {else}
                                <span style="color:blue;font-size:24px;">-</span>
                                Assault anbu points have been awarded.
                                <span style="color:blue;font-size:24px;">-</span>
                                <br>
                                <br>
                              {/if}
                            {/if}
                            
                            {if $changes['village_points'] != NULL}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have earned {$changes['village_points']} {if $village != 'Syndicate'}village{else}Syndicate{/if} {if $changes['village_points'] == 1}fund{else}funds{/if} for
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              {if $village != 'Syndicate'}
                                your village.
                              {else}
                                the Syndicate.
                              {/if}
                              <br>
                              <br>
                            {/if}
  
                            {foreach $changes['jutsus']['level'] as $jutsu_name => $jutsu_level}
                              <span style="color:blue;font-size:24px;">-</span>
                              Your jutsu, '{$jutsu_name}',
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                               is now level {$jutsu_level}.
                              <br>
                              <br>
                            {/foreach}
  
                            {foreach $changes['jutsus']['exp'] as $jutsu_name => $jutsu_exp}
                              <span style="color:blue;font-size:24px;">-</span>
                              Your jutsu, '{$jutsu_name}', has gained {$jutsu_exp} exp.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/foreach}
  
                            {if $changes['exp'] != NULL}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have earned {$changes['exp']} experience.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                                {if $changes['bounty'] != NULL}
                            <span style="color:blue;font-size:24px;">-</span>
                            You have collected a bounty of {$changes['bounty']} ryo.
                            <span style="color:blue;font-size:24px;">-</span>
                            <br>
                            <br>
                            {/if}
                            
                                {if $changes['bounty_exp'] != NULL}
                            <span style="color:blue;font-size:24px;">-</span>
                            You have earned {$changes['bounty_exp']} {if $village != 'Syndicate'}bounty hunter{else}mercenary{/if} experience.
                            <span style="color:blue;font-size:24px;">-</span>
                            <br>
                            <br>
                            {/if}
                            
                            {if $changes['money'] != NULL && $changes['money'] > 0}
                              <span style="color:blue;font-size:24px;">-</span>
                              You have stolen {$changes['money']} ryo.
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
  
                          </td>
                        {/if}
  
                        <!-- right column in summary contains all bad things -->
                        {if $no_negative_changes === false}
                          <td>
                            
                            {if $changes['territory_battle_result'] != $owner['team'] && $changes['territory_battle_result']!= NULL}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have lost the {$changes['territory_battle_rank']} territory battle.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
  
                            {if $changes['territory_challenge_result'] != $owner['team'] && $changes['territory_challenge_result'] != NULL}
                              <span style="color:blue;font-size:24px;">-</span>
                              With the conclusion of this battle {$changes['territory_challenge_result']}`s
                              <span style="color:blue;font-size:24px;">-</span>
                              <br>
                              control of {$changes['territory_challenge_location']} has been secured.
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['torn'] === false}
                              <span style="color:orange;font-size:24px;">-</span>
                              You failed to break your old high score!
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              Current Torn High Score: {$changes['torn_record']}
                              <br>
                              Attempt: {$changes['torn_attempt']}
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['kage_replaced'] === true && $owner['team'] == 'kage'}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have been removed from your position
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              as {if $village != 'Syndicate'}kage{else}warlord{/if}.
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['kage_replaced'] === false && $owner['team'] == 'challenger'}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have failed to take leadership.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            
                            {if $changes['clan_replaced'] === true && $owner['team'] == 'leader'}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have been removed from your position
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              as leader of the clan.
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['clan_replaced'] === false && $owner['team'] == 'challenger'}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have failed to take leadership.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            
                            {if $changes['jailed'] == true} <!-- used for kage battles-->
                              <span style="color:orange;font-size:24px;">-</span>
                              You have been jailed.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['turn_outlaw'] == true}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have been exiled from your village.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
        
                            {if $changes['heal_time'] != NULL}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have been hospitalized.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              You will be done healing in {$changes['heal_time']} {if $changes['heal_time'] == 1}minute{else}minutes{/if}.
                              <br>
                              <br>
                            {/if}
        
                            {if $changes['diplomacy'] != NULL}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have lost {$changes['diplomacy']['amount']} diplomacy with
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              {if $changes['diplomacy']['village'] != 'Syndicate'}{$changes['diplomacy']['village']} village{else} the Syndicate{/if}.
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['loyalty'] != NULL}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have lost {$changes['loyalty']} loyalty with your village.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
  
                            {if $changes['pvp_streak'] === false}
                              <span style="color:orange;font-size:24px;">-</span>
                              Your pvp streak has been broken.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
  
                            {foreach $changes['remove'] as $id => $name}
                              <span style="color:orange;font-size:24px;">-</span>
                              Your item, '{$name}', has broken.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/foreach}
  
                            {foreach $changes['durability'] as $name => $amount}
                              {if $amount < 50}
                                <span style="color:orange;font-size:24px;">-</span>
                                Your item, '{$name}', was damaged.
                                <span style="color:orange;font-size:24px;">-</span>
                                <br>
                                It has {round($amount,2)} durability points remaining.
                                <br>
                                <br>
                              {/if}
                            {/foreach}
                            
                            {foreach $changes['stack'] as $name => $amount}
                              {if $amount <= 5}
                                <span style="color:orange;font-size:24px;">-</span>
                                you have {$amount} {$name} left.
                                <span style="color:orange;font-size:24px;">-</span>
                                <br>
                                <br>
                              {/if}
                            {/foreach}
                            
                            {if $changes['money'] != NULL && $changes['money'] < 0}
                              <span style="color:orange;font-size:24px;">-</span>
                              You have had {($changes['money'] * -1)} stolen from you.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            {if $changes['bounty_collected'] != NULL}
                              <span style="color:orange;font-size:24px;">-</span>
                              Your bounty was collected by a {$changes['bounty_collected']}.
                              <span style="color:orange;font-size:24px;">-</span>
                              <br>
                              <br>
                            {/if}
                            
                            <!--    {if $changes['times_used'] != NULL}
                            <span style="color:grey;font-size:24px;">-</span>
                            times used: {var_dump($changes['times_used'])}
                            <span style="color:grey;font-size:24px;">-</span>
                            <br>
                            <br>
                            {/if}-->
                              
                          </td>
                        {/if}
                      </tr>
                    
                    {/if}
                    
                  </table>
                    
                </td>
  	  				</tr>
              <tr>
                <td style="border-right:1px solid;">
                  <a href="?id=113">Battle History</a>{if $hide_extra_link != true} - <a href="?id={$return_id}">Return to {$return_name}.</a>{/if}
                </td>
              </tr>
  	  			</tbody>
  	  		</table>
  	  	</td>
  	  </tr>
    {/if}
  	<tr>
      <td style="text-align:left;padding:0px;padding-right:1px;" width="60%" valign="top">
        <table id="battle_log" align="center" width="97.5%">
          <tr>
  											<td class="subHeader" colspan="3" style="padding:5px;">Battle Log <div style="font-family: 'Asul', sans-serif;font-size:12px;-webkit-filter: blur(0.000001px); -webkit-font-smoothing: subpixel-antialiased;">{$time}</div></td>
  										</tr>
  										<tr>
  											<td style="padding:0px;padding-right:0px;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;">
                          <table style="width:100%;border:none;">
                            {foreach array_combine( array_reverse( array_keys( $battle_log )), array_reverse( array_values( $battle_log ) ) ) as $round_number => $round_users}
                              <tr class="header_round{$round_number+1}">
                                  <td class="tableColumns" style="border:1px solid black; font-size:16px;">
                                    Round: {$round_number + 1}
                                  </td>
                              </tr>
                            
                              <tr style="background-color: #ECECEC">
                                <td class="round{$round_number+1}_" style="padding:0px;padding-right:3px;">
                                  <table style="width:100%;border:none;">
                                    {foreach $round_users as $username => $userdata}
                                      <tr class="round{$round_number+1}_{str_replace(' ','-',$username)}"><td style="padding:3px"></td></tr>
                                      <tr class="round{$round_number+1}_{str_replace(' ','-',$username)}">
                                        <td></td>
                                        <td class="tableColumns" style="height:25px;border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                                                                                                                           background: -webkit-radial-gradient(circle, #EAE3D9 95%, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 5%);
                                                                                                                                                           background: -o-radial-gradient     (circle, #EAE3D9 95%, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 5%);
                                                                                                                                                           background: -moz-radial-gradient   (circle, #EAE3D9 95%, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 5%);
                                                                                                                                                           background: radial-gradient        (circle, #EAE3D9 95%, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 5%);">
                                          <!--background: repeating-linear-gradient( 0deg,  #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if}, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 0px,  #bfbcac 6px, #bfbcac 22px);">-->
  
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
                                          
                                          {else if $userdata['type'] == 'flee'}                                 
                                            tried to flee from the battle!
                                          {else if $userdata['type'] == 'call_for_help'}
                                            called for help!
                                          {else if $userdata['type'] == 'stunned'}
                                            was stunned this turn.
                                          {else}
                                            ?.
                                          {/if}
                                        </td>
                                      </tr>
                                      <!-- ///////////////////////////////////////////// extra information \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->
                                      <tr class="details_round{$round_number+1}_{str_replace(' ','-',$username)}"></tr>
                                      <tr class="details_round{$round_number+1}_{str_replace(' ','-',$username)}">
                                        <td></td>
                                        <td>
                                          
                                          <table style="width:100%;border:none;">
                                            <tr>
                                              <td></td>
                                              <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                       background: -webkit-radial-gradient(circle, #EAE3D9 95%, #808080 5%);
                                                       background: -o-radial-gradient     (circle, #EAE3D9 95%, #808080 5%);
                                                       background: -moz-radial-gradient   (circle, #EAE3D9 95%, #808080 5%);
                                                       background: radial-gradient        (circle, #EAE3D9 95%, #808080 5%);
                                                       padding-left: 6px;padding-right: 6px;">
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
                                              </td>
                                            </tr>
                                            <tr><td></td></tr>
                                          </table>
                                          
                                          {if isset($userdata['killed']) }
                                           <table style="width:100%;border:none;">
                                             <tr>
                                               <td></td>
                                               <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                 {if $owner['team'] != $round_users[ $userdata['killed'] ]['team'] }
                                                   background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                   background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                   background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                   background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                 {else}
                                                   background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                   background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                   background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                   background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                 {/if}
                                                 padding-left: 6px;padding-right: 6px;">
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
                                               </td>
                                             </tr>
                                             <tr><td></td></tr>
                                           </table>
                                          {/if}
  
                                          {if isset($userdata['died']) }
                                            <table style="width:100%;border:none;">
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $userdata['died'] ]['team'] }
                                                    background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                    background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                    background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                    background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {else}
                                                    background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                    background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                    background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                    background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">
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
                                                </td>
                                              </tr>
                                              <tr><td></td></tr>
                                            </table>
                                          {/if}
                                          
                                          <table style="width:100%;border:none;">
                                            {if isset($userdata['jutsu_description']) && $userdata['failure'] != 'failure'}
                                              <tr>
                                                <td></td>
                                                  <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                        background: -webkit-radial-gradient(circle, #EAE3D9 95%, #653E1D 5%);
                                                        background: -o-radial-gradient     (circle, #EAE3D9 95%, #653E1D 5%);
                                                        background: -moz-radial-gradient   (circle, #EAE3D9 95%, #653E1D 5%);
                                                        background: radial-gradient        (circle, #EAE3D9 95%, #653E1D 5%);
                                                        padding-left: 6px;padding-right: 6px;">
  
                                                    <strong><span style="font-size:13px;font-family: 'Merienda', cursive;-webkit-font-smoothing: subpixel-antialiased;">{$userdata['jutsu_description']}</span></strong>
                                                  </td>
                                              </tr>
                                              <tr><td></td></tr>
                                            {/if}
                                            {if isset($userdata['damage_delt']) }
                                              {foreach $userdata['damage_delt'] as $damage_delt }
                                                <tr>
                                                  <td></td>
                                                  <!-- for later these are the colors for good: 179917 bad: a61919 nuetral: 7f7f7f brown: 653E1D-->
                                                
                                                  <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                    {if $owner['team'] == $round_users[ $username ]['team'] }
                                                        background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                        background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                        background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                        background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                    {else}
                                                        background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                        background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                        background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                        background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                    {/if}
                                                    padding-left: 6px;padding-right: 6px;">
                                                    
                                                    <strong>{if $damage_delt['oneHitKill'] === true}one hit kill{else}{$damage_delt['type']} damage dealt{/if}{if $damage_delt['aoe']} from aoe{/if}:</strong> {if $damage_delt['crit'] == true}<span style="color:darkorange;" title="critical">{$damage_delt['amount']}</span>{else}{$damage_delt['amount']}{/if}
                                                  </td>
                                                </tr>
                                              {/foreach}
                                              <tr><td></td></tr>
  
                                            {else if isset($userdata['fled']) && $userdata['failure'] != 'failure'}
                                              {if $userdata['fled'] == true}
                                                <tr>
                                                  <td></td>
                                                  <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                    {if $owner['team'] == $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                    {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                    {/if}
                                                    padding-left: 6px;padding-right: 6px;">
                                                    The Attempt was Successful.
                                                  </td>
                                                </tr>
                                                <tr><td></td></tr>
                                              {else}
                                                <tr>
                                                  <td></td>
                                                  <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                    {if $owner['team'] != $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                    {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                    {/if}
                                                    padding-left: 6px;padding-right: 6px;">
                                                    The Attempt Failed.
                                                  </td>
                                                </tr>
                                              {/if}
                                            {/if}
                                              
                                            {if isset($userdata['damage_over_time_delt']) }
                                              {foreach $userdata['damage_over_time_delt'] as $damage_over_time_delt}
                                                <tr>
                                                  <td></td>
                                                  <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                    {if $owner['team'] == $round_users[ $username ]['team'] }
                                                        background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                        background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                        background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                        background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                    {else}
                                                        background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                        background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                        background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                        background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                    {/if}
                                                    padding-left: 6px;padding-right: 6px;">

                                                    <strong>{$damage_over_time_delt['type']} damage over time dealt{if $damage_over_time_delt['aoe']} from aoe{/if}:</strong> {if $damage_over_time_delt['crit'] == true}<span style="color:darkorange;" title="critical">{$damage_over_time_delt['amount']}</span>{else}{$damage_over_time_delt['amount']}{/if}
                                                  </td>
                                                </tr>
                                              {/foreach}
                                            {/if}
                                            
                                            {if isset($userdata['recoil']) }
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">

                                                  <strong>recoil damage:</strong> {$userdata['recoil']}
                                                </td>
                                              </tr>
                                            {/if}
                                            
                                            {if isset($userdata['leach']) }
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] != $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">

                                                  <strong>health leached:</strong> {$userdata['leach']}
                                                </td>
                                              </tr>
                                            {/if}
                                            
                                            {if isset($userdata['heal_delt']) }
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] != $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">

                                                  <strong>health restored:</strong> {$userdata['heal_delt']}
                                                </td>
                                              </tr>
                                            {/if}
                                            
                                            {if isset($userdata['heal_over_time_delt']) }
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] != $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">

                                                  <strong>health restored over time:</strong> {$userdata['heal_over_time_delt']}
                                                </td>
                                              </tr>
                                            {/if}
                                              
                                            {if isset($userdata['absorb']) }
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">

                                                  <strong>damage absorbed:</strong> {$userdata['absorb']}
                                                </td>
                                              </tr>
                                            {/if}
                                            
                                            {if isset($userdata['reflect']) }
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">

                                                  <strong>damage reflected:</strong> {$userdata['reflect']}
                                                </td>
                                              </tr>
                                            {/if}
                                              
                                            {if is_numeric($userdata['rob']) && $userdata['failure'] != 'failure'}
                                              <tr><td></td></tr>
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">
  
                                                  <strong>stole: </strong>{$userdata['rob']}
                                                </td>
                                              </tr>
                                            {else if $userdata['rob'] == 'fail' && $userdata['failure'] != 'failure'}
                                              <tr><td></td></tr>
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] != $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">
  
                                                  <strong>Failed to rob anything.</strong>
                                                </td>
                                              </tr>
                                            {/if}
                                            
                                            {if isset($userdata['disable']) }
                                              <tr><td></td></tr>
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">
  
                                                  <strong>disabled: </strong>{$userdata['disable']}
                                                </td>
                                              </tr>
                                            {/if}
                                            
                                            {if isset($userdata['stagger']) }
                                              <tr><td></td></tr>
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">
  
                                                  <strong>staggered: </strong>{$userdata['stagger']}
                                                </td>
                                              </tr>
                                            {/if}
                                            
                                            {if isset($userdata['stun']) }
                                              <tr><td></td></tr>
                                              <tr>
                                                <td></td>
                                                <td class="tableColumns" style="border:1px solid black; font-size:15px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {else}
                                                      background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                      background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {/if}
                                                  padding-left: 6px;padding-right: 6px;">
  
                                                  <strong>stunned: </strong>{$userdata['stun']}
                                                </td>
                                              </tr>
                                            {/if}
                                          </table>
                                        </td>
                                      </tr>
                                    {/foreach}
                                  </table>
                                </td>
                              </tr>
                            
                            {/foreach}
                          </table>
                        </td>
  										</tr>
      <!--
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
      -->
    
        </table>
      </td>
	  </tr>
  </table>
</form>

