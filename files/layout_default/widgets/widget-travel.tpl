{if $smarty.get.id != 8 || $smarty.get.map == 'territory'}
    <div id="widget-travel">
    
		<div id="widget-travel-title" class="lazy widget-title {if $_COOKIE['widget-travel-closed'] == true}closed{/if}">
			<div id="widget-travel-title-button"
				{if ($userStatus == 'asleep' || $userStatus == 'awake' || $userStatus == 'combat')
            	    && 
					strlen($sleepLink) > 25
            	    && 
					(
            	      $userdata['village'] == 'Syndicate' 
            	      ||
            	      $userdata['village'] != $user_location
            	      ||
            	      (
            	        $userdata['village'] == $user_location
            	        &&
            	        (
            	          $userdata['apartment'] >= 1
            	          ||
            	          $userdata['village'] == 'Syndicate'
            	        )
            	      ) 
            	    )
            	}
					{if $userStatus == 'awake' || $userStatus == 'combat'}
						class="lazy ui-button sleep-button"
						title="Sleep"
						onclick="loadPage(null,'all','doSleep:sleep');"
					{else if $userStatus == 'asleep'}
						class="lazy ui-button wakeup-button"
						title="Wake Up"
						onclick="loadPage(null,'all','doSleep:wakeup');"
					{/if}

				{/if}
			>
			</div>
			<a id="widget-travel-title-text" title="travel page" href="/?id=8">
	            Travel
			</a>
			<div id="piece-of-title"></div>
		</div>
    
		<div id="widget-travel-content" class="lazy widget-content" {if $_COOKIE['widget-travel-closed'] == true}style="display:none;"{/if}>
			<div id="widget-travel-content-top">
				{$user_location}
				<hr>
			</div>
    
			<div id="widget-travel-content-middle">
				{assign var=b_x value=0}
				{assign var=b_y value=0}
				{assign var=low_x_bound value=-1}
				{assign var=high_x_bound value=1}
				{assign var=low_y_bound value=-1}
				{assign var=high_y_bound value=1}
    
				{for $not_y=$low_y_bound to $high_y_bound}
				{$y = $not_y * -1}
    
				  {for $x=$low_x_bound to $high_x_bound}
				    
				    <!-- finding if this square is impassable -->
				    {if abs($user_longitude + $x) > 125 || abs($user_latitude + $y) > 100}
				      {assign var=impassable value=1}
				    {else if !isset($map_data[($user_latitude + $y)][($user_longitude + $x)]) || ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]))}
				      {assign var=impassable value=1}
				    {else}
				      {assign var=impassable value=0}
				    {/if}
				    
				    {if ! $impassable}
				      <!-- finding if this is a direction square -->
				      {if $y == $high_y_bound && $x == 0 && is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_north",$map_data[($user_latitude + $y)][($user_longitude + $x)]) }
				        {assign var=direction value='N'}
				      {else if $y == $low_y_bound && $x == 0 && is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_south",$map_data[($user_latitude + $y)][($user_longitude + $x)]) }
				        {assign var=direction value='S'}
				      {else if $y == 0 && $x == $high_x_bound && is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_east",$map_data[($user_latitude + $y)][($user_longitude + $x)]) }
				        {assign var=direction value='E'}
				      {else if $y == 0 && $x == $low_x_bound && is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_west",$map_data[($user_latitude + $y)][($user_longitude + $x)]) }
				        {assign var=direction value='W'}
				      {else if $y == $high_y_bound && $x == $low_x_bound && is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_north",$map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_west",$map_data[($user_latitude + $y)][($user_longitude + $x)]) }
				        {assign var=direction value='NW'}
				      {else if $y == $high_y_bound && $x == $high_x_bound && is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_north",$map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_east",$map_data[($user_latitude + $y)][($user_longitude + $x)]) }
				        {assign var=direction value='NE'}
				      {else if $y == $low_y_bound && $x == $low_x_bound && is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_south",$map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_west",$map_data[($user_latitude + $y)][($user_longitude + $x)]) }
				        {assign var=direction value='SW'}
				      {else if $y == $low_y_bound && $x == $high_x_bound && is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_south",$map_data[($user_latitude + $y)][($user_longitude + $x)]) && !in_array("impassable_east",$map_data[($user_latitude + $y)][($user_longitude + $x)]) }
				        {assign var=direction value='SE'}
				      {else if $y == 0 && $x == 0 }
				        {assign var=direction value='Enter'}
				      {else}
				        {assign var=direction value=''}
				      {/if}
				    {else}
				      {assign var=direction value=''}
				    {/if}
				    
				    <div class="widget-travel-button-level-0 lazy lpbtn" {if $direction != '' && ($direction != 'Enter' || $sub_location)} id="{$direction}" {if $userStatus == 'awake'  || $userStatus == 'combat'}onclick="loadPage(null,'all','doTravel:{$direction}');"{/if} {/if} style="padding:0px;background-image:{if $user_longitude + $x <= 125 && $user_longitude + $x >= -125 && $user_latitude + $y <= 100 && $user_latitude +$y >= -100}url({$s3}/seichi/Seichi-{if $user_longitude + $x + 126 < 10}0{/if}{$user_longitude + $x + 126}-{if 101 - $user_latitude - $y < 10}0{/if}{101 - $user_latitude - $y}.png){else}black{/if};" Title="{if isset($map_data[($user_latitude + $y)][($user_longitude + $x)])}{$map_data[($user_latitude + $y)][($user_longitude + $x)][1]}: {$map_data[($user_latitude + $y)][($user_longitude + $x)][0]}{/if}">
				      
				      <div class="widget-travel-button-level-1 
				           {$b_x = 0}
				           {$b_y = 0}
				           {if $user_latitude + 1 + $y > 100 || 
				              ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) ) || 
				              ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_north",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
    
				              {$b_y = $b_y + 1}
				              widget-travel-border-top-solid
				           {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y + 1 ][$user_longitude + $x     ][1]}
				              {$b_y = $b_y + 1}
				              widget-travel-border-top-light
				           {/if}
				           
				           {if $user_latitude  - 1 + $y < -100 || 
				             ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) ) || 
				             ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_south",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
				             {$b_y = $b_y + 1}
				             widget-travel-border-bottom-solid                            
				           {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y - 1 ][$user_longitude + $x     ][1]}
				             {$b_y = $b_y + 1}
				             widget-travel-border-bottom-light
				           {/if}
				             
				           {if $user_longitude + 1 + $x > 125 || 
				             ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) ) || 
				             ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_east",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
				             {$b_x = $b_x + 1}
				             widget-travel-border-right-solid                            
				           {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x + 1 ][1]}
				             {$b_x = $b_x + 1}
				             widget-travel-border-right-light
				           {/if}
				             
				           {if $user_longitude - 1 + $x < -125 || 
				             ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) ) || 
				             ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_west",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
				             {$b_x = $b_x + 1}
				             widget-travel-border-left-solid                           
				           {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x - 1 ][1]}
				             {$b_x = $b_x + 1}
				             widget-travel-border-left-light
				           {/if}
				             
				           {if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("konoki",    $map_data[($user_latitude + $y)][($user_longitude + $x )])}
											{if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_konoki)}
												{$regions_konoki[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
												{$regions_flip = array_flip($regions_konoki)}
											{/if}
											page-travel-background-konoki-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
										{else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("silence",   $map_data[($user_latitude + $y)][($user_longitude + $x )])}
											{if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_silence)}
												{$regions_silence[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
												{$regions_flip = array_flip($regions_silence)}
											{/if}
											page-travel-background-silence-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
										{else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("shroud",    $map_data[($user_latitude + $y)][($user_longitude + $x )])}
											{if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_shroud)}
												{$regions_shroud[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
												{$regions_flip = array_flip($regions_shroud)}
											{/if}
											page-travel-background-shroud-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
										{else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("shine",     $map_data[($user_latitude + $y)][($user_longitude + $x )])}
											{if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_shine)}
												{$regions_shine[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
												{$regions_flip = array_flip($regions_shine)}
											{/if}
											page-travel-background-shine-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
										{else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("samui",     $map_data[($user_latitude + $y)][($user_longitude + $x )])}
											{if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_samui)}
												{$regions_samui[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
												{$regions_flip = array_flip($regions_samui)}
											{/if}
											page-travel-background-samui-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
										{else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("syndicate", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
											{if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_syndicate)}
												{$regions_syndicate[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
												{$regions_flip = array_flip($regions_syndicate)}
											{/if}
											page-travel-background-syndicate-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
										{/if}
										">
				        <div class="widget-travel-button-level-2">
				          <div class="widget-travel-button-text-box widget-travel-button-level-3">
				            <b class="widget-travel-button-text widget-travel-button-level-4">{if ($userStatus == 'awake' || $userStatus == 'combat') && $direction != '' && ($direction != 'Enter' || $sub_location)}{$direction}{/if}</b>
				          </div>
				        </div>
				      </div>
				      
				    </div>
				  {/for}
    
				{/for}
			</div>
    
			<div id="widget-travel-content-bottom" class="lazy">
				<hr><hr>
				<div id="widget-travel-content-region">
				  {$user_region}
				</div>
				<div id="widget-travel-content-x-y">
				  ({$user_longitude},{$user_latitude})
				</div>
			</div>
			{if $userdata['user_rank'] == 'Admin'}
				<br>
				<br>
				<div id="widget-travel-form-title" class="page-sub-title-no-margin toggle-button-drop closed" data-target="#widget-travel-form">Map Editor</div>
				<form id="widget-travel-form" class="toggle-target closed" name="travel_form" method="post" action="">
					<div class='font-large'>Name</div>
        	<input name='name' id="widget-travel-form-name" type="text" class="input-text" value="{$map_data[$user_latitude][$user_longitude][0]}"/>
					<br>
					<br>
					<div class='font-large'>Region</div>
					<select name='region' id="widget-travel-form-region">
						{foreach $map_region_data as $region}
							<option {if $region['region'] == {$map_data[$user_latitude][$user_longitude][1]}}selected{/if} value="{$region['region']}">{$region['region']}</option>
						{/foreach}
					</select>
					<br>
					<br>
					<div class='font-large'>claimable</div>
					<input name='claimable' id="widget-travel-form-claimable" type="checkbox" {if $map_data[$user_latitude][$user_longitude][2] == 'claimable'}checked{/if}/>
					<br>
					<div class='font-large'>Owner</div>
					<select name='owner' id="widget-travel-form-owner">
						<option {if $map_data[$user_latitude][$user_longitude][3] == 'konoki'}selected{/if} value="konoki">konoki</option>
						<option {if $map_data[$user_latitude][$user_longitude][3] == 'shine'}selected{/if} value="shine">shine</option>
						<option {if $map_data[$user_latitude][$user_longitude][3] == 'silence'}selected{/if} value="silence">silence</option>
						<option {if $map_data[$user_latitude][$user_longitude][3] == 'shroud'}selected{/if} value="shroud">shroud</option>
						<option {if $map_data[$user_latitude][$user_longitude][3] == 'samui'}selected{/if} value="samui">samui</option>
						<option {if $map_data[$user_latitude][$user_longitude][3] == 'syndicate'}selected{/if} value="syndicate">syndicate</option>
						<option {if $map_data[$user_latitude][$user_longitude][3] == 'none'}selected{/if} value="none">none</option>
					</select>
					<br>
					<div class='font-large'>impassability</div>
					<label><input name='impassability_impassable' id="widget-travel-form-impassability-impassable" type="checkbox" {if in_array('impassable',$map_data[$user_latitude][$user_longitude])}checked{/if}/>all</label>
					<label><input name='impassability_north' id="widget-travel-form-impassability-north" type="checkbox" {if in_array('impassable_north',$map_data[$user_latitude][$user_longitude])}checked{/if}/>north</label>
					<label><input name='impassability_south' id="widget-travel-form-impassability-south" type="checkbox" {if in_array('impassable_south',$map_data[$user_latitude][$user_longitude])}checked{/if}/>south</label>
					<label><input name='impassability_east' id="widget-travel-form-impassability-east" type="checkbox" {if in_array('impassable_east',$map_data[$user_latitude][$user_longitude])}checked{/if}/>east</label>
				  <label><input name='impassability_west' id="widget-travel-form-impassability-west" type="checkbox" {if in_array('impassable_west',$map_data[$user_latitude][$user_longitude])}checked{/if}/>west</label>
					<br>
					<br>
					<input id="widget-travel-form-submit"   type="submit" class="button-fill lazy" name="MapUpdate" value="Submit" />
    		</form>

				<br>
				<br>
				<div id="widget-travel-jump-title" class="page-sub-title-no-margin toggle-button-drop closed" data-target="#widget-travel-jump">Jump</div>
				<form id="widget-travel-jump" class="toggle-target closed" name="travel_jump" method="post" action="">
					<div class='font-large'>Village</div>
					<select name="jumpVillage" id="widget-travel-jump-village">
						<option value="nill">nill</option>
						<option value="4,50">Konoki</option>
						<option value="17,31">Shine</option>
						<option value="40,38">Silence</option>
						<option value="40,62">Shroud</option>
						<option value="17,69">Samui</option>
						<option value="24,50">Gambler's Den</option>
						<option value="-14,62">Bandit's Outpost</option>
						<option value="05,12">Poacher's Camp</option>
						<option value="91,35">Pirate's Hideout</option>
					</select>
					<br>
					<br>

					<div class='font-large'>X</div>
					<input name='jumpX' id="widget-travel-jump-x" type="text" class="input-text" placeholder="x"/>
					<br>
					<br>

					<div class='font-large'>y</div>
					<input name='jumpY' id="widget-travel-jump-y" type="text" class="input-text" placeholder="y"/>
					<br>
					<br>

					<input id="widget-travel-jump-submit"   type="submit" class="button-fill lazy" name="jump" value="Submit" />
    		</form>
			{/if}
		</div>
    </div>
{/if}