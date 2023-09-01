<!-- New Character Tab -->
<div class="menuContainer" id="mainUserRankWidget">
    <div class="menuBg1 menuBg1-right">
        <div class="characterRibbon">
        </div>
        <div class="menuPerforationVertical menuPerforationVerticalRight">
            <div class="perforationMenuHorizontal perforationMenuHorizontalTop">
            </div>
            <div style="height:130px; width:160px;">
                <div style="position:absolute; top:10px; left:10px;width:117px;color:{$userColor}; font-family:sakura; font-size:13px; font-variant:small-caps; font-weight:bold; height:35px; letter-spacing:1px; text-align:left;">
                    <font style="color:{$userColor};">
                    {$user_name}
                    </font>
                    <br>
                    of {$user_village}
                </div>
                <div style="height:30px;">
                </div>
                <div style="height:10px; width:160px;">
                </div>

                <div class="ribbonRank">
                    {$user_rank}
                </div>
                <div style="float:right;">
                    <img src="{$user_avatar}" alt="User Menu Avatar" width="75px" />
                </div>
            </div>
            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom">
            </div>
        </div>
    </div>
</div>
<!-- this Stats Menu -->
<div class="menuContainer" id="mainUserStatWidget">
    <div class="menuBg1 menuBg1-right">
        <div class="menuPerforationVertical menuPerforationVerticalRight">
            <div class="perforationMenuHorizontal perforationMenuHorizontalTop">
            </div>

            <div class="menuBg2 menuBg2-left">
                <div class="wrapper_outer">
                    <div class="wrapper_inner">
                        <table cellpadding="0" cellspacing="0" style="color:#3f3e3c; font-family:verdana; font-size:10px;">
                            <tr>
                                <td class="hpcpsp">
                                    HEALTH
                                </td>
                                <td>
                                    <div class="bar">
                                        <img src="{$HP}" height="10" width="{$health_perc}px" id="heaBar" alt="heaBar" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                </td>
                                <td class="stat_num" id="healthTxtBar">
                                    {$cur_health|string_format:"%.2f"}/{$max_health|string_format:"%.2f"}
                                </td>
                            </tr>
                            <tr>
                                <td class="hpcpsp">
                                    CHAKRA
                                </td>
                                <td>
                                    <div class="bar">
                                        <img src="{$CP}" height="10" width="{$cha_perc}px" id="chaBar" alt="chaBar" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                </td>
                                <td class="stat_num" id="chakraTxtBar">
                                    {$cur_cha|string_format:"%.2f"}/{$max_cha|string_format:"%.2f"}
                                </td>
                            </tr>
                            <tr>
                                <td class="hpcpsp">
                                    STAMINA
                                </td>
                                <td>
                                    <div class="bar">
                                        <img src="{$SP}" height="10" width="{$sta_perc}px" id="staBar" alt="staBar"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                </td>
                                <td class="stat_num" id="staminaTxtBar">
                                    {$cur_sta|string_format:"%.2f"}/{$max_sta|string_format:"%.2f"}
                                </td>
                            </tr>
                            <tr>
                                <td class="hpcpsp">
                                    STATUS
                                </td>
                                <td id="sleepLink" class="hpcpsp" style="font-weight: normal;text-align: left;">
                                    {$userStatus|capitalize} {if isset($sleepLink)}({$sleepLink}){/if}
                                </td>
                            </tr>
                            <tr>
                                <td class="hpcpsp">
                                    MONEY
                                </td>
                                <td id="currentRyoField" class="hpcpsp" style="font-weight: normal;text-align: left;">
                                    {$userMoney} Ryo
                                    {if isset($userRegeneration)} {$userRegeneration} {/if} 
                                </td>
                            </tr>                            
                            <tr>
                                <td class="hpcpsp">
                                    POWER
                                </td>
                                <td id="currentRyoField" class="hpcpsp" style="font-weight: normal;text-align: left;">
                                    {$strengthFactor}
                                </td>
                            </tr>
                            <tr style="height:30px;">
                                <td class="hpcpsp">
                                    LOGOUT
                                </td>
                                <td id="currentRyoField" class="hpcpsp" style="font-weight: normal;text-align: left;">
                                    {$logoutTimer}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom">
            </div>
        </div>
    </div>
    <div class="ribbonStats ribbonStats-left">
        <div class="ribbonText" style="text-align:center">
            stats &nbsp;
        </div>
    </div>
</div>

{if $userStatus == 'awake' || $userStatus == 'combat'}

  <!-- New Travel Widget -->
  <script type="text/javascript">
    var disabled_flag = false;
    
    $(document).ready(function() {
    readyTravelWidget();
    });
  
    function readyTravelWidget()
    {
      travelOn();
    }
    
    function travelOff()
    {
      $('#N').off('click');
      $('#NW').off('click');
      $('#NE').off('click');
      $('#ENTER').off('click');
      $('#W').off('click');
      $('#E').off('click');
      $('#S').off('click');
      $('#SW').off('click');
      $('#SE').off('click');
    }
    
    function travelOn()
    {
      travelOff();
      if(!disabled_flag)
      {
        $('#N').on('click', function(){   doTravel("N");   });
        $('#NW').on('click', function(){   doTravel("NW");   });
        $('#NE').on('click', function(){   doTravel("NE");   });
        $('#ENTER').on('click', function(){   doTravel("ENTER");   });
        $('#W').on('click', function(){   doTravel("W");   });
        $('#E').on('click', function(){   doTravel("E");   });
        $('#S').on('click', function(){   doTravel("S");   });
        $('#SW').on('click', function(){   doTravel("SW");   });
        $('#SE').on('click', function(){   doTravel("SE");   });
      }
    }
  
    function doTravel( instruction )
    {
      $(".contentTable").first().load( (window.location.href.replace(/&act=wake|&act=sleep|&act=gather|&act=equip|&act=delete|&process=split|&process=merge|&process=take_out|&forget=\d*|&start=\d*|&quit=\d*|&track=\d*|&invID=\d*|&turn_in=\d*|&dialog_option=[^&]*/ig, '')) + " #mainContent", { doTravel: instruction }, function(response) {       readyMenu(); readyTravelWidget(); if($(response).find('#confirm_popups').length) eval($(response).find('#confirm_popups')[0]       .innerHTML); });
      disabled_flag = true;
      travelOff();
      setTimeout(function(){
          disabled_flag = false;
         travelOn();
      },250);
    }
  
  </script>
  <div class="menuContainer" style="padding-top:5px;" id="mainUserTravelWidget">
    {if $smarty.get.map != 'local' && $smarty.get.id != '8'}
    <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.4/jquery.lazy.min.js"></script>
      <div class="menuBg1 menuBg1-right">
        <div class="menuPerforationVertical menuPerforationVerticalRight">
          <div class="perforationMenuHorizontal perforationMenuHorizontalTop">
          </div>
          <div class="menuBg2 menuBg2-left" style="padding-top:38px;">
            <table style="width:100%;">
              <tr>
                <td style="font-family:Segoe UI;font-size:11px;">
                  <span style="font-weight:600;">{$user_location}</span>
                </td>
              </tr>
            </table>
            <table style="width:100%;">
              <tr>
                <td style="font-family:Segoe UI;font-size:10px; style="text-align:left;">
                  <span style="font-weight:600;">Region: </span>{$user_region}
                </td>
                <td style="font-family:Segoe UI;font-size:10px; style="text-align:right;">
                  ({$user_longitude},{$user_latitude})
                </td>
              </tr>
            </table>
            <div class="wrapper_outer">
    
              <table cellpadding="0" cellspacing="0" style="position:relative;left:-8px;line-height:0;background-repeat:no-repeat;background:white;border:1px solid black;background:URL(./images/maps/travel_widget_background.png);">

                {assign var=b_x value=0}
                {assign var=b_y value=0}
                {assign var=low_x_bound value=-1}
                {assign var=high_x_bound value=1}
                {assign var=low_y_bound value=-1}
                {assign var=high_y_bound value=1}

                {for $not_y=$low_y_bound to $high_y_bound}
                {$y = $not_y * -1}
                <tr>
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
                      
                      <td class="lazy" {if $direction != '' && ($direction != 'Enter' || $sub_location)} id="{$direction}" {/if} class="disabled" style="padding:0px;background:{if $user_longitude + $x <= 125 && $user_longitude + $x >= -125 && $user_latitude + $y <= 100 && $user_latitude +$y >= -100}url({$s3}/seichi/Seichi-{if $user_longitude + $x + 126 < 10}0{/if}{$user_longitude + $x + 126}-{if 101 - $user_latitude - $y < 10}0{/if}{101 - $user_latitude - $y}.png){else}black{/if};" Title="{if isset($map_data[($user_latitude + $y)][($user_longitude + $x)])}{$map_data[($user_latitude + $y)][($user_longitude + $x)][1]}: {$map_data[($user_latitude + $y)][($user_longitude + $x)][0]}{/if}">
                        
                        <div style="border-color:RGBA(0,0,0,0.33);color:RGBA(0,0,0,0.25);
                             {$b_x = 0}
                             {$b_y = 0}
                             {if $user_latitude + 1 + $y > 100 || 
                                ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y + 1)][($user_longitude + $x)]) ) || 
                                ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_north",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }

                                {$b_y = $b_y + 1}
                                border-top:1px solid black;
                             {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y + 1 ][$user_longitude + $x     ][1]}
                                {$b_y = $b_y + 1}
                                border-top:1px solid;
                             {/if}
                             
                             {if $user_latitude  - 1 + $y < -100 || 
                               ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y - 1)][($user_longitude + $x)]) ) || 
                               ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_south",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                               {$b_y = $b_y + 1}
                               border-bottom:1px solid black;                             
                             {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y - 1 ][$user_longitude + $x     ][1]}
                               {$b_y = $b_y + 1}
                               border-bottom:1px solid;
                             {/if}
                               
                             {if $user_longitude + 1 + $x > 125 || 
                               ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x + 1)]) ) || 
                               ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_east",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                               {$b_x = $b_x + 1}
                               border-right:1px solid black;                             
                             {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x + 1 ][1]}
                               {$b_x = $b_x + 1}
                               border-right:1px solid;
                             {/if}
                               
                             {if $user_longitude - 1 + $x < -125 || 
                               ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && is_array($map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) && in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x)]) != in_array("impassable",$map_data[($user_latitude + $y)][($user_longitude + $x - 1)]) ) || 
                               ( is_array($map_data[($user_latitude + $y)][($user_longitude + $x)]) && in_array("impassable_west",$map_data[($user_latitude + $y)][($user_longitude + $x)]) ) }
                               {$b_x = $b_x + 1}
                               border-left:1px solid black;                             
                             {else if $map_data[$user_latitude + $y ][$user_longitude + $x ][1] != $map_data[$user_latitude + $y     ][$user_longitude + $x - 1 ][1]}
                               {$b_x = $b_x + 1}
                               border-left:1px solid;
                             {/if}
                               
                             width:{60 - $b_x}px;
                             height:{60 - $b_y}px;
                             
                             {if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("konoki",    $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                               background:RGBA( 100, 200, 100, 0.25);
                             {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("silence",   $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                               background:RGBA(  100,  150,  200, 0.25);
                             {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("shroud",    $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                               background:RGBA( 150, 100, 200, 0.25);
                             {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("shine",     $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                               background:RGBA( 200, 100, 100, 0.25);
                             {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("samui",     $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                               background:RGBA( 200, 150, 100, 0.25);
                             {else if is_array($map_data[($user_latitude + $y)][($user_longitude + $x )]) && in_array("syndicate", $map_data[($user_latitude + $y)][($user_longitude + $x )])}
                               background:RGBA(   0,   0,   0, 0.25);
                             {/if}
                             ">
                          <div>
                            <div>
                              <b style="position:relative;top:30px;-webkit-touch-callout: none;-webkit-user-select: none;-khtml-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;">{if $direction != '' && ($direction != 'Enter' || $sub_location)}{$direction}{/if}</b>
                            </div>
                        	</div>
                        </div>
                        
                      </td>
                    {/for}
                  </tr>
                {/for}
              </table>
            </div>
          </div>
          <div class="perforationMenuHorizontal2">
          </div>
        </div>
      </div>

      <div class="ribbonStats ribbonStats-left">
        <div class="ribbonText" style="text-align:center;">
          <a href='?id=8' style='font-family:sakura;color:#EEE;font-weight:500;' title="Travel Page">Travel</a>
        </div>
      </div>
    <script>
        $(function() {
            $('.lazy').lazy();
        });
    </script>
    {/if}
  </div>
{/if}