<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.4/jquery.lazy.min.js"></script>

{literal}
<style>
  svg{transform:scale(1)}
  path{stroke-width:0.01}
  #territory-map-container{position: relative;height: 534px;max-height: 80vh;}
  #territory-map{background-size: contain;background-repeat: no-repeat;background-position-x: center;width: 100% !important;height: 100% !important;}
  #territory-map-overlay{position: absolute;top: 0px;left: 0px;-webkit-transform: scale(1);transform: scale(1);width: 100%;max-height: 100%;}

  .uncharted,.river,.shore,.ocean,.lake,.dead_lake{fill:rgba(64,64,64,0.50);stroke:rgba(64,64,64,0.50)}
  .Elemental_Shrine{fill:rgba(255,0,255,0.5) !important}
  .ramen{fill:rgba(255,255,0,0.5) !important}
  .konoki-1   {fill:rgba(100,200,100,0.50);stroke:rgba(100,200,100,0.50);filter:brightness(150%) saturate(0.5)}  .konoki-2   {fill:rgba(100,200,100,0.50);stroke:rgba(100,200,100,0.50);filter:brightness(110%) saturate(1.1)}  .konoki-3   {fill:rgba(100,200,100,0.50);stroke:rgba(100,200,100,0.50);filter:brightness( 70%) saturate(1.7)}  .konoki-4   {fill:rgba(100,200,100,0.50);stroke:rgba(100,200,100,0.50);filter:brightness(130%) saturate(0.8)}  .konoki-5   {fill:rgba(100,200,100,0.50);stroke:rgba(100,200,100,0.50);filter:brightness( 90%) saturate(1.4)}  .konoki-6   {fill:rgba(100,200,100,0.50);stroke:rgba(100,200,100,0.50);filter:brightness( 50%) saturate(2.0)}
  .silence-1  {fill:rgba(100,150,200,0.50);stroke:rgba(100,150,200,0.50);filter:brightness(150%) saturate(0.5)}  .silence-2  {fill:rgba(100,150,200,0.50);stroke:rgba(100,150,200,0.50);filter:brightness(110%) saturate(1.1)}  .silence-3  {fill:rgba(100,150,200,0.50);stroke:rgba(100,150,200,0.50);filter:brightness( 70%) saturate(1.7)}  .silence-4  {fill:rgba(100,150,200,0.50);stroke:rgba(100,150,200,0.50);filter:brightness(130%) saturate(0.8)}  .silence-5  {fill:rgba(100,150,200,0.50);stroke:rgba(100,150,200,0.50);filter:brightness( 90%) saturate(1.4)}  .silence-6  {fill:rgba(100,150,200,0.50);stroke:rgba(100,150,200,0.50);filter:brightness( 50%) saturate(2.0)}
  .shroud-1   {fill:rgba(150,100,200,0.50);stroke:rgba(150,100,200,0.50);filter:brightness(150%) saturate(0.5)}  .shroud-2   {fill:rgba(150,100,200,0.50);stroke:rgba(150,100,200,0.50);filter:brightness(110%) saturate(1.1)}  .shroud-3   {fill:rgba(150,100,200,0.50);stroke:rgba(150,100,200,0.50);filter:brightness( 70%) saturate(1.7)}  .shroud-4   {fill:rgba(150,100,200,0.50);stroke:rgba(150,100,200,0.50);filter:brightness(130%) saturate(0.8)}  .shroud-5   {fill:rgba(150,100,200,0.50);stroke:rgba(150,100,200,0.50);filter:brightness( 90%) saturate(1.4)}  .shroud-6   {fill:rgba(150,100,200,0.50);stroke:rgba(150,100,200,0.50);filter:brightness( 50%) saturate(2.0)}
  .shine-1    {fill:rgba(200,100,100,0.50);stroke:rgba(200,100,100,0.50);filter:brightness(150%) saturate(0.5)}  .shine-2    {fill:rgba(200,100,100,0.50);stroke:rgba(200,100,100,0.50);filter:brightness(110%) saturate(1.1)}  .shine-3    {fill:rgba(200,100,100,0.50);stroke:rgba(200,100,100,0.50);filter:brightness( 70%) saturate(1.7)}  .shine-4    {fill:rgba(200,100,100,0.50);stroke:rgba(200,100,100,0.50);filter:brightness(130%) saturate(0.8)}  .shine-5    {fill:rgba(200,100,100,0.50);stroke:rgba(200,100,100,0.50);filter:brightness( 90%) saturate(1.4)}  .shine-6    {fill:rgba(200,100,100,0.50);stroke:rgba(200,100,100,0.50);filter:brightness( 50%) saturate(2.0)}
  .samui-1    {fill:rgba(200,150,100,0.50);stroke:rgba(200,150,100,0.50);filter:brightness(150%) saturate(0.5)}  .samui-2    {fill:rgba(200,150,100,0.50);stroke:rgba(200,150,100,0.50);filter:brightness(110%) saturate(1.1)}  .samui-3    {fill:rgba(200,150,100,0.50);stroke:rgba(200,150,100,0.50);filter:brightness( 70%) saturate(1.7)}  .samui-4    {fill:rgba(200,150,100,0.50);stroke:rgba(200,150,100,0.50);filter:brightness(130%) saturate(0.8)}  .samui-5    {fill:rgba(200,150,100,0.50);stroke:rgba(200,150,100,0.50);filter:brightness( 90%) saturate(1.4)}  .samui-6    {fill:rgba(200,150,100,0.50);stroke:rgba(200,150,100,0.50);filter:brightness( 50%) saturate(2.0)}
  .syndicate-1{fill:rgba( 78, 49, 69,0.50);stroke:rgba( 78, 49, 69,0.50);filter:brightness(250%) saturate(0.5)}  .syndicate-2{fill:rgba( 78, 49, 69,0.50);stroke:rgba( 78, 49, 69,0.50);filter:brightness(170%) saturate(1.1)}  .syndicate-3{fill:rgba( 78, 49, 69,0.50);stroke:rgba( 78, 49, 69,0.50);filter:brightness( 90%) saturate(1.7)}  .syndicate-4{fill:rgba( 78, 49, 69,0.50);stroke:rgba( 78, 49, 69,0.50);filter:brightness(210%) saturate(0.8)}  .syndicate-5{fill:rgba( 78, 49, 69,0.50);stroke:rgba( 78, 49, 69,0.50);filter:brightness(130%) saturate(1.4)}  .syndicate-6{fill:rgba( 78, 49, 69,0.50);stroke:rgba( 78, 49, 69,0.50);filter:brightness( 50%) saturate(2.0)}
  .konoki-1.village, .silence-1.village, .shroud-1.village, .shine-1.village, .samui-1.village{filter: brightness(190%) saturate(1.0)}  .konoki-2.village, .silence-2.village, .shroud-2.village, .shine-2.village, .samui-2.village{filter: brightness(150%) saturate(1.6)}  .konoki-3.village, .silence-3.village, .shroud-3.village, .shine-3.village, .samui-3.village{filter: brightness(110%) saturate(2.2)}  .konoki-4.village, .silence-4.village, .shroud-4.village, .shine-4.village, .samui-4.village{filter: brightness(170%) saturate(1.3)}  .konoki-5.village, .silence-5.village, .shroud-5.village, .shine-5.village, .samui-5.village{filter: brightness(130%) saturate(1.9)}  .konoki-6.village, .silence-6.village, .shroud-6.village, .shine-6.village, .samui-6.village{filter: brightness( 90%) saturate(2.5)}
  .syndicate-1.village{filter: brightness(200%) saturate(0.0)}  .syndicate-2.village{filter: brightness(120%) saturate(0.6)}  .syndicate-3.village{filter: brightness( 40%) saturate(1.2)}  .syndicate-4.village{filter: brightness(160%) saturate(0.3)}  .syndicate-5.village{filter: brightness( 80%) saturate(0.9)}  .syndicate-6.village{filter: brightness(  0%) saturate(1.5)}
</style>
{/literal}

<div align="center" style="position:relative;top:-10px;">
    <table class="table" style="position:relative;width:100.4%;left:-1px;margin-bottom:0px;">
        <tr>
            <td class="subHeader"><a href="?id={$smarty.get.id}&map=local" >Local Map</a></td>
            <td class="subHeader"><a href="?id={$smarty.get.id}&map=territory" >Territory Map</a></td>
        </tr>
      
        <tr class="tableColumns">
          <td style="text-align:left;">
            Location: {$x},{$y} - {$location}
          </td>
          <td style="text-align:right;">
            ({$region}, {if $map == ''}Seichi{else}{$map}{/if})
          </td>
        </tr>
    </table>

    {if $smarty.get.map == 'territory'}
      <div id="territory-map-container">
        <div id="territory-map" class="lazy" style="background-image:url({$s3}/seichi/SeichiTerritoryMap.png);" title="territory map"></div>

        {$map_overlay}

        <circle id="player-marker" class="map-overlay-addition" cx="{$x + 0.5}" cy="{100 - ($y - 0.5)}" r="0.5" style="fill:red;"><title>You are here!</title></circle>
        <img src onerror="$('.map-overlay-addition').each(function(){ $('#territory-map-overlay')[0].innerHTML += $(this)[0].outerHTML; })">

      </div>
    {else}
      <table cellpadding="0" cellspacing="0" style="position:relative;left:-1px;top:-1px;line-height:0;background-repeat:no-repeat;background:white;border:1px solid black;background:URL(./images/maps/travel_widget_background.png);">

        {assign var=b_x value=0}
        {assign var=b_y value=0}
        {assign var=low_x_bound value=-4}
        {assign var=high_x_bound value=4}
        {assign var=low_y_bound value=-4}
        {assign var=high_y_bound value=4}

        {for $not_y=$low_y_bound to $high_y_bound}
        {$y = $not_y * -1}
        <tr>
            {for $x=$low_x_bound to $high_x_bound}

              <!-- finding if this square is impassable -->
              {if abs($user_longitude + $x) > 125 || abs($user_latitude + $y) > 100}
                {assign var=impassable value=1}
              {else if !isset($map_data[($user_latitude + $y)][($user_longitude + $x)]) || !is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) || in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)])}
                {assign var=impassable value=1}
              {else}
                {assign var=impassable value=0}
              {/if}

              <!-- finding if this is a direction square -->
              {if      $y == $high_y_bound && $x == 0 && abs($user_longitude + 0) <= 125 && abs($user_latitude + 1) <= 100 && !in_array("impassable",$map_data[($user_latitude + 1)][($user_longitude + 0)]) && !in_array("impassable_north",$map_data[($user_latitude)][($user_longitude)])}
                {assign var=direction value='N'}
              {else if  $y == $low_y_bound && $x == 0 && abs($user_longitude + 0) <= 125 && abs($user_latitude - 1) <= 100 && !in_array("impassable",$map_data[($user_latitude - 1)][($user_longitude + 0)]) && !in_array("impassable_south",$map_data[($user_latitude)][($user_longitude)])}
                {assign var=direction value='S'}
              {else if $y == 0 && $x == $high_x_bound && abs($user_longitude + 1) <= 125 && abs($user_latitude + 0) <= 100 && !in_array("impassable",$map_data[($user_latitude + 0)][($user_longitude + 1)]) && !in_array("impassable_east",$map_data[($user_latitude)][($user_longitude)])}
                {assign var=direction value='E'}
              {else if  $y == 0 && $x == $low_x_bound && abs($user_longitude - 1) <= 125 && abs($user_latitude + 0) <= 100 && !in_array("impassable",$map_data[($user_latitude + 0)][($user_longitude - 1)]) && !in_array("impassable_west",$map_data[($user_latitude)][($user_longitude)])}
                {assign var=direction value='W'}
              {else if $y == $high_y_bound && $x == $low_x_bound && abs($user_longitude - 1) <= 125 && abs($user_latitude + 1) <= 100 && !in_array("impassable",$map_data[($user_latitude + 1)][($user_longitude - 1)]) && !in_array("impassable_north",$map_data[($user_latitude)][($user_longitude)]) && !in_array("impassable_west",$map_data[($user_latitude)][($user_longitude)])}
                {assign var=direction value='NW'}
              {else if $y == $high_y_bound && $x == $high_x_bound && abs($user_longitude + 1) <= 125 && abs($user_latitude + 1) <= 100 && !in_array("impassable",$map_data[($user_latitude + 1)][($user_longitude + 1)]) && !in_array("impassable_north",$map_data[($user_latitude)][($user_longitude)]) && !in_array("impassable_east",$map_data[($user_latitude)][($user_longitude)])}
                {assign var=direction value='NE'}
              {else if $y == $low_y_bound && $x == $low_x_bound && abs($user_longitude - 1) <= 125 && abs($user_latitude - 1) <= 100 && !in_array("impassable",$map_data[($user_latitude - 1)][($user_longitude - 1)]) && !in_array("impassable_south",$map_data[($user_latitude)][($user_longitude)]) && !in_array("impassable_west",$map_data[($user_latitude)][($user_longitude)])}
                {assign var=direction value='SW'}
              {else if $y == $low_y_bound && $x == $high_x_bound && abs($user_longitude + 1) <= 125 && abs($user_latitude - 1) <= 100 && !in_array("impassable",$map_data[($user_latitude - 1)][($user_longitude + 1)]) && !in_array("impassable_south",$map_data[($user_latitude)][($user_longitude)]) && !in_array("impassable_east",$map_data[($user_latitude)][($user_longitude)])}
                {assign var=direction value='SE'}
              {else if $y == 0 && $x == 0 }
                {assign var=direction value='Enter'}
              {else}
                {assign var=direction value=''}
              {/if}

              <td class="lazy" {if $direction != '' && ($direction != 'Enter' || $sub_location)} id="{$direction}" {/if} class="disabled" style="padding:0px;background:{if $user_longitude + $x <= 125 && $user_longitude + $x >= -125 && $user_latitude + $y <= 100 && $user_latitude +$y >= -100}url({$s3}/seichi/Seichi-{if $user_longitude + $x + 126 < 10}0{/if}{$user_longitude + $x + 126}-{if 101 - $user_latitude - $y < 10}0{/if}{101 - $user_latitude - $y}.png){else}black{/if};" Title="{if isset($map_data[($user_latitude + $y)][($user_longitude + $x)])}{$map_data[($user_latitude + $y)][($user_longitude + $x)][1]}: {$map_data[($user_latitude + $y)][($user_longitude + $x)][0]}{/if}">

                <div style="border-color:RGBA(0,0,0,0.33);color:RGBA(0,0,0,0.25); 
                    {$b_x = 0}
                    {$b_y = 0}
                    {if $user_latitude + 1 + $y > 100 || !is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) || !is_array($map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) || in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) || in_array("impassable_north",$map_data[($user_latitude + $y)][($user_longitude + $x)])}
                        {$b_y = $b_y + 1}
                        border-top:1px solid black;
                     {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y + 1 ][$user_longitude + $x     ][1]}
                        {$b_y = $b_y + 1}
                        border-top:1px solid;
                     {/if}

                     {if $user_latitude  - 1 + $y < -100 || !is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) || !is_array($map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) || in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) || in_array("impassable_south",$map_data[($user_latitude + $y)][($user_longitude + $x)])}
                       {$b_y = $b_y + 1}
                       border-bottom:1px solid black;                             
                     {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y - 1 ][$user_longitude + $x     ][1]}
                       {$b_y = $b_y + 1}
                       border-bottom:1px solid;
                     {/if}

                     {if $user_longitude + 1 + $x > 125 || !is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) || !is_array($map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) || in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) || in_array("impassable_east",$map_data[($user_latitude + $y)][($user_longitude + $x)])}
                       {$b_x = $b_x + 1}
                       border-right:1px solid black;                             
                     {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x + 1 ][1]}
                       {$b_x = $b_x + 1}
                       border-right:1px solid;
                     {/if}

                     {if $user_longitude - 1 + $x < -125 || !is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) || !is_array($map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) || in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) || in_array("impassable_west",$map_data[($user_latitude + $y)][($user_longitude + $x)])}
                       {$b_x = $b_x + 1}
                       border-left:1px solid black;                             
                     {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x - 1 ][1]}
                       {$b_x = $b_x + 1}
                       border-left:1px solid;
                     {/if}

                     width:{60 - $b_x}px;
                     height:{60 - $b_y}px;

                     {if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )])}
                      {if in_array("konoki",    $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                        background:RGBA( 100, 200, 100, 0.25);
                      {else if in_array("silence",   $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                        background:RGBA(  100,  150,  200, 0.25);
                      {else if in_array("shroud",    $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                        background:RGBA( 150, 100, 200, 0.25);
                      {else if in_array("shine",     $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                        background:RGBA( 200, 100, 100, 0.25);
                      {else if in_array("samui",     $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                        background:RGBA( 200, 150, 100, 0.25);
                      {else if in_array("syndicate", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                        background:RGBA(   0,   0,   0, 0.25);

  				            <!--{else if in_array("uncharted", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                          background:RGBA(   128,   128,   128, 0.50);
  				            {else if in_array("ocean", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                          background:RGBA(   0,   0,   128, 0.50);
  				            {else if in_array("river", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                          background:RGBA(   60,   60,   192, 0.50);
  				            {else if in_array("lake", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                          background:RGBA(   0,   0,   192, 0.50);
  				            {else if in_array("dead lake", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                          background:RGBA(   128,   128,   192, 0.50);
  				            {else if in_array("shore", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                          background:RGBA(   130,   190,   255, 0.50);
                      {/if}-->
                     {/if}
                     ">
                  <div style="width:{60 - $b_x}px;height:{60 - $b_y}px;">
                    <div style="width:{60 - $b_x}px;height:{60 - $b_y}px;">
                      <b style="position:relative;top:30px;-webkit-touch-callout: none;-webkit-user-select: none;-khtml-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;">{if $direction != '' && ($direction != 'Enter' || $sub_location)}{$direction}{/if} <!--if in_array("claimable",$map_data[($user_latitude + $y)][($user_longitude + $x)])}C/if}if $x==0 && $y==0}X/if}--></b>
                    </div>
              	  </div>
                </div>
              
              </td>
            {/for}
          </tr>
        {/for}
      </table>
    {/if}
    <table border="0" class="table" cellspacing="0" cellpadding="0" style="position:relative;width:100.4%;top:-2px;left:-1px;">
        <tr>
            <td align="center" style="border-top:none;" class="subHeader">Village Locations</td>
        </tr>

        <tr>
            <td>
                <div style="padding-top:5px;padding-bottom:5px;">
                    <table style="width:100%;">
                        <tr>
                            <td align="center" width="20%">Konoki<br>4,50</td>
                            <td align="center" width="20%">Silence<br>40,38</td>
                            <td align="center" width="20%">Shroud<br>40,62</td>
                            <td align="center" width="20%">Shine<br>17,31</td>
                            <td align="center" width="20%">Samui<br>17,69</td>
                        </tr>
                    </table>
                    <br>
                    <table style="width:100%;">
                        <tr>
                            <td align="center" width="25%">Gambler's Den<br>24,50</td>
                            <td align="center" width="25%">Bandit's Outpost<br>-14,62</td>
                            <td align="center" width="25%">Poacher's Camp<br>5,12</td>
                            <td align="center" width="25%">Pirate's Hideout<br>91,35</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
    <br>

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

</div>
<script>
    $(function() {
        $('.lazy').lazy();
    });
</script>