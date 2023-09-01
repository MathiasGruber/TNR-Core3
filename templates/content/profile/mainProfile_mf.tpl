<div class="page-box">
    <div class="page-title">
        Profile
    </div>
    <div class="page-content">

        {if isset($levelInfo)}
            <div class="page-sub-title-top">
                Character Advancement
            </div>
            <div>
                {if isset($levelInfo.href)}
                    <a href="{$levelInfo.href}">{$levelInfo.info}</a>
                {else}
                    {$levelInfo.info}
                {/if}
            </div>
        {/if}

        <div class="{if isset($levelInfo)}page-sub-title{else}page-sub-title-top{/if}">
            Overview
        </div>

        <div class="page-grid page-column-2">
            <div>
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
            </div>

            <div>
                <b>::User picture::</b><br>
                <img src="{$charInfo.avatar}" alt="User Avatar" /><br><br>
                <b>Statistics:</b><br>
                Health: <span id="curheafield">{$charInfo.cur_health}</span> / <span id="maxheafield">{$charInfo.max_health}</span> <br>
                <div style="border: 1px solid #000000; margin-bottom:10px;text-align: left;">
                    <img id="healthbar" src="./images/life_bar.jpg" style="border-right:1px solid #000000;height:5px;" width="{$charInfo.lifePerc}%" alt="HP bar"/>
                </div>
                Chakra: <span id="curchafield">{$charInfo.cur_cha}</span> / <span id="maxchafield">{$charInfo.max_cha}</span> <br>
                <div style="border: 1px solid #000000; margin-bottom:10px;text-align: left;">
                    <img id="chakrabar" src="./images/cha_bar.jpg" style="border-right:1px solid #000000;height:5px;" width="{$charInfo.chaPerc}%" alt="Chakra bar" />
                </div>
                Stamina: <span id="curstafield">{$charInfo.cur_sta}</span> / <span id="maxstafield">{$charInfo.max_sta}</span><br>
                <div style="border: 1px solid #000000;text-align: left;">
                    <img id="staminabar" src="./images/sta_bar.jpg" style="border-right:1px solid #000000;height:5px;" width="{$charInfo.staPerc}%" alt="Stamina bar" />
                </div>
                <br>   
            </div>
        </div>

        <div>
            <b>Game Time: {$charInfo.gameTime}</b>
        </div>

        <a href="?id=2&amp;load=statistics" class="expandProfile page-sub-title" onclick="return false;">⇓ Profile Statistics ⇓</a>

        <div id="loadStats">
            {if isset($charStats)}
                {if file_exists($absPath|cat:'/templates/content/profile/profileStatistics_mf.tpl')}
                    {include file="{$absPath}/templates/content/profile/profileStatistics_mf.tpl" title="CharacterStats"}
                {else}
                    {include file="{$absPath}/templates/content/profile/profileStatistics_mf.tpl" title="CharacterStats"}
                {/if}
            {/if}
        </div>

        <a href="?id=2&amp;load=timers" class="expandTimers page-sub-title" onclick="return false;">⇓ Timers ⇓</a>

        <div id="timers" class="page-grid page-column-2">
           {if isset($timers)}
                {if file_exists($absPath|cat:'/templates/content/profile/profileTimers_mf.tpl')}
                    {include file="{$absPath}/templates/content/profile/profileTimers_mf.tpl" title="CharacterTimers"}
                {else}
                    {include file="{$absPath}/templates/content/profile/profileTimers.tpl" title="CharacterTimers"}
                {/if}
            {/if}
        </div>
    </div>
</div>

<script type="text/javascript" src="files/javascript/general.js"></script>

 <script defer type="text/javascript">
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