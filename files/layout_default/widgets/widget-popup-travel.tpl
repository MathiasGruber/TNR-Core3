<div id="widget-popup-travel-wrapper" {if $_COOKIE['widget-popup-travel-open'] == true}style="display:block;"{/if}>
    <div id="widget-popup-travel">
    
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
          
          <div class="widget-popup-travel-button-level-0 lazy" {if $direction != '' && ($direction != 'Enter' || $sub_location)} id="popup-{$direction}" {if $userStatus == 'awake' || $userStatus == 'combat'}onclick="loadPage(null,'all','doTravel:{$direction}');"{/if} {/if} style="padding:0px;background-image:{if $user_longitude + $x <= 125 && $user_longitude + $x >= -125 && $user_latitude + $y <= 100 && $user_latitude +$y >= -100}url({$s3}/seichi/Seichi-{if $user_longitude + $x + 126 < 10}0{/if}{$user_longitude + $x + 126}-{if 101 - $user_latitude - $y < 10}0{/if}{101 - $user_latitude - $y}.png){else}black{/if};" Title="{if isset($map_data[($user_latitude + $y)][($user_longitude + $x)])}{$map_data[($user_latitude + $y)][($user_longitude + $x)][1]}: {$map_data[($user_latitude + $y)][($user_longitude + $x)][0]}{/if}">
            
            <div class="widget-popup-travel-button-level-1
                 {$b_x = 0}
                 {$b_y = 0}
                 {if $user_latitude + 1 + $y > 100 || 
                    ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) ) || 
                    ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_north",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
    
                    {$b_y = $b_y + 1}
                    widget-popup-travel-border-top-solid
                 {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y + 1 ][$user_longitude + $x     ][1]}
                    {$b_y = $b_y + 1}
                    widget-popup-travel-border-top-light
                 {/if}
                 
                 {if $user_latitude  - 1 + $y < -100 || 
                   ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) ) || 
                   ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_south",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                   {$b_y = $b_y + 1}
                   widget-popup-travel-border-bottom-solid
                 {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y - 1 ][$user_longitude + $x     ][1]}
                   {$b_y = $b_y + 1}
                   widget-popup-travel-border-bottom-light
                 {/if}
                   
                 {if $user_longitude + 1 + $x > 125 || 
                   ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) ) || 
                   ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_east",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                   {$b_x = $b_x + 1}
                   widget-popup-travel-border-right-solid
                 {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x + 1 ][1]}
                   {$b_x = $b_x + 1}
                   widget-popup-travel-border-right-light
                 {/if}
                   
                 {if $user_longitude - 1 + $x < -125 || 
                   ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) ) || 
                   ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_west",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                   {$b_x = $b_x + 1}
                   widget-popup-travel-border-left-solid
                 {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x - 1 ][1]}
                   {$b_x = $b_x + 1}
                   widget-popup-travel-border-left-light
                 {/if}
                   
                 /*width:{60 - $b_x}px;*/
                 /*height:{60 - $b_y}px;*/
                 
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
              <div class="widget-popup-travel-button-level-2">
                <div class="widget-popup-travel-button-text-box widget-popup-travel-button-level-3">
                  <b class="widget-popup-travel-button-text widget-popup-travel-button-level-4">{if ($userStatus == 'awake'  || $userStatus == 'combat') && $direction != '' && ($direction != 'Enter' || $sub_location)}{$direction}{/if}</b>
                </div>
              </div>
            </div>
            
          </div>
        {/for}
    
      {/for}
      <div id="widget-popup-travel-title-button" 
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
      <div></div>
      <div id="widget-popup-travel-xy">({$user_longitude},{$user_latitude})</div>
    </div>
</div>