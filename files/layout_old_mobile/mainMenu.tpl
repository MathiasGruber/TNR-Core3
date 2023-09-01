<div data-role="panel" data-ajax="false"  id="gameMenuPanel" data-position="right" data-display="overlay">
    <ul data-role="collapsibleset" data-inset="false">
      
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
    <div id="notifications">
      {if isset($notifications) && count($notifications) != 0}
        <div data-role="collapsible"  data-collapsed-icon="alert" and data-expanded-icon="arrow-d">
          <h2>Notifications <span class="ui-li-count">{count($notifications)}</span></h2>
          <ul data-role="listview">
            {foreach $notifications as $notification}
                
              <li style="padding:0 0 0 0;">
                <table style="height:{if $notification['buttons'] != 'none' }84{else if $notification['select'] != 'none'}90{else}42{/if}px;display:block;">
                  <tbody style="height:{if $notification['buttons'] != 'none' }84{else if $notification['select'] != 'none'}90{else}42{/if}px;display:block;">
                    <tr style="height:42px;display:block;">
                      <td style="background-color: #f6f6f6;padding:11px 0 11px 0;width:100%">
                        {if !is_array($notification['text'])}
                          {$notification['text']}
                        {else}
                          <a  {if $notification['text'][2] == 'yes'}
                                onclick="$.confirm({ theme: 'light', type: 'blue', boxWidth: '75%', useBootstrap: false,
                                  title: '{$notification['text'][1]}',
                                  content: 'Are you sure you would like to go?',
                                  buttons: { yes: function () {
                                          location.href = '{$notification['text'][0]}';
                                      }, no: function () { return true; }
                                  }}); "
                              {else}
                                 href='{$notification['text'][0]}'
                              {/if} style="margin:0 0 0 0;">{$notification['text'][1]}</a>
                        {/if}
                      </td>

                      {if $notification['dismiss'] == 'yes'}
                        <td style="background-color: #f6f6f6;border-left: solid 1px #ddd;vertical-align:middle;padding:0 17px 0 16px;">
                          <a href="#" class="notification-close" onClick="(function(){
                               
                              var xmlHttp = new XMLHttpRequest();
                              xmlHttp.open( &quot;GET&quot;, location.protocol + '//' + location.host + location.pathname + 'clean_room/notification_backend/?notification_dismiss={$notification['id']}', false );
                              xmlHttp.send( null );
                              $('.notification-{$notification['id']}').toggle();
                              return false;
                              })();return false;">✖</a>
                        </td>
                      {/if}

                    </tr>
                    
                    {if $notification['buttons'] != 'none' && !is_array($notification['buttons'][0])}
                      <tr style="height:42px;display:block;">
                        <td style="background-color: #fafafa;border-top:1px dashed #ddd;"></td>
                        <td style="background-color: #fafafa;padding:10px 0 11px 0;width:100%;border-top:1px dashed #ddd;">
                          <a  {if $notification['buttons'][2] == 'yes'}
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
                            {$notification['buttons'][1]}
                          </a>
                        </td>
                      </tr>
                    {else if $notification['buttons'] != 'none'}
                      <tr style="height:42px;display:block;">
                        <td style="background-color: #fafafa;border-top:1px dashed #ddd;"></td>
                        <td style="background-color: #fafafa;padding:10px 0 11px 0;width:100%;border-top:1px dashed #ddd;">
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
                            {$notification['buttons'][0][1]}
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
                                {$button[1]}
                              </a>
                            {/if}
                          {/foreach}
                        </td>
                      </tr>
                    {/if}
                    
                    {if $notification['select'] != 'none'}
                      <tr style="height:42px;display:block;">
                        <td style="background-color: #fafafa;border-top:1px dashed #ddd;padding:0 0 0 0;width:100%">
                          <form id="dropdown_{$notification['id']}" method="get" style="position:relative;top:-7px;max-width:272px;">
                            {foreach $notification['select'][1] as $data_key => $data}
                              {if isset($notification['select'][1][$data_key + 1]) && $data_key % 2 == 0}
                                <input type="hidden" name="{$data}" value="{$notification['select'][1][$data_key + 1]}">
                              {/if}
                            {/foreach}
                            <select form="dropdown_{$notification['id']}" name="option">
                              {foreach $notification['select'][0] as $option_key => $option}
                                {if $option_key % 2 == 0}
                                  <option value="{$option}">{$notification['select'][0][$option_key+1]}</option>
                                {/if}
                              {/foreach}
                            </select>
				                  </form>
                        </td>
                        <td style="background-color: #fafafa;border-top:1px dashed #ddd;;vertical-align:middle;padding:0 14px 14px 14px;">
                            <a class="notification-a-button notification-submit notification-close" {if $notification['select'][1][count($notification['select'][1]) - 1] == 'yes'}
                                onclick="$.confirm({ theme: 'light', type: 'blue', boxWidth: '75%', useBootstrap: false,
                                  title: function() { return $('#dropdown_{$notification['id']}').find(':selected').text();},
                                  content: 'Are you sure you would like to choose this.',
                                  buttons: { yes: function () {
                                      $('#dropdown_{$notification['id']}').submit();
                                      }, no: function () { return true; }
                                  }}); "
                                {/if}>
                              go
                            </a>
                        </td>
                      </tr>
                    {/if}
                    
                  </tbody>
                </table>
              </li>
                
            {/foreach}
          </ul>
        </div>
      {/if}
    </div>
     
        <div data-role="collapsible" data-collapsed-icon="user" and data-expanded-icon="arrow-d">
            <h2>Character</h2>
            <ul data-role="listview">
                {if isset($menuArray["character"][0]) }
                    {foreach name=characterMenuList from=$menuArray["character"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
            </ul>
        </div>
        <div data-role="collapsible" data-collapsed-icon="comment" and data-expanded-icon="arrow-d">
            <h2>Communication</h2>
            <ul data-role="listview">
                {if isset($menuArray["communication"][0]) }
                    {foreach name=commMenuList from=$menuArray["communication"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
            </ul>
        </div>
        <div data-role="collapsible" data-collapsed-icon="home" and data-expanded-icon="arrow-d">
            <h2>Village</h2>
            <ul data-role="listview">
                {if isset($menuArray["village"][0]) }
                    {foreach name=vilMenuList from=$menuArray["village"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
            </ul>
        </div>
        <div data-role="collapsible" data-collapsed-icon="clock" and data-expanded-icon="arrow-d">
            <h2>Training & Missions</h2>
            <ul data-role="listview">
                {if isset($menuArray["training"][0]) }
                    {foreach name=trainMenuList from=$menuArray["training"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
                {if isset($menuArray["missions"][0]) }
                    {foreach name=missionMenuList from=$menuArray["missions"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
            </ul>
        </div>
        <div data-role="collapsible" data-collapsed-icon="grid" and data-expanded-icon="arrow-d">
            <h2>Map</h2>
            <ul data-role="listview">
                {if isset($menuArray["map"][0]) }
                    {foreach name=mapMenuList from=$menuArray["map"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
            </ul>
        </div>
        <div data-role="collapsible" data-collapsed-icon="recycle" and data-expanded-icon="arrow-d">
            <h2>Combat</h2>
            <ul data-role="listview">
                {if isset($menuArray["combat"][0]) }
                    {foreach name=combatMenuList from=$menuArray["combat"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
            </ul>
        </div>
        <div data-role="collapsible" data-collapsed-icon="star" and data-expanded-icon="arrow-d">
            <h2>Support TNR</h2>
            <ul data-role="listview">
                {if isset($menuArray["support"][0]) }
                    {foreach name=supportMenuList from=$menuArray["support"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
            </ul>
        </div>
        <div data-role="collapsible" data-collapsed-icon="gear" and data-expanded-icon="arrow-d">
            <h2>General</h2>
            <ul data-role="listview">
                {if isset($menuArray["general"][0]) }
                    {foreach name=supportMenuList from=$menuArray["general"] item=item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                {/if} 
                <li><a href="?id=1&amp;act=logout">Log out</a></li>
            </ul>
        </div>
    </ul>
</div>