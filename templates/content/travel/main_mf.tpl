<div class="page-box">
    <div class="page-title page-grid page-column-2">
        <a href="?id={$smarty.get.id}&map=local" >Local Map</a>
        <a href="?id={$smarty.get.id}&map=territory" >Territory Map</a>
    </div>
    <div class="page-content">
        <div class="page-grid page-column-2">
            <div class="text-left">
                Location: {$x},{$y} - {$location}
            </div>
            <div class="text-right">
                ({$region}, {if $map == ''}Seichi{else}{$map}{/if})
            </div>
        </div>

        {if $smarty.get.map == 'territory'}

          {literal}
            <script src="https://unpkg.com/scrollbooster@1.1.0/dist/scrollbooster.min.js"></script> 
            <script>

              function setUpTerritoryMap(){
                //set up territory map events
                $('path').on("click mouseover touchstart",function(e){
                  var matchterm = e.target.textContent;
                  var matchClass = $("path:contains(" + matchterm +")").attr("class");
                  var pathList = $('path').get();
                  $.each(pathList,function(index,value){
                    value.classList.remove('highlight')
                  });
                  $("path:contains(" + matchterm +")").attr("class", matchClass + " highlight");

                  $("#territory-map-info").text(matchterm);
                });


                //set up draggable scrolling on map
                let viewport = document.querySelector('#territory-map-container-wrapper');
                let content = document.querySelector('#territory-map-container');
                let sb = new ScrollBooster({
                  viewport: viewport,
                  content: content,
                  handle: document.querySelector('#territory-map-container'), 
                  bounce: true,
                  textSelection: false,
                  emulateScroll: false,
                  onUpdate: (data)=> {
                    content.style.transform = `translate(
                      ${-data.position.x}px,
                      ${-data.position.y}px
                    )`
                  },
                  shouldScroll: (data, event) => {
                      return true
                  }
                });

                var wrapperWidth = document.getElementById('territory-map-container-wrapper').getBoundingClientRect().width;
                var imageWidth = document.getElementById('territory-map-container').getBoundingClientRect().width;
                var wrapperHeight = document.getElementById('territory-map-container-wrapper').getBoundingClientRect().height;
                var imageHeight = document.getElementById('territory-map-container').getBoundingClientRect().height;

                var x = (((imageWidth - wrapperWidth)/2)*0.75);
                var y = (imageHeight - wrapperHeight)/2;

                if(x < 0)
                  x = 0;
                if(y < 0)
                  y = 0;

                sb.setPosition({
                  x: x,
                  y: y
                });

              }

              //on document ready
              $('document').ready(function(){
                setUpTerritoryMap();
              });
            </script>
          {/literal}

          <div id="territory-map-info" class="page-sub-title-no-margin">Seichi</div>
          <div id="territory-map-container-wrapper">
            <div id="territory-map-container">

              <div id="territory-map" class="lazy" style="background-image:url({$s3}/seichi/SeichiTerritoryMap.png);" title="territory map"></div>

              {$map_overlay}

              <circle id="player-marker" class="map-overlay-addition" cx="{$x + 0.5}" cy="{100 - ($y - 0.5)}" r="0.5" style="fill:red;"><title>You are here!</title></circle>
              <img src onerror="$('.map-overlay-addition').each(function(){ $('#territory-map-overlay')[0].innerHTML += $(this)[0].outerHTML; })">

            </div>
          </div>
        {else}
          <div class="page-travel-grid">
            {assign var=b_x value=0}
            {assign var=b_y value=0}
            {assign var=low_x_bound value=-4}
            {assign var=high_x_bound value=4}
            {assign var=low_y_bound value=-4}
            {assign var=high_y_bound value=4}
            {$regions_konoki    = array()}
            {$regions_samui     = array()}
            {$regions_shroud    = array()}
            {$regions_silence   = array()}
            {$regions_shine     = array()}
            {$regions_syndicate = array()}

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
                
                <div class="page-travel-button-level-0 lazy lpbtn {if $x == 0 && $y == 0}page-travel-button-center{/if}" {if $direction != '' && ($direction != 'Enter' || $sub_location)} id="{$direction}" onclick="loadPage(null,'all','doTravel:{$direction}');" {/if} style="padding:0px;background-image:{if $user_longitude + $x <= 125 && $user_longitude + $x >= -125 && $user_latitude + $y <= 100 && $user_latitude +$y >= -100}url({$s3}/seichi/Seichi-{if $user_longitude + $x + 126 < 10}0{/if}{$user_longitude + $x + 126}-{if 101 - $user_latitude - $y < 10}0{/if}{101 - $user_latitude - $y}.png){else}black{/if};" Title="{if isset($map_data[($user_latitude + $y)][($user_longitude + $x)])}{$map_data[($user_latitude + $y)][($user_longitude + $x)][1]}: {$map_data[($user_latitude + $y)][($user_longitude + $x)][0]}{/if}">
                  
                  <div class="page-travel-button-level-1 
                       {$b_x = 0}
                       {$b_y = 0}
                       {if $user_latitude + 1 + $y > 100 || 
                          ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) ) || 
                          ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_north",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }

                          {$b_y = $b_y + 1}
                          page-travel-border-top-solid
                       {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y + 1 ][$user_longitude + $x     ][1]}
                          {$b_y = $b_y + 1}
                          page-travel-border-top-light
                       {/if}
                       
                       {if $user_latitude  - 1 + $y < -100 || 
                         ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) ) || 
                         ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_south",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                         {$b_y = $b_y + 1}
                         page-travel-border-bottom-solid
                       {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y - 1 ][$user_longitude + $x     ][1]}
                         {$b_y = $b_y + 1}
                         page-travel-border-bottom-light
                       {/if}
                         
                       {if $user_longitude + 1 + $x > 125 || 
                         ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) ) || 
                         ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_east",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                         {$b_x = $b_x + 1}
                         page-travel-border-right-solid
                       {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x + 1 ][1]}
                         {$b_x = $b_x + 1}
                         page-travel-border-right-light
                       {/if}
                         
                       {if $user_longitude - 1 + $x < -125 || 
                         ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) ) || 
                         ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_west",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                         {$b_x = $b_x + 1}
                         page-travel-border-left-solid
                       {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x - 1 ][1]}
                         {$b_x = $b_x + 1}
                         page-travel-border-left-light
                       {/if}

                       {if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("konoki",    $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                         {if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_konoki)}
                           {$regions_konoki[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
                         {/if}
                         {$regions_flip = array_flip($regions_konoki)}
                         page-travel-background-konoki-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
                       {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("silence",   $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                         {if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_silence)}
                           {$regions_silence[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
                         {/if}
                         {$regions_flip = array_flip($regions_silence)}
                         page-travel-background-silence-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
                       {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("shroud",    $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                         {if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_shroud)}
                           {$regions_shroud[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
                         {/if}
                         {$regions_flip = array_flip($regions_shroud)}
                         page-travel-background-shroud-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
                       {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("shine",     $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                         {if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_shine)}
                           {$regions_shine[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
                         {/if}
                         {$regions_flip = array_flip($regions_shine)}
                         page-travel-background-shine-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
                       {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("samui",     $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                         {if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_samui)}
                           {$regions_samui[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
                         {/if}
                         {$regions_flip = array_flip($regions_samui)}
                         page-travel-background-samui-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
                       {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("syndicate", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                         {if !in_array($map_data[($user_latitude + $y)][($user_longitude + $x )][1], $regions_syndicate)}
                           {$regions_syndicate[] = $map_data[($user_latitude + $y)][($user_longitude + $x )][1]}
                         {/if}
                         {$regions_flip = array_flip($regions_syndicate)}
                         page-travel-background-syndicate-{(($regions_flip[$map_data[($user_latitude + $y)][($user_longitude + $x )][1]] + 1) % 6)+1}
                       {/if}
                       ">
                    <div class="page-travel-button-level-2">
                      <div class="page-travel-button-text-box page-travel-button-level-3">
                        <b class="page-travel-button-text page-travel-button-level-4">{if $direction != '' && ($direction != 'Enter' || $sub_location)}{$direction}{/if}</b>
                      </div>
                    </div>
                  </div>
                  
                </div>
              {/for}

            {/for}
          </div>
        {/if}

        <div class="page-sub-title toggle-button-drop closed" data-target="#village-locations">
            Village Locations
        </div>
        <div class="page-grid page-column-5 toggle-target closed" id="village-locations">
            <div>
                Konoki <br/>  4,50
            </div>

            <div>
                Silence<br/> 40,38
            </div>

            <div>
                Shroud <br/> 40,62
            </div>

            <div>
                Shine  <br/> 17,31
            </div>

            <div>
                Samui  <br/> 17,69
            </div>
        </div>
        <div class="page-sub-title-no-margin toggle-button-drop closed" data-target="#syndicate-locations">
            Hideout Locations
        </div>
        <div class="page-grid page-column-4 toggle-target closed" id="syndicate-locations">
            <div>
                Gambler's Den   <br/> 24,50
            </div>

            <div>
                Bandit's Outpost<br/>-14,62
            </div>

            <div>
                Poacher's Camp  <br/> 05,12
            </div>

            <div>
                Pirate's Hideout<br/> 91,35
            </div>

        </div>

        {if $userdata['user_rank'] == 'Admin'}
				  <div id="travel-form-title" class="page-sub-title-no-margin toggle-button-drop closed" data-target="#travel-form">Map Editor</div>
				  <form id="travel-form" class="toggle-target closed" name="travel_form" method="post" action="">
				  	<div class='font-large'>Name</div>
          	<input name='name' id="travel-form-name" type="text" class="input-text" value="{$map_data[$user_latitude][$user_longitude][0]}"/>
				  	<div class='font-large'>Region</div>
				  	<select name='region' id="travel-form-region">
				  		{foreach $map_region_data as $region}
				  			<option {if $region['region'] == {$map_data[$user_latitude][$user_longitude][1]}}selected{/if} value="{$region['region']}">{$region['region']}</option>
				  		{/foreach}
				  	</select>
				  	<div class='font-large'>claimable</div>
				  	<input name='claimable' id="travel-form-claimable" type="checkbox" {if $map_data[$user_latitude][$user_longitude][2] == 'claimable'}checked{/if}/>
				  	<div class='font-large'>Owner</div>
				  	<select name='owner' id="travel-form-owner">
				  		<option {if $map_data[$user_latitude][$user_longitude][3] == 'konoki'}selected{/if} value="konoki">konoki</option>
				  		<option {if $map_data[$user_latitude][$user_longitude][3] == 'shine'}selected{/if} value="shine">shine</option>
				  		<option {if $map_data[$user_latitude][$user_longitude][3] == 'silence'}selected{/if} value="silence">silence</option>
				  		<option {if $map_data[$user_latitude][$user_longitude][3] == 'shroud'}selected{/if} value="shroud">shroud</option>
				  		<option {if $map_data[$user_latitude][$user_longitude][3] == 'samui'}selected{/if} value="samui">samui</option>
				  		<option {if $map_data[$user_latitude][$user_longitude][3] == 'syndicate'}selected{/if} value="syndicate">syndicate</option>
				  		<option {if $map_data[$user_latitude][$user_longitude][3] == 'none'}selected{/if} value="none">none</option>
				  	</select>
            <div class='font-large'>impassability</div>
					  <label><input name='impassability_impassable' id="widget-travel-form-impassability-impassable" type="checkbox" {if in_array('impassable',$map_data[$user_latitude][$user_longitude])}checked{/if}/>all</label>
					  <label><input name='impassability_north' id="widget-travel-form-impassability-north" type="checkbox" {if in_array('impassable_north',$map_data[$user_latitude][$user_longitude])}checked{/if}/>north</label>
					  <label><input name='impassability_south' id="widget-travel-form-impassability-south" type="checkbox" {if in_array('impassable_south',$map_data[$user_latitude][$user_longitude])}checked{/if}/>south</label>
					  <label><input name='impassability_east' id="widget-travel-form-impassability-east" type="checkbox" {if in_array('impassable_east',$map_data[$user_latitude][$user_longitude])}checked{/if}/>east</label>
					  <label><input name='impassability_west' id="widget-travel-form-impassability-west" type="checkbox" {if in_array('impassable_west',$map_data[$user_latitude][$user_longitude])}checked{/if}/>west</label>
				  	<input id="travel-form-submit"   type="submit" class="button-fill lazy" name="MapUpdate" value="Submit" />
    		  </form>

				  <div id="travel-jump-title" class="page-sub-title-no-margin toggle-button-drop closed" data-target="#travel-jump">Jump</div>
				  <form id="travel-jump" class="toggle-target closed" name="travel_jump" method="post" action="">
				  	<div class='font-large'>Village</div>
				  	<select name="jumpVillage" id="travel-jump-village">
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

				  	<div class='font-large'>X</div>
				  	<input name='jumpX' id="travel-jump-x" type="text" class="input-text" placeholder="x"/>

				  	<div class='font-large'>y</div>
				  	<input name='jumpY' id="travel-jump-y" type="text" class="input-text" placeholder="y"/>

				  	<input id="travel-jump-submit"   type="submit" class="button-fill lazy" name="jump" value="Submit" />
    		  </form>
			  {/if}
    </div>
</div>

<!--<table>
	<tr>
		<td colspan="2">
			Region:
			<select name="region" form="map_data">
				<option {if $smarty.get.region == "Wildwonder Forest"}selected{/if} value="Wildwonder Forest">Wildwonder Forest</option>
				<option {if $smarty.get.region == "Fireheart Forest"}selected{/if} value="Fireheart Forest">Fireheart Forest</option>
				<option {if $smarty.get.region == "Verdant Woodlands"}selected{/if} value="Verdant Woodlands">Verdant Woodlands</option>
				<option {if $smarty.get.region == "Oakwood Forest"}selected{/if} value="Oakwood Forest">Oakwood Forest</option>
				<option {if $smarty.get.region == "Misty Marshland"}selected{/if} value="Misty Marshland">Misty Marshland</option>
				<option {if $smarty.get.region == "Black Spruce Bog"}selected{/if} value="Black Spruce Bog">Black Spruce Bog</option>
				<option {if $smarty.get.region == "Shining Dunes"}selected{/if} value="Shining Dunes">Shining Dunes</option>
				<option {if $smarty.get.region == "Northern Desert"}selected{/if} value="Northern Desert">Northern Desert</option>
				<option {if $smarty.get.region == "Savage Hills"}selected{/if} value="Savage Hills">Savage Hills</option>
				<option {if $smarty.get.region == "Sunrise Canyon"}selected{/if} value="Sunrise Canyon">Sunrise Canyon</option>
				<option {if $smarty.get.region == "Southern Desert"}selected{/if} value="Southern Desert">Southern Desert</option>
				<option {if $smarty.get.region == "Salient Flats"}selected{/if} value="Salient Flats">Salient Flats(replacing jutting flats)</option>
				<option {if $smarty.get.region == "Blackpeak Mountains"}selected{/if} value="Blackpeak Mountains">Blackpeak Mountains</option>
				<option {if $smarty.get.region == "Fortune Mountains"}selected{/if} value="Fortune Mountains">Fortune Mountains</option>
				<option {if $smarty.get.region == "Plateau of Quietude"}selected{/if} value="Plateau of Quietude">Plateau of Quietude</option>
				<option {if $smarty.get.region == "Grey Hills"}selected{/if} value="Grey Hills">Grey Hills</option>
				<option {if $smarty.get.region == "Grey Desert"}selected{/if} value="Grey Desert">Grey Desert</option>
				<option {if $smarty.get.region == "Broken Coast"}selected{/if} value="Broken Coast">Broken Coast(replacing craggy cliffs)</option>
				<option {if $smarty.get.region == "Windswept Grasslands"}selected{/if} value="Windswept Grasslands">Windswept Grasslands</option>
				<option {if $smarty.get.region == "Tornado Valley"}selected{/if} value="Tornado Valley">Tornado Valley</option>
				<option {if $smarty.get.region == "Whirling Valley"}selected{/if} value="Whirling Valley">Whirling Valley</option>
				<option {if $smarty.get.region == "Frozen Highlands"}selected{/if} value="Frozen Highlands">Frozen Highlands</option>
				<option {if $smarty.get.region == "Hyuogaan Mountains"}selected{/if} value="Hyuogaan Mountains">Hyuogaan Mountains</option>
				<option {if $smarty.get.region == "Hyuogaan Icesheet"}selected{/if} value="Hyuogaan Icesheet">Hyuogaan Icesheet</option>
				<option {if $smarty.get.region == "Misty Morass"}selected{/if} value="Misty Morass">Misty Morass</option>
				<option {if $smarty.get.region == "Swamp of Sorrow"}selected{/if} value="Swamp of Sorrow">Swamp of Sorrow</option>
				<option {if $smarty.get.region == "Spirit Lagoon"}selected{/if} value="Spirit Lagoon">Spirit Lagoon</option>
				<option {if $smarty.get.region == "Mistmire"}selected{/if} value="Mistmire">Mistmire</option>
				<option {if $smarty.get.region == "Savage Lakes"}selected{/if} value="Savage Lakes">Savage Lakes</option>
				<option {if $smarty.get.region == "Ravaged Sands"}selected{/if} value="Ravaged Sands">Ravaged Sands</option>
				<option {if $smarty.get.region == "Forest\'s End"}selected{/if} value="Forest\'s End">Forest's End</option>
				<option {if $smarty.get.region == "Ironwood Forest"}selected{/if} value="Ironwood Forest">Ironwood Forest</option>
				<option {if $smarty.get.region == "Deadwood Forest"}selected{/if} value="Deadwood Forest">Deadwood Forest</option>
				<option {if $smarty.get.region == "Shrouded Savannah"}selected{/if} value="Shrouded Savannah">Shrouded Savannah</option>
				<option {if $smarty.get.region == "Darkland Savannah"}selected{/if} value="Darkland Savannah">Darkland Savannah</option>
				<option {if $smarty.get.region == "Gambler\'s Valley"}selected{/if} value="Gambler\'s Valley">Gambler's Valley</option>
				<option {if $smarty.get.region == "Deadwood Hillside"}selected{/if} value="Deadwood Hillside">Deadwood Hillside</option>
				<option {if $smarty.get.region == "Solace Valley"}selected{/if} value="Solace Valley">Solace Valley(replacing Wayfinder's Refuge)</option>
				<option {if $smarty.get.region == "Silvergrass Marshland"}selected{/if} value="Silvergrass Marshland">Silvergrass Marshland</option>
				<option {if $smarty.get.region == "Manatee Island"}selected{/if} value="Manatee Island">Manatee Island</option>
				<option {if $smarty.get.region == "Dolphine cove"}selected{/if} value="Dolphine cove">Dolphine cove</option>
				<option {if $smarty.get.region == "Wayfinder\'s Refuge"}selected{/if} value="Wayfinder\'s Refuge">Wayfinder's Refuge(replacing finny rocks)</option>
				<option {if $smarty.get.region == "Banana Bar"}selected{/if} value="Banana Bar">Banana Bar</option>
				<option {if $smarty.get.region == "uncharted"}selected{/if} value="uncharted">uncharted</option>
				<option {if $smarty.get.region == "shore"}selected{/if} value="shore">shore</option>
				<option {if $smarty.get.region == "river"}selected{/if} value="river">river</option>
				<option {if $smarty.get.region == "lake"}selected{/if} value="lake">lake</option>
				<option {if $smarty.get.region == "dead lake"}selected{/if} value="dead lake">dead lake</option>
				<option {if $smarty.get.region == "ocean"}selected{/if} value="ocean">ocean</option>
			</select> 
		</td>
	</tr>
	</tr>
		<td>
			Owner:
			<select name="owner" form="map_data" selected="{$smarty.post.owner}">
				<option {if $smarty.get.owner == "konoki"}selected{/if} value="konoki">konoki</option>
				<option {if $smarty.get.owner == "shine"}selected{/if} value="shine">shine</option>
				<option {if $smarty.get.owner == "silence"}selected{/if} value="silence">silence</option>
				<option {if $smarty.get.owner == "shroud"}selected{/if} value="shroud">shroud</option>
				<option {if $smarty.get.owner == "samui"}selected{/if} value="samui">samui</option>
				<option {if $smarty.get.owner == "syndicate"}selected{/if} value="syndicate">syndicate</option>
				<option {if $smarty.get.owner == "none"}selected{/if} value="none">none</option>
			</select> 
		</td>
		<td>
			<form action="" id="map_data" method="get">
				<input type="radio" name="claimable" value="claimable" {if $smarty.get.claimable == "claimable"}checked{/if}>-claimable OR
				<input type="radio" name="claimable" value="unclaimable" {if $smarty.get.claimable == "unclaimable"}checked{/if}>-unclaimable
				<input type="hidden" name="id" value="8">
				<input type="hidden" name="x" value="{$x_start}">
				<input type="hidden" name="y" value="{$y_start}">
				<input type="submit">
				<input type="checkbox" name="submit_on_move" {if $smarty.get.submit_on_move == "on"}checked{/if}>snail trail
			</form>
		</td>
	</tr>
</table>

<form action="" id="map_data" method="post">
    <input type="text" name="data">
    <input type="submit">
</form>-->