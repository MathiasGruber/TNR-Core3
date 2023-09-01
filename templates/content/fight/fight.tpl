<!--show/hide columns-->
{$village = $settings['village']} 
{$rank = $settings['rank']}
{$activity = $settings['activity']}
{$DSR = $settings['dsr']}

<!--show/hide direction-->
{$directions = $settings['directions']}

<!--changes the color of all text to match that rows alliance color exept direction text color-->
{$name_text_color_match_alliance = $settings['name_text_color_match_alliance']}
{$all_text_color_match_alliance = $settings['all_text_color_match_alliance']}

<!--abriviates ranks down to two letters or less-->
{$rank_compress = $settings['rank_compress']}

    <!--replaces syndicate abrivates with village rank abriviations-->
    {$hide_syndicate_ranks = $settings['hide_syndicate_ranks']}

<!--hide your own row-->
{$hide_self = $settings['hide_self']}

<!--hide all allies-->
{$hide_ally = $settings['hide_ally']}
    <!--hide buttons on ally rows-->
    {$hide_betray = $settings['hide_betray']}
    {$hide_call_for_help = $settings['hide_call_for_help']}

<!--hide glimpseable rows-->
{$hide_glimpseable = $settings['hide_glimpseable']}
    <!--hide chase button-->
    {$hide_chase = $settings['hide_chase']}

{$default_colors = [
    'Ally' => '#6aa84f',
    'Self' => '#6aa84f',
    
    'Neutral' => '#3c78d8',
    
    'Enemy' => '#a61c00',
    
    'Betray' => '#a64d79',
    
    'Faint' => '#999999',
    
    'Attack-Neutral' => '#3c78d8',
    'Attack-Enemy' => '#a61c00',

    'Chase-Neutral' => '#3c78d8',
    'Chase-Enemy' => '#a61c00',

    'Help' => '#6aa84f'
    ]}

{$colors = $settings['colors']}

<div style="border: 1px solid black;position:relative;top:-10px;" width="95%">

    <table align="center" width="100%">
        <tbody>
            <tr>
                <td align="center" class="subHeader">Combat - ( {$userdata['longitude']} . {$userdata['latitude']} )</td>
            </tr>
            <tr>
                <td>
                    Links displayed under the 'Action' column are things that your character can attempt to do. 'Chase' is a less accurate version of 'Attack' that you can try from a distance. 'Betray' is an ill advised action you can take against your fellow - doing so has consequences.  'Calling for Help' allows you to respond to a distress signal of one of your fellows and join them in their current battle.
                    <br>
                    Village colors indicate a player's standing in relation to your village - war and peace time can influence this directly.  Review the 'Alliance' page for current village standings.
                    <br>
                    There are many options available for customization of this page in 'User Preferences' under 'Combat Settings'.
                </td>
            </tr>
        </tbody>
    </table>

    <table class="sortable" style="border-left:none;border-right:none" width="100%">
        <thead>
            <tr>
                <td class="tdTop">User</td>

                {if $village}
                    <td class="tdTop">Village</td>
                {/if}

                {if $rank}
                    <td class="tdTop">Rank</td>
                {/if}

                <td class="tdTop">Location</td>

                {if $activity}
                    <td class="tdTop">Activity</td>
                {/if}

                {if $DSR}
                    <td class="tdTop">DSR</td>
                {/if}

                <td class="tdTop">Action</td>
            </tr>
        </thead>
        
        
        <tbody>
            {$i = 0}
            {foreach $users as $key => $user}
                {if ($hide_self && $user['standing'] == 'Self') ||
                    ($hide_ally && $user['standing'] == 'Ally') ||
                    ($hide_glimpseable && substr( $user['action-link-type'], 0, 5 ) == 'Chase')}
                {else}
                    {$i = $i + 1}

                    <tr>
                        <td style="{if $all_text_color_match_alliance || $name_text_color_match_alliance}color:{$colors[$user['standing']]};{/if}">
                            {$user['username']}
                        </td>

                        {if $village}
                            <td style="color:{$colors[$user['standing']]};">
                                {$user['village']}
                            </td>
                        {/if}

                        {if $rank}
                            <td style="{if $all_text_color_match_alliance}color:{$colors[$user['standing']]};{/if}">
                                {if !$rank_compress}
                                    {$user['rank']}
                                {else if $user['rank'] == 'Academy Student'}
                                    AS
                                {else if $user['rank'] == 'Genin'}
                                    G
                                {else if $user['rank'] == 'Chuunin' || ($hide_syndicate_ranks && $user['rank'] == 'Lower Outlaw')}
                                    C
                                {else if $user['rank'] == 'Lower Outlaw'}
                                    LO
                                {else if $user['rank'] == 'Jounin' || ($hide_syndicate_ranks && $user['rank'] == 'Higher Outlaw')}
                                    J
                                {else if $user['rank'] == 'Higher Outlaw'}
                                    HO
                                {else if $user['rank'] == 'Elite Jounin' || ($hide_syndicate_ranks && $user['rank'] == 'Elite Outlaw')}
                                    EJ
                                {else if $user['rank'] == 'Elite Outlaw'}
                                    EO
                                {else}
                                    {$user['rank']}
                                {/if}
                            </td>
                        {/if}

                        <td style="{if $all_text_color_match_alliance}color:{$colors[$user['standing']]};{/if}">
                            {$user['longitude']} . {$user['latitude']}{if $directions}<span style="padding-left:8px;color:{$colors['Faint']};">({$user['direction']})</span>{/if}
                        </td>

                        {if $activity}
                            <td style="{if $all_text_color_match_alliance}color:{$colors[$user['standing']]};{/if}">
                                {$time = (time() + 32) - $user['last_activity']}
                                {if $user['standing'] == 'Self'}
                                    -
                                {else if $time == 0}
                                    Now
                                {else}
                                    {if $time >= 60}
                                        {floor($time / 60)}m : {$time % 60}s
                                    {else}
                                        {$time}s
                                    {/if}
                                {/if}
                            </td>
                        {/if}

                        {if $DSR}
                            <td style="{if $all_text_color_match_alliance}color:{$colors[$user['standing']]};{/if}">
                                {$user['DSR']}
                            </td>
                        {/if}

                        <td style="white-space:nowrap">
                            {if $user['standing'] == 'Self' ||
                                ($hide_chase && substr( $user['action-link-type'], 0, 5 ) == 'Chase') ||
                                ($hide_betray && $user['action-link-type'] == 'Betray') ||
                                ($hide_call_for_help && $user['action-link-type'] == 'Help')}
                                -
                            {else}
                                {$user['action-text']} {if isset($user['action-link']) && strlen($user['action-link']) > 6}<span style="color:{$colors[$user['action-link-type']]};" style="width:100%;">{$user['action-link']}</span>{/if}
                            {/if}
                        </td>
                    </tr>

                {/if}
            {/foreach}
        </tbody>
    </table>
<div>