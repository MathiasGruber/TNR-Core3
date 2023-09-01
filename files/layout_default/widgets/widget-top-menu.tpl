<div id="widget-top-menu">
    <div id="top-menu-character"       title="Character"           class="top-menu-button lazy" data-menu="character"></div>
    <div id="top-menu-communication"   title="Communication"       class="top-menu-button lazy" data-menu="communication"></div>
    <div id="top-menu-village"         title="{$user_factionType}" class="top-menu-button lazy" data-menu="village"></div>
    <div id="top-menu-training"        title="Training & Missions" class="top-menu-button lazy" data-menu="training"></div>
    <div id="top-menu-map"             title="Map"                 class="top-menu-button lazy" data-menu="map"></div>
    <div id="top-menu-combat"          title="Combat"              class="top-menu-button lazy" data-menu="combat"></div>
    <div id="top-menu-support"         title="Support TNR"         class="top-menu-button lazy" data-menu="support"></div>
    <div id="top-menu-general"         title="Settings"            class="top-menu-button lazy" data-menu="general"></div>

    <div id="top-menu-box" class="lazy">
        {$menu_types = ['character'     => 'Character',
                        'communication' => 'Communication',
                        'village'       => $user_factionType,
                        'training'      => 'Training & Missions',
                        'map'           => 'Map',
                        'combat'        => 'Combat',
                        'support'       => 'Support TNR',
                        'general'       => 'Settings'
        ]}

        {foreach $menu_types as $type => $header}
            <div class="top-menu-type-box lazy {if $_COOKIE['menu-open-tab'] == $type || (!isset($_COOKIE['menu-open-tab']) && $type == 'character')}active{/if}" id="top-menu-{$type}-box">
                <div class="top-menu-type-box-title" id="top-menu-{$type}-box-title">
                    {$header}
                </div>

                {if isset($menuArray[$type][0]) || ($type == 'training' && isset($menuArray['missions'][0])) }

                    {if $type == 'training'}
                        {$menuArray[$type] = array_merge($menuArray[$type], $menuArray['missions'])}
                    {/if}

                    {foreach $menuArray[$type] as $menu_item_key => $menu_item}
                        <a class="top-menu-item top-menu-link lazy" href="{$menu_item.link}">
                            {$menu_item.name}
                        </a>
                    {/foreach}
                {else}
                    <!-- add sleeping or awake or location warning-->
                    {if $userStatus == "asleep"}
                        <a class="top-menu-item top-menu-link lazy" onclick="loadPage(null,'all','doSleep:wakeup');">Wake Up!<br>(N/A while asleep)</a>
                    {elseif $userStatus == "combat"}
                        <div class="top-menu-item lazy">You Are In Combat testing</div>
                    {else}
                        <div class="top-menu-item lazy">Currently N/A</div>
                    {/if}
                {/if}

                {if $type == 'general'}
                    <a href="?id=1&amp;act=logout" class="top-menu-item top-menu-link lazy">Log out</a>
                {/if}
            </div>
        {/foreach}
    </div>
</div>