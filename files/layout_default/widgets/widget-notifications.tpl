{if count($notifications) > 0}    
    <div id="widget-notifications" class="widget-box lazy"> 
        <div id="widget-notifications-title" class="widget-title lazy {if $_COOKIE['widget-notifications-closed'] == true}closed{/if}">
            <span id="widget-notifications-title-span-title" class="label">Notifications</span><span id="widget-notifications-title-span-title-counter">({count($notifications)})</span>
        </div>
        <div id="widget-notifications-content" class="widget-content lazy" {if $_COOKIE['widget-notifications-closed'] == true}style="display:none;"{/if}>
          {foreach $notifications as $notification_key => $notification}
            {if ($notification['duration'] !== 'dismissed' && $notification['duration'] !== 'done') || $smarty.get.show-all-notifications == 'yes'}
              <div class="widget-notifications-notification notification-{$notification['id']} notification-{$notification_key % 2} lazy">
                {if $notification['dismiss'] == 'yes'}
                  <a href="#" class="notification-close lazy" onClick="(function(){
                     
                    var xmlHttp = new XMLHttpRequest();
                    xmlHttp.open( &quot;GET&quot;, location.protocol + '//' + location.host + location.pathname + 'clean_room/notification_backend/?notification_dismiss={$notification['id']}', false );
                    xmlHttp.send( null );
                    $('.notification-{$notification['id']}').toggle();
                    return false;
                    })();return false;">✖</a>
                {/if}
                
                {if !is_array($notification['text'])}
                  <div class="notification-text lazy">
                    {$notification['text']}
                  </div>
                {else}
                  <a class="notification-text-link lazy"
                    {if $notification['text'][2] == 'yes'}
                      onclick="$.confirm({ theme: 'light', type: 'blue', boxWidth: '75%', useBootstrap: false,
                        title: '{$notification['text'][1]}',
                        content: 'Are you sure you would like to go?',
                        buttons: { yes: function () {
                                location.href = '{$notification['text'][0]}';
                            }, no: function () { return true; }
                        }}); "
                    {else}
                       href='{$notification['text'][0]}'
                    {/if}>
                      
                      
                      {$notification['text'][1]}</a>
                {/if}
                
                <br/>
                    
                {if $notification['buttons'] != 'none' && !is_array($notification['buttons'][0])}
                  <div class="notification-buttonbox lazy">
                    <a class="notification-buttonbox-button lazy"
                      {if $notification['buttons'][2] == 'yes'}
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
                  </div>
                {else if $notification['buttons'] != 'none'}
                  <div class="notification-buttonbox lazy">
                    <a class="notification-buttonbox-button lazy"
                      {if $notification['buttons'][0][2] == 'yes'}
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
                        <a class="notification-buttonbox-button lazy"
                          {if $button[2] == 'yes'}
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
                  </div>
                {/if}
                
                {if $notification['select'] != 'none'}
                  <form id="dropdown_{$notification['id']}" class="notification-form lazy" method="get">
                    {foreach $notification['select'][1] as $data_key => $data}
                      {if isset($notification['select'][1][$data_key + 1]) && $data_key % 2 == 0}
                        <input type="hidden" name="{$data}" value="{$notification['select'][1][$data_key + 1]}">
                      {/if}
                    {/foreach}
                    <select form="dropdown_{$notification['id']}" id="dropdown_{$notification['id']}" name="option" class="notification-form-dropdown lazy" style="max-width:165px;">
                      {foreach $notification['select'][0] as $option_key => $option}
                        {if $option_key % 2 == 0}
                          <option value="{$option}">{$notification['select'][0][$option_key+1]}</option>
                        {/if}
                      {/foreach}
                    </select>
                    <a class="notification-a-button notification-form-submit lazy" {if $notification['select'][1][count($notification['select'][1]) - 1] == 'yes'}
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
                  </form>
                {/if}
              </div>
            {/if}
          {/foreach}
        </div>
    </div>
{/if}