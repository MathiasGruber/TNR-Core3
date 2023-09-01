{if $quest_widget == 'yes' && ($tracked_quest != '' || count($trackable_quests) > 0)}

    {function generate_simple_req_display}
        {foreach $data as $requirement_key => $requirement}
            {if isset($requirement['joined']) && substr( $requirement['joined'], 0, 4 ) === "join"}
                {$data[$requirement['joined']][$requirement_key] = $requirement}
                {$data[$requirement_key] = null}
            {/if}
        {/foreach}

        {foreach $data as $requirement_key => $requirement}
            {if isset($requirement['status']) && substr( $requirement_key, 0, 4 ) === "join"}

                {$status = true}
                {$hover = []}
                {$write_ups = []}
                {$tips = []}

                {foreach $requirement as $joined_requirement_key => $joined_requirement_data}
                    {if $joined_requirement_data['status'] == 0}
                        {$status = false}
                        {$hover[] = $joined_requirement_key|cat:' is not satisfied'}
                    {else}
                        {$hover[] = $joined_requirement_key|cat:' is satisfied'}
                    {/if}

                    {if $requirement['write_up'] != ''}
                        {$write_ups[] = $requirement['write_up']}
                    {elseif $requirement['status'] != 0}
                        {$write_ups[] = 'None'}
                    {else}
                        {$write_ups[] = 'Un-Known'}
                    {/if}

                    {if $requirement['tip'] != ''}
                        {$tips[] = $requirement['tip']}
                    {elseif $requirement['status'] != 0}
                        {$tips[] = 'None'}
                    {else}
                        {$tips[] = 'Un-Known'}
                    {/if}

                {/foreach}

                {if $status === false}
                    <span title="{implode(' and ', $tips)}"><font color="red" class="shadow-outline noColorTip" title="{implode(' and ', $hover)}>&#10007</font> - {implode(' and ', $write_ups)}</span><br>
                {else}
                    <span title="{implode(' and ', $tips)}"><font color="green" class="highlight-outline bold noColorTip" title="{implode(' and ', $hover)}>&#10003</font> - {implode(' and ', $write_ups)}</span><br>
                {/if}

            {else if $requirement != null}
                <span title="{$requirement['tip']}">
                {if $requirement['status'] == 0}
                    <font color="red" class="shadow-outline">&#10007</font>
                {else}
                    <font color="green" class="highlight-outline bold">&#10003</font>
                {/if}
                 - {if $requirement['write_up'] != ''}{$requirement['write_up']}{elseif $requirement['status'] != 0}None{else}Un-Known{/if}</span>
                <br>
            {/if}
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
                {$tips = []}


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

                    {if $joined_requirement_data['tip'] != ''}
                        {$tips[] = $joined_requirement_data['tip']}
                    {/if}
                {/foreach}

                {if $status === false}
                    <span title="{implode(' and ', $tips)}"><font color="red" class="shadow-outline noColorTip" title="{implode(' and ', $hover)}">&#10007</font>
                    {assign var="color" value="red"} - {implode(' and ', $write_ups)}</span><br>
                {else}
                    <span title="{implode(' and ', $tips)}"><font color="green" class="highlight-outline bold noColorTip" title="{implode(' and ', $hover)}">&#10003</font>
                    {assign var="color" value="green"} - {implode(' and ', $write_ups)}</span><br>
                {/if}

            {else if $requirement_data != null}
                <span title="{$requirement_data['tip']}">
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

                - {$requirement_data['write_up']}</span>

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
                <br>
            {elseif substr( $type, 0, 7 ) === "bullets"}
                <ul style="list-style-type:disc;padding-left:30px;">
                    {foreach $stuff as $bullets}
                        <li>{$bullets}</li>
                    {/foreach}
                </ul>
                <br>
            {elseif substr( $type, 0, 7 ) === "numbers"}
                <ol style="list-style-type:decimal;padding-left:30px;">
                    {foreach $stuff as $numbers}
                        <li>{$numbers}</li>
                    {/foreach}
                </ol>
                <br>
            {else}
                <ol style="list-style-type:{strtok($type,'_')};padding-left:30px;">
                    {foreach $stuff as $things}
                        <li>{$things}</li>
                    {/foreach}
                </ol>
                <br>
            {/if}
        {/foreach}
    {/function}

    <div id="widget-quests" class="widget-box lazy">
        <div id="widget-quests-title" class="widget-title lazy {if $_COOKIE['widget-quests-closed'] == true}closed{/if}">
            <a id="widget-quests-title-text" href="/?id=120" title="quest journal page">Quests</a>
            <div id="widget-quests-mode-button" 
                 class="lazy ui-button widget-quests-mode-button-{$questing_mode}"
                 alt="{$questing_mode}"
                 title="Questing Mode: {$questing_mode}"
                 onclick="event.stopPropagation();loadPage('?id=120&questing_mode={$questing_mode}','#widget-quests');"
            ></div>
        </div>
        <div id="widget-quests-content" class="widget-content lazy" {if $_COOKIE['widget-quests-closed'] == true}style="display:none;"{/if}>
            {if $tracked_quest != ''}
                <div id="widget-quests-tracked-quest-box">
                
                    <a id="widget-quests-tracked-quest-title" 
                       class="lazy fancy-box dark-solid-box box"
                       title="{$tracked_quest->description}"
                       href="{if $tracked_quest->category != 'mission'}?id=120&details={$tracked_quest->qid}{else}?id=121{/if}"
                       >
                        {{$tracked_quest->name}}
                    </a>

                    <!--starting if known and completion requirements exist, option to hide?-->
                    {if $tracked_quest->status == 0 && $tracked_quest->failed && is_array($tracked_quest->starting_requirements_post_failure) && count($tracked_quest->starting_requirements_post_failure) > 0 && !$tracked_quest->hide_starting_requirements_post_failure}


                        <div id="widget-quests-starting-requirements-r" class="lazy widget-quests-requirements widget-quests-starting-requirements fancy-box dark-plain-box">
                            Starting Requirements (R) 
                            <br><hr>
                            {generate_simple_req_display data=$tracked_quest->starting_requirements_post_failure}
                        </div>

                    {elseif $tracked_quest->status == 0 && is_array($tracked_quest->starting_requirements) && count($tracked_quest->starting_requirements) > 0  && !$tracked_quest->hide_starting_requirements}

                        <div id="widget-quests-starting-requirements" class="lazy widget-quests-requirements widget-quests-starting-requirements fancy-box dark-plain-box">
                            Starting Requirements 
                            <br><hr>
                            {generate_simple_req_display data=$tracked_quest->starting_requirements}
                        </div>

                    {/if}

                    <!--completion if active and completion requirements exist, option to hide?-->
                    {if $tracked_quest->status == 1 && $tracked_quest->failed && is_array($tracked_quest->completion_requirements_post_failure) && count($tracked_quest->completion_requirements_post_failure) > 0  && !$tracked_quest->hide_completion_requirements_post_failure}


                        <div id="widgets-quests-completion-requirements-r" class="lazy widget-quests-requirements widget-quests-completion-requirements fancy-box dark-plain-box">
                            Completion Requirements (R) 
                            <br><hr>
                            {generate_complex_req_display requirement_container=$tracked_quest->completion_requirements_post_failure requirement_category='completion'}
                        </div>

                    {elseif $tracked_quest->status == 1 && is_array($tracked_quest->completion_requirements) && count($tracked_quest->completion_requirements) > 0 && !$tracked_quest->hide_completion_requirements}

                        <div id="widgets-quests-completion-requirements" class="lazy widget-quests-requirements widget-quests-completion-requirements fancy-box dark-plain-box">
                            Completion Requirements 
                            <br><hr>
                            {generate_complex_req_display requirement_container=$tracked_quest->completion_requirements requirement_category='completion'}
                        </div>

                    {/if}

                    <!--failure if active and failure requirements exist, option to hide?-->
                    {if $tracked_quest->status == 1 && $tracked_quest->failed && is_array($tracked_quest->failure_requirements_post_failure) && count($tracked_quest->failure_requirements_post_failure) > 0 && !$tracked_quest->hide_failure_requirements_post_failure}

                        <div id="widgets-quests-failure-requirements-r" class="lazy widget-quests-requirements widget-quests-failure-requirements fancy-box dark-plain-box">
                            Failure Requirements (R) 
                            <br><hr>
                            {generate_complex_req_display requirement_container=$tracked_quest->failure_requirements_post_failure requirement_category='failure'}
                        </div>

                    {elseif $tracked_quest->status == 1 && is_array($tracked_quest->failure_requirements) && count($tracked_quest->failure_requirements) > 0 && !$tracked_quest->hide_failure_requirements}

                        <div id="widgets-quests-failure-requirements" class="lazy widget-quests-requirements widget-quests-failure-requirements fancy-box dark-plain-box">
                            Failure Requirements 
                            <br><hr>
                            {generate_complex_req_display requirement_container=$tracked_quest->failure_requirements requirement_category='failure'}
                        </div>

                    {/if}

                    <!--turn in if completed-->
                    {if $tracked_quest->status == 2 && $tracked_quest->failed && is_array($tracked_quest->turn_in_requirements_post_failure) && count($tracked_quest->turn_in_requirements_post_failure) > 0  && !$tracked_quest->hide_turn_in_requirements_post_failure}

                        <div id="widgets-quests-turn-in-requirements-r" class="lazy widget-quests-requirements widget-quests-turn-in-requirements fancy-box dark-plain-box">
                            Turn In Requirements (R) 
                            <br><hr>
                            {generate_simple_req_display data=$tracked_quest->turn_in_requirements_post_failure}
                        </div>

                    {elseif $tracked_quest->status == 2 && is_array($tracked_quest->turn_in_requirements) && count($tracked_quest->turn_in_requirements) > 0 && !$tracked_quest->hide_turn_in_requirements}

                        <div id="widgets-quests-turn-in-requirements" class="lazy widget-quests-requirements widget-quests-turn-in-requirements fancy-box dark-plain-box">
                            Turn In Requirements 
                            <br><hr>
                            {generate_simple_req_display data=$tracked_quest->turn_in_requirements}
                        </div>

                    {/if}

                    {if isset($tracked_quest->actions) && count($tracked_quest->actions) > 1}

                        <div id="widget-quests-tracked-button-box" class="lazy fancy-box dark-solid-box">
                            <div id="widget-quests-untrack-button-paired"
                                 class="widget-quests-button-box-item lazy"
                                 onclick="(function()
                                 {
                                    loadPage('?id=120&track={$tracked_quest->qid}','#widget-quests');
                                    $('.track-check-box').prop('checked', false);
                                 })();"
                            >
                                Untrack
                            </div>

                            <select id="widget-quests-button-dropdown"
                                    class="widget-quests-button-box-item lazy"
                                    onchange="window.location.href = this.options[this.selectedIndex].value;"
                                    >
                                <option value="" disabled selected>Actions</option>
                                {foreach $tracked_quest->actions as $action}
                                    <option value="{$action['link']}">{$action['text']}</option>
                                {/foreach}
                            </select>
                        </div>


                    {elseif isset($tracked_quest->actions) && count($tracked_quest->actions) == 1}

                        <div id="widget-quests-tracked-button-box" class="lazy fancy-box dark-solid-box">
                            <div id="widget-quests-untrack-button-paired"
                                 class="widget-quests-button-box-item lazy"
                                 onclick="(function()
                                 {
                                    loadPage('?id=120&track={$tracked_quest->qid}','#widget-quests');
                                    $('.track-check-box').prop('checked', false);
                                 })();"
                            >
                                Untrack
                            </div>

                            <a id="widget-quests-button-link"
                               class="widget-quests-button-box-item widget-quests-button-box-link lazy"
                               href="{$tracked_quest->actions[0]['link']}">
                                {$tracked_quest->actions[0]['text']}
                            </a>
                        </div>

                    {else}

                        <div id="widget-quests-untrack-button-solo"
                             class="widget-quests-button-box-item lazy"
                             onclick="(function()
                             {
                                loadPage('?id=120&track={$tracked_quest->qid}','#widget-quests');
                                $('.track-check-box').prop('checked', false);
                             })();"
                        >
                            Untrack
                        </div>

                    {/if}

                </div>
            {else}
                <div id="widget-quests-trackable-quest-box">
                    <div id="widget-quests-trackable-quests-title" class="lazy fancy-box dark-solid-box">Quests</div>
                    {foreach $trackable_quests as $qid => $quest}
                        <div class="widget-quests-trackable-quests-buttons lazy fancy-box {if $quest@iteration % 2 == 1}dark-plain-box{else}dark-solid-box{/if}"
                            onclick="(function()
                            {
                                loadPage('?id=120&track={$qid}','#widget-quests'); 
                                $('.track-check-box').prop('checked', false);
                                $('#track_{$qid}').prop('checked', true);
                            })();"
                        >
                            {$quest->name}
                        </div>
                    {/foreach}
                </div>
            {/if}
        </div>
    </div>
{/if}