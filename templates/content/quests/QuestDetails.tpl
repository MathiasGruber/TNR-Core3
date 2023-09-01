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

<div width="95%" style="border: 1px solid black;position:relative;top:-10px;"> 
    <table class="table" style="border:none;margin:0px;" width="100%">
        <tr>
            <td style="padding:0px;">
                <table style="border:none;margin:0px;" width="100%">
                    {if !isset($mission_count)}
                        <tr>
                            <td style="text-align:left;color:rgb(97, 35, 24);font-family:sakura;font-size:21px;background-color:#d7c8b464;border-bottom: 1px solid black;">
                                {ucwords($quest->name)} - {ucwords($statuses[$quest->status])}
                            </td>
                            <td style="text-align:right;color:rgb(97, 35, 24);font-family:sakura;font-size:21px;background-color:#d7c8b464;border-bottom: 1px solid black;">
                                {if $quest->category_skin != ''}{ucwords($quest->category_skin)}{else}{ucwords($quest->category)}{/if}
                            </td>
                        </tr>
                    {else}
                        <tr>
                            <td style="text-align:left;color:rgb(97, 35, 24);font-family:sakura;font-size:21px;background-color:#d7c8b464;border-bottom: 1px solid black;">
                                Current Mission
                            </td>
                            <td style="text-align:right;color:rgb(97, 35, 24);font-family:sakura;font-size:21px;background-color:#d7c8b464;border-bottom: 1px solid black;">
                                Completed: {$mission_count}/{$mission_count_per_day}
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align:left;">
                                {ucwords($quest->name)} - {ucwords($statuses[$quest->status])}
                            </td>
                            <td style="text-align:right;">
                                {if $quest->category_skin != ''}{ucwords($quest->category_skin)}{else}{ucwords($quest->category)}{/if}
                            </td>
                        </tr>
                    {/if}
                    <tr>
                        <td style="text-align:left;">
                            ({if $quest->repeatable}Repeatable{if $quest->time_gap_requirement_text != ''} in {$quest->time_gap_requirement_text}{/if}, {else}Non-Repeatable, {/if}{if $quest->failable}Failable, {if $quest->hard_fail}Permanent, {/if}Chances: {$quest->chances}{else}Non-Failable{/if})
                        </td>
                        <td style="text-align:right;">
                            {if $quest->category_skin != ''}({ucwords($quest->category)}){/if}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="tdTop sorttable_nosort">
                Description
            </td>
        </tr>
        <tr>
            <td>
                {$quest->description}
                <br/>
                {if $quest->image != ''}
                    image goes here
                {/if}
            </td>
        </tr>

        <!--starting if known and completion requirements exist, option to hide?-->
        {if $quest->status == 0 && $quest->failed && is_array($quest->starting_requirements_post_failure) && count($quest->starting_requirements_post_failure) > 0 && !$quest->hide_starting_requirements_post_failure}
            
            <tr>
                <td class="tdTop sorttable_nosort">
                    Starting Requirements (Revised)
                </td>
            </tr>

            <tr>
                <td>
                    {generate_simple_req_display data=$quest->starting_requirements_post_failure}
                </td>
            </tr>

        {elseif $quest->status == 0 && is_array($quest->starting_requirements) && count($quest->starting_requirements) > 0  && !$quest->hide_starting_requirements}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Starting Requirements
                </td>
            </tr>

            <tr>
                <td>
                    {generate_simple_req_display data=$quest->starting_requirements}
                </td>
            </tr>

        {/if}

        <!--completion if active and completion requirements exist, option to hide?-->
        {if $quest->status == 1 && $quest->failed && is_array($quest->completion_requirements_post_failure) && count($quest->completion_requirements_post_failure) > 0  && !$quest->hide_completion_requirements_post_failure}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Completion Requirements (Revised)
                </td>
            </tr>

            <tr>
                <td>
                    {generate_complex_req_display requirement_container=$quest->completion_requirements_post_failure requirement_category='completion'}
                </td>
            </tr>

        {elseif $quest->status == 1 && is_array($quest->completion_requirements) && count($quest->completion_requirements) > 0 && !$quest->hide_completion_requirements}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Completion Requirements
                </td>
            </tr>

            <tr>
                <td>
                    {generate_complex_req_display requirement_container=$quest->completion_requirements requirement_category='completion'}
                </td>
            </tr>

        {/if}

        <!--failure if active and failure requirements exist, option to hide?-->
        {if $quest->status == 1 && $quest->failed && is_array($quest->failure_requirements_post_failure) && count($quest->failure_requirements_post_failure) > 0 && !$quest->hide_failure_requirements_post_failure}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Failure Requirements (Revised)
                </td>
            </tr>

            <tr>
                <td>
                    {generate_complex_req_display requirement_container=$quest->failure_requirements_post_failure requirement_category='failure'}
                </td>
            </tr>

        {elseif $quest->status == 1 && is_array($quest->failure_requirements) && count($quest->failure_requirements) > 0 && !$quest->hide_failure_requirements}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Failure Requirements
                </td>
            </tr>

            <tr>
                <td>
                    {generate_complex_req_display requirement_container=$quest->failure_requirements requirement_category='failure'}
                </td>
            </tr>

        {/if}

        <!--turn in if completed-->
        {if $quest->status == 2 && $quest->failed && is_array($quest->turn_in_requirements_post_failure) && count($quest->turn_in_requirements_post_failure) > 0  && !$quest->hide_turn_in_requirements_post_failure}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Turn In Requirements (Revised)
                </td>
            </tr>

            <tr>
                <td>
                    {generate_simple_req_display data=$quest->turn_in_requirements_post_failure}
                </td>
            </tr>

        {elseif $quest->status == 2 && is_array($quest->turn_in_requirements) && count($quest->turn_in_requirements) > 0 && !$quest->hide_turn_in_requirements}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Turn In Requirements
                </td>
            </tr>

            <tr>
                <td>
                    {generate_simple_req_display data=$quest->turn_in_requirements}
                </td>
            </tr>

        {/if}

        <!--rewards and punishements-->
        {if $quest->failed && is_array($quest->rewards_post_failure) && count($quest->rewards_post_failure) > 0  && !$quest->hide_rewards_post_failure}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Rewards (Revised)
                </td>
            </tr>

            <tr>
                <td>
                    {generate_gift_display data=$quest->rewards_post_failure_write_up}
                </td>
            </tr>

        {elseif is_array($quest->rewards) && count($quest->rewards) > 0  && !$quest->hide_rewards}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Rewards
                </td>
            </tr>

            <tr>
                <td>
                    {generate_gift_display data=$quest->rewards_write_up}
                </td>
            </tr>

        {/if}
        {if $quest->failed && is_array($quest->punishments_post_failure) && count($quest->punishments_post_failure) > 0 && !$quest->hide_punishments_post_failure}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Punishments (Revised)
                </td>
            </tr>

            <tr>
                <td>
                    {generate_gift_display data=$quest->punishments_post_failure_write_up}
                </td>
            </tr>

        {elseif is_array($quest->punishments) && count($quest->punishments) > 0 && !$quest->hide_punishments}

            <tr>
                <td class="tdTop sorttable_nosort">
                    Punishments
                </td>
            </tr>

            <tr>
                <td>
                    {generate_gift_display data=$quest->punishments_write_up}
                </td>
            </tr>

        {/if}

        <!--dialog history-->
        {if is_array($quest->dialog_chain) && count($quest->dialog_chain) > 0 && $quest->dialog_history}
            <tr>
                <td style="padding:0px;" colspan="{count($quest->actions)}">
                    <details>
                        <summary>
                            <div class="tableColumns" style="font-family:sakura;font-size:15px;position:relative;top:-8px;border:none;padding:0px;border-top: 1px solid black;border-bottom: 1px solid black;height:24px;user-select: none;-moz-user-select: none;">
                                Dialog ⏷
                            </div>
                        </summary>
                        {$last_link = array()}
                        {$tab_count = 0}
                        <div style="text-align:left;">
                            {foreach $quest->dialog_chain as $link}
                                {if $link !== $last_link}
                                    {if $link['m'] == 'start'}
                                        {$tab_count = 0}
                                    {else}
                                        {$tab_count = $tab_count + 1}
                                    {/if}
                                    
                                    {$last_link = $link}
                                    
                                    {$dialog = $quest->{$link['d']}}
                                    {$message = $dialog[$link['m']]['message']}
                                    {$option = $dialog[$link['m']]['options'][$link['o']]['text']}
                                    <div style="padding-left: {5+($tab_count*1)}em; text-indent: -4em;display:inline-block;">Quest: {$message}</div>
                                    <br/>
                                    <br/>
                                    <div style="padding-left: {5+($tab_count*2)}em; text-indent: -2em;display:inline-block;">You: {$option}</div>
                                    <br/>
                                    <br/>
                                {/if}
                            {/foreach}
                        </div>
                    </details>
                </td>
            </tr>
        {/if}

    </table>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
</div>