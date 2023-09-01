{if isset($levelInfo)}
    <tr>
        <td><headerText>Character Advancement</headerText></td>
    </tr>
    <tr>
        <td>
            {if isset($levelInfo.href)}
                <button href="{$levelInfo.href}">{$levelInfo.info}</button>
            {else}
                <text>{$levelInfo.info}</text>
            {/if}
        </td>
    </tr>
{/if}

<tr>
    <td>
        <headerText>Profile Overview</headerText>
    </td>
</tr>
<tr>
    <td>        
<text stripTags="true">Level {$charInfo.level}, {$charInfo.rank}<br>
Village: {$charInfo.village} <br>
Money: {$charInfo.money} <br>
Banked: {$charInfo.bank}<br>
ANBU: {$charInfo.anbu}<br>
Clan: {$charInfo.clan}<br>
{if isset($charInfo.sensei) && $charInfo.sensei !== "None"}
    Sensei: {$charInfo.sensei}<br>
{/if}
<br>
Experience: {$charInfo.experience}<br>
Exp needed: {$charInfo.required_exp}<br>
PvP Experience: {$charInfo.pvp_experience}<br>
PvP Streak: {$charInfo.pvp_streak}<br>
DSR: {$charInfo.DSR}<br>
Bloodline: {if !empty($charInfo.bloodlineMask)}{$charInfo.bloodlineMask}{else}{$charInfo.bloodline}{/if}<br>

{if isset($charInfo.elemental_master_1) && $charInfo.elemental_master_1 !== ""}
<br>Primary affinity: {$charInfo.elemental_master_1}<br>
{if isset($charInfo.elemental_master_2) && $charInfo.elemental_master_2 !== ""}
Secondary affinity: {$charInfo.elemental_master_2}<br>
{/if}
{if isset($charInfo.specialAffinity) && $charInfo.specialAffinity !== ""}
Special affinity: {$charInfo.specialAffinity}<br>
{/if}
{/if}
<br>
Status: {$charInfo.status} <br>
Total Regen: {$charInfo.regen_data.Show}<br>
{if isset($charInfo.regen_data.PVP)}
<i>-- PVP Bonus: +{$charInfo.regen_data.PVP}%</i><br>
{if isset($charInfo.regen_data.battleRegen)}
<i>--Combat Bonus: -{$charInfo.regen_data.battleRegen}%</i><br>
{/if}
{/if}
Login Streak: {$charInfo.login_streak} days<br>
</text>
    </td>
    <td>
        <img src="{$charInfo.avatar}" title="userAvatar" /><br>
        <bar refillSpeed="{$charInfo.regen_data.per_second}" curVal="{$charInfo.cur_health}" maxVal="{$charInfo.max_health}" title="Health" showValues="true" color="solidred"></bar><br>
        <bar refillSpeed="{$charInfo.regen_data.per_second}" curVal="{$charInfo.cur_cha}" maxVal="{$charInfo.max_cha}" title="Chakra" showValues="true" color="solidblue"></bar><br>
        <bar refillSpeed="{$charInfo.regen_data.per_second}" curVal="{$charInfo.cur_sta}" maxVal="{$charInfo.max_sta}" title="Stamina" showValues="true" color="solidgreen"></bar><br>                                               
    </td>
</tr>

<!-- Mobile Notifications -->
{if $charInfo.cur_sta < $charInfo.max_sta && $charInfo.regen_data.per_second > 0}
    <mobileNotification notificationID="1" title="Stamina Restored" time="{ceil(($charInfo.max_sta-$charInfo.cur_sta)/$charInfo.regen_data.per_second)}">Your stamina pool has been fully restored.</mobileNotification>
{/if}
{if $charInfo.cur_cha < $charInfo.max_cha && $charInfo.regen_data.per_second > 0}
    <mobileNotification notificationID="2" title="Chakra Restored" time="{ceil(($charInfo.max_cha-$charInfo.cur_cha)/$charInfo.regen_data.per_second)}">Your chakra pool has been fully restored.</mobileNotification>
{/if}
<!-- Mobile Notifications End -->

{if isset($charStats)}
    {include file="{$absPath}/templates/content/profile/profileStatistics_mobile.tpl" title="CharacterStats"}
{/if}

{if isset($teacherURL)}
    <teacherNinja name="tutorialNinja" href="{$teacherURL}"></teacherNinja>
{/if}