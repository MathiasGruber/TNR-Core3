<script type="text/javascript"  id="menuScript">
    {include file="./Scripts/menuScript.js"}
</script>

{function generate_simple_req_display}
    {foreach $data as $requirement}
        {if $requirement['status'] == 0}
            <font color="red">&#10007</font>
        {else}
            <font color="green">&#10003</font>
        {/if}
         - {if $requirement['write_up'] != ''}{$requirement['write_up']}{elseif $requirement['status'] != 0}None{else}Un-Known{/if} 
        <br/>
    {/foreach}
{/function}

{function generate_complex_req_display}

    {foreach $requirement_container as $requirement_key => $requirement}
        {if isset($requirement['joined']) && substr( $requirement['joined'], 0, 4 ) === "join"}
            {$requirement_container[$requirement['joined']][$requirement_key] = $requirement}
            {$requirement_container[$requirement_key] = null}
        {/if}
    {/foreach}

    {foreach $requirement_container as $requirement_key => $requirement_data}
        {if is_array($requirement_data) && substr( $requirement_key, 0, 4 ) === "join"}

            {$status = true}
            {$hover = []}
            {$write_ups = []}
            {foreach $requirement_data as $joined_requirement_key => $joined_requirement_data }
                {if $tracked_quest->data[$requirement_category|cat:'_check_list'][$joined_requirement_key] === false}
                    {$status = false}
                    {if $joined_requirement_data['write_up'] != ''}
                        {$hover[] = $joined_requirement_key|cat:' is not satisfied'}
                    {/if}
                {else if $joined_requirement_data['write_up'] != ''}
                    {$hover[] = $joined_requirement_key|cat:' is satisfied'}
                {/if}

                {if isset($tracked_quest->data[$requirement_category|cat:'_gains'][$joined_requirement_key]['gains']) && $joined_requirement_data['write_up'] != ''}
                    {$joined_requirement_data['write_up'] = str_replace('[]','<font color="blue">('|cat:$tracked_quest->data[$requirement_category|cat:'_gains'][$joined_requirement_key]['gains']|cat:')</font>',$joined_requirement_data['write_up'])}

                {elseif isset($tracked_quest->data[$requirement_category|cat:'_losses'][$joined_requirement_key]['losses']) && $joined_requirement_data['write_up'] != ''}
                    {$joined_requirement_data['write_up'] = str_replace('[]','<font color="blue">('|cat:$tracked_quest->data[$requirement_category|cat:'_gains'][$joined_requirement_key]['losses']|cat:')</font>',$joined_requirement_data['write_up'])}                        

                {elseif is_array($tracked_quest->data[$requirement_category|cat:'_gains'][$joined_requirement_key])}
                    {foreach $tracked_quest->data[$requirement_category|cat:'_gains'][$joined_requirement_key] as $context => $data_temp}
                        {if isset($data_temp['gains']) && $joined_requirement_data['write_up'] != ''}
                            {$joined_requirement_data['write_up'] = str_replace('['|cat:$context|cat:']','<font color="blue">('|cat:$data_temp['gains']|cat:')</font>',$joined_requirement_data['write_up'])}
                        {/if}
                    {/foreach}
                {elseif is_array($tracked_quest->data[$requirement_category|cat:'_losses'][$joined_requirement_key])}
                    {foreach $tracked_quest->data[$requirement_category|cat:'_losses'][$joined_requirement_key] as $context => $data_temp}
                        {if isset($data_temp['losses']) && $joined_requirement_data['write_up'] != ''}
                            {$joined_requirement_data['write_up'] = str_replace('['|cat:$context|cat:']','<font color="blue">('|cat:$data_temp['losses']|cat:')</font>',$joined_requirement_data['write_up'])}
                        {/if}
                    {/foreach}
                {/if}

                {if $joined_requirement_data['write_up'] != ''}
                    {$write_ups[] = $joined_requirement_data['write_up']}
                {/if}
            {/foreach}

            {if $status === false}
                <font color="red" class="shadow-outline" title="{implode(' and ', $hover)}">&#10007</font>
                {assign var="color" value="red"} - {implode(' and ', $write_ups)} <br>
            {else}
                <font color="green" class="highlight-outline bold" title="{implode(' and ', $hover)}">&#10003</font>
                {assign var="color" value="green"} - {implode(' and ', $write_ups)} <br>
            {/if}

        {else if $requirement_data != null}
            {if $tracked_quest->data[$requirement_category|cat:'_check_list'][$requirement_key] === false }
                <font color="red" class="shadow-outline">&#10007</font>
                {assign var="color" value="red"}
            {else}
                <font color="green" class="highlight-outline bold">&#10003</font>
                {assign var="color" value="green"}
            {/if}

            {if isset($tracked_quest->data[$requirement_category|cat:'_gains'][$requirement_key]['gains'])}
                {$requirement_data['write_up'] = str_replace('[]','<font color="blue">('|cat:$tracked_quest->data[$requirement_category|cat:'_gains'][$requirement_key]['gains']|cat:')</font>',$requirement_data['write_up'])}

            {elseif isset($tracked_quest->data[$requirement_category|cat:'_losses'][$requirement_key]['losses'])}
                {$requirement_data['write_up'] = str_replace('[]','<font color="blue">('|cat:$tracked_quest->data[$requirement_category|cat:'_gains'][$requirement_key]['losses']|cat:')</font>',$requirement_data['write_up'])}                        

            {elseif is_array($tracked_quest->data[$requirement_category|cat:'_gains'][$requirement_key])}
                {foreach $tracked_quest->data[$requirement_category|cat:'_gains'][$requirement_key] as $context => $data_temp}
                    {if isset($data_temp['gains'])}
                        {$requirement_data['write_up'] = str_replace('['|cat:$context|cat:']','<font color="blue">('|cat:$data_temp['gains']|cat:')</font>',$requirement_data['write_up'])}
                    {/if}
                {/foreach}
            {elseif is_array($tracked_quest->data[$requirement_category|cat:'_losses'][$requirement_key])}
                {foreach $tracked_quest->data[$requirement_category|cat:'_losses'][$requirement_key] as $context => $data_temp}
                    {if isset($data_temp['losses'])}
                        {$requirement_data['write_up'] = str_replace('['|cat:$context|cat:']','<font color="blue">('|cat:$data_temp['losses']|cat:')</font>',$requirement_data['write_up'])}
                    {/if}
                {/foreach}
            {/if}

            - {$requirement_data['write_up']}

            <br>
        {/if}
    {/foreach}
{/function}

{function generate_gift_display}
    {foreach $data as $type => $stuff}
        {if substr( $type, 0, 4 ) === "text"}
            {foreach $stuff as $text}
                {$text}
            {/foreach}
            <br/>
        {elseif substr( $type, 0, 7 ) === "bullets"}
            <ul style="list-style-type:disc;padding-left:30px;">
                {foreach $stuff as $bullets}
                    <li>{$bullets}</li>
                {/foreach}
            </ul>
            <br/>
        {elseif substr( $type, 0, 7 ) === "numbers"}
            <ol style="list-style-type:decimal;padding-left:30px;">
                {foreach $stuff as $numbers}
                    <li>{$numbers}</li>
                {/foreach}
            </ol>
            <br/>
        {else}
            <ol style="list-style-type:{strtok($type,'_')};padding-left:30px;">
                {foreach $stuff as $things}
                    <li>{$things}</li>
                {/foreach}
            </ol>
            <br/>
        {/if}
    {/foreach}
{/function}

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
      <div class="menuContainer">
        <div class="menuBg1 menuBg1-left">
          <div class="menuPerforationVertical menuPerforationVerticalLeft">
            <div class="perforationMenuHorizontal perforationMenuHorizontalTop">
            </div>
            <div class="menuBg2 menuBg2-right">
              
              <table cellpadding="0" cellspacing="0" width="95%" align="center" class="notification-table">
    
                {foreach $notifications as $notification_key => $notification}
                  {if ($notification['duration'] !== 'dismissed' && $notification['duration'] !== 'done') || $smarty.get.show-all-notifications == 'yes'}
                    <tr class="notification-tr-{$notification_key % 2} notification-{$notification['id']}">
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
                        
                        {if !is_array($notification['text'])}
                          {$notification['text']}
                        {else}
                          <a
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
                          <div class="notification-pad">
                            <a class="notification-a-button"
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
                            
                              <pre class="notification-a-pre">  {$notification['buttons'][1]}  </pre>
                            </a>
                          </div>
                        {else if $notification['buttons'] != 'none'}
                          <div class="notification-pad">
                            <a class="notification-a-button"
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
                                
                              <pre class="notification-a-pre">  {$notification['buttons'][0][1]}  </pre>
                            </a>
                            
                            {foreach $notification['buttons'] as $button_key => $button}
                              {if $button_key != '0'}
                                &nbsp;&nbsp;&nbsp;
                                <a class="notification-a-button"
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
                              <select form="dropdown_{$notification['id']}" id="dropdown_{$notification['id']}" name="option" class="notification-select" style="max-width:165px;">
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
              
            </div>
            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom">
            </div>
          </div>
        </div>
        <div class="ribbonStats ribbonStats-right">
          <div class="ribbonText">
            <a href='?id={$smarty.get.id}&show-all-notifications=yes' style='font-family:sakura;color:#EEE;font-weight:500;'>notifications</a>
          </div>
        </div>
      </div>
    {/if}
</div>

<div id="quest_widget">
  {if $tracked_quest != '' && $quest_widget == 'yes'}
      <div class="menuContainer">
        <div class="menuBg1 menuBg1-left">
          <div class="menuPerforationVertical menuPerforationVerticalLeft">
            <div class="perforationMenuHorizontal perforationMenuHorizontalTop">
            </div>
            <div class="menuBg2 menuBg2-right">
              
              <table cellpadding="0" cellspacing="0" width="95%" align="center" class="notification-table">
                <div class="wrapper_outer" style="padding:0px;width:168px;">
                  <div class="wrapper_inner">
                    <ul style="color:#3f3e3c; font-family:segoe ui; font-size:12px;">
                        <li class="listHeader" title="{$tracked_quest->description}" style="padding:3px;">
                            <a href="?id=120&details={$tracked_quest->qid}" title="QuestDetails" style="color:white;">{$tracked_quest->name}</a>
                        </li>

                        <!--starting if known and completion requirements exist, option to hide?-->
                        {if $tracked_quest->status == 0 && $tracked_quest->failed && is_array($tracked_quest->starting_requirements_post_failure) && count($tracked_quest->starting_requirements_post_failure) > 0 && !$tracked_quest->hide_starting_requirements_post_failure}

                            <li class="notification-tr-0" style="padding:3px;">    
                               Starting Requirements (R): 
                            </li>

                            <li class="notification-tr-0" style="padding:3px;">
                                {generate_simple_req_display data=$tracked_quest->starting_requirements_post_failure}
                            </li>

                        {elseif $tracked_quest->status == 0 && is_array($tracked_quest->starting_requirements) && count($tracked_quest->starting_requirements) > 0  && !$tracked_quest->hide_starting_requirements}

                            <li class="notification-tr-0" style="padding:3px;">    
                              Starting Requirements: 
                            </li>

                            <li class="notification-tr-0" style="padding:3px;">
                                {generate_simple_req_display data=$tracked_quest->starting_requirements}
                            </li>

                        {/if}

                        <!--completion if active and completion requirements exist, option to hide?-->
                        {if $tracked_quest->status == 1 && $tracked_quest->failed && is_array($tracked_quest->completion_requirements_post_failure) && count($tracked_quest->completion_requirements_post_failure) > 0  && !$tracked_quest->hide_completion_requirements_post_failure}

                            <li class="notification-tr-0" style="padding:3px;">    
                              Completion Requirements (R): 
                            </li>

                            <li class="notification-tr-0" style="padding:3px;">
                                {generate_complex_req_display requirement_container=$tracked_quest->completion_requirements_post_failure requirement_category='completion'}
                            </li>

                        {elseif $tracked_quest->status == 1 && is_array($tracked_quest->completion_requirements) && count($tracked_quest->completion_requirements) > 0 && !$tracked_quest->hide_completion_requirements}

                            <li class="notification-tr-0" style="padding:3px;">    
                              Completion Requirements: 
                            </li>

                            <li class="notification-tr-0" style="padding:3px;">
                                {generate_complex_req_display requirement_container=$tracked_quest->completion_requirements requirement_category='completion'}
                            </li>

                        {/if}

                        <!--failure if active and failure requirements exist, option to hide?-->
                        {if $tracked_quest->status == 1 && $tracked_quest->failed && is_array($tracked_quest->failure_requirements_post_failure) && count($tracked_quest->failure_requirements_post_failure) > 0 && !$tracked_quest->hide_failure_requirements_post_failure}

                            <li class="notification-tr-0" style="padding:3px;">    
                              Failure Requirements (R): 
                            </li>

                            <li class="notification-tr-0" style="padding:3px;">
                                {generate_complex_req_display requirement_container=$tracked_quest->failure_requirements_post_failure requirement_category='failure'}
                            </li>

                        {elseif $tracked_quest->status == 1 && is_array($tracked_quest->failure_requirements) && count($tracked_quest->failure_requirements) > 0 && !$tracked_quest->hide_failure_requirements}

                            <li class="notification-tr-0" style="padding:3px;">    
                              Failure Requirements: 
                            </li>

                            <li class="notification-tr-0" style="padding:3px;">
                                {generate_complex_req_display requirement_container=$tracked_quest->failure_requirements requirement_category='failure'}
                            </li>

                        {/if}

                        <!--turn in if completed-->
                        {if $tracked_quest->status == 2 && $tracked_quest->failed && is_array($tracked_quest->turn_in_requirements_post_failure) && count($tracked_quest->turn_in_requirements_post_failure) > 0  && !$tracked_quest->hide_turn_in_requirements_post_failure}

                            <li class="notification-tr-0" style="padding:3px;">    
                              Turn In Requirements (R): 
                            </li>

                            <li class="notification-tr-0" style="padding:3px;">
                                {generate_simple_req_display data=$tracked_quest->turn_in_requirements_post_failure}
                            </li>

                        {elseif $tracked_quest->status == 2 && is_array($tracked_quest->turn_in_requirements) && count($tracked_quest->turn_in_requirements) > 0 && !$tracked_quest->hide_turn_in_requirements}

                            <li class="notification-tr-0" style="padding:3px;">    
                              Turn In Requirements: 
                            </li>

                            <li class="notification-tr-0" style="padding:3px;">
                                {generate_simple_req_display data=$tracked_quest->turn_in_requirements}
                            </li>

                        {/if}

                        {if isset($tracked_quest->actions) && count($tracked_quest->actions) > 1}

                            <li style="padding-top:4px;">

                                <div style="display:inline-block;padding-left:8px;width:44%;text-align:left;">
                                    <input type="button" value="Untrack" style="white-space:normal;background:none!important;color:inherit;border:none;padding:0!important;font: inherit;font-weight:700;cursor: pointer;" onclick=
                                    "{
                                        $('#track_{$tracked_quest->qid}').prop('checked', false);
                                    	$('.track-check-box').prop('disabled', true);
                                        $(this).prop('disabled', true);
                                    	$.get
                                    	(
                                    		'?id=120&track={$tracked_quest->qid}', 
                                    		function( data )
                                    		{
                                    			$('.track-check-box').prop('disabled', false);
                                                $('#quest_widget').html( $(data).find('#quest_widget') );
                                    		}
                                    	);
                                    }">
                                </div>

                                <div style="display:inline-block;padding-right:8px;width:44%;text-align:right;">
                                    <select  onchange="window.location.href = this.options[this.selectedIndex].value;" style="border:none;background:none;font:inherit;color:inherit;font:inherit;font-weight:700;cursor:pointer;-webkit-appearance: none;-moz-appearance:none;text-indent:1px;text-overflow:'';">
                                        <option value=""         >Actions</option>
                                        {foreach $tracked_quest->actions as $action}
                                            <option value="{$action['link']}">{$action['text']}</option>
                                        {/foreach}
                                    </select>
                                </div>

                            </li>
                        

                        {elseif isset($tracked_quest->actions) && count($tracked_quest->actions) == 1}

                            <li style="padding-top:4px;">
                                <div style="display:inline-block;padding-left:8px;width:44%;text-align:left;">
                                    <input type="button" value="Untrack" style="white-space:normal;background:none!important;color:inherit;border:none;padding:0!important;font: inherit;font-weight:700;cursor: pointer;" onclick=
                                    "{
                                        $('#track_{$tracked_quest->qid}').prop('checked', false);
                                    	$('.track-check-box').prop('disabled', true);
                                        $(this).prop('disabled', true);
                                    	$.get
                                    	(
                                    		'?id=120&track={$tracked_quest->qid}', 
                                    		function( data )
                                    		{
                                    			$('.track-check-box').prop('disabled', false);
                                                $('#quest_widget').html( $(data).find('#quest_widget') );
                                    		}
                                    	);
                                    }">
                                </div>

                                <div style="display:inline-block;padding-right:8px;width:44%;text-align:right;">
                                    <a href="{$tracked_quest->actions[0]['link']}">{$tracked_quest->actions[0]['text']}</a>
                                </div>
                            </li>

                        {else}

                            <li style="padding-top:4px;">
                                <input type="button" value="Untrack" style="white-space:normal;background:none!important;color:inherit;border:none;padding:0!important;font: inherit;font-weight:700;cursor: pointer;" onclick=
                                    "{
                                        $('#track_{$tracked_quest->qid}').prop('checked', false);
                                    	$('.track-check-box').prop('disabled', true);
                                        $(this).prop('disabled', true);
                                    	$.get
                                    	(
                                    		'?id=120&track={$tracked_quest->qid}', 
                                    		function( data )
                                    		{
                                    			$('.track-check-box').prop('disabled', false);
                                                $('#quest_widget').html( $(data).find('#quest_widget') );
                                    		}
                                    	);
                                    }">
                            </li>

                        {/if}


                    </ul>
                  </div>
                </div>

              </table>
              
            </div>
            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom">
            </div>
          </div>
        </div>
        <div class="ribbonStats ribbonStats-right">
          <div class="ribbonText">
            <!--<a href='?id=120&details={$tracked_quest->qid}' style='font-family:sakura;color:#EEE;font-weight:500;'>{$tracked_quest->name}</a>-->
              <a href='?id=120' style='font-family:sakura;color:#EEE;font-weight:500;position:relative;top:-15px;left:-10px;' title="QuestJournal">Quest</a>
              <input type="image" id="questing_mode_button" border="0" alt="{$questing_mode}" src="./images/{$questing_mode}.png" width="31" height="32" title="In {$questing_mode} mode." style="position:relative;top:-6px;left:-15px;" onClick=
                            "{
                                $('#questing_mode_button').prop('disabled', true);
                            	$.get
                            	(
                            		'?id=120&questing_mode={$questing_mode}', 
                            		function( data )
                            		{
                            			$('#questing_mode_button').prop('disabled', false);
                                        $('#quest_widget').html( $(data).find('#quest_widget') );
                            		}
                            	);
                            }">
          </div>
        </div>
      </div>
    {elseif $quest_widget == 'yes'}
        <div class="menuContainer">
        <div class="menuBg1 menuBg1-left">
          <div class="menuPerforationVertical menuPerforationVerticalLeft">
            <div class="perforationMenuHorizontal perforationMenuHorizontalTop">
            </div>
            <div class="menuBg2 menuBg2-right">
              
              <table cellpadding="0" cellspacing="0" width="95%" align="center" class="notification-table">
                <div class="wrapper_outer" style="padding:0px;width:168px;">
                  <div class="wrapper_inner" style="padding-bottom:0px;">
                    <ul style="color:#3f3e3c; font-family:segoe ui; font-size:12px;">
                        <li class="listHeader" style="padding:3px;">
                            Trackable Quests
                        </li>
                    </ul>
                    <ul style="color:#3f3e3c; font-family:segoe ui; font-size:12px; max-height:200px; overflow-y:auto;">
                        {foreach $trackable_quests as $qid => $quest}
                            <li class="notification-tr-0" title="{$quest->description}">
                                <input type="button" value="{$quest->name}" style="white-space:normal;background:none!important;color:inherit;border:none;padding:0!important;font: inherit;font-weight:700;cursor: pointer;" onclick=
                                    "{
                                        $('#track_{$qid}').prop('checked', true);
                                    	$('.track-check-box').prop('disabled', true);
                                        $(this).prop('disabled', true);
                                    	$.get
                                    	(
                                    		'?id=120&track={$qid}', 
                                    		function( data )
                                    		{
                                    			$('.track-check-box').prop('disabled', false);
                                                $('#quest_widget').html( $(data).find('#quest_widget') );
                                    		}
                                    	);
                                    }">
                            </li>
                        {/foreach}

                    </ul>
                  </div>
                </div>

              </table>
              
            </div>
            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom">
            </div>
          </div>
        </div>
        <div class="ribbonStats ribbonStats-right">
          <div class="ribbonText">
            <!--<a href='?id=120&details={$tracked_quest->qid}' style='font-family:sakura;color:#EEE;font-weight:500;'>{$tracked_quest->name}</a>-->
              <a href='?id=120' style='font-family:sakura;color:#EEE;font-weight:500;position:relative;top:-15px;left:-10px;' title="QuestJournal">Quest</a>
              <input type="image" id="questing_mode_button" border="0" alt="{$questing_mode}" src="./images/{$questing_mode}.png" width="31" height="32" title="In {$questing_mode} mode." style="position:relative;top:-6px;left:-15px;" onClick=
                            "{
                                $('#questing_mode_button').prop('disabled', true);
                            	$.get
                            	(
                            		'?id=120&questing_mode={$questing_mode}', 
                            		function( data )
                            		{
                            			$('#questing_mode_button').prop('disabled', false);
                                        $('#quest_widget').html( $(data).find('#quest_widget') );
                            		}
                            	);
                            }">
          </div>
        </div>
      </div>
    {/if}
</div>



        <!-- main menu Main Menu -->
        <div class="menuContainer">
            <div class="menuBg1 menuBg1-left">
                <div class="menuPerforationVertical menuPerforationVerticalLeft">
                    <div class="perforationMenuHorizontal perforationMenuHorizontalTop">
                    </div>

                    <div class="menuBg2 menuBg2-right">
                        <table style="width:175px;border-spacing:0px;border-collapse: collapse;margin-bottom:0px;padding-bottom:0px;">
                            <tr>
                                <td>
                                    <a id="h_character">
                                        <img src="{$buttonCHARACTER}">
                                    </a>
                                </td>
                                <td>
                                    <a id="h_communication">
                                        <img src="{$buttonCOMMUNICATION}">
                                    </a>
                                </td>
                                <td>
                                    <a id="h_village">
                                        <img src="{$buttonVILLAGE}">
                                    </a>
                                </td>
                                <td>
                                    <a id="h_training">
                                        <img src="{$buttonTRAIN}">
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" style="padding:0px;">
                                    <div class="wrapper_outer" id="menuTable">
                                        <div class="wrapper_inner">
                                            <div class="jsHide" id="div_character">
                                                <ul class="ac-menu">
                                                    <li class="listHeader nohover">
                                                        <b>
                                                            Character
                                                        </b>
                                                    </li>
                                                    {if isset($menuArray["character"][0])} 
                                                        {foreach $menuArray["character"] as $item} 
                                                            <li class="menuLink">
                                                                <a href="{$item.link}">
                                                                    {$item.name}
                                                                </a>
                                                            </li>
                                                        {/foreach}
                                                    {else} 
                                                        <li class="menuLink">
                                                            {if $userStatus == "asleep"}
                                                                {if isset($sleepLink)}
                                                                    {$sleepLink}
                                                                {else}
                                                                    <a href="?id=2">You are Sleeping</a>
                                                                {/if}
                                                            {else}
                                                                <a href="?id=2">Currently N/A</a>
                                                            {/if}
                                                        </li>
                                                    {/if}
                                                </ul>
                                            </div>
                                            <div class="jsHide" id="div_communication">
                                                <ul class="ac-menu">
                                                    <li class="listHeader nohover">
                                                        <b>
                                                            Communication
                                                        </b>
                                                    </li>
                                                    {if isset($menuArray["communication"][0])} 
                                                        {foreach $menuArray["communication"] as $item} 
                                                            <li class="menuLink">
                                                                <a href="{$item.link}">
                                                                    {$item.name}
                                                                </a>
                                                            </li>
                                                        {/foreach}
                                                    {else} 
                                                        <li class="menuLink">
                                                            {if $userStatus == "asleep"}
                                                                {if isset($sleepLink)}
                                                                    {$sleepLink}
                                                                {else}
                                                                    <a href="?id=2">You are Sleeping</a>
                                                                {/if}
                                                            {else}
                                                                <a href="?id=2">Currently N/A</a>
                                                            {/if}
                                                        </li>
                                                    {/if}
                                                </ul>
                                            </div>
                                            <div class="jsHide" id="div_village">
                                                <ul class="ac-menu">
                                                    <li class="listHeader nohover">
                                                        <b>
                                                            {$user_factionType}
                                                        </b>
                                                    </li>
                                                    {if isset($menuArray["village"][0])} 
                                                        {if $userStatus == "asleep"}
                                                            <li class="nohover">
                                                                <a  style="font-family:segoe ui;font-weight:normal;line-height:normal;color:darkred;">
                                                                    You are sleeping
                                                                </a>
                                                            </li>
                                                        {/if}
                                                        {foreach $menuArray["village"] as $item} 
                                                            <li class="menuLink">
                                                                <a href="{$item.link}">
                                                                    {$item.name}
                                                                </a>
                                                            </li>
                                                        {/foreach}
                                                    {else} 
                                                        <li class="menuLink">
                                                            {if $userStatus == "asleep"}
                                                                {if isset($sleepLink)}
                                                                    {$sleepLink}
                                                                {else}
                                                                    <a href="?id=2">You are Sleeping</a>
                                                                {/if}
                                                            {else}
                                                                <a href="?id=2">Currently N/A</a>
                                                            {/if}
                                                        </li>
                                                    {/if}
                                                </ul>
                                            </div>
                                            <div class="jsHide" id="div_training">
                                                <ul class="ac-menu">
                                                    <li class="listHeader nohover">
                                                        <b>
                                                            Training & Missions
                                                        </b>
                                                    </li>
                                                    {if isset($menuArray["training"][0]) || isset($menuArray["missions"][0])}
                                                        {if $userStatus == "asleep"}
                                                            <li class="nohover">
                                                                <a  style="font-family:segoe ui;font-weight:normal;line-height:normal;color:darkred;">
                                                                    You are sleeping
                                                                </a>
                                                            </li>
                                                        {/if}
                                                        {if isset($menuArray["training"][0])} 
                                                            {foreach $menuArray["training"] as $item} 
                                                                <li class="menuLink">
                                                                    <a href="{$item.link}">
                                                                        {$item.name}
                                                                    </a>
                                                                </li>
                                                            {/foreach}
                                                        {/if}
                                                        {if isset($menuArray["missions"][0])} 
                                                            {foreach $menuArray["missions"] as $item} 
                                                                <li class="menuLink">
                                                                    <a href="{$item.link}">
                                                                        {$item.name}
                                                                    </a>
                                                                </li>
                                                            {/foreach}
                                                        {/if}
                                                    {else} 
                                                        <li class="menuLink">
                                                            {if $userStatus == "asleep"}
                                                                {if isset($sleepLink)}
                                                                    {$sleepLink}
                                                                {else}
                                                                    <a href="?id=2">You are Sleeping</a>
                                                                {/if}
                                                            {else}
                                                                <a href="?id=2">Currently N/A</a>
                                                            {/if}
                                                        </li>
                                                    {/if}
                                                </ul>
                                            </div>
                                            <div class="jsHide" id="div_map">
                                                <ul class="ac-menu">
                                                    <li class="listHeader nohover">
                                                        <b>
                                                            Map
                                                        </b>
                                                    </li>
                                                    {if $userStatus == "asleep"}
                                                        <li class="nohover">
                                                            <a  style="font-family:segoe ui;font-weight:normal;line-height:normal;color:darkred;">
                                                                You are sleeping
                                                            </a>
                                                        </li>
                                                    {/if}
                                                    {if isset($menuArray["map"][0])} 
                                                        {foreach $menuArray["map"] as $item} 
                                                            <li class="menuLink">
                                                                <a href="{$item.link}">
                                                                    {$item.name}
                                                                </a>
                                                            </li>
                                                        {/foreach}
                                                    {else} 
                                                        <li class="menuLink">
                                                            {if $userStatus == "asleep"}
                                                                {if isset($sleepLink)}
                                                                    {$sleepLink}
                                                                {else}
                                                                    <a href="?id=2">You are Sleeping</a>
                                                                {/if}
                                                            {else}
                                                                <a href="?id=2">Currently N/A</a>
                                                            {/if}
                                                        </li>
                                                    {/if}
                                                </ul>
                                            </div>
                                            <div class="jsHide" id="div_combat">
                                                <ul class="ac-menu">
                                                    <li class="listHeader nohover">
                                                        <b>
                                                            Combat
                                                        </b>
                                                    </li>
                                                    {if isset($menuArray["combat"][0])} 
                                                        {if $userStatus == "asleep"}
                                                            <li class="nohover">
                                                                <a  style="font-family:segoe ui;font-weight:normal;line-height:normal;color:darkred;">
                                                                    You are sleeping
                                                                </a>
                                                            </li>
                                                        {/if}
                                                        {foreach $menuArray["combat"] as $item} 
                                                            <li class="menuLink">
                                                                <a href="{$item.link}">
                                                                    {$item.name}
                                                                </a>
                                                            </li>
                                                        {/foreach}
                                                    {else} 
                                                        <li class="menuLink">
                                                            {if $userStatus == "asleep"}
                                                                {if isset($sleepLink)}
                                                                    {$sleepLink}
                                                                {else}
                                                                    <a href="?id=2">You are Sleeping</a>
                                                                {/if}
                                                            {else}
                                                                <a href="?id=2">Currently N/A</a>
                                                            {/if}
                                                        </li>
                                                    {/if}
                                                </ul>
                                            </div>
                                            <div class="jsHide" id="div_missions">
                                                <ul class="ac-menu">
                                                    <li class="listHeader nohover">
                                                        <b>
                                                            Support TNR
                                                        </b>
                                                    </li>
                                                    {if isset($menuArray["support"][0])} {foreach $menuArray["support"] as $item} 
                                                            <li class="menuLink">
                                                                <a href="{$item.link}">
                                                                    {$item.name}
                                                                </a>
                                                            </li>
                                                        {/foreach} {/if}
                                                    </ul>
                                                </div>
                                                <div class="jsHide" id="div_general">
                                                    <ul class="ac-menu">
                                                        <li class="listHeader nohover">
                                                            <b>
                                                                General
                                                            </b>
                                                        </li>
                                                        {if isset($menuArray["general"][0])} {foreach $menuArray["general"] as $item} 
                                                                <li class="menuLink">
                                                                    <a href="{$item.link}">
                                                                        {$item.name}
                                                                    </a>
                                                                </li>
                                                            {/foreach} {/if}
                                                            <li class="menuLink">
                                                                <a href="?id=1&amp;act=logout">
                                                                    Log out
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a id="h_map">
                                                <img src="{$buttonMAP}">
                                            </a>
                                        </td>
                                        <td>
                                            <a id="h_combat">
                                                <img src="{$buttonCOMBAT}">
                                            </a>
                                        </td>
                                        <td>
                                            <a id="h_missions">
                                                <img src="{$buttonMISSIONS}">
                                            </a>
                                        </td>
                                        <td>
                                            <a id="h_general">
                                                <img src="{$buttonSUPPORT}">
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:right;color:graytext;margin-bottom:0px;padding-bottom:0px;">
                                            {$gameVersion}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom">
                            </div>
                        </div>
                    </div>
                    <div class="ribbonStats ribbonStats-right">
                        <div class="ribbonText">
                            main menu &nbsp;
                        </div>
                    </div>
                </div>
                     
                
                                        
                                        
                                        
                {if empty($fbData)} 
                    <br>
                    <a href="?id=82">
                        <img width="150px" src="{$fbCONNECT}" alt="Facebook Connect" title="Facebook Connect" />
                    </a>
                    <div style="display:inline;float:right;padding-top:3px;" 
                        class="fb-like" 
                        layout="button_count" 
                        data-href='http://www.facebook.com/TheNinjaRPG' 
                        data-send='false' 
                        data-width='100' 
                        data-show-faces='false'>
                    </div>
                    <br>
                {/if}
                <div style="width:151px;align:center;">
                    <center>
                        {if isset($userIsNew)}
                            <span id="autoStartTutorial"></span>
                        {/if}
                        <img id="tutorialIcon" src="{$tutorialIcon}" style="display:inline;float:left;padding-left:5px;" class="imageHover">
                        

                    </center>
                </div>
                        