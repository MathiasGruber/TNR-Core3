{function bar}
	<div class="record-bar {$type} {$side} {$addToClass}">

		{if $side=="right"}
			<div class="backer" style="width:{100 - $target[$type] / $target[$type|cat:'Max'] * 100}%;"></div>
		{/if}

		<div class="fill" 
			style="width:{$target[$type] / $target[$type|cat:'Max'] * 100}%;" 
			data-{$type}txt="{number_format((float)$target[$type], 0, '.', '')} / {number_format((float)$target[$type|cat:'Max'], 0, '.', '')}"
			data-{$thpe}Percent="{number_format(((float)$target[$type]/(float)$target[$type|cat:'Max'])*100, 2, '.', '')}">
		</div>

		{if  $side=="left"}
			<div class="backer" style="width:{100 - $target[$type] / $target[$type|cat:'Max'] * 100}%;"></div>
		{/if}

		<div class="font-small floater" title="{$target[$type]}/{$target[$type|cat:'Max']}">
			{number_format(((float)$target[$type]/(float)$target[$type|cat:'Max'])*100, 2, '.', '')}%
		</div>
	</div>
{/function}

{function generateAvatar}
	{if $character['ai'] == true && $characterName != 'Mirror Entity' && substr($characterName,0,strpos($characterName,'#') - 1) != 'Mirror Entity'}

		{if isset($character['stunned']) || isset($character['staggered']) || isset($character['disabled']) }
			<div class="record-portrait {$side} ai-portrait dim-portrait r{$character['rank']}">
				AI
			</div>
		{else}
			<div class="record-portrait {$side} ai-portrait r{$character['rank']}">
				AI
			</div>
		{/if}
	
	{elseif $characterName == 'Mirror Entity' || substr($characterName,0,strpos($characterName,'#') - 1) == 'Mirror Entity'}
		
		{if isset($character['stunned']) || isset($character['staggered']) || isset($character['disabled']) }
			<img src="{$owner['avatar']}"
				class="record-portrait {$side} dim-portrait flip-portrait r{$character['rank']}"/>
		{else}
			<img src="{$owner['avatar']}" class="record-portrait {$side} flip-portrait r{$character['rank']}">
		{/if}

	{else}

		{if isset($character['stunned']) || isset($character['staggered']) || isset($character['disabled']) }
			<img src="{$character['avatar']}"
				class="record-portrait {$side} dim-portrait r{$character['rank']}"/>
		{else}
			<img src="{$character['avatar']}" class="record-portrait {$side} r{$character['rank']}">
		{/if}

	{/if}
{/function}

{function generateTitle}

	{if $side == 'left'}
		<div class="record-i toggle-button-info closed" data-target=".{str_replace("#", "",str_replace(" ", "-", $characterName))}-info"></div>
	{/if}

	{if $character['ai'] == true}
		<a>
	{else}
		<a target="_blank" title="View Profile" href="?id=13&page=profile&name={$characterName}">
	{/if}
	
	{if $character['show_count'] == 'yes'}
		{$characterName}
	{elseif $character['show_count'] == 'no'}
		{if strpos($characterName,'#') !== false}
			{substr($characterName,0,strpos($characterName,'#') - 1)}
		{else}
			{$characterName}
		{/if}
	{/if}
	</a>

	{if $side == 'right'}
		<div class="record-i toggle-button-info closed" data-target=".{str_replace("#", "",str_replace(" ", "-", $characterName))}-info"></div>
	{/if}
{/function}

{function generateRecord}

	<div id="record-{str_replace(" ","-",$characterName)}" class="record {if $self}self{/if} {$side}" data-name="{$characterName}" data-relationship="{if $self}self{else if $side == 'left'}ally{else}opponent{/if}">

		{if $self}
			<div class="record-title {$side}">
				{generateTitle character=$character characterName=$characterName side=$side}
			</div>
		{/if}

		{if $side == 'left'}
			{generateAvatar character=$character characterName=$characterName side=$side}
		{/if}

		<div class="record-details">

			{if !$self}
				<div class="record-bars">
					<div class="record-title {$side}">
						{generateTitle character=$character characterName=$characterName side=$side}
					</div>

					{bar type="health"  side=$side target=$character characterName=$characterName addToClass="toggle-target {str_replace	("#", "",str_replace(" ", "-", $characterName))}-info"}
				</div>
			{else}
				<div class="record-bars toggle-target {str_replace("#", "",str_replace(" ", "-", $owner['name']))}-info">
					{bar type="health"  side="left" target=$owner}
					{bar type="chakra"  side="left" target=$owner}
					{bar type="stamina" side="left" target=$owner}
				</div>
			{/if}

			<div class="record-information stiff-grid stiff-column-fr-2 font-small text-left table-alternate-2 table-cell toggle-target {str_replace("#", "",str_replace(" ", "-", $characterName))}-info closed">
				<div>
					Rank: {$character['display_rank']} <br>
					Village: {$character['team']} <br>
					{if {$character['bloodline']} != ''}Bloodline: {if $character['bloodlinemask'] !=''}{$character['bloodlinemask']}{else}{$character['bloodline']}{/if} <br>{/if}
					<br>
				</div>
				<div>
					{if isset($character['stunned'])}Stunned: {$character['stunned']}<br>{/if}
					{if isset($character['staggered'])}Staggered: {$character['staggered']}<br>{/if}
					{if isset($character['disabled'])}Disabled: {$character['disabled']}<br>{/if}
					<br>
				</div>
			</div>

		</div>

		{if $side == 'right'}
			{generateAvatar character=$character characterName=$characterName}
		{/if}

		{if $turn_counter != 0}
			{$last_round = $battle_log[$turn_counter - 1][$characterName]}
			<details class="record-extra-details">
				<summary class="">
					{if $last_round['failure'] == 'failure'}
						N/A
					{else if $last_round['type'] == 'respondent'}
						Basic Attack
					{else if $last_round['name'] == 'Basic Attack'}
						Basic Attack
					{else if $last_round['type'] == 'jutsu'}
						{$last_round['name']}
					{else if $last_round['type'] == 'weapon'}
						{$last_round['name']}
					{else if $last_round['type'] == 'item'}
						{$last_round['name']}
					{else if $last_round['type'] == 'flee' }                                 
						Flee Attempt
					{else if $last_round['type'] == 'call_for_help'}
						Called for Help
					{else if $last_round['type'] == 'stunned'}
						Stunned
					{else}
						was ?.
					{/if}
				</summary>
				<div class="text-left font-small">
					{foreach $last_round['effects'] as $target => $effects}
						{foreach $effects as $effect => $messages}
							{$target}-> {$effect}:
							{foreach $messages as $key => $message}
								{if $key != 0}, {/if}
								{$message}
							{/foreach}
							<br/>
						{/foreach}
					{/foreach}
				</div>
			</details>
		{/if}
	</div>

{/function}

<div id="friends">
    {generateRecord character=$owner characterName=$owner['name'] side='left' self=true}
    
    {foreach $users as $username => $userdata}
        {if $userdata['team'] == $owner['team'] && $owner['name'] != $username}
            {generateRecord character=$userdata characterName=$username side='left'}
        {/if}
    {/foreach}
</div>

<div id="vs">
    VS
</div>

<div id="foes">
    {foreach $users as $username => $userdata}
        {if $userdata['team'] != $owner['team']}
            {generateRecord character=$userdata characterName=$username side='right'}
        {/if}
    {/foreach}
</div>