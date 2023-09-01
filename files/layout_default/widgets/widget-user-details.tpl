<div id="widget-user-details" class="widget-box lazy">

    {if isset($userRegeneration)} {$userRegeneration} {/if}

	<div id="widget-user-details-title" class="widget-title lazy {if $_COOKIE['widget-user-details-closed'] == true}closed{/if}">
		<a id="widget-user-details-title-text" href="/?id=39" Title="training page">
		    User Details
		</a>
	</div>

    <div id="widget-user-details-content" class="widget-content lazy" {if $_COOKIE['widget-user-details-closed'] == true}style="display:none;"{/if}>
        <div class="widget-user-details-legend">
            HEALTH 	
        </div>

        <div id="widget-user-details-health-bar" class="widget-user-details-bar" title="{($cur_health/$max_health*100)|string_format:"%.2f"}">
            <div id="widget-user-details-health-fill" style="width:{$health_perc}px;">
            </div>
            <div id="widget-user-details-health-text">
                {$cur_health|string_format:"%.0f"} / {$max_health|string_format:"%.0f"}
            </div>
        </div>

        <div class="widget-user-details-legend">
            CHAKRA 	
        </div>

        <div id="widget-user-details-chakra-bar" class="widget-user-details-bar" title="{($cur_cha/$max_cha*100)|string_format:"%.2f"}">
            <div id="widget-user-details-chakra-fill" style="width:{$cha_perc}px;">
            </div>
            <div id="widget-user-details-chakra-text">
                {$cur_cha|string_format:"%.0f"} / {$max_cha|string_format:"%.0f"}
            </div>
        </div>

        <div class="widget-user-details-legend">
            STAMINA 	
        </div>

        <div id="widget-user-details-stamina-bar" class="widget-user-details-bar" title="{($cur_sta/$max_sta*100)|string_format:"%.2f"}">
            <div id="widget-user-details-stamina-fill" style="width:{$sta_perc}px;">
            </div>
            <div id="widget-user-details-stamina-text">
                {$cur_sta|string_format:"%.0f"} / {$max_sta|string_format:"%.0f"}
            </div>
        </div>

        <div class="widget-user-details-legend">
            STATUS
        </div>

        <div id="widget-user-details-status">
            {$userStatus|capitalize}
        </div>

        <div class="widget-user-details-legend">
            MONEY
        </div>

        <div id="widget-user-details-money">
            {$userMoney} Ryo
        </div>

        <div class="widget-user-details-legend">
            POWER
        </div>

        <div id="widget-user-details-power">
            {$strengthFactor}
        </div>

        <div class="widget-user-details-legend">
            LOGOUT
        </div>

        <div id="widget-user-details-logout" class="count-down" data-show="Show" data-timer-seconds="{$logoutTimer}" data-callback="refreshPage"> 
            
        </div>
    </div>
</div>