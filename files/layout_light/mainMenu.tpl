<script type="text/javascript"  id="menuScript">
    {include file="./Scripts/menuScript.js"}
</script>

{if isset($popups) && count($popups) != 0}
  <script type="text/javascript" id="confirm_popups">

    {foreach $popups as $popup }
    
      $.confirm({
      
        useBootstrap: false,
        closeIcon:  true,
        
        title:      {if $popup['title'] != ''}
                      "{$popup['title']}"
                    {else}
                      "Hey{if random_int(1,1000) == 1} listen{/if}!"
                    {/if},

        boxWidth:   {if $popup['boxWidth'] != ''}
                      "{$popup['boxWidth']}"
                    {else}
                      "75%"
                    {/if},


        type:       {if $popup['color'] != ''}
                      "{$popup['color']}"
                    {else}
                      "blue"
                    {/if},

        theme:      {if $popup['theme'] != ''}
                      "{$popup['theme']}"
                    {else}
                      "light"
                    {/if},

        content:    {if $popup['text'] != '' &&  !is_array($popup['text'])}
                      "{str_replace('"', '&quot;',preg_replace("/[\n\r]/","",$popup['text']))}"
                    {else if $popup['text'] != '' && is_array($popup['text'])}
                      "<a href='{$popup['text'][0]}'>{str_replace('"', '&quot;',preg_replace("/[\n\r]/","",$popup['text'][1]))}</a>"
                    {else}
                      "Empty."
                    {/if},

        buttons:    {if $popup['buttons'] != '' && $popup['buttons'] != 'none' && !is_array($popup['buttons'][0])}
                      {
                        "{$popup['buttons'][1]}": { btnClass: "btn-{if $popup['color'] != ''}{$popup['color']}{else}blue{/if}", action: function(){ location.href = "{$popup['buttons'][0]}"; } }
                      }
                    {else if $popup['buttons'] != '' && is_array($popup['buttons'][0])}
                      {
                        {foreach $popup['buttons'] as $button}
                        
                          "{$button[1]}": { btnClass: "btn-{if $popup['color'] != ''}{$popup['color']}{else}blue{/if}", action: function(){ location.href = "{$button[0]}"; } },
                          
                        {/foreach}
                      }
                    {else}
                      {
                        ok: function(){}
                      }
                    {/if}
    });

    {/foreach}

  </script>
{/if}

<div id="div_menuInclude">

<div id="notifications">
    {if isset($notifications) && count($notifications) != 0}
      <table cellpadding="0" cellspacing="0" width="95%" align="center" class="table">
        
        <tr><td class="subHeader" width="33%"></td><td class="subHeader" width="34%"><a href='?id={$smarty.get.id}&show-all-notifications=yes' style='font-family:none;'>Notifications</a></td><td class="subHeader" width="33%"></td></tr>
        
        {foreach $notifications as $notification_key => $notification}
          {if ($notification['duration'] !== 'dismissed' && $notification['duration'] !== 'done') || $smarty.get.show-all-notifications == 'yes'}
            <tr class="row{($notification_key % 2)+1} notification-{$notification['id']}">
              <td class="notification-td"></td>
              <td class="notification-td">
                {if !is_array($notification['text'])}
                  {$notification['text']}
                {else}
                  <a {if $notification['text'][2] == 'yes'}
                              onclick="$.confirm({ theme: 'light', type: 'blue', boxWidth: '75%', useBootstrap: false,
                                title: '{$notification['text'][1]}',
                                content: 'Are you sure you would like to go?',
                                buttons: { yes: function () {
                                        location.href = '{$notification['text'][0]}';
                                    }, no: function () { return true; }
                                }}); "
                            {else}
                               href='{$notification['text'][0]}'
                            {/if}>{$notification['text'][1]}</a>
                {/if}
              </td>
              <td class="notification-td">
                {if $notification['dismiss'] == 'yes'}
                  <a href="#" class="notification-close" onClick="(function(){
                     
                    var xmlHttp = new XMLHttpRequest();
                    xmlHttp.open( &quot;GET&quot;, location.protocol + '//' + location.host + location.pathname + 'clean_room/notification_backend/?notification_dismiss={$notification['id']}', false );
                    xmlHttp.send( null );
                    $('.notification-{$notification['id']}').toggle();
                    return false;
                    })();return false;">✖</a>
                {/if}
                    
                {if $notification['buttons'] != 'none' && !is_array($notification['buttons'][0])}
                  <div class="notification-pad">
                    <a class="notification-a-button" {if $notification['buttons'][2] == 'yes'}
                                onclick="$.confirm({ theme: 'light', type: 'blue', boxWidth: '75%', useBootstrap: false,
                                  title: '{$notification['buttons'][1]}',
                                  content: 'Are you sure you would like to choose this.',
                                  buttons: { yes: function () {
                                          location.href = '{$notification['buttons'][0]}';
                                      }, no: function () { return true; }
                                  }}); "
                              {else}
                                 href='{$notification['buttons'][0]}'
                              {/if}>
                      <pre class="notification-a-pre">  {$notification['buttons'][1]}  </pre>
                    </a>
                  </div>
                {else if $notification['buttons'] != 'none'}
                  <div class="notification-pad">
                    <a class="notification-a-button" {if $notification['buttons'][0][2] == 'yes'}
                                 onclick="$.confirm({ theme: 'light', type: 'blue', boxWidth: '75%', useBootstrap: false,
                                   title: '{$notification['buttons'][0][1]}',
                                   content: 'Are you sure you would like to choose this.',
                                   buttons: { yes: function () {
                                           location.href = '{$notification['buttons'][0][0]}';
                                       }, no: function () { return true; }
                                   }}); "
                               {else}
                                  href='{$notification['buttons'][0][0]}'
                               {/if}>
                      <pre class="notification-a-pre">  {$notification['buttons'][0][1]}  </pre>
                    </a>
                    
                    {foreach $notification['buttons'] as $button_key => $button}
                      {if $button_key != '0'}
                        &nbsp;&nbsp;&nbsp;
                        <a class="notification-a-button" {if $button[2] == 'yes'}
                                    onclick="$.confirm({ theme: 'light', type: 'blue', boxWidth: '75%', useBootstrap: false,
                                      title: '{$button[1]}',
                                      content: 'Are you sure you would like to choose this.',
                                      buttons: { yes: function () {
                                              location.href = '{$button[0]}';
                                          }, no: function () { return true; }
                                      }}); "
                                    {else}
                                       href='{$button[0]}'
                                    {/if}>
                          <pre class="notification-a-pre">  {$button[1]}  </pre>
                        </a>
                      {/if}
                    {/foreach}
                  </div>
                {/if}
                
                {if $notification['select'] != 'none'}
                  <div>
                    <form id="dropdown_{$notification['id']}" class="notification-pad" method="get">
                      {foreach $notification['select'][1] as $data_key => $data}
                        {if isset($notification['select'][1][$data_key + 1]) && $data_key % 2 == 0}
                          <input type="hidden" name="{$data}" value="{$notification['select'][1][$data_key + 1]}">
                        {/if}
                      {/foreach}
                      <select form="dropdown_{$notification['id']}" name="option" class="notification-select" style="max-width:467px;">
                        {foreach $notification['select'][0] as $option_key => $option}
                          {if $option_key % 2 == 0}
                            <option value="{$option}">{$notification['select'][0][$option_key+1]}</option>
                          {/if}
                        {/foreach}
                      </select>
                      <span class="notification-submit-span">
                        <a class="notification-a-button" {if $notification['select'][1][count($notification['select'][1]) - 1] == 'yes'}
                            onclick="$.confirm({ theme: 'light', type: 'blue', boxWidth: '75%', useBootstrap: false,
                              title: function() { return $('#dropdown_{$notification['id']}').find(':selected').text();},
                              content: 'Are you sure you would like to choose this.',
                              buttons: { yes: function () {
                                  $('#dropdown_{$notification['id']}').submit();
                                  }, no: function () { return true; }
                              }}); "
                            {/if}>
                          <pre class="notification-a-pre">  go  </pre>
                        </a>
                      </span>
                </form>
                  </div>
                {/if}
              </td>
            </tr>
          {/if}
        {/foreach}
      </table>
    {/if}
</div>

  
  <!-- light Main Menu -->           
  <table style="width:100%;border-spacing:0px;border-collapse: collapse;">
      <tr>
          {if isset($deviceType) && $deviceType == "phone"}
              <td style="width:12%;"><a id="h_character"><img style="width:100%;" src="./files/layout_default/images/button_character.png"></a></td>
              <td style="width:12%;"><a id="h_communication"><img style="width:100%;" src="./files/layout_default/images/button_communication.png"></a></td>
              <td style="width:12%;"><a id="h_village"><img style="width:100%;" src="./files/layout_default/images/button_village.png"></a></td>
              <td style="width:12%;"><a id="h_training"><img style="width:100%;" src="./files/layout_default/images/button_train.png"></a></td>
              <td style="width:12%;"><a id="h_map"><img style="width:100%;" src="./files/layout_default/images/button_map.png"></a></td>
              <td style="width:12%;"><a id="h_combat"><img style="width:100%;" src="./files/layout_default/images/button_combat.png"></a></td>
              <td style="width:12%;"><a id="h_missions"><img style="width:100%;" src="./files/layout_default/images/button_missions.png"></a></td>
              <td style="width:12%;"><a id="h_general"><img style="width:100%;" src="./files/layout_default/images/button_support.png"></a></td>
         {else}
              <td style="width:12%;"><a id="h_character"><img style="width:33px;" src="./files/layout_default/images/button_character.png"></a></td>
              <td style="width:12%;"><a id="h_communication"><img style="width:33px;" src="./files/layout_default/images/button_communication.png"></a></td>
              <td style="width:12%;"><a id="h_village"><img style="width:33px;" src="./files/layout_default/images/button_village.png"></a></td>
              <td style="width:12%;"><a id="h_training"><img style="width:33px;" src="/files/layout_default/images/button_train.png"></a></td>
              <td style="width:12%;"><a id="h_map"><img style="width:33px;" src="./files/layout_default/images/button_map.png"></a></td>
              <td style="width:12%;"><a id="h_combat"><img style="width:33px;" src="./files/layout_default/images/button_combat.png"></a></td>
              <td style="width:12%;"><a id="h_missions"><img style="width:33px;" src="./files/layout_default/images/button_missions.png"></a></td>
              <td style="width:12%;"><a id="h_general"><img style="width:33px;" src="./files/layout_default/images/button_support.png"></a></td>
          {/if}
          </tr>
      <tr>
          <td colspan="8" style="padding:0px;">
              <div class="jsHide" id="div_character" style="text-align:left;">
                  <span class="menuSpan"><b>Character:</b></span>
                  {if isset($menuArray["character"][0]) }
                      {foreach name=characterMenuList from=$menuArray["character"] item=item}
                          <span class="menuSpan" ><a href="{$item.link}">{$item.name}</a> {if !$smarty.foreach.characterMenuList.last} - {/if}</span> 
                      {/foreach}
                  {/if}  
              </div>
              <div class="jsHide" id="div_communication" style="text-align:left;">
                  <span class="menuSpan"><b>Communication:</b></span>
                  {if isset($menuArray["communication"][0]) }
                      {foreach name=commMenuList from=$menuArray["communication"] item=item}
                          <span class="menuSpan"><a href="{$item.link}">{$item.name}</a> {if !$smarty.foreach.commMenuList.last} - {/if}</span> 
                      {/foreach}
                  {/if}   
              </div>
              <div class="jsHide" id="div_village" style="text-align:left;">
                  <span class="menuSpan"><b>Village:</b></span>
                  {if isset($menuArray["village"][0]) }
                      {foreach name=vilMenuList from=$menuArray["village"] item=item}
                          <span class="menuSpan"><a href="{$item.link}">{$item.name}</a> {if !$smarty.foreach.vilMenuList.last} - {/if}</span> 
                      {/foreach}
                  {/if}
              </div>
              <div class="jsHide" id="div_training" style="text-align:left;">
                  <span class="menuSpan"><b>Training & Missions: </b></span>
                   {if isset($menuArray["training"][0]) }
                      {foreach name=trainMenuList from=$menuArray["training"] item=item}
                          <span class="menuSpan"><a href="{$item.link}">{$item.name}</a> - </span>
                      {/foreach}
                  {/if} 
                  {if isset($menuArray["missions"][0]) }
                      {foreach name=missionMenuList from=$menuArray["missions"] item=item}
                          <span class="menuSpan"><a href="{$item.link}">{$item.name}</a> {if !$smarty.foreach.missionMenuList.last} - {/if}</span>
                      {/foreach}
                  {/if} 
              </div>
              <div class="jsHide" id="div_map" style="text-align:left;">
                  {if isset($menuArray["map"][0]) }
                      <span class="menuSpan"><b>Map:</b></span>
                      {foreach name=mapMenuList from=$menuArray["map"] item=item}
                           <span class="menuSpan"><a href="{$item.link}">{$item.name}</a> {if !$smarty.foreach.mapMenuList.last} - {/if}</span>
                      {/foreach}
                  {/if}   
              </div>
              <div class="jsHide" id="div_combat" style="text-align:left;">
                  <span class="menuSpan"><b>Combat:</b></span>
                  {if isset($menuArray["combat"][0]) }
                      {foreach name=combatMenuList from=$menuArray["combat"] item=item}
                          <span class="menuSpan"><a href="{$item.link}">{$item.name}</a> {if !$smarty.foreach.combatMenuList.last} - {/if}</span>
                      {/foreach}
                  {/if}    
              </div>
              <div class="jsHide" id="div_missions" style="text-align:left;">
                  <span class="menuSpan"><b>Support TNR:</b></span>
                  {if isset($menuArray["support"][0]) }
                      {foreach name=supportMenuList from=$menuArray["support"] item=item}
                          <span class="menuSpan"><a href="{$item.link}">{$item.name}</a> {if !$smarty.foreach.supportMenuList.last} - {/if} </span>
                      {/foreach}
                  {/if}
              </div>
              <div class="jsHide" id="div_general" style="text-align:left;">
                  <span class="menuSpan"><b>General:</b></span>
                  {if isset($menuArray["general"][0]) }
                      {foreach $menuArray["general"] as $item}
                          <span class="menuSpan"><a href="{$item.link}">{$item.name}</a> - </span>
                      {/foreach}
                  {/if}
                  <span class="menuSpan"><a href="?id=1&amp;act=logout">Log out</a></span>
              </div>
          </td>
      </tr>
  </table>
  
</div>