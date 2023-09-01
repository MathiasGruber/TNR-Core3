{literal}
	<script>

	

		var optionDefaults = {
							  "#move-north":"w,up,8",
							  "#move-west":"a,left,4",
							  "#move-south":"s,down,2",
							  "#move-east":"d,right,6",
							  "#move-north-west":"q,7",
							  "#move-south-west":"z,1",
							  "#move-south-east":"c,3",
							  "#move-north-east":"e,9",
							  "#link-anbu":"shift+a",
							  "#link-bank":"b",
							  "#link-clan":"f1",
							  "#link-combat":"`",
							  "#link-errands":"shift+e",
							  "#link-home-inventory":"shift+i",
							  "#link-inbox":"g",
							  "#link-inventory":"i",
							  "#link-jutsu":"j",
							  "#link-marriage":"n",
							  "#link-missions":"m",
							  "#link-missions-a":"alt+a,shift+4",
							  "#link-missions-b":"alt+b,shift+3",
							  "#link-missions-c":"alt+c,shift+2",
							  "#link-missions-d":"alt+d,shift+1",
							  "#link-occupation":"h",
							  "#link-preferences":"y",
							  "#link-profession":"u",
							  "#link-profile":"p",
							  "#link-quests":"comma",
							  "#link-ramen":"r",
								"#link-ramen-full-heal":"shift+r",
							  "#link-rob":"f",
							  "#link-scout":"x",
							  "#link-sleep-wake":"tab",
							  "#link-tavern":"o",
							  "#link-training":"t",
							  "#link-travel":"k",
							  "#key_bindings_status":"On"
							  };
						

		function duplicateBlock()
		{
			$.each(optionDefaults,function(objKey,objValue)
			{
				binding = $(objKey).val();

				$.each(optionDefaults,function(comparedObjKey,comparedObjValue)
				{
					comparedBinding = $(comparedObjKey).val();
					if (binding == comparedbinding){
						$(comparedObjKey).va;('');
						alert("Duplicates not allowed");
					}
				});
			});
			
		}	
    
    
    	$('input').change(function()
    	{
      		duplicateBlock();
    	});

		$("#keybinding_settings input").change(function(e){
			this.value = this.value.replace('`','~');
		});

    
	</script>
{/literal}


{$movementOptions = [
					'Move North' => 'move-north',
					'Move West' => 'move-west',
					'Move South' => 'move-south',
					'Move East' => 'move-east',
					'Move North West' => 'move-north-west',
					'Move South West' => 'move-south-west',
					'Move South East' => 'move-south-east',
					'Move North East' => 'move-north-east'
					]
}

{$linkOptions = [
				'Anbu' => 'link-anbu',
				'Bank' => 'link-bank',
				'Clan' => 'link-clan',
				'Global Trade' => 'link-global-trade',
				'Combat' => 'link-combat',
				'Errands / Small Crimes' => 'link-errands',
				'Home Inventory' => 'link-home-inventory',
				'Inbox' => 'link-inbox',
				'Inventory' => 'link-inventory',
				'Jutsu' => 'link-jutsu',
				'Marriage' => 'link-marriage',
				'Missions / Crimes' => 'link-missions',
				'A-Rank Mission / crime' => 'link-missions-a',
				'B-Rank Mission / crime' => 'link-missions-b',
				'C-Rank Mission / crime' => 'link-missions-c',
				'D-Rank Mission' => 'link-missions-d',
				'Occupation' => 'link-occupation',
				'Preferences' => 'link-preferences',
				'Profession' => 'link-profession',
				'Profile' => 'link-profile',
				'Quest Journal' => 'link-quests',
				'Ramen / Scavenge' => 'link-ramen',
				'Ramen Full Heal' => 'link-ramen-full-heal',
				'Rob' => 'link-rob',
				'Scout' => 'link-scout',
				'Sleep' => 'link-sleep',
				'Wakeup' => 'link-wakeup',
				'Tavern' => 'link-tavern',
				'Training' => 'link-training',
				'Local Map' => 'link-travel',
				'Territory Map' => 'link-territory'
				]}

{$keybindStatus = [
					'1' => 'On',
					'0' => 'Off'
				  ]
}

<div class="lazy page-box">

	<div class="lazy page-title">
    	Key Binding Settings Change    
	</div>

	<form id="keybinding_settings" class="page-content" action="?id={$smarty.get.id}&act={$smarty.get.act}" method="post" enctype="multipart/form-data">

		<div class="page-sub-title-top toggle-button-drop closed" data-target="#key-bindings-options">
			Options
		</div>

    	<div class="toggle-target closed" id="key-bindings-options">
			a-z, 0-9, f1-12, ~ - = [ ] \ ; ' . / * +"
			<br>
			<br>

			For modifier keys you can use shift, ctrl, alt, or meta.
			<br>
			<br>

			You can substitute option for alt and command for meta.
			<br>
			<br>

			Key combinations require a modifier key placed in the beginning e.g "shift+a"
			<br>
			<br>

			Other special keys that can be used are backspace, comma, tab, enter, return, capslock, esc, escape, space, pageup, pagedown, end, home, left, up, right, down, ins, del, and plus.
			<br>
			<br>

			To use a special key type it like it is above.
			<br>
			<br>
			If you'd like to bind multiple keys to the same command seperate by a comma e.g "a,b"
			
    	</div>
        <div class="page-sub-title">
		<b>
				Turn Key Binds On or Off
		</b>
		</div>


		{html_options id="key_bindings_status" name="key_bindings_status" options=$keybindStatus selected=$key_bindings_status class="lazy page-drop-down-fill"}

		<br>
		<br>

		<div class="page-sub-title">
			<b>
				Keybindings For Movement
			</b>
		</div>

		<div class="page-grid page-column-fr-5">
			{foreach $movementOptions as $key=>$value}

				<div class="light-solid-box table-cell bold span-2">
      				{$key}
    			</div>

				<input class="lazy page-text-input span-3" name="{$value}" id="{$value}" value="{str_replace('`','~',$keyBindings[$value])}">
	
			{/foreach}
		</div>



		<div class="page-sub-title">
			<b>
				Keybindings For Links
			</b>
		</div>
    
		<div class="page-grid page-column-fr-5">

		
    
			{foreach $linkOptions as $key =>$value}

				<div class="light-solid-box table-cell bold span-2">
					{$key}
				</div>

				<input class="lazy page-text-input span-3" name="{$value}" id="{$value}" value="{str_replace('`','~',$keyBindings[$value])}">

			{/foreach}
			
		</div>


		<div class="page-grid page-column-fr-4">
			<div class="span-4"></div>
			<input class="lazy page-button-fill" type="submit" name="Submit" value="Reset Keybinding Defaults" id='keybind-defaults' style="color:red;" onclick="return confirm('Are you sure you would like to reset these settings to their defaults!?');">
			<input class="lazy page-button-fill span-3" type="submit" name="Submit" value="Change Keybinding Settings">
		</div>

	</form>
</div>