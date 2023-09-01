{function canUseJutsu}
    {$assigned = false}
    {if $jutsu['uses'] == $jutsu['max_uses'] && $jutsu['max_uses'] >= 0}
        {assign var="usable" value="Out of Uses" scope=parent}
    {else if $jutsu['cooldown_status'] != 'off'}
        {assign var="usable" value="Cooldown for `$jutsu['cooldown_status']` Rounds." scope=parent}
    {else}
        {if $jutsu['weapons'] != ''}

            {if !is_array($jutsu['weapons'])}
                {$jutsu['weapons'] = explode(',',$jutsu['weapons'])}
            {/if}

            {$flags = []}

            {foreach $jutsu['weapons'] as $required}
                {$flags[$required] = 0}
            {/foreach}


            {foreach $owner['equipment'] as $id => $equipment}
                {foreach $jutsu['weapons'] as $required}
                    {if strpos($equipment['weapon_classifications'], $required) !== false}
                        {$flags[$required] = $flags[$required] + 1}
                    {/if}
                {/foreach}
            {/foreach}

            {$do_not_print_this_return_value_thanks = sort($flags)}
            {$flags = array_values($flags)}

            {foreach $flags as $key => $value}
                {if !$assigned && $key >= $value}
                    {$needed = implode(',',$jutsu['weapons'])}
                    {assign var="usable" value="Missing Equipment! <span class='font-tiny'>(`$needed`)</span>" scope=parent}
                    {$assigned = true}
                {/if}
            {/foreach}
        {/if}

        {if !$assigned && ($jutsu['reagents'] != '' || count($jutsu['reagents']) > 0) && !$jutsu['reagent_status']}
            {assign var="usable" value="Missing Reagent! <span class='font-tiny'>(`$jutsu['reagents']`)</span>" scope=parent}
            {$assigned = true}
        {/if}

        {if !$assigned && $no_jutsu !== false && $jutsu['name'] != "Basic Attack"}
            {assign var="usable" value="Disabled" scope=parent}
        {/if}

        {if !$assigned}
            {assign var="usable" value=true scope=parent}
        {/if}
    {/if}		
{/function}

{function printJutsuButton}
    <div id="jutsu-{$jid}-button" class="option-button jutsu-button r{if $jutsu['required_rank'] != ''}{$jutsu['required_rank']}{else}{$owner['rank']}{/if} {if $usable !== true}disabled{/if}"
        data-targeting_type="{$jutsu['targeting_type']}" onclick="optionclick('jutsu','{$jid}')">
        <div class="color-layer {$jutsu['element']} {$jutsu['village']} {$jutsu['clan']} {$jutsu['kage']} {$jutsu['targeting_type']}">

            {if $jutsu['name'] != "Basic Attack"}
                {if in_array($jutsu['jutsu_type'], ['normal','event'])}
                    {$exp_required = 1000}
                {else if in_array($jutsu['jutsu_type'], ['bloodline','clan'])}
                    {$exp_required = 1500}
                {else if in_array($jutsu['jutsu_type'], ['special','loyalty'])}
                    {$exp_required = 2000}
                {else if $jutsu['jutsu_type'] == ['village','loyalty']}
                    {$exp_required = 3000}
                {else if in_array($jutsu['jutsu_type'], ['forbidden','kage'])}
                    {$exp_required = 5000}
                {/if}

                {if $jutsu['level'] == $jutsu['max_level']}
                    <div class="top-bar exp" style="width: calc(100% + 16px)"></div>
                {else}
                    <div class="top-bar exp" style="width: calc({($jutsu['exp']/$exp_required)*100}% + {16 * ($jutsu['exp']/$exp_required)}px)"></div>
                {/if}

                <div class="top-bubble font-tiny">
                    Lvl: {$jutsu['level']}
                </div>
            {else}
                <div class="top-bubble font-tiny" style="border:none;margin-top:-3px;"></div>
            {/if}

            <div class="jutsu-button-title">

                <div class="jutsu-cooldown font-tiny {$jutsu['jutsu_type']}">
                    {if $jutsu['name'] == "Basic Attack"}
                        0
                    {else if $jutsu['jutsu_type'] == 'normal'}
                        2
                    {else if $jutsu['jutsu_type'] == 'bloodline' || $jutsu['jutsu_type'] == 'clan'}
                        3
                    {else if $jutsu['jutsu_type'] == 'special' || $jutsu['jutsu_type'] == 'loyalty' || $jutsu['jutsu_type'] == 'kage'}
                        4
                    {else if $jutsu['jutsu_type'] == 'village' || $jutsu['jutsu_type'] == 'forbidden'}
                        5
                    {/if}
                </div>

                {if strpos($jutsu['name'], ':') !== false}
                    {$exploded_name = explode(':',$jutsu['name'])}
                    <div class="jutsu-title option-title" title="{$exploded_name[0]}">
                        {$exploded_name[1]}
                    </div>
                {else}
                    <div class="jutsu-title option-title">
                        {$jutsu['name']}
                    </div>
                {/if}
            </div>
            <div class="jutsu-button-body font-small text-left">
                {if $usable !== true}
                    {$usable}
                {/if}
            </div>

            {if $jutsu['max_uses'] < 0}
                <div class="bottom-bar" style="width:0px;"></div>
                <div class="bottom-bubble font-tiny" style="border:none;margin-bottom:-8px;"></div>
            {else}
                <div class="bottom-bar" style="width: calc({(($jutsu['max_uses']-$jutsu['uses'])/$jutsu['max_uses'])*100}% + {16 * (($jutsu['max_uses']-$jutsu['uses'])/$jutsu['max_uses'])}px)"></div>
                <div class="bottom-bubble font-tiny">{$jutsu['max_uses']-$jutsu['uses']} / {$jutsu['max_uses']}</div>
            {/if}
        </div>
    </div>
{/function}

{function printJutsuRecord}
    <div id="jutsu-{$jid}-record" class="option-record jutsu-record r{$jutsu['required_rank']}{if $usable !== true} disabled{/if}" data-jid="{$jid}">
        <div class="color-layer {$jutsu['element']} {$jutsu['village']} {$jutsu['clan']} {$jutsu['kage']} {$jutsu['targeting_type']}">

            {if $jutsu['name'] != "Basic Attack"}
                {if in_array($jutsu['jutsu_type'], ['normal','event'])}
                    {$exp_required = 1000}
                {else if in_array($jutsu['jutsu_type'], ['bloodline','clan'])}
                    {$exp_required = 1500}
                {else if in_array($jutsu['jutsu_type'], ['special','loyalty'])}
                    {$exp_required = 2000}
                {else if $jutsu['jutsu_type'] == ['village','loyalty']}
                    {$exp_required = 3000}
                {else if in_array($jutsu['jutsu_type'], ['forbidden','kage'])}
                    {$exp_required = 5000}
                {/if}

                {if $jutsu['level'] == $jutsu['max_level']}
                    <div class="top-bar exp" style="width: calc(100% + 16px)"></div>
                {else}
                    <div class="top-bar exp" style="width: calc({($jutsu['exp']/$exp_required)*100}% + {16 * ($jutsu['exp']/$exp_required)}px)"></div>
                {/if}

                <div class="top-bubble font-tiny">
                    Lvl: {$jutsu['level']}
                </div>
            {else}
                <div class="top-bubble font-tiny" style="border:none;margin-top:-3px;"></div>
            {/if}

            <div class="jutsu-record-title">

                <div class="jutsu-cooldown font-tiny {$jutsu['jutsu_type']}">
                    {if $jutsu['name'] == "Basic Attack"}
                        0
                    {else if $jutsu['jutsu_type'] == 'normal'}
                        2
                    {else if $jutsu['jutsu_type'] == 'bloodline' || $jutsu['jutsu_type'] == 'clan'}
                        3
                    {else if $jutsu['jutsu_type'] == 'special' || $jutsu['jutsu_type'] == 'loyalty' || $jutsu['jutsu_type'] == 'kage'}
                        4
                    {else if $jutsu['jutsu_type'] == 'village' || $jutsu['jutsu_type'] == 'forbidden'}
                        5
                    {/if}
                </div>

                {if strpos($jutsu['name'], ':') !== false}
                    {$exploded_name = explode(':',$jutsu['name'])}
                    <div class="jutsu-title option-title text-large" title="{$exploded_name[0]}">
                        {$exploded_name[1]}
                    </div>
                {else}
                    <div class="jutsu-title option-title">
                        {$jutsu['name']}
                    </div>
                {/if}

                <div class="cancel-button" onclick="optionCancel('jutsu')">
                    X
                </div>
            </div>

            <div class="option-description jutsu-description">
                {$jutsu['description']}
            </div>

            <div class="jutsu-record-body font-small text-left">
                {if $usable !== true}
                    {$usable}
                {else}
                    {$owner['jutsu_weapon_selects_new'][$jid]}
                {/if}
            </div>

            <div class="option-effects jutsu-effects">
                <details>
                    <summary>
                        Effects
                    </summary>
                    <div class="text-left font-small jutsu-effects option-effects">

                        {if strlen($jutsu['effects']) > 15}
                            <div>
                            {implode('</div><div>',
                                explode('-new-line-',
                                    trim(
                                        trim(
                                            trim(
                                                str_replace(
                                                    '-new-line-    ',
                                                    ", ",
                                                    $jutsu['effects']
                                                ),
                                                "</pre>"
                                            ),
                                            "<pre>"
                                        ),
                                        '-new-line-'
                                    )
                                )
                            )}
                            </div>
                        {else}
                            N/A
                        {/if}
                    </div>
                </details>
            </div>

            {if $jutsu['max_uses'] < 0}
                <div class="bottom-bar" style="width:0px;"></div>
                <div class="bottom-bubble font-tiny" style="border:none;margin-bottom:-8px;"></div>
            {else}
                <div class="bottom-bar" style="width: calc({(($jutsu['max_uses']-$jutsu['uses'])/$jutsu['max_uses'])*100}% + {16 * (($jutsu['max_uses']-$jutsu['uses'])/$jutsu['max_uses'])}px)"></div>
                <div class="bottom-bubble font-tiny">{$jutsu['max_uses']-$jutsu['uses']} / {$jutsu['max_uses']}</div>
            {/if}
        </div>
    </div>
{/function}


{if (is_numeric($stunned) && $stunned > 0) || $stunned === true}
    {stunned}

{elseif $owner['waiting_for_next_turn'] === true}
    {waiting}

{else}
    <!--getting owner specialization-->
    {$specialization = explode(':',$userdata['specialization'])}
    {if $specialization != "" && $specialization[1] == 1}
        {$specialization = $specialization[0]}

        {if $specialization == "W"}
            {$specialization = "B"}
        {/if}
    {else}
        {$specialization = ""}
    {/if}
        
    {foreach $owner['jutsus'] as $jid => $jutsu}
        <!--finding split data and pulling out the correct data-->
        {if $specialization != ""}
            {foreach $jutsu as $field_key => $field_data}
                {if is_array($field_data) && in_array($specialization, $field_data)}
                    {$fliped_field_data = array_flip($field_data)}
                    {$jutsu[$field_key] = $fliped_field_data[$specialization]}
                {/if}
            {/foreach}
        {/if}

        {canUseJutsu      jid=$jid jutsu=$jutsu}
        {printJutsuRecord jid=$jid jutsu=$jutsu useable=$usable owner=$owner}
        {printJutsuButton jid=$jid jutsu=$jutsu useable=$usable}
    {/foreach}
{/if}