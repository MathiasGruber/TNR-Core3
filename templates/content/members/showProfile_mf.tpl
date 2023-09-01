<div class="page-box">
    <div class="page-title page-grid page-column-3">
        <div></div>

        <div>
            {$charInfo.username}'s Profile
        </div>

        {if $onStatus == 1}
            <div class="text-right" style="color:green">Online</div>
        {elseif isset($lastOnline)}
            <div class="text-right" style="color:red">Offline ({$lastOnline})</div>
        {else}
            <div class="text-right" style="color:red">Offline</div>
        {/if}

    </div>
    <div class="page-content">
        <div class="page-sub-title-top">
            Profile Overview
        </div>

        <div class="page-grid page-column-2">
            <div>
                <b>Character status:</b><br>
                Username: {$charInfo.username}<br>
                Gender:  {$charInfo.gender} <br>
                {$charInfo.marriedTo}   
                Level {$charInfo.level}, {$charInfo.rank}<br>         
                Village: {$charInfo.village} <br>
                {if $charInfo.village != "Syndicate"}           
                    ANBU: {$charInfo.anbu}<br>
                    Clan: {$charInfo.clan}<br>
                {/if}
                {if $charInfo.rank_id <= 2}
                    Sensei: {$charInfo.sensei}<br> 
                {/if}
                <br>
                Experience: {$charInfo.experience}<br>
                PvP Experience: {$charInfo.pvp_experience}<br>
                Bloodline: {if !empty($charInfo.bloodlineMask)}{$charInfo.bloodlineMask}{else}{$charInfo.bloodline}{/if}<br>
			    DSR: {$charInfo.DSR}<br>
            </div>

            <div>
                <b>::User picture::</b><br>
                <img src="{$charInfo.avatar}" alt="User Avatar" /><br><br>
                {if $charInfo.rank_id >= 4 && $charInfo.village != "Syndicate"}                                     
                    Student #1: {$charInfo.student_1}<br>
                    Student #2: {$charInfo.student_2}<br>
                    Student #3: {$charInfo.student_3}<br>
                {/if}
            </div>
        </div>

        {if isset($charInfo.battles_won)}
            <div class="page-sub-title">
                Profile Statistics
            </div>

            <div class="page-grid page-column-2">
                <div>
                    <b>PVP Battles</b><br>
                    Battles won: {$charInfo.battles_won} <br>
                    Battles lost: {$charInfo.battles_lost} <br>
                    Battles fled: {$charInfo.battles_fled}<br>
                    Battle draws: {$charInfo.battles_draws}<br>
                    Battles fought: {math equation="won+lost+fled+draws" won=$charInfo.battles_won lost=$charInfo.battles_lost fled=$charInfo.battles_fled draws=$charInfo.battles_draws}<br>
                    Win Percentage: <font class="{$charInfo.color}">{$charInfo.percentage}%</font><br>
                    <br>
                    <b>AI Battles</b><br>
                    Battles won: {$charInfo.AIwon} <br>
                    Battles lost: {$charInfo.AIlost} <br>
                    Battles fled: {$charInfo.AIfled} <br>
                    Battle draws: {$charInfo.AIdraw} <br>
                    Arena Record: {$charInfo.torn_record}<br>
                </div>
                <div>
                    <b>Site Support:</b><br>
                    Federal support: {$charInfo.federal}<br>
                    Federal level: {$charInfo.federal_level}<br>
                    Reputation points ever: {$charInfo.rep_ever}<br>
                    Reputation points now: {$charInfo.rep_now}<br>
                    Popularity points ever: {$charInfo.pop_ever}<br>
                    Popularity points now: {$charInfo.pop_now}<br>
                    <br>
                    <a href="?id=3&act=newMessage&amp;toUser={$charInfo.username}">Send PM</a><br>
                    <a href="?id={$smarty.get.id}&amp;page=view_nindo&amp;uid={$charInfo.id}">View Nindo</a><br>
                    <a href="?id=53&amp;act=user&amp;uid={$charInfo.id}">
                        <img style="border:none;" src="./images/report.gif" alt="Report user" width="15" height="15">
                    </a>  
                </div>
            </div>
        {/if}

        {if isset($achievements)}
            <div class="page-sub-title">
                Achievements
            </div>
            <div>
                {$achievements}
            </div>
        {/if}
        
        {if isset($loadPayPal) && $loadPayPal == 1}
            <div class="page-sub-title">
                Reputation Donation Form
            </div>
            <div>
                If you wish to donate reputation points to this user, you can do this with the forms below.
                <br/>
                <br/>
            </div>
            {$page_addon = true}
            {include file="file:{$absPath}/templates/content/rep_shop/repshop_show_mf.tpl" title="Reputation Purchases"}
        {/if}
    </div>
</div>