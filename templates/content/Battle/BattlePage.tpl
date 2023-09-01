<link href="https://fonts.googleapis.com/css?family=Calligraffitti|Merienda:700|Asul" rel="stylesheet">
<style>
  {literal}
  .healthBar{background-color:#FF0000; background-image:
  radial-gradient(circle at 100% 150%, #800000 24%, #FF0000 25%, #FF0000 28%, #800000 29%, #800000 36%, #FF0000 36%, #FF0000 40%, transparent 40%, transparent),
  radial-gradient(circle at 0    150%, #800000 24%, #FF0000 25%, #FF0000 28%, #800000 29%, #800000 36%, #FF0000 36%, #FF0000 40%, transparent 40%, transparent),
  radial-gradient(circle at 50%  100%, #FF0000   10%,  #800000 11%, #800000 23%, #FF0000 24%, #FF0000 30%, #800000 31%, #800000 43%, #FF0000 44%, #FF0000 50%, #800000 51%, #800000 63%, #FF0000 64%, #FF0000 71%, transparent 71%, transparent),
  radial-gradient(circle at 100% 50%,  #FF0000   5%,   #800000 6%, #800000 15%, #FF0000 16%, #FF0000 20%, #800000 21%, #800000 30%, #FF0000 31%, #FF0000 35%, #800000 36%, #800000 45%, #FF0000 46%, #FF0000 49%, transparent 50%, transparent),
  radial-gradient(circle at 0    50%,  #FF0000   5%,   #800000 6%, #800000 15%, #FF0000 16%, #FF0000 20%, #800000 21%, #800000 30%, #FF0000 31%, #FF0000 35%, #800000 36%, #800000 45%, #FF0000 46%, #FF0000 49%, transparent 50%, transparent);
  background-size:20px 10px;}
  .chakraBar{background-color:#4848FF; background-image:
  radial-gradient(circle at 100% 150%, #000080 24%, #4848FF 25%, #4848FF 28%, #000080 29%, #000080 36%, #4848FF 36%, #4848FF 40%, transparent 40%, transparent),
  radial-gradient(circle at 0    150%, #000080 24%, #4848FF 25%, #4848FF 28%, #000080 29%, #000080 36%, #4848FF 36%, #4848FF 40%, transparent 40%, transparent),
  radial-gradient(circle at 50%  100%, #4848FF   10%,  #000080 11%, #000080 23%, #4848FF 24%, #4848FF 30%, #000080 31%, #000080 43%, #4848FF 44%, #4848FF 50%, #000080 51%, #000080 63%, #4848FF 64%, #4848FF 71%, transparent 71%, transparent),
  radial-gradient(circle at 100% 50%,  #4848FF   5%,   #000080 6%, #000080 15%, #4848FF 16%, #4848FF 20%, #000080 21%, #000080 30%, #4848FF 31%, #4848FF 35%, #000080 36%, #000080 45%, #4848FF 46%, #4848FF 49%, transparent 50%, transparent),
  radial-gradient(circle at 0    50%,  #4848FF   5%,   #000080 6%, #000080 15%, #4848FF 16%, #4848FF 20%, #000080 21%, #000080 30%, #3232FF 31%, #3232FF 35%, #000080 36%, #000080 45%, #3232FF 46%, #3232FF 49%, transparent 50%, transparent);
  background-size:20px 10px;}
  .staminaBar{background-color:#00AA00; background-image:
  radial-gradient(circle at 100% 150%, #008000 24%, #00AA00 25%, #00AA00 28%, #008000 29%, #008000 36%, #00AA00 36%, #00AA00 40%, transparent 40%, transparent),
  radial-gradient(circle at 0    150%, #008000 24%, #00AA00 25%, #00AA00 28%, #008000 29%, #008000 36%, #00AA00 36%, #00AA00 40%, transparent 40%, transparent),
  radial-gradient(circle at 50%  100%, #00AA00   10%,  #008000 11%, #008000 23%, #00AA00 24%, #00AA00 30%, #008000 31%, #008000 43%, #00AA00 44%, #00AA00 50%, #008000 51%, #008000 63%, #00AA00 64%, #00AA00 71%, transparent 71%, transparent),
  radial-gradient(circle at 100% 50%,  #00AA00   5%,   #008000 6%, #008000 15%, #00AA00 16%, #00AA00 20%, #008000 21%, #008000 30%, #00AA00 31%, #00AA00 35%, #008000 36%, #008000 45%, #00AA00 46%, #00AA00 49%, transparent 50%, transparent),
  radial-gradient(circle at 0    50%,  #00AA00   5%,   #008000 6%, #008000 15%, #00AA00 16%, #00AA00 20%, #008000 21%, #008000 30%, #00AA00 31%, #00AA00 35%, #008000 36%, #008000 45%, #00AA00 46%, #00AA00 49%, transparent 50%, transparent);
  background-size:20px 10px;}

  select::-ms-expand {
  display: none;
  }

  .select-wrapper
  {
  border: 0 !important;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  text-overflow:'';
  text-indent: 0.01px;
  overflow:hidden;
  margin-right: 20px;
  overflow-y:hidden;
  text-align: center;
  text-align-last:center;
  font: inherit;
  font-size: 18px;
  }
  option:hover
  {
  background-color: #d2b48c;
  }

  option:checked
  {
  background: linear-gradient(#d2b48c, #d2b48c);
  }
  {/literal}</style>

<script>
  var the_id={$the_id};
</script>
  
<div id="combat_page" align ="left">
  
  {literal}
  <script id="BattlePageJS" type="text/javascript" src="files/javascript/BattlePage.js"></script>
  {/literal}
  
  <form action="" id="battle_form" method="post">
    <div id="summary" summary="no"></div>
    
		<table style="margin-left:auto;margin-right:auto;border:none;position:relative;top:-10px;width:100%;max-width:960px;font-family: 'Asul', sans-serif;font-size:16px; -webkit-font-smoothing: subpixel-antialiased;">
			<tr>
				<td>
					<!-- this is the player information box -->
					<div id="player_information" align="center" style="width:100%">
						<table class="table" align="center" style="width:100%">
							<tbody>
								<tr><td class="subHeader" colspan="3" style="padding:5px;">{$battle_type} Battleground <div style="font-family: 'Asul', sans-serif;font-size:12px; -webkit-font-smoothing: subpixel-antialiased;">{$time}</div></td></tr>
								<tr>
									<td style="border-right:1px solid;">
										<!-- here goes the users health display and what not -->
										<table align="center" width="100%" style="border:none;">
											<tr>
												<td style="vertical-align:middle;" width="45%">
													<!-- left side of player_information -->

													<!-- this user -->
													<table align="left" width="45%" style="border:none;">
														<tr>
															<td>
                                {if isset($owner['stunned']) || isset($owner['staggered']) || isset($owner['disabled']) }
                                  <div
                                    title="{if isset($owner['stunned'])}Stunned: {$owner['stunned']}&#010;{/if}{if isset($owner['staggered'])}Staggered: {$owner['staggered']}&#010;{/if}{if isset($owner['disabled'])}Disabled: {$owner['disabled']}&#010;{/if}"
                                  style="background:grey;">
                                    
                                    <img style="border:1px solid #e9cb0c;outline:1px solid #4d2600;opacity:0.5;" src="{$owner['avatar']}" width="65" height="65">
                                  <div/>
                                {else}
                                  <img style="border:1px solid #e9cb0c;outline:1px solid #4d2600;" src="{$owner['avatar']}" width="65" height="65">
                                {/if}
															</td>
															<td style="text-align:left;font-size:14px;">
																<a target="_blank" title="bloodline: {$owner['bloodline']}" href="?id=13&page=profile&name={$owner['name']}">
																	<b class="owner" value="{$owner['name']}">{$owner['name']}</b>
																</a>
																<br>
                                
                                {if strlen($owner['display_rank']) + strlen($owner['team']) + 2 >= 24}
                                  <b style="font-size:10px;">
                                    
                                {else if strlen($owner['display_rank']) + strlen($owner['team']) + 2 >= 22}
                                  <b style="font-size:11px;">
                                    
                                {else if strlen($owner['display_rank']) + strlen($owner['team']) + 2 >= 20}
                                  <b style="font-size:12px;">
                                    
                                {else if strlen($owner['display_rank']) + strlen($owner['team']) + 2>= 19}
                                  <b style="font-size:13px;">
                                    
                                {else}
                                  <b>
                                    
                                {/if}
                                  {$owner['display_rank']}, {$owner['team']}
                                  
                                </b>
                                
																<br>
																<div style="background-color:#998b08;display:inline-block;border:2px solid #917f08;">
																	<div style="height:5px; width:125px; border:1px solid #4d2600; outline:1px solid #e9cb0c;">
																		<div class="healthBar" title="{$owner['health']} / {$owner['healthMax']}" id="ownerHealthBar" style="float:left;height:5px;width:{$owner['health'] / $owner['healthMax'] * 100}%;" data-healthtxt="{number_format((float)$owner['health'], 2, '.', '')}/{number_format((float)$owner['healthMax'], 2, '.', '')}"></div>
																		<div style="float:right;background-color:lightgray;height:5px;width:{100 - $owner['health'] / $owner['healthMax'] * 100}%;"></div>
																	</div>

																	<div style="height:2px;"></div>
																	<div style="height:5px; width:125px; border:1px solid #4d2600; outline:1px solid #e9cb0c;">
																		<div class="chakraBar" title="{$owner['chakra']} / {$owner['chakraMax']}" id="ownerChakraBar" style="float:left;height:5px;width:{$owner['chakra'] / $owner['chakraMax'] * 100}%;" data-chakratxt="{number_format((float)$owner['chakra'], 2, '.', '')}/{number_format((float)$owner['chakraMax'], 2, '.', '')}"></div>
																		<div style="float:right;background-color:lightgray;height:5px;width:{100 - $owner['chakra'] / $owner['chakraMax'] * 100}%;"></div>
																	</div>

																	<div style="height:2px;"></div>
																	<div style="height:5px; width:125px; border:1px solid #4d2600; outline:1px solid #e9cb0c;">
																		<div class="staminaBar" title="{$owner['stamina']} / {$owner['staminaMax']}" id="ownerStaminaBar" style="float:left;height:5px;width:{$owner['stamina'] / $owner['staminaMax'] * 100}%;" data-staminatxt="{number_format((float)$owner['stamina'], 2, '.', '')}/{number_format((float)$owner['staminaMax'], 2, '.', '')}"></div>
																		<div style="float:right;background-color:lightgray;height:5px;width:{100 - $owner['stamina'] / $owner['staminaMax'] * 100}%;"></div>
																	</div>
																</div>
															</td>
														</tr>
													</table>
													<br>

													<!-- teamates -->
													{foreach $users as $username => $userdata}
														{if $userdata['team'] == $owner['team'] && $owner['name'] != $username}
															<br><br><br><br><br>
															<table align="left" width="45%" style="border:none;">
																<tr>
																	<td>
                                    {if $userdata['ai'] == true}
                                      {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                        <div style="background:silver; border:1px solid silver;outline:1px solid #4d2600;width:50px;height:50px;font-size: 40px;"
                                             title="{if isset($userdata['stunned'])}Stunned: {$userdata['stunned']}&#010;{/if}{if isset($userdata['staggered'])}Staggered: {$userdata['staggered']}&#010;{/if}{if isset($userdata['disabled'])}Disabled: {$userdata['disabled']}&#010;{/if}"
                                             >AI</div>
                                        
                                      {else}
                                        <div style="background:white; border:1px solid silver;outline:1px solid #4d2600;width:50px;height:50px;font-size: 40px;">AI</div>
                                      {/if}
                                    {else}
                                      {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                        <div
                                            title="{if isset($userdata['stunned'])}Stunned: {$userdata['stunned']}&#010;{/if}{if isset($userdata['staggered'])}Staggered: {$userdata['staggered']}&#010;{/if}{if isset($userdata['disabled'])}Disabled: {$userdata['disabled']}&#010;{/if}"
                                            style="background:grey;">
                                          
                                          <img style="border:1px solid #e9cb0c;outline:1px solid #4d2600;opacity:0.5;" src="{$userdata['avatar']}" width="50" height="50">
                                        <div/>
                                      {else}
                                        <img style="border:1px solid #e9cb0c;outline:1px solid #4d2600;" src="{$userdata['avatar']}" width="50" height="50">
                                      {/if}
                                    {/if}
																	</td>
																	<td style="text-align:left;font-size:14px;">
                                    {if $userdata['ai'] == true}
                                      <a href="">
                                    {else}
                                      <a target="_blank" title="bloodline: {$userdata['bloodline']}" href="?id=13&page=profile&name={$username}">
                                    {/if}
																			<b>
                                        {if $userdata['show_count'] == 'yes'}
                                          {$username}
                                        {else if $userdata['show_count'] == 'no'}
                                          {if strpos($username,'#') !== false}
                                            {substr($username,0,strpos($username,'#') - 1)}
                                          {else}
                                            {$username}
                                          {/if}
                                        {/if}
                                      </b>
																		</a>
																		<br>
                                        
                                    {if strlen($userdata['display_rank']) + strlen($userdata['team']) + 2 >= 24}
                                      <b style="font-size:10px;">
                                        
                                    {else if strlen($userdata['display_rank']) + strlen($userdata['team']) + 2 >= 22}
                                      <b style="font-size:11px;">
                                        
                                    {else if strlen($userdata['display_rank']) + strlen($userdata['team']) + 2 >= 20}
                                      <b style="font-size:12px;">
                                        
                                    {else if strlen($userdata['display_rank']) + strlen($userdata['team']) + 2>= 19}
                                      <b style="font-size:13px;">
                                        
                                    {else}
                                      <b>
                                        
                                    {/if}
                                      {$userdata['display_rank']}, {$userdata['team']}
                                      
                                    </b>
                                        
																		<br>
																		<div style="height:5px; width:125px; border:1px solid #4d2600;">
																			<div class="healthBar" {if $userdata['ai'] != true} title="{$userdata['health']} / {$userdata['healthMax']}" {/if} style="float:left;height:5px;width:{$userdata['health'] / $userdata['healthMax'] * 100}%;"></div>
																			<div style="float:right;background-color:lightgray;height:5px;width:{100 - $userdata['health'] / $userdata['healthMax'] * 100}%;"></div>
																		</div>
																	</td>
																</tr>
															</table>
														{/if}
													{/foreach}
												</td>
                        
												<td style="vertical-align:middle;font-family:'Calligraffitti';font-size: 34px;-webkit-font-smoothing: subpixel-antialiased;" width="10%">
													<b>
														VS.
													</b>
												</td>

												<td style="vertical-align:middle;" width="45%">
													<!-- right side of player_information -->
													{foreach $users as $username => $userdata}
														{if $userdata['team'] != $owner['team']}
															<table align="right" width="45%" style="border:none;">
																<tr>
																	<td style="text-align:right;font-size:14px;">
																		{if $userdata['ai'] == true}
                                      <a href="">
                                    {else}
                                      <a target="_blank" title="bloodline: {$userdata['bloodline']}" href="?id=13&page=profile&name={$username}">
                                    {/if}
																			<b>
                                        {if $userdata['show_count'] == 'yes'}
                                          {$username}
                                        {else if $userdata['show_count'] == 'no'}
                                          {if strpos($username,'#') !== false}
                                            {substr($username,0,strpos($username,'#') - 1)}
                                          {else}
                                            {$username}
                                          {/if}
                                        {/if}
                                      </b>
																		</a>
																		<br>
                                        
                                        
																		{if strlen($userdata['display_rank']) + strlen($userdata['team']) + 2 >= 24}
                                      <b style="font-size:10px;">
                                        
                                    {else if strlen($userdata['display_rank']) + strlen($userdata['team']) + 2 >= 22}
                                      <b style="font-size:11px;">
                                        
                                    {else if strlen($userdata['display_rank']) + strlen($userdata['team']) + 2 >= 20}
                                      <b style="font-size:12px;">
                                        
                                    {else if strlen($userdata['display_rank']) + strlen($userdata['team']) + 2>= 19}
                                      <b style="font-size:13px;">
                                        
                                    {else}
                                      <b>
                                        
                                    {/if}
                                      {$userdata['display_rank']}, {$userdata['team']}
                                      
                                    </b>
                                        
                                        
																		<br>
																		<div style="height:5px; width:125px; border:1px solid #4d2600;">
																			<div class="healthBar" {if $userdata['ai'] == true} title="{$userdata['health']} / {$userdata['healthMax']}" {/if} style="float:left;height:5px;width:{$userdata['health'] / $userdata['healthMax'] * 100}%;"></div>
																			<div style="float:right;background-color:lightgray;height:5px;width:{100 - $userdata['health'] / $userdata['healthMax'] * 100}%"></div>
																		</div>
																	</td>
																	<td>
                                    {if $userdata['ai'] == true && $username != 'Mirror Entity' && substr($username,0,strpos($username,'#') - 1) != 'Mirror Entity'}
                                      {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                        <div style="background:silver; border:1px solid silver;outline:1px solid #4d2600;width:50px;height:50px;font-size: 40px;"
                                             title="{if isset($userdata['stunned'])}Stunned: {$userdata['stunned']}&#010;{/if}{if isset($userdata['staggered'])}Staggered: {$userdata['staggered']}&#010;{/if}{if isset($userdata['disabled'])}Disabled: {$userdata['disabled']}&#010;{/if}"
                                             >AI</div>
                                        
                                      {else}
                                        <div style="background:white; border:1px solid silver;outline:1px solid #4d2600;width:50px;height:50px;font-size: 40px;">AI</div>
                                      {/if}
                                    {else if $username != 'Mirror Entity' && substr($username,0,strpos($username,'#') - 1) != 'Mirror Entity'}
                                      {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                        <div
                                            title="{if isset($userdata['stunned'])}Stunned: {$userdata['stunned']}&#010;{/if}{if isset($userdata['staggered'])}Staggered: {$userdata['staggered']}&#010;{/if}{if isset($userdata['disabled'])}Disabled: {$userdata['disabled']}&#010;{/if}"
                                            style="background:grey;">
                                          
                                          <img style="border:1px solid silver;outline:1px solid #4d2600;opacity:0.5;" src="{$userdata['avatar']}" width="50" height="50">
                                        <div/>
                                      {else}
                                        <img style="border:1px solid silver;outline:1px solid #4d2600;" src="{$userdata['avatar']}" width="50" height="50">
                                      {/if}
                                    {else}
                                    
                                      {if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                                        <div
                                            title="{if isset($userdata['stunned'])}Stunned: {$userdata['stunned']}&#010;{/if}{if isset($userdata['staggered'])}Staggered: {$userdata['staggered']}&#010;{/if}{if isset($userdata['disabled'])}Disabled: {$userdata['disabled']}&#010;{/if}"
                                            style="background:grey;">
                                          
                                          <img style="opacity:0.5;border:1px solid #e9cb0c;outline:1px solid #4d2600;-moz-transform: scale(-1, 1); -o-transform: scale(-1, 1); -webkit-transform: scale(-1, 1); transform: scale(-1, 1);" src="{$owner['avatar']}" width="50" height="50">
                                        <div/>
                                      {else}
                                        <img style="border:1px solid #e9cb0c;outline:1px solid #4d2600;-moz-transform: scale(-1, 1); -o-transform: scale(-1, 1); -webkit-transform: scale(-1, 1); transform: scale(-1, 1);" src="{$owner['avatar']}" width="50" height="50">
                                      {/if}
                                    
                                    {/if}
                                  </td>
																</tr>
															</table>
															<br><br><br><br><br>
														{/if}
													{/foreach}
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<!-- here goes the battle timer -->
									<td style="font-size:20px;border-right:1px solid;">Round: <b class="turn_counter">{$turn_counter + 1}</b><br>Time Left: <b id="turn_timer">{$turn_timer - time()}</b></td>
								</tr>
								<tr>
									<!-- here goes DSR header -->
									<td class="tableColumns" colspan="3" style="font-size:16px;"><b>Damage by Survivability Rating (DSR)</b></td>
								</tr>
								<tr>
									<!-- here goes sf -->
									<td style="border-right:1px solid;">
										<table style="border:none;width:100%;">
											<tr>
                        <td style="text-align:left;font-size:15px;">
                          Your DSR: <b>{base_convert(floor(sqrt($owner['DSR']+$rng+4)), 10, 9)}</b>
                          <br>
                          Your Team's DSR: <b>{base_convert(floor(sqrt($friendlyDSR+$rng+4)), 10, 9)}</b>
                          <br>
                          {if $cfhRange1 != 'N/A'}
                            CFH Range: <b>{base_convert(floor(sqrt($cfhRange1+$rng+4)), 10, 9)}</b> to {base_convert(floor(sqrt($cfhRange2+$rng+4)), 10, 9)}</b>
                          {else}
                            CFH Range: N/A
                          {/if}
                        </td>
												<td style="text-align:right;font-size:15px;">
													Opponent Team's DSR: <b>{base_convert(floor(sqrt($opponentDSR+$rng+4)), 10, 9)}</b>
                        </td>
											</tr>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</td>
			</tr>

			<tr>
				<td>
					<!-- bottom half of page -->
					<div align="center">
						<table class="table" style="border:none;width:100%;" >
							<tr align="center" width ="95%">
								<td style="text-align:left;padding:0px;" width="40%" valign="top">
									<!-- actions go here -->
									<table id="available_actions" align="left" width="97.5%">
                    {if (is_numeric($stunned) && $stunned > 0) || $stunned === true}
                      <tr>
                        <td class="subHeader" colspan="3" >Stunned...</td>
                      </tr>
                      <tr>
                        <td style="text-align:center;border:1px solid black">
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
                        </td>
                      </td>
                    {else if $owner['waiting_for_next_turn'] === true}
                      <tr>
                        <td class="subHeader" id="waiting" colspan="3" >Waiting...</td>
                      </tr>
                      <tr>
                        <td style="text-align:center;border:1px solid black">
                          You are now waiting on your opponents.
                          <br>
                          <br>
                          Please wait for them to choose their action or for the round to end.
                          <br>
                          <br>
                          When appropriate, this page will automatically refresh.
                          <br>
                        </td>
                      </td>
                    {else}
										  <tr>
										  	<td class="subHeader" colspan="3">Actions</td>
										  </tr>
										  <tr>
										  	<td style="margin:0;padding:0px;border:1px solid black;border-bottom:2px solid black;">
										  		<select  style="width:100%;text-align-last:center;" class="tableColumns select-wrapper" name="action_select" id="action_select" size="1">
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
										  	</td>

										  </tr>
										  <tr>
										  	<td style="margin:0;padding:0px;border:1px solid black;">
										  		<select style="width:100%;text-align-last:center;" class="tableColumns select-wrapper" name="jutsu_select" id="jutsu_select" size="1">
										  			<option selected disabled value="default">Select a Jutsu</option>
                            {if random_int(0,1) == 1 }<option disabled></option>{/if}
                            
										  			{foreach $owner['jutsus'] as $jutsu_id => $jutsu_data}
										  			cooldown status: {$jutsu_data['cooldown_status']}
                              {if ( (is_numeric($no_jutsu) && $no_jutsu > 0) || $no_jutsu === true) && $jutsu_data['name'] != 'Basic Attack'}
                                <option class="{$jutsu_data['targeting_type']}" title="You have been disabled for {$no_jutsu} {if $no_jutsu == 1}round{else}rounds{/if}." value="{$jutsu_id}" disabled>
										  						{$jutsu_data['name']}
										  					</option>
										  				{else if ($jutsu_data['cooldown_status'] == 'off' || $jutsu_data['cooldown_status'] == '') && $jutsu_data['reagent_status'] == true && $jutsu_data['max_uses'] > $jutsu_data['uses']}
										  					<option class="{$jutsu_data['targeting_type']}" title="{if $jutsu_data['max_uses'] - $jutsu_data['uses'] <=5 }(uses left: {$jutsu_data['uses']}/{$jutsu_data['max_uses']}){/if}&#010;{$jutsu_data['description']}" value="{$jutsu_id}">
										  						{$jutsu_data['name']}
										  					</option>
										  				{else if $jutsu_data['reagent_status'] == false}
										  					<option title="(out of required reagents)&#010;{$jutsu_data['description']}" disabled>
										  						{$jutsu_data['name']} (no more uses)
										  					</option>
										  				{else if $jutsu_data['max_uses'] <= $jutsu_data['uses']}
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

										  		<select style="width:100%;text-align-last:center;" class="tableColumns select-wrapper"  name="weapon_attack_select" id="weapon_attack_select" size="1">
										  			<option selected disabled value="default">Select a Weapon</option>
										  				{foreach $owner['equipment'] as $equipment_id => $equipment_data}
										  					{if $equipment_data['type'] == 'weapon'}
										  						{if $equipment_data['element'] == '' || $equipment_data['element'] == 'none' || $equipment_data['element'] == 'None' || (
                                   in_array($equipment_data['element'], $owner['elements']) && $owner['element_masteries'][ array_search($equipment_data['element'], $owner['elements']) ] > 25)}
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
                           
                           <select style="width:100%;text-align-last:center;" class="tableColumns select-wrapper"  name="item_attack_select" id="item_attack_select" size="1">
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
										  	</td>
										  </tr>
										  <tr id="jutsu_weapon_select_tr">
										  	<td style="margin:0;padding:0px;border:1px solid black;">
										  		{$owner['jutsu_weapon_selects']}
										  	</td>
										  </tr>
										  <tr id="target_select_tr">
										  	<td style="margin:0;padding:0px;border:1px solid black;">
										  		<select style="width:100%;text-align-last:center;" class="tableColumns select-wrapper"  name="target_select" id="target_select" size="1">
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
										  	</td>
										  </tr>
										  <tr>
										  	<td style="margin:0;padding:0;border:1px solid black;">
										  		<!--<button style="width:100%;height:35px;" class="tableColumns" id="button" type="submit" name="button" value=""> </button>-->
                          <button style="width:100%;height:35px;" class="tableColumns" id="button" name="button" code="{$link_code}"> </button>
										  	</td>
										  </tr>
                    {/if}
									</table>
								</td>
                
                <!-- start of battle log -->
								<td style="text-align:left;padding:0px;padding-right:1px;" width="60%" valign="top">
									<table id="battle_log" align="right" width="97.5%">
										<tr>
											<td class="subHeader" colspan="3">Battle Log</td>
										</tr>
										<tr>
											<td style="padding:0px;padding-right:0px;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;">
                        <table style="width:100%;border:none;">
                          {foreach array_combine( array_reverse( array_keys( $battle_log )), array_reverse( array_values( $battle_log ) ) ) as $round_number => $round_users}
                            {if $turn_counter - $round_number <= $turn_log_length}
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
                                          
                                          {else if $userdata['type'] == 'flee' }                                 
                                            tried to flee from the battle!
                                          {else if $userdata['type'] == 'call_for_help'}
                                            called for help!
                                          {else if $userdata['type'] == 'stunned'}
                                            was stunned this turn.
                                          {else}
                                            was ?.
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
                                             <tr><td><td/><tr/>
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
                                             <tr><td><td/><tr/>
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
                                            {if isset($userdata['damage_delt'])}
                                              {foreach $userdata['damage_delt'] as $damage_delt}
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
                                              
                                            {if isset($userdata['damage_over_time_delt'])}
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
                              
                                                <strong>recoil damage:</strong>{$userdata['recoil']}
                                              </td>
                                            </tr>
                                            {/if}
                              
                                            {if isset($userdata['absorb'])}
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
                              
                                                <strong>damage absorbed:</strong>{$userdata['absorb']}
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
                                              
                                            {if is_numeric($userdata['rob'])  && $userdata['failure'] != 'failure'}
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
                                            
                                            {if isset($userdata['disable'])}
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
                            {/if}
                          {/foreach}
                        </table>
                      </td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>

		<br>
		<br>

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
      
		<input type="submit" style="display:none;position: absolute;" id="refresh_button" name="button" value="refreshBattle">
	</form>
</div>
</div>
</div>