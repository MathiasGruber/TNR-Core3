<script id="the_id">
  var the_id={$the_id};
</script>

<div class="page-box" id="combat_page">

    <div class="page-title">
        {$battle_type} Battleground <span class="toggle-button-info closed" data-target=".battle-information"/>
    </div>

    {literal}
        <script id="BattlePageJS" type="text/javascript" src="files/javascript/BattlePage.js"></script>
    {/literal}
  
    <form action="" id="battle_form" method="post">
        <div id="summary" summary="no"></div>

        <div class="page-content">
            <div class="page-sub-title-top stiff-grid stiff-column-2 page-grid-justify-stretch">
                <div class="text-left font-small">
                    {$time}
                </div>
                <div class="text-right">
                    --<span id="turn_timer_box">(<span title="round timer" id="turn_timer">{$turn_timer - time()}</span>s)</span> - #<span title="round counter" class="turn_counter">{$turn_counter + 1}</span>
                </div>
            </div>

            <div class="page-sub-title toggle-target closed battle-information">
                Damage by Survivability Rating (DSR)
            </div>

            <div class="toggle-target closed battle-information">
                Your DSR: <b>{base_convert(floor(sqrt($owner['DSR']+$rng+4)), 10, 9)}</b>
                <br>
                Your Team's DSR: <b>{base_convert(floor(sqrt($friendlyDSR+$rng+4)), 10, 9)}</b>
                <br>
                Opponent Team's DSR: <b>{base_convert(floor(sqrt($opponentDSR+$rng+4)), 10, 9)}</b>
                <br>
                {if $cfhRange1 != 'N/A'}
                    CFH Range: <b>{base_convert(floor(sqrt($cfhRange1+$rng+4)), 10, 9)}</b> to {base_convert(floor(sqrt($cfhRange2+$rng+4)), 10, 9)}</b>
                {else}
                    CFH Range: N/A
                {/if}
            </div>

            <!-- this is the display of the users in the combat -->
            <div class="page-grid page-column-fr-3" id="player_information">
                <!--left pane-->
                <div id="left-users-pane">
                    <!--self-->
                    <div class="page-grid stiff-column-min-left-2 self-card">

                        <div class="self-portrait-box r{$owner['rank']}"><!--portrait-->
							
                            {if isset($owner['stunned']) || isset($owner['staggered']) || isset($owner['disabled']) }
                               <img src="{$owner['avatar']}"
                                    class="self-portrait dim-portrait"/>
                            {else}
                               <img src="{$owner['avatar']}" class="self-portrait"/>
                            {/if}

                            <span class="toggle-button-info self-portrait-button closed" data-target=".{$owner['name']}-info"/>
                        </div>
                            
                        <div class="self-details-box"><!--details-->
                            <div class="self-name-box font-larger toggle-target {$owner['name']}-info"><!--name-->
                                <a target="_blank" title="View Profile" href="?id=13&page=profile&name={$owner['name']}">{$owner['name']}</a>
                            </div>

                            <div class="self-info-box toggle-target closed font-small text-left table-alternate-1 table-cell {$owner['name']}-info"><!--info-->
                                rank: {$owner['display_rank']} <br/>
                                village: {$owner['team']} <br/>
                                {if {$owner['bloodline']} != ''}bloodline: {$owner['bloodline']} <br/>{/if}
                                {if isset($owner['stunned'])}Stunned: {$owner['stunned']}<br/>{/if}
                                {if isset($owner['staggered'])}Staggered: {$owner['staggered']}<br/>{/if}
                                {if isset($owner['disabled'])}Disabled: {$owner['disabled']}<br/>{/if}
                            </div>

                            <div class="self-bars-box toggle-target {$owner['name']}-info"><!-- bars -->
                                <div class="self-health-box">
                                   <div class="self-health-bar" 
                                        title="{$owner['health']} / {$owner['healthMax']}" 
                                        id="ownerHealthBar" 
                                        style="width:{$owner['health'] / $owner['healthMax'] * 100}%;" 
                                        data-healthtxt="{number_format((float)$owner['health'], 2, '.', '')}/{number_format((float)$owner['healthMax'], 2, '.', '')}"
                                        data-healthtxt2="{number_format((float)$owner['health'], 0, '.', '')} / {number_format((float)$owner['healthMax'], 0, '.', '')}"
										data-healthPercent="{number_format(((float)$owner['health']/(float)$owner['healthMax'])*100, 0, '.', '')}"
										data-healthPercent2="{number_format(((float)$owner['health']/(float)$owner['healthMax'])*100, 2, '.', '')}">
                                   </div>

                                   <div class="self-health-backer" 
                                        style="width:{100 - $owner['health'] / $owner['healthMax'] * 100}%;">
                                   </div>
                                </div>

                                <div class="self-chakra-box">
                                   <div class="self-chakra-bar" 
                                        title="{$owner['chakra']} / {$owner['chakraMax']}" 
                                        id="ownerChakraBar" 
                                        style="width:{$owner['chakra'] / $owner['chakraMax'] * 100}%;" 
                                        data-chakratxt="{number_format((float)$owner['chakra'], 2, '.', '')}/{number_format((float)$owner['chakraMax'], 2, '.', '')}"
                                        data-chakratxt2="{number_format((float)$owner['chakra'], 0, '.', '')} / {number_format((float)$owner['chakraMax'], 0, '.', '')}"
										data-chakraPercent="{number_format(((float)$owner['chakra']/(float)$owner['chakraMax'])*100, 0, '.', '')}"
										data-chakraPercent2="{number_format(((float)$owner['chakra']/(float)$owner['chakraMax'])*100, 2, '.', '')}">
                                   </div>

                                   <div class="self-chakra-backer"
                                        style="width:{100 - $owner['chakra'] / $owner['chakraMax'] * 100}%;">
                                   </div>
                                </div>

                                <div class="self-stamina-box">
                                   <div class="self-stamina-bar" 
                                        title="{$owner['stamina']} / {$owner['staminaMax']}" 
                                        id="ownerStaminaBar" 
                                        style="width:{$owner['stamina'] / $owner['staminaMax'] * 100}%;" 
                                        data-staminatxt="{number_format((float)$owner['stamina'], 2, '.', '')}/{number_format((float)$owner['staminaMax'], 2, '.', '')}"
                                        data-staminatxt2="{number_format((float)$owner['stamina'], 0, '.', '')} / {number_format((float)$owner['staminaMax'], 0, '.', '')}"
										data-staminaPercent="{number_format(((float)$owner['stamina']/(float)$owner['staminaMax'])*100, 0, '.', '')}"
										data-staminaPercent2="{number_format(((float)$owner['stamina']/(float)$owner['staminaMax'])*100, 2, '.', '')}">
                                   </div>

                                   <div class="self-stamina-backer"
                                        style="width:{100 - $owner['stamina'] / $owner['staminaMax'] * 100}%;">
                                   </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--self-->



                    <!--team-->
                    {foreach $users as $username => $userdata}
                        {if $userdata['team'] == $owner['team'] && $owner['name'] != $username}

                            <div class="page-grid stiff-column-min-left-2 team-card">

                                <div class="team-portrait-box r{$userdata['rank']}"><!--portrait-->
                                    {if $userdata['ai'] == true}

                                        {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                           <div class="team-portrait ai-portrait dim-portrait">
                                               AI
                                           </div>
                                        {else}
                                           <div class="team-portrait ai-portrait">
                                               AI
                                           </div>
                                        {/if}

                                    {else}

                                        {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                           <img src="{$userdata['avatar']}"
                                                class="team-portrait dim-portrait"/>
                                        {else}
                                           <img src="{$userdata['avatar']}" class="team-portrait">
                                        {/if}

                                    {/if}

                                    <span class="toggle-button-info team-portrait-button closed" data-target=".{str_replace("#", "",str_replace(" ", "-", $username))}-info"/>
                                </div>
                                    
                                <div class="team-details-box"><!--details-->
                                    <div class="team-name-box font-larger toggle-target {str_replace("#", "",str_replace(" ", "-", $username))}-info"><!--name-->
                                        {if $userdata['ai'] == true}
                                            <a>
                                        {else}
                                            <a target="_blank" title="View Profile" href="?id=13&page=profile&name={$username}">
                                        {/if}
                                        
                                        {if $userdata['show_count'] == 'yes'}
                    		                {$username}
                    		            {elseif $userdata['show_count'] == 'no'}
                    		                {if strpos($username,'#') !== false}
                    		                    {substr($username,0,strpos($username,'#') - 1)}
                    		                {else}
                    		                    {$username}
                    		                {/if}
                    		            {/if}

                                        </a>
                                    </div>

                                    <div class="team-info-box toggle-target closed font-small text-left table-alternate-1 table-cell {str_replace("#", "",str_replace(" ", "-", $username))}-info"><!--info-->
                                        rank: {$userdata['display_rank']} <br/>
                                        village: {$userdata['team']} <br/>
                                        {if {$userdata['bloodline']} != ''}bloodline: {$userdata['bloodline']} <br/>{/if}
                                        {if isset($userdata['stunned'])}Stunned: {$userdata['stunned']}<br/>{/if}
                                        {if isset($userdata['staggered'])}Staggered: {$userdata['staggered']}<br/>{/if}
                                        {if isset($userdata['disabled'])}Disabled: {$userdata['disabled']}<br/>{/if}
                                    </div>

                                    <div class="team-bars-box toggle-target {str_replace("#", "",str_replace(" ", "-", $username))}-info"><!-- bars -->
                                        <div class="team-health-box">
                                           <div class="team-health-bar" 
                                                
                                                {if $userdata['ai'] != true}
                                                    title="{$userdata['health']} / {$userdata['healthMax']}"
                                                {/if}

                                                id="ownerHealthBar" 
                                                style="width:{$userdata['health'] / $userdata['healthMax'] * 100}%;" 
                                                data-healthtxt="{number_format((float)$userdata['health'], 2, '.', '')}/{number_format((float)$userdata['healthMax'], 2, '.', '')}">
                                           </div>

                                           <div class="team-health-backer" 
                                                style="width:{100 - $userdata['health'] / $userdata['healthMax'] * 100}%;">
                                           </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                    <!--team-->

                </div>
                <!--left pane-->

                <div id="vs-pane">
                    VS
                </div>

                <!--right pane-->
                <div>
                    {foreach $users as $username => $userdata}
                        {if $userdata['team'] != $owner['team']}

                            <div class="page-grid stiff-column-min-right-2 opponent-card">

                                <div class="opponent-details-box"><!--details-->
                                    <div class="opponent-name-box font-larger toggle-target {str_replace("#", "",str_replace(" ", "-", $username))}-info"><!--name-->

                                        {if $userdata['ai'] == true}
                                            <a>
                                        {else}
                                            <a target="_blank" title="View Profile" href="?id=13&page=profile&name={$username}">
                                        {/if}
                                        
                                        {if $userdata['show_count'] == 'yes'}
                    		                {$username}
                    		            {elseif $userdata['show_count'] == 'no'}
                    		                {if strpos($username,'#') !== false}
                    		                    {substr($username,0,strpos($username,'#') - 1)}
                    		                {else}
                    		                    {$username}
                    		                {/if}
                    		            {/if}

                                        </a>

                                    </div>

                                    <div class="opponent-info-box toggle-target closed font-small text-left table-alternate-1 table-cell {str_replace("#", "",str_replace(" ", "-", $username))}-info"><!--info-->
                                        rank: {$userdata['display_rank']} <br/>
                                        village: {$userdata['team']} <br/>
                                        {if {$userdata['bloodline']} != ''}bloodline: {$userdata['bloodline']} <br/>{/if}
                                        {if isset($userdata['stunned'])}Stunned: {$userdata['stunned']}<br/>{/if}
                                        {if isset($userdata['staggered'])}Staggered: {$userdata['staggered']}<br/>{/if}
                                        {if isset($userdata['disabled'])}Disabled: {$userdata['disabled']}<br/>{/if}
                                    </div>

                                    <div class="opponent-bars-box toggle-target {str_replace("#", "",str_replace(" ", "-", $username))}-info"><!-- bars -->
                                        <div class="opponent-health-box">
                                           <div class="opponent-health-bar" 
                                                
                                                {if $userdata['ai'] != true}
                                                    title="{$userdata['health']} / {$userdata['healthMax']}"
                                                {/if}

                                                id="ownerHealthBar" 
                                                style="width:{$userdata['health'] / $userdata['healthMax'] * 100}%;" 
                                                data-healthtxt="{number_format((float)$userdata['health'], 2, '.', '')}/{number_format((float)$userdata['healthMax'], 2, '.', '')}">
                                           </div>

                                           <div class="opponent-health-backer" 
                                                style="width:{100 - $userdata['health'] / $userdata['healthMax'] * 100}%;">
                                           </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="opponent-portrait-box r{$userdata['rank']}"><!--portrait-->
                                    {if $userdata['ai'] == true && $username != 'Mirror Entity' && substr($username,0,strpos($username,'#') - 1) != 'Mirror Entity'}

                                        {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                           <div class="opponent-portrait ai-portrait dim-portrait">
                                               AI
                                           </div>
                                        {else}
                                           <div class="opponent-portrait ai-portrait">
                                               AI
                                           </div>
                                        {/if}
                                    
                                    {elseif $username == 'Mirror Entity' || substr($username,0,strpos($username,'#') - 1) == 'Mirror Entity'}
                                        
                                        {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                           <img src="{$owner['avatar']}"
                                                class="opponent-portrait dim-portrait flip-portrait"/>
                                        {else}
                                           <img src="{$owner['avatar']}" class="opponent-portrait flip-portrait">
                                        {/if}

                                    {else}

                                        {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                           <img src="{$userdata['avatar']}"
                                                class="opponent-portrait dim-portrait"/>
                                        {else}
                                           <img src="{$userdata['avatar']}" class="opponent-portrait">
                                        {/if}

                                    {/if}

                                    <span class="toggle-button-info opponent-portrait-button closed" data-target=".{str_replace("#", "",str_replace(" ", "-", $username))}-info"></span>
                                </div>
                                    
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <!--right pane-->
            </div>
            <!-- this is the display of the users in the combat -->

            <!--this is the bottom section of the page-->
            <div class="page-grid page-column-2" id="bottom-page-box">



                <!--this is the user action box-->
                <div class="dark-solid-box page-grid grid-gap-none" id="available_actions">
                    

                	{if (is_numeric($stunned) && $stunned > 0) || $stunned === true}
                	    <div class="solid-box bold table-cell">
                            Stunned...
                        </div>

                	    <div>
                	    	You are now waiting on your opponents while stunned.
                	    	<br>
                	    	Stunned for {$stunned} {if $stunned == 1}round{else}rounds{/if}.
                	    	<br>
                	    	<br>
                	    	Please wait for them to choose their action or for the round to end.
                	    	<br>
                	    	<br>
                	    	{if $battle_type_pve === true}
                	    	<a id='RefreshPage' href="http://www.theninja-rpg.com/">Go to the next round.</a>
                	    	{else}
                	    	When appropriate, this page will automatically refresh.
                	    	{/if}
                	    	<br>
                	    </div>

                	{elseif $owner['waiting_for_next_turn'] === true}
                        <div class="solid-box bold table-cell" id="waiting">
                            Waiting...
                        </div>

                	    <div>
                	    	You are now waiting on your opponents.
                	    	<br>
                	    	<br>
                	    	Please wait for them to choose their action or for the round to end.
                	    	<br>
                	    	<br>
                	    	When appropriate, this page will automatically refresh.
                	    	<br>
                	    </div>

                	{else}
                	    <div class="solid-box bold table-cell">
                            Actions
                        </div>

                	    <select class="page-drop-down-fill-dark select-wrapper" name="action_select" id="action_select">
                	    	<option selected disabled value="default">Select an Action</option>
                	    	<option>Jutsus</option>
                	    	<option>Weapons</option>
                	    	<option>Items</option>
                	    	{if $no_flee != true && $owner['attacker'] != true} <!-- if the owner did not initiate the fight -->
                	    	    <option>Flee</option>
                	    	{/if}
                	    	{if $no_cfh != true && $owner['no_cfh'] != true && $cfhRange1 != 'N/A'} <!-- if the owner did not initiate the fight or if the owner did initiate the fight and the opponent has called for help -->
                	    	    <option>Call For Help</option>
                	    	{/if}
                	    </select>

                	    <select class="page-drop-down-fill-dark select-wrapper" name="jutsu_select" id="jutsu_select">
                	    	<option selected disabled value="default">Select a Jutsu</option>
                	    	{if random_int(0,1) == 1 }
                	    	    <option disabled></option>
                	    	{/if}
                	    	{foreach $owner['jutsus'] as $jutsu_id => $jutsu_data}
                	    	    cooldown status: {$jutsu_data['cooldown_status']}
                	    	    {if ( (is_numeric($no_jutsu) && $no_jutsu > 0) || $no_jutsu === true) && $jutsu_data['name'] != 'Basic Attack'}
                	    	        <option class="{$jutsu_data['targeting_type']}" title="You have been disabled for {$no_jutsu} {if $no_jutsu == 1}round{else}rounds{/if}." value="{$jutsu_id}" disabled>
                	    	        	{$jutsu_data['name']}
                	    	        </option>
                	    	    {elseif ($jutsu_data['cooldown_status'] == 'off' || $jutsu_data['cooldown_status'] == '') && $jutsu_data['reagent_status'] == true && $jutsu_data['max_uses'] > $jutsu_data['uses']}
                	    	        <option class="{$jutsu_data['targeting_type']}" title="{if $jutsu_data['max_uses'] - $jutsu_data['uses'] <=5 }(uses left: {$jutsu_data['uses']}/{$jutsu_data['max_uses']}){/if}&#010;{$jutsu_data['description']}" value="{$jutsu_id}">
                	    	        	{$jutsu_data['name']}
                	    	        </option>
                	    	    {elseif $jutsu_data['reagent_status'] == false}
                	    	        <option title="(out of required reagents)&#010;{$jutsu_data['description']}" disabled>
                	    	        	{$jutsu_data['name']} (no more uses)
                	    	        </option>
                	    	    {elseif $jutsu_data['max_uses'] <= $jutsu_data['uses']}
                	    	        <option title="(no more uses {$jutsu_data['uses']}/{$jutsu_data['max_uses']})&#010;{$jutsu_data['description']}" disabled>
                	    	        	{$jutsu_data['name']} (no more uses)
                	    	        </option>
                	    	    {else}
                	    	        <option title="(this is on cooldown for {$jutsu_data['cooldown_status']} turn{if $jutsu_data['cooldown_status'] != 1}s{/if}.) {if $jutsu_data['max_uses'] - $jutsu_data['uses'] <=5 }&#010;(uses left: {$jutsu_data['uses']}/{$jutsu_data['max_uses']}){/if}&#010;{$jutsu_data['description']}" disabled>
                	    	        	{$jutsu_data['name']} ({$jutsu_data['cooldown_status']})
                	    	        </option>
                	    	    {/if}
                	    	{/foreach}
                	    </select>
                	    <select class="page-drop-down-fill-dark select-wrapper"  name="weapon_attack_select" id="weapon_attack_select">
                	    	<option selected disabled value="default">Select a Weapon</option>
                	    	{foreach $owner['equipment'] as $equipment_id => $equipment_data}
                	    	    {if $equipment_data['type'] == 'weapon'}
                	    	        {if $equipment_data['element'] == '' || $equipment_data['element'] == 'none' || $equipment_data['element'] == 'None' || (in_array($equipment_data['element'], $owner['elements']) && $owner['element_masteries'][ array_search($equipment_data['element'], $owner['elements']) ] > 25)}
                	    	            {if $owner['equipment_used'][ $equipment_data['iid'] ]['uses'] < $owner['equipment_used'][ $equipment_data['iid'] ]['max_uses']}
                	    	                <option title="uses-left: {$owner['equipment_used'][ $equipment_data['iid'] ]['max_uses'] - $owner['equipment_used'][ $equipment_data['iid'] ]['uses']}" class="{$equipment_data['targeting_type']}" value="{$equipment_id}">
                	    	                	{$equipment_data['name']}
                	    	                </option>
                	    	            {else}
                	    	                <option disabled title="uses-left: {$owner['equipment_used'][ $equipment_data['iid'] ]['max_uses'] - $owner['equipment_used'][ $equipment_data['iid'] ]['uses']}" class="{$equipment_data['targeting_type']}" value="{$equipment_id}">
                	    	                	{$equipment_data['name']}
                	    	                </option>
                	    	            {/if}
                	    	        {/if}
                	    	    {/if}
                	    	{/foreach}
                	    </select>
                	    <select class="page-drop-down-fill-dark select-wrapper"  name="item_attack_select" id="item_attack_select">
                	    	<option selected disabled value="default">Select a Item</option>
                	    	{foreach $owner['items'] as $invin_id => $item}
                	    	    {if $item['stack'] != 0 }
                	    	        {if $item['max_uses'] - $owner['items_used'][$item['iid']] > 0}
                	    	            <option title="stack: {$item['stack']} charges-left: {$item['uses'] - $items['times_used']}&#010;uses-left: {$item['max_uses'] - $owner['items_used'][$item['iid']]}" class="{$item['targeting_type']}" value="{$invin_id}">{$item['name']}</option>
                	    	        {else}
                	    	            <option disabled title="stack: {$item['stack']} charges-left: {$item['uses'] - $items['times_used']}&#010;uses-left: {$item['max_uses'] - $owner['items_used'][$item['iid']]}" class="{$item['targeting_type']}" value="{$invin_id}">{$item['name']}</option>
                	    	        {/if}
                	    	    {/if}
                	    	{/foreach}
                	    </select>

                	    <div id="jutsu_weapon_select_tr" class="page-grid">
                	    	{$owner['jutsu_weapon_selects']}
                	    </div>

                	    <div id="target_select_tr" class="page-grid">
                	    	<select class="page-drop-down-fill-dark select-wrapper"  name="target_select" id="target_select">
                	    		<option selected disabled value="default" class="na">Select a Target</option>
                	    		{foreach $users as $username => $userdata}
                	    		    <option
                	    		    {if $username == $owner['name']}
                	    		        class="self"
                	    		    {else if $userdata['team'] == $owner['team']}
                	    		        class="ally"
                	    		    {else}
                	    		        class="opponent"
                	    		    {/if}
                	    		    value="{$username}">
                	    		    {if $userdata['show_count'] == 'yes'}
                	    		        {$username}
                	    		    {else if $userdata['show_count'] == 'no'}
                	    		        {if strpos($username,'#') !== false}
                	    		            {substr($username,0,strpos($username,'#') - 1)}
                	    		        {else}
                	    		            {$username}
                	    		        {/if}
                	    		    {/if}
                	    		    </option>
                	    		{/foreach}
                	    	</select>
                	    </div>

                	    <button class="page-button-fill" id="button" name="button" code="{$link_code}"> </button>
                	{/if}


                </div>
                <!--this is the user action box-->



                <!--this is the user battle log box-->
                <div class="page-box dark-solid-box" id="battle_log">
                    
                	<div class="solid-box bold table-cell">
                        Battle Log
                    </div>

                	{foreach array_combine( array_reverse( array_keys( $battle_log )), array_reverse( array_values( $battle_log ) ) ) as $round_number => $round_users}
                	    {if $turn_counter - $round_number <= $turn_log_length || $turn_log_length < 1}
                	        <div class="header_round{$round_number+1} table-cell bold plain-box">
                	    		Round: {$round_number + 1}
                	        </div>

                	    	<div class="round{$round_number+1}_">

                	    		{foreach $round_users as $username => $userdata}

                	    		    <div class="round{$round_number+1}_{str_replace(' ','-',$username)}">
										<div class="{if $username == $owner['name']}bracket-teal{else if $owner['team'] == $userdata['team']}bracket-blue{else}bracket-orange{/if}">
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
                	    {/if}
                	{/foreach}



                </div>
                <!--this is the user battle log box-->



            </div>
            <!--this is the bottom section of the page-->

        </div>
    </form>
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

{literal}
<!--{if $owner['name'] == 'Koala'}
<div>
	{$this_dump}

	{$users_dump}
	
	{$kill_button}
</div>
{/if}-->
{/literal}
