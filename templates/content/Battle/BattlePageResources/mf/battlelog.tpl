{foreach array_combine( array_reverse( array_keys( $battle_log )), array_reverse( array_values( $battle_log ) ) ) as $round_number => $round_users}
    {if $turn_counter - $round_number <= $turn_log_length || $turn_log_length < 1}
        <div class="header_round{$round_number+1} table-cell bold plain-box">
            Round: {$round_number + 1}
        </div>

        <div class="round{$round_number+1}_">

            {foreach $round_users as $username => $userdata}

                <div class="round{$round_number+1}_{str_replace(' ','-',$username)}">
                    <div class="{if $username == $owner['name']}bracket-teal{else if $owner['team'] == $userdata['team']}bracket-blue{else}bracket-orange{/if}">
                        {if $userdata['show_count'] == 'yes'}
                            {assign var=the_users_name value=$username}
                        {else}
                            {if strpos($username,'#') !== false}
                                {assign var=the_users_name value=substr($username,0,strpos($username,'#') - 1)}
                            {else}
                                {assign var=the_users_name value= $username}
                            {/if}
                        {/if}

                        {if $round_users[$userdata['target']]['show_count'] == 'yes'}
                            {assign var=the_targets_name value=$userdata['target']}
                        {else}
                            {if strpos($userdata['target'],'#') !== false}
                                {assign var=the_targets_name value=substr($userdata['target'],0,strpos($userdata['target'],'#') - 1)}
                            {else}
                                {assign var=the_targets_name value=$userdata['target']}
                            {/if}
                        {/if}

                        {if $userdata['order'] != ''}
                            {$userdata['order']}- {$the_users_name}
                        {else}
                            ?- {$the_users_name}
                        {/if}

                        {if $userdata['failure'] == 'failure'}
                            was defeated before they could take their action.
                        {else if $userdata['type'] == 'respondent'}
                            <strong style="font-size:16px;"> has responded to </strong><br>
                            <strong style="font-size:16px;"> the call for help and has </strong><br>
                            <strong style="font-size:16px;"> attacked </strong> {$the_targets_name} <strong style="font-size:16px;">!</strong>
                        {else if $userdata['name'] == 'Basic Attack'}
                            <strong style="font-size:16px;"> attacked </strong> {$the_targets_name} <strong style="font-size:16px;">!</strong>
                        {else if $userdata['type'] == 'jutsu'}
                            <strong style="font-size:16px;"> attacked </strong> {$the_targets_name} <strong style="font-size:16px;"> with </strong> <br>
                            <strong style="font-size:16px;"> the jutsu {$userdata['name']}!</strong>
                        {else if $userdata['type'] == 'weapon'}
                            <strong style="font-size:16px;"> attacked </strong> {$the_targets_name} <strong style="font-size:16px;"> with </strong> <br>
                            <strong style="font-size:16px;"> their {$userdata['name']}!</strong>
                        {else if $userdata['type'] == 'item'}
                            <strong style="font-size:16px;">used {$userdata['name']}</strong><br>
                            <strong style="font-size:16px;"> on </strong> {$the_targets_name} <strong style="font-size:16px;">!</strong>
                        {else if $userdata['type'] == 'flee' }                                 
                            tried to flee from the battle!
                        {else if $userdata['type'] == 'call_for_help'}
                            called for help!
                        {else if $userdata['type'] == 'stunned'}
                            was stunned this turn.
                        {else}
                            was ?.
                        {/if}
                    </div>
                </div>

                <!-- ///////////////////////////////////////////// extra information \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->
                <div class="details_round{$round_number+1}_{str_replace(' ','-',$username)}"></div>
                <div class="details_round{$round_number+1}_{str_replace(' ','-',$username)}">
                    <div class="bracket-grey">
                        <details>
                            <summary>Effects: </summary>
                            {foreach $userdata['effects'] as $target => $effects}
                                {foreach $effects as $effect => $messages}
                                    {$target}-> {$effect}:
                                    {foreach $messages as $key => $message}
                                        {if $key != 0}, {/if}
                                        {$message}
                                    {/foreach}
                                    <br/>
                                {/foreach}
                            {/foreach}
                        </details>
                    </div>

                    {if isset($userdata['killed']) }
                        <div class="
                            {if $owner['team'] != $round_users[ $userdata['killed'] ]['team'] }
                                bracket-green">
                            {else}
                                bracket-red">
                            {/if}

                            {$the_users_name} <strong style="font-size:16px;">defeated</strong>

                            {if $round_users[$userdata['killed']]['show_count'] == 'yes'}
                                {$userdata['killed']}
                            {else}
                                {if strpos($userdata['killed'],'#') !== false}
                                    {substr($userdata['killed'],0,strpos($userdata['killed'],'#') - 1)}
                                {else}
                                    {$userdata['killed']}
                                {/if}
                            {/if}
                        </div>
                    {/if}

                    {if isset($userdata['died']) }
                        <div class="
                            {if $owner['team'] == $round_users[ $userdata['died'] ]['team'] }
                                bracket-green">
                            {else}
                                bracket-red">
                            {/if}

                            {$the_users_name} <strong style="font-size:16px;">was defeated at the hands of</strong>

                            {if $round_users[$userdata['died']]['show_count'] == 'yes'}
                                {$userdata['died']}
                            {else}
                                {if strpos($userdata['died'],'#') !== false}
                                    {substr($userdata['died'],0,strpos($userdata['died'],'#') - 1)}
                                {else}
                                    {$userdata['died']}
                                {/if}
                            {/if}
                        </div>
                    {/if}

                    {if isset($userdata['jutsu_description']) && $userdata['failure'] != 'failure'}
                        <div class="bracket-brown">
                            <strong><span style="font-size:13px;font-family: 'Merienda', cursive;-webkit-font-smoothing: subpixel-antialiased;">{$userdata['jutsu_description']}</span></strong>
                        </div>
                    {/if}
                    {if isset($userdata['damage_delt'])}
                        {foreach $userdata['damage_delt'] as $damage_delt}
                            <!-- for later these are the colors for good: 179917 bad: a61919 nuetral: 7f7f7f brown: 653E1D-->
                            <div class="
                                {if $owner['team'] == $round_users[ $username ]['team'] }
                                    bracket-green">
                                {else}
                                    bracket-red">
                                {/if}
                                <strong>{if $damage_delt['oneHitKill'] === true}one hit kill{else}{$damage_delt['type']} damage dealt{/if}{if $damage_delt['aoe']} from aoe{/if}:</strong> {if $damage_delt['crit'] == true}<span style="color:darkorange;" title="critical">{$damage_delt['amount']}</span>{else}{$damage_delt['amount']}{/if}
                            </div>
                        {/foreach}
                    {elseif isset($userdata['fled']) && $userdata['failure'] != 'failure'}
                        {if $userdata['fled'] == true}
                            <div class="
                                {if $owner['team'] == $round_users[ $username ]['team'] }
                                    bracket-green">
                                {else}
                                    bracket-red">
                                {/if}
                                The Attempt was Successful.
                            </div>
                        {else}
                            <div class="
                                {if $owner['team'] != $round_users[ $username ]['team'] }
                                    bracket-green">
                                {else}
                                    bracket-red">
                                {/if}
                                The Attempt Failed.
                            </div>
                        {/if}
                    {/if}

                    {if isset($userdata['damage_over_time_delt'])}
                        {foreach $userdata['damage_over_time_delt'] as $damage_over_time_delt}
                            <div class="
                                {if $owner['team'] == $round_users[ $username ]['team'] }
                                    bracket-green">
                                {else}
                                    bracket-red">
                                {/if}
                                <strong>{$damage_over_time_delt['type']} damage over time dealt{if $damage_over_time_delt['aoe']} from aoe{/if}:</strong> {if $damage_over_time_delt['crit'] == true}<span style="color:darkorange;" title="critical">{$damage_over_time_delt['amount']}</span>{else}{$damage_over_time_delt['amount']}{/if}
                            </div>
                        {/foreach}
                    {/if}

                    {if isset($userdata['recoil']) }
                        <div class="
                            {if $owner['team'] == $round_users[ $username ]['team'] }
                                bracket-red">
                            {else}
                                bracket-green">
                            {/if}
                            
                            <strong>recoil damage:</strong>{$userdata['recoil']}
                        </div>
                    {/if}

                    {if isset($userdata['absorb'])}
                        <div class="
                            {if $owner['team'] == $round_users[ $username ]['team'] }
                                bracket-red">
                            {else}
                                bracket-green">
                            {/if}
                            
                            <strong>damage absorbed:</strong>{$userdata['absorb']}
                        </div>
                    {/if}

                    {if isset($userdata['leach']) }
                        <div class="
                            {if $owner['team'] != $round_users[ $username ]['team'] }
                                bracket-red">
                            {else}
                                bracket-green">
                            {/if}
                            
                            <strong>health leached:</strong> {$userdata['leach']}
                        </div>
                    {/if}

                    {if isset($userdata['heal_delt']) }
                        <div class="
                            {if $owner['team'] != $round_users[ $username ]['team'] }
                                bracket-red">
                            {else}
                                bracket-green">
                            {/if}
                            
                            <strong>health restored:</strong> {$userdata['heal_delt']}
                        </div>
                    {/if}

                    {if isset($userdata['heal_over_time_delt']) }
                        <div class="
                            {if $owner['team'] != $round_users[ $username ]['team'] }
                                bracket-red">
                            {else}
                                bracket-green">
                            {/if}
                            
                            <strong>health restored over time:</strong> {$userdata['heal_over_time_delt']}
                        </div>
                    {/if}

                    {if isset($userdata['reflect']) }
                        <div class="
                            {if $owner['team'] == $round_users[ $username ]['team'] }
                                bracket-red">
                            {else}
                                bracket-green">
                            {/if}
                            
                            <strong>damage reflected:</strong> {$userdata['reflect']}
                        </div>
                    {/if}

                    {if is_numeric($userdata['rob'])  && $userdata['failure'] != 'failure'}
                        <div class="
                            {if $owner['team'] == $round_users[ $username ]['team'] }
                                bracket-green">
                            {else}
                                bracket-red">
                            {/if}
                            
                            <strong>stole: </strong>{$userdata['rob']}
                        </div>
                    {elseif $userdata['rob'] == 'fail' && $userdata['failure'] != 'failure'}
                        <div class="
                            {if $owner['team'] != $round_users[ $username ]['team'] }
                                bracket-green">
                            {else}
                                bracket-red">
                            {/if}
                            
                            <strong>Failed to rob anything.</strong>
                        </div>
                    {/if}

                    {if isset($userdata['disable'])}
                        <div class="
                            {if $owner['team'] == $round_users[ $username ]['team'] }
                                bracket-green">
                            {else}
                                bracket-red">
                            {/if}
                            
                            <strong>disabled: </strong>{$userdata['disable']}
                        </div>
                    {/if}

                    {if isset($userdata['stagger']) }
                        <div class="
                            {if $owner['team'] == $round_users[ $username ]['team'] }
                                bracket-green">
                            {else}
                                bracket-red">
                            {/if}
                            
                            <strong>staggered: </strong>{$userdata['stagger']}
                        </div>
                    {/if}

                    {if isset($userdata['stun']) }
                        <div class="
                            {if $owner['team'] == $round_users[ $username ]['team'] }
                                bracket-green">
                            {else}
                                bracket-red">
                            {/if}
                            
                            <strong>stunned: </strong>{$userdata['stun']}
                        </div>
                    {/if}
                </div>
            {/foreach}
        </div>
    {/if}
{/foreach}