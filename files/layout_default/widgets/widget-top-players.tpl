<div id="widget-top-players" class="widget-box lazy">
    <div id="widget-top-players-title" class="widget-title lazy">
        Top Players
    </div>
    
    <div class="widget-content lazy" id="widget-top-players-content">
        {if isset($topPlayers)}
            {foreach $topPlayers as $key => $item}
                <div class="widget-top-players-box fancy-box {if $key % 2 == 0}solid-box{else}plain-box{/if}">
                    <div class="widget-top-players-numbers">
                        #{($key+1)}
                    </div>
                    <div>
                        <b class="widget-top-players-name">{$item.username}</b><br>
                        <i class="widget-top-players-rank">{$item.rank}</i><br>
                        <i class="widget-top-players-exp">{$item.pvp_experience} PvP Exp</i>
                    </div>
                </div>
            {/foreach}
        {/if}
    </div>
</div>