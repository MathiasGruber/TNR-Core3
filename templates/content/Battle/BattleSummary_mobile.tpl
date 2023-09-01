<tr>
  <td>
    {if $hide_top !== true}
      <!-- header -->
      <tr>
        <td>
          <headerText color="black">Battle Summary</headerText>
          <br>
          <headerText color="{if $owner['win_lose']}blue{else if $owner['flee'] === true}black{else}red{/if}" >{if $owner['win_lose']}Victory{else if $owner['flee'] === true}Fled{else}Defeat{/if}</headerText>
          <br>
        </td>
      </tr>
      <br>
      <br>
      {if $hide_changes !== true}
    
        <!-- positive changes -->
        {if $no_positive_changes === false}
          <tr color="blue">
            <td>
              <headerText color="soliddarkblue">Positive Changes</headerText>
            </td>
          </tr>
        
          {if $changes['territory_battle_result'] == $owner['team'] && $changes['territory_battle_result'] != NULL}
            <text>You have won the {$changes['territory_battle_rank']} territory battle.</text>
            <br>
          {/if}
  
          {if $changes['territory_challenge_result'] == $owner['team'] && $changes['territory_challenge_result'] != NULL}
            <text>With the conclusion of this battle you have secured {$owner['team']}`s control of {$changes['territory_challenge_location']}.</text>
            <br>
          {/if}
  
          {if $changes['torn'] === true}
            <text>You broke your old high score!</text>
            <text>Old Torn High Score: {$changes['torn_record']}</text>
            <text>New Torn High Score: {$changes['torn_attempt']}</text>
            <br>
          {/if}
  
          {if $changes['kage_replaced'] === false && $owner['team'] == 'kage'}
            <text>You have maintained your position as {if $village != 'Syndicate'}kage{else}warlord{/if}.</text>
            <br>
          {/if}
          
          {if $changes['kage_replaced'] === true && $owner['team'] == 'challenger'}
            <text>You have taken leadership by force.</text>
            <br>
          {/if}
          
          {if $changes['clan_replaced'] === false && $owner['team'] == 'leader'}
            <text>You have maintained your position as the leader of your clan.</text>
            <br>
          {/if}
          
          {if $changes['clan_replaced'] === true && $owner['team'] == 'challenger'}
            <text>You have taken leadership by force.</text>
            <br>
          {/if}
          
          {if $changes['pvp_experience'] != NULL}
            <text>You have gained {$changes['pvp_experience']} pvp experience.</text>
            <br>
          {/if}
  
          {if $changes['pvp_streak'] != NULL && $changes['pvp_streak'] !== false }
            {if $changes['pvp_streak'] != 1}
              <text>You have won {$changes['pvp_streak']} consecutive pvp battles.</text>
            {else}
              <text>You have started your pvp battle streak.</text>
            {/if}
            <br>
          {/if}
  
          {if $changes['health_gain'] != NULL}
            <text>Your health pool has increased in size by {$changes['health_gain']}.</text>
            <br>
          {/if}
  
          {if $changes['gen_pool_gain'] != NULL}
            <text>Your secondary pools and general stat have increased in size by {$changes['gen_pool_gain']}.</text>
            <br>
          {/if}
  
          {if $changes['ryo_gain'] != NULL}
            <text>You have earned {$changes['ryo_gain']} ryo.</text>
            <br>
          {/if}
  
          {if $changes['clan'] != NULL}
            <text>Clan points have been awarded.</text>
            <br>
          {/if}
  
          {if $changes['anbu'] != NULL}
            {if $changes['anbu'] == 'def'}
              <text>Defense anbu points have been awarded.</text>
              <br>
            {else}
              <text>Assault anbu points have been awarded.</text>
              <br>
            {/if}
          {/if}
          
          {if $changes['village_points'] != NULL}
            <text>You have earned {$changes['village_points']} {if $village != 'Syndicate'}village{else}Syndicate{/if} {if $changes['village_points'] == 1}fund{else}funds{/if} for{if $village != 'Syndicate'} your village.{else} the Syndicate.{/if}</text>
            <br>
          {/if}
  
          {foreach $changes['jutsus']['level'] as $jutsu_name => $jutsu_level}
            <text>Your jutsu, '{$jutsu_name}', is now level {$jutsu_level}.</text>
            <br>
          {/foreach}
  
          {foreach $changes['jutsus']['exp'] as $jutsu_name => $jutsu_exp}
            <text>Your jutsu, '{$jutsu_name}', has gained {$jutsu_exp} exp.</text>
            <br>
          {/foreach}
  
          {if $changes['exp'] != NULL}
            <text>You have earned {$changes['exp']} experience.</text>
            <br>
          {/if}
          
          {if $changes['bounty'] != NULL}
            <text>You have collected a bounty of {$changes['bounty']} ryo.</text>
            <br>
          {/if}
          
          {if $changes['bounty_exp'] != NULL}
            <text>You have earned {$changes['bounty_exp']} {if $village != 'Syndicate'}bounty hunter{else}mercenary{/if} experience.</text>
            <br>
          {/if}
          
          {if $changes['money'] != NULL && $changes['money'] > 0}
            <text>You have stolen {$changes['money']} ryo.</text>
            <br>
          {/if}
          <br>
          <br>
        {/if}
      
        <!-- negative changes -->
        {if $no_negative_changes === false}
          <tr color="red" style="text-shadow: 0px 0px 2px black, 1px 1px #323232;">
            <td>
              <headerText color="soliddarkred">Negative Changes</headerText>
            </td>
          </tr>
        
          {if $changes['territory_battle_result'] != $owner['team'] && $changes['territory_battle_result']!= NULL}
            <text>You have lost the {$changes['territory_battle_rank']} territory battle.</text>
            <br>
          {/if}
  
          {if $changes['territory_challenge_result'] != $owner['team'] && $changes['territory_challenge_result'] != NULL}
            <text>With the conclusion of this battle {$changes['territory_challenge_result']}`s control of {$changes['territory_challenge_location']} has been secured</text>
            <br>
          {/if}
          
          {if $changes['torn'] === false}
            <text>You failed to break your old high score!</text>
            <text>Current Torn High Score: {$changes['torn_record']}</text>
            <text>Attempt: {$changes['torn_attempt']}</text>
            <br>
          {/if}
          
          {if $changes['kage_replaced'] === true && $owner['team'] == 'kage'}
            <text>You have been removed from your position as {if $village != 'Syndicate'}kage{else}warlord{/if}.</text>
            <br>
          {/if}
          
          {if $changes['kage_replaced'] === false && $owner['team'] == 'challenger'}
            <text>You have failed to take leadership.</text>
            <br>
          {/if}
          
          
          {if $changes['clan_replaced'] === true && $owner['team'] == 'leader'}
            <text>You have been removed from your position as leader of the clan.</text>
            <br>
          {/if}
          
          {if $changes['clan_replaced'] === false && $owner['team'] == 'challenger'}
            <text>You have failed to take leadership.</text>
            <br>
          {/if}
          
          
          {if $changes['jailed'] == true} <!-- used for kage battles-->
              <text>You have been jailed.</text>
            <br>
          {/if}
          
          {if $changes['turn_outlaw'] == true}
            <text>You have been exiled from your village.</text>
            <br>
          {/if}
        
          {if $changes['heal_time'] != NULL}
            <text>You have been hospitalized.</text>
            <text>You will be done healing in {$changes['heal_time']} {if $changes['heal_time'] == 1}minute{else}minutes{/if}.</text>
            <br>
          {/if}
        
          {if $changes['diplomacy'] != NULL}
            <text>You have lost {$changes['diplomacy']['amount']} diplomacy with {if $changes['diplomacy']['village'] != 'Syndicate'}{$changes['diplomacy']['village']} village{else} the Syndicate{/if}.</text>
            <br>
          {/if}
          
          {if $changes['loyalty'] != NULL}
            <text>You have lost {$changes['loyalty']} loyalty with your village.</text>
            <br>
          {/if}
  
          {if $changes['pvp_streak'] === false}
            <text>Your pvp streak has been broken.</text>
            <br>
          {/if}
  
          {foreach $changes['remove'] as $id => $name}
            <text>Your item, '{$name}', has broken.</text>
            <br>
          {/foreach}
  
          {foreach $changes['durability'] as $name => $amount}
            {if $amount < 50}
              <text>Your item, '{$name}', was damaged.</text>
              <text>It has {round($amount,2)} durability points remaining.</text>
              <br>
            {/if}
          {/foreach}
          
          {foreach $changes['stack'] as $name => $amount}
            {if $amount <= 5}
              <text>you have {$amount} {$name} left.</text>
              <br>
            {/if}
          {/foreach}
          
          {if $changes['money'] != NULL && $changes['money'] < 0}
            <text>You have had {($changes['money'] * -1)} stolen from you.</text>
            <br>
          {/if}
          
          {if $changes['bounty_collected'] != NULL}
            <text>You bounty was collected by a {$changes['bounty_collected']}.</text>
            <br>
          {/if}
          
        {/if}
    
      {/if}
    {/if}
    
    <!-- //////////////////// battle log \\\\\\\\\\\\\\\\\\\\ -->
      <a color="darkbrown" contentControl="battleLog">Battle Log</a>
      <contentElement name="battleLog">
        <!-- foreach round -->
        {foreach array_combine( array_reverse( array_keys( $battle_log )), array_reverse( array_values( $battle_log ) ) ) as $round_number => $round_users}
        
          <tr>
            <td>
              <a color="fadedblack" contentControl="Round-{$round_number+1}">Round-{$round_number+1}</a>
              
              <contentElement name="Round-{$round_number+1}">

                <!-- foreach user -->
                {foreach $round_users as $username => $userdata}
                  <tr>
                    <td>
                      <a color="{if $username == $owner['name']}blue{else if $owner['team'] == $round_users[ $username ]['team']}green{else}red{/if}" contentControl="Round-{$round_number+1}-User-{$username}">{$username}</a>
                      
                      <contentElement name="Round-{$round_number+1}-User-{str_replace(' ','-',$username)}">
                        
                        <!-- displaying action informaiton -->
                        <tr color="dim">
                          <td>
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

                            {if $userdata['failure'] == 'failure'}
                            
                              <text>{$userdata['order']}- was defeated before they could take their action.</text>
                            
                            {else if $userdata['type'] == 'respondent'}

                              <text>{$userdata['order']}- {$the_users_name} has responded to the call for help and has attacked {$the_targets_name}!</text>
                            
                            {else if $userdata['name'] == 'Basic Attack'}
                            
                              <text>{$userdata['order']}- {$the_users_name} attacked {$the_targets_name}!</text>
                            
                            {else if $userdata['type'] == 'jutsu'}
            
                              <text>{$userdata['order']}- {$the_users_name} attacked {$the_targets_name} with the jutsu {$userdata['name']}!</text>
                            
                            {else if $userdata['type'] == 'weapon'}
                            
                              <text>{$userdata['order']}- {$the_users_name} attacked {$the_targets_name} with their {$userdata['name']}!</text>
                            
                            {else if $userdata['type'] == 'item'}
                            
                              <text>{$userdata['order']}- {$the_users_name} used {$userdata['name']} on {$the_targets_name}!</text>
                            
                            {else if $userdata['type'] == 'flee' }                                 
                              <text>{$userdata['order']}- {$the_users_name} tried to flee from the battle! </text>
                            {else if $userdata['type'] == 'call_for_help'}
                              <text>{$userdata['order']}- {$the_users_name} called for help! </text>
                            {else if $userdata['type'] == 'stunned'}
                              <text>{$userdata['order']}- {$the_users_name} was stunned this turn. </text>
                            {else}
                              <text>?- {$the_users_name} ?. </text>
                            {/if}
                          </td>
                        </tr>

                        <!-- displaying if a user was killed by this action -->
                        {if isset($userdata['killed'])}
                          <tr color="{if $owner['team'] != $round_users[ $userdata['killed'] ]['team']}blue{else}red{/if}">
                            <td>
                              {if $round_users[$userdata['killed']]['show_count'] == 'yes'}
                                <text>{$the_users_name} defeated {$userdata['killed']}</text>
                              {else}
                                {if strpos($userdata['killed'],'#') !== false}
                                  <text>{$the_users_name} defeated {substr($userdata['killed'],0,strpos($userdata['killed'],'#') - 1)}</text>
                                {else}
                                  <text>{$the_users_name} defeated {$userdata['killed']}</text>
                                {/if}
                              {/if}
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- if this user died -->
                        {if isset($userdata['died']) }
                          <tr color="{if $owner['team'] == $round_users[ $userdata['killed'] ]['team']}blue{else}red{/if}">
                            <td >
                              {if $round_users[$userdata['died']]['show_count'] == 'yes'}
                                <text>{$the_users_name} was defeated at the hands of {$userdata['died']}</text>
                              {else}
                                {if strpos($userdata['died'],'#') !== false}
                                  <text>{$the_users_name} was defeated at the hands of {substr($userdata['died'],0,strpos($userdata['died'],'#') - 1)}</text>
                                {else}
                                  <text>{$the_users_name} was defeated at the hands of {$userdata['died']}</text>
                                {/if}
                              {/if}
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- jutsu description -->
                        {if isset($userdata['jutsu_description']) && $userdata['failure'] != 'failure'}
                          <tr color="0.5,0.2,0.1,0.5">
                            <td>
                              <text>{$userdata['jutsu_description']}</text>
                            </td>
                          </tr>
                        {/if}

                        <!-- damage delt -->
                        {if isset($userdata['damage_delt'])}
                          {foreach $userdata['damage_delt'] as $damage_delt}
                            <tr color="{if $owner['team'] == $round_users[ $username ]['team']}blue{else}red{/if}">
                              <td>
                                <text {if $damage_delt['crit'] == true}color="orange"{/if}>{if $damage_delt['oneHitKill'] === true}one hit kill{else}{$damage_delt['type']} damage dealt{/if}{if $damage_delt['aoe']} from aoe{/if}: {$damage_delt['amount']}</text>
                              </td>
                            </tr>
                          {/foreach}
                        {/if}
                        
                        <!-- fled -->
                        {if isset($userdata['fled']) && $userdata['failure'] != 'failure'}
                          {if $userdata['fled'] == true}
                            <tr color="{if $owner['team'] == $round_users[ $userdata['killed'] ]['team']}blue{else}red{/if}">
                              <td>
                                <text>The Attempt was Successful.</text>
                              </td>
                            </tr>
                          {else}
                            <tr color="{if $owner['team'] != $round_users[ $userdata['killed'] ]['team']}blue{else}red{/if}">
                              <td>
                                <text>The Attempt Failed.</text>
                              </td>
                            </tr>
                          {/if}
                        {/if}
                        
                        <!-- damage over time -->
                        {if isset($userdata['damage_over_time_delt']) }
                          {foreach $userdata['damage_over_time_delt'] as $damage_over_time_delt}
                            <tr color="{if $owner['team'] == $round_users[ $username ]['team']}blue{else}red{/if}">
                              <td>
                                <text {if $damage_over_time_delt['crit'] == true}color="orange"{/if}>{$damage_over_time_delt['type']} damage over time dealt{if $damage_over_time_delt['aoe']} from aoe{/if}: {$damage_over_time_delt['amount']}</text>
                              </td>
                            </tr>
                          {/foreach}
                        {/if}
                        
                        <!-- recoil -->
                        {if isset($userdata['recoil'])}
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>recoil damage: {$userdata['recoil']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- leach -->
                        {if isset($userdata['leach'])}
                          <tr color="{if $owner['team'] != $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>health leached: {$userdata['leach']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- heal_delt -->
                        {if isset($userdata['heal_delt'])}
                          <tr color="{if $owner['team'] != $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>health restored: {$userdata['heal_delt']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- heal_over_time_delt -->
                        {if isset($userdata['heal_over_time_delt'])}
                          <tr color="{if $owner['team'] != $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>health restored over time: {$userdata['heal_over_time_delt']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- absorb -->
                        {if isset($userdata['absorb'])}
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>damage absorbed: {$userdata['absorb']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- reflect -->
                        {if isset($userdata['reflect'])}
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>damage reflected: {$userdata['reflect']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- rob -->
                        {if is_numeric($userdata['rob']) && $userdata['failure'] != 'failure'}
                          <tr color ="{if $owner['team'] == $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>stole: {$userdata['rob']}</text>
                            </td>
                          </tr>
                        {else if $userdata['rob'] == 'fail' && $userdata['failure'] != 'failure'}
                          <tr color="{if $owner['team'] != $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>Failed to rob anything.</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- disable -->
                        {if isset($userdata['disable']) }
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>disabled: {$userdata['disable']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- stagger -->
                        {if isset($userdata['stagger']) }
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>staggered: {$userdata['stagger']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- stun -->
                        {if isset($userdata['stun']) }
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>stunned: {$userdata['stun']}</text>
                            </td>
                          </tr>
                        {/if}

                      </contentElement>
                      
                    </td>
                  </tr>
                {/foreach}
                
              </contentElement>
              
            </td>
          </tr>
        
        {/foreach}
      </contentElement>
  </td>
</tr>