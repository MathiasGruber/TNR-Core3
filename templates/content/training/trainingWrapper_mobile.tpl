<tr>
    <td>
        <headerText>Training Overview</headerText>
    </td>
</tr>
<tr>
    <td>
<text>Rank: Lvl. {$user['level']} {$user['rank']}<br>
<br>
Taijutsu str: {$user['tai_off']}<br>
Ninjutsu str: {$user['nin_off']}<br>
Genjutsu str: {$user['gen_off']}<br>
Bukijutsu str: {$user['weap_off']}<br>
<br>
Primary element: {$user['primary_element_mastery']}<br>
Secondary element: {$user['secondary_element_mastery']}<br>
</text>

<!-- Mobile Notifications -->
{if $user.cur_sta < $user.max_sta && $currentRegenRate > 0}
    <mobileNotification notificationID="1" title="Stamina Restored" time="{ceil(($user.max_sta-$user.cur_sta)/($currentRegenRate/60))}">Your stamina pool has been fully restored.</mobileNotification>
{/if}
{if $user.cur_cha < $user.max_cha && $currentRegenRate > 0}
    <mobileNotification notificationID="2" title="Chakra Restored" time="{ceil(($user.max_cha-$user.cur_cha)/($currentRegenRate/60))}">Your chakra pool has been fully restored.</mobileNotification>
{/if}
<!-- Mobile Notifications End -->

    </td>
    <td>
        <img src="{$avatar}" title="userAvatar" />
    </td>
</tr>
<tr>
    <td>
<text>Taijutsu def: {$user['tai_def']}<br>
Ninjutsu def: {$user['nin_def']}<br>
Genjutsu def: {$user['gen_def']}<br>
Bukijutsu def: {$user['weap_def']}</text>
    </td>
    <td>
<text>Strength: {$user['strength']}<br>
Intelligence: {$user['intelligence']}<br>
Willpower: {$user['willpower']}<br>
Speed: {$user['speed']}    
</text>
    </td>
</tr>
{if isset($release)}
<tr>
    <td>
        <text>Time until release from jail:</text> {$release}
    </td>
</tr>
{/if}
<br>

{if isset($wrapLoad)}
    {if isset($apiCall) && $apiCall == true}
        {include file="file:{$absPath}/{$wrapLoad|replace:'.tpl':'_mobile.tpl'}" title="Training options"}
    {else}
        {include file="file:{$absPath}/{$wrapLoad}" title="Training options"}
    {/if}
{/if}

