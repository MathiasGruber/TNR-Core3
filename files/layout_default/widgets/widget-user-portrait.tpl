<div id="widget-user-portrait" style="text-shadow: 0px 0px 1px #000 !important;" class="widget-box lazy">
	<div id="widget-user-portrait-title" class="widget-title lazy {if $_COOKIE['widget-user-portrait-closed'] == true}closed{/if}" >
		<a id="widget-user-portrait-title-text" style="color:{$userColor} !important;" href="/?id=2" title="profile page">
		    {$user_name}
		    <br>
            {if strpos($user_rank, '<br>') !== false }
                {str_replace("<br>"," ",$user_rank)}<br>
            {else}
                {$user_rank}
            {/if}
		     of {$user_village}
		</a>
	</div>
    <div id="widget-user-portrait-content" class="widget-content lazy" {if $_COOKIE['widget-user-portrait-closed'] == true}style="display:none;"{/if}>
        <div id="widget-user-portrait-avatar" class="rank-{$userdata['rank_id']}" style="background-image:url({$user_avatar});"></div>
         
        <div id="widget-user-portrait-badge">
            <div id="widget-user-portrait-rank">
                {$user_rank}
            </div>
        </div>
    </div>
</div>