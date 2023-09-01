<script type="text/javascript">
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

<div id="notifications">
    {if isset($notifications) && count($notifications) != 0}
      <ul class="ac-menu">
        <li id="epic">
          <a href='?id={$smarty.get.id}&show-all-notifications=yes'>Notifications</a>
          <ul class="sub-menu" style="position:relative !important;padding:5px 4px 0px 4px;">
            
            {foreach $notifications as $notification_key => $notification}
              {if ($notification['duration'] !== 'dismissed' && $notification['duration'] !== 'done') || $smarty.get.show-all-notifications == 'yes'}
                <li class="notification-li">
                  {if $notification['dismiss'] == 'yes'}
                    <a href="#" class="notification-close" onClick="(function(){
                       
                      var xmlHttp = new XMLHttpRequest();
                      xmlHttp.open( &quot;GET&quot;, location.protocol + '//' + location.host + location.pathname + 'clean_room/notification_backend/?notification_dismiss={$notification['id']}', false );
                      xmlHttp.send( null );
                      $('.notification-{$notification['id']}').toggle();
                      return false;
                      })();return false;">✖</a>
                  {/if}
                  
                  {if !is_array($notification['text'])}
                    {$notification['text']}
                  {else}
                    <a class="notification-text-a nohover" {if $notification['text'][2] == 'yes'}
                              onclick="$.confirm({ theme: 'dark', type: 'blue', boxWidth: '75%', useBootstrap: false,
                                title: '{$notification['text'][1]}',
                                content: 'Are you sure you would like to go?',
                                buttons: { yes: function () {
                                        location.href = '{$notification['text'][0]}';
                                    }, no: function () { return true; }
                                }}); "
                            {else}
                               href='{$notification['text'][0]}'
                            {/if} style="position:static;border-bottom:none;font:bold 12px/100% arial, sans-serif;min-height:0px;min-width:0px;padding:0 0 0 0;display: inline;box-shadow:none;-webkit-box-shadow:none;">{$notification['text'][1]}</a>
                  {/if}
                  
                  <br/>
                      
                  {if $notification['buttons'] != 'none' && !is_array($notification['buttons'][0])}
                    <div class="notification-pad">
                      <a class="notification-a-button nohover" {if $notification['buttons'][2] == 'yes'}
                                onclick="$.confirm({ theme: 'dark', type: 'blue', boxWidth: '75%', useBootstrap: false,
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
                      <a class="notification-a-button nohover" {if $notification['buttons'][0][2] == 'yes'}
                                 onclick="$.confirm({ theme: 'dark', type: 'blue', boxWidth: '75%', useBootstrap: false,
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
                          <a class="notification-a-button nohover" {if $button[2] == 'yes'}
                                    onclick="$.confirm({ theme: 'dark', type: 'blue', boxWidth: '75%', useBootstrap: false,
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
                    <div class="notification-pad">
                      <form id="dropdown_{$notification['id']}" method="get">
                        {foreach $notification['select'][1] as $data_key => $data}
                          {if isset($notification['select'][1][$data_key + 1]) && $data_key % 2 == 0}
                            <input type="hidden" name="{$data}" value="{$notification['select'][1][$data_key + 1]}">
                          {/if}
                        {/foreach}
                        <select form="dropdown_{$notification['id']}" name="option" class="notification-select" style="max-width:165px;">
                          {foreach $notification['select'][0] as $option_key => $option}
                            {if $option_key % 2 == 0}
                              <option value="{$option}">{$notification['select'][0][$option_key+1]}</option>
                            {/if}
                          {/foreach}
                        </select>
                        <span class="notification-submit-span">
                          <a class="notification-a-button" {if $notification['select'][1][count($notification['select'][1]) - 1] == 'yes'}
                              onclick="$.confirm({ theme: 'dark', type: 'blue', boxWidth: '75%', useBootstrap: false,
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
                </li>
              {/if}
            {/foreach}  
          
            
            
            
          </ul>
        </li>
      </ul>
      <br>
    {/if}
</div>

<div id="wrapper">
    <ul class="ac-menu">
        {if isset($menuArray["character"][0]) }
            <li id="one">
                <a id="h_character">Character</a>
                <ul class="sub-menu jsHide" id="div_character">
                    {foreach $menuArray["character"] as $item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if isset($menuArray["map"][0]) }
            <li id="ninth">
                <a id="h_map">Travel Map</a>
                <ul class="sub-menu jsHide" id="div_map">
                    {foreach $menuArray["map"] as $item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if isset($menuArray["combat"][0]) }
            <li id="two">
                <a id="h_combat">Combat</a>
                <ul class="sub-menu jsHide" id="div_combat">
                    {foreach $menuArray["combat"] as $item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if isset($menuArray["communication"][0]) }
            <li id="three">
                <a id="h_communication">Communication</a>
                <ul class="sub-menu jsHide" id="div_communication">
                    {foreach $menuArray["communication"] as $item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if isset($menuArray["village"][0]) }
            <li id="four">
                <a id="h_village">Village</a>
                <ul class="sub-menu jsHide" id="div_village">
                    {foreach $menuArray["village"] as $item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if isset($menuArray["training"][0]) }
            <li id="five">
                <a id="h_training">Training</a>
                <ul class="sub-menu jsHide" id="div_training">
                    {foreach $menuArray["training"] as $item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if isset($menuArray["missions"][0]) }
            <li id="six">
                <a id="h_missions">Missions</a>
                <ul class="sub-menu jsHide" id="div_missions">
                    {foreach $menuArray["missions"] as $item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if isset($menuArray["support"][0]) }
            <li id="seven">
                <a id="h_support">Support</a>
                <ul class="sub-menu jsHide" id="div_support">
                    {foreach $menuArray["support"] as $item}
                        <li><a href="{$item.link}">{$item.name}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        <li id="eight">
            <a id="h_general">General</a>
            <ul class="sub-menu jsHide" id="div_general">
                {foreach $menuArray["general"] as $item}
                    <li><a href="{$item.link}">{$item.name}</a></li>
                {/foreach}
                <li><a href="?id=1&amp;act=logout">Log out</a></li>
                <li><a style="font-size:10px;padding-top:5px;font-weight:bold;line-height:normal;">Automatic Logout: <br>{$logoutTimer}</a></li>
            </ul>
        </li>
        
    </ul>
</div>
            


<br>

{if empty($fbData)}    
    <br>
    <a href="?id=82"><img width="150px" src="./images/fbconnect.png" alt="Facebook Connect" title="Facebook Connect" /></a><br>
    <div style="width:100px;align:center;">
        <center>
            <div class="fb-like" layout="button_count" data-href='https://www.facebook.com/TheNinjaRPG' data-send='false' data-width='100' data-show-faces='false'></div>
        </center>
    </div>
{/if}

