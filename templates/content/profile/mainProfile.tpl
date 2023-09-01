{if isset($levelInfo)}
<table class="table" style="width:95%;" >
    <tr>
        <td class="subHeader">Character Advancement</td>
    </tr>
    <tr>
        <td style="font-size:18px;font-weight:bold;">
            {if isset($levelInfo.href)}
                <a href="{$levelInfo.href}">{$levelInfo.info}</a>
            {else}
                {$levelInfo.info}
            {/if}
        </td>
    </tr>
</table>            
{/if}



<table class="table" style="width:95%;" >
<tr>
    <td colspan="2" class="subHeader">Profile Overview</td>
</tr>
<tr>
    <td style="text-align:left;padding:10px;width:50%;">
        <b>Character status:</b><br>
        Level {$charInfo.level}, {$charInfo.rank}<br>
        Village: {$charInfo.village} <br>
        Money: {$charInfo.money} <br>
        Banked: {$charInfo.bank}<br>
        ANBU: {$charInfo.anbu}<br>
        Clan: {$charInfo.clan}<br>
        {if isset($charInfo.sensei) && $charInfo.sensei !== "None"}
            Sensei: {$charInfo.sensei}<br>
        {/if}
		{if isset($join_date)}
			Join Date: {$join_date}<br>
		{/if}
        <br>
        Experience: {$charInfo.experience}<br>
        Exp needed: {$charInfo.required_exp}<br>
        PvP Experience: {$charInfo.pvp_experience}<br>
        PvP Streak: {$charInfo.pvp_streak}<br>
		DSR: {$charInfo.DSR}<br>
        Bloodline: {if !empty($charInfo.bloodlineMask)}{$charInfo.bloodlineMask}{else}{$charInfo.bloodline}{/if}<br>
        <br>
        Status: {$charInfo.status} <br>
        Total Regeneration: {$charInfo.regen_data.Show} {$charInfo.regen_data.Timer}<br>
        {if isset($charInfo.regen_data.PVP)}
        <i>-- Including PVP Bonus: <font class='green'>+{$charInfo.regen_data.PVP}% to base regen</font></i><br>
        {/if}
        Login Streak: {$charInfo.login_streak} days<br>
        <i>-- Next:  {$charInfo.loginStreakTimer}</i><br>
        {if isset($charInfo.regen_data.battleRegen)}
            Combat modification: <font class='red'>-{$charInfo.regen_data.battleRegen}% to total regen</font><br>
        {/if}
        <br>
		{if	isset($charInfo.bloodline_offense)}
			Offense: <b>{$charInfo.bloodline_offense}</b><br>
		{/if}
        {if isset($charInfo.element_affinity_1) && $charInfo.element_affinity_1 !== ""}
            Primary affinity: <b>{$charInfo.element_affinity_1}</b><br>
            {if isset($charInfo.element_affinity_2) && $charInfo.element_affinity_2 !== ""}
                Secondary affinity: <b>{$charInfo.element_affinity_2}</b><br>
            {/if}
            {if isset($charInfo.element_affinity_special) && $charInfo.element_affinity_special !== ""}
                Special affinity: <b>{$charInfo.element_affinity_special}</b>
            {/if}
        {/if}
    </td>
    <td valign="top" style="text-align:left;padding:15px;width:50%;border-left:1px solid">
        <b>::User picture::</b><br>
        <img src="{$charInfo.avatar}" alt="User Avatar" /><br><br>
        <b>Statistics:</b><br>
            Health: <span id="curheafield">{$charInfo.cur_health}</span> / <span id="maxheafield">{$charInfo.max_health}</span> <br>
            <div style="height:5px; width:200px; border: 1px solid #000000; margin-bottom:10px;">
                <img id="healthbar" src="./images/life_bar.jpg" style="border-right:1px solid #000000;height:5px;" width="{$charInfo.lifePerc}%" alt="HP bar"/>
            </div>
            Chakra: <span id="curchafield">{$charInfo.cur_cha}</span> / <span id="maxchafield">{$charInfo.max_cha}</span> <br>
            <div style="height:5px; width:200px; border: 1px solid #000000; margin-bottom:10px;">
                <img id="chakrabar" src="./images/cha_bar.jpg" style="border-right:1px solid #000000;height:5px;" width="{$charInfo.chaPerc}%" alt="Chakra bar" />
            </div>
            Stamina: <span id="curstafield">{$charInfo.cur_sta}</span> / <span id="maxstafield">{$charInfo.max_sta}</span><br>
            <div style="height:5px; width:200px; border: 1px solid #000000;">
                <img id="staminabar" src="./images/sta_bar.jpg" style="border-right:1px solid #000000;height:5px;" width="{$charInfo.staPerc}%" alt="Stamina bar" />
            </div>
            <br>                                      
    </td>
</tr>
</table>

<font size="+1"><b>Game Time: {$charInfo.gameTime}</b></font><br><br>

 <script type="text/javascript">
     $(document).ready(function() {    

        // Hide initially
        $("#timers").hide();
        $("#loadStats").hide();
        
        // Location of the backend file
        var loadUrl = "./ajaxLibs/mainBackend.php";

        // An array of the link-classes to which we want to attach the backend
        var linkClasses = new Array( ".expandProfile",".expandTimers" ); //
        var submitClasses = new Array();

        // Setup the backend system
        setupBackend( 
            loadUrl, 
            linkClasses, 
            submitClasses, 
            "profileBackend", 
            "&token={$pageToken}&uid={$smarty.session.uid}&id={$smarty.get.id}" 
        ); 
    });
 </script>

<table class="table" style="width:95%;" >
    <tr>
        <td colspan="2" class="subHeader" style="padding-left:20px;padding-right:20px;border-bottom:0px;">
            <a href="?id=2&amp;load=statistics" style="float:left;"  class="expandProfile" onclick="return false;">&dArr;</a>
            <a href="?id=2&amp;load=statistics"                      class="expandProfile" onclick="return false;">Profile Statistics</a>
            <a href="?id=2&amp;load=statistics" style="float:right;" class="expandProfile" onclick="return false;">&dArr;</a>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding:0px;">
            <div id="loadStats">
            {if isset($charStats)}
                {include file="{$absPath}/templates/content/profile/profileStatistics.tpl" title="CharacterStats"}
            {/if}
            </div>
        </td>
    </tr>
</table>

<table class="table" style="width:95%;" >
    <tr>
        <td colspan="2" class="subHeader" style="padding-left:20px;padding-right:20px;border-bottom:0px;">
            <a href="?id=2&amp;load=timers" style="float:left;"  class="expandTimers" onclick="return false;">&dArr;</a>
            <a href="?id=2&amp;load=timers"                      class="expandTimers" onclick="return false;">Timers</a>
            <a href="?id=2&amp;load=timers" style="float:right;" class="expandTimers" onclick="return false;">&dArr;</a>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding:0px;">
            <div id="timers">
               {if isset($timers)}
                    {include file="{$absPath}/templates/content/profile/profileTimers.tpl" title="CharacterTimers"}
                {/if}
            </div>
        </td>
    </tr>
</table>
