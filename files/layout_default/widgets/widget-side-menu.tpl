<div id="widget-side-menu" class="widget-box">
    <div id="widget-side-menu-title" class="widget-title lazy {if $_COOKIE['widget-side-menu-closed'] == true}closed{/if}">
        Main Menu
    </div>
    <div id="widget-side-menu-content" class="widget-content" {if $_COOKIE['widget-side-menu-closed'] == true}style="display:none;"{/if}>
        <div id="side-menu-character"       title="Character"           class="side-menu-button lazy" data-menu="character"></div>
        <div id="side-menu-communication"   title="Communication"       class="side-menu-button lazy" data-menu="communication"></div>
        <div id="side-menu-village"         title="{$user_factionType}" class="side-menu-button lazy" data-menu="village"></div>
        <div id="side-menu-training"        title="Training & Missions" class="side-menu-button lazy" data-menu="training"></div>
        <div id="side-menu-map"             title="Map"                 class="side-menu-button lazy" data-menu="map"></div>
        <div id="side-menu-combat"          title="Combat"              class="side-menu-button lazy" data-menu="combat"></div>
        <div id="side-menu-support"         title="Support TNR"         class="side-menu-button lazy" data-menu="support"></div>
        <div id="side-menu-general"         title="Settings"            class="side-menu-button lazy" data-menu="general"></div>

        <div id="side-menu-box" class="lazy">
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
                <div class="side-menu-type-box lazy {if $_COOKIE['menu-open-tab'] == $type || (!isset($_COOKIE['menu-open-tab']) && $type == 'character')}active{/if}" id="side-menu-{$type}-box">
                    <div class="side-menu-type-box-title fancy-box dark-solid-box lazy" id="side-menu-{$type}-box-title">
                        {$header}
                    </div>

                    {if isset($menuArray[$type][0]) || ($type == 'training' && isset($menuArray['missions'][0])) }

                        {if $type == 'training'}
                            {$menuArray[$type] = array_merge($menuArray[$type], $menuArray['missions'])}
                        {/if}

                        {foreach $menuArray[$type] as $menu_item_key => $menu_item}
                            <a class="side-menu-item side-menu-link fancy-box {if $menu_item_key is even} dark-plain-box {else} dark-solid-box{/if} lazy" href="{$menu_item.link}">
                                {$menu_item.name}
                            </a>
                        {/foreach}
                    {else}
                        <!-- add sleeping or awake or location warning-->
                        {if $userStatus == "asleep"}
                            <a class="side-menu-item side-menu-link fancy-box dark-plain-box lazy" onclick="loadPage(null,'all','doSleep:wakeup');">Wake Up!<br>(N/A while asleep)</a>
                        {elseif $userStatus == "combat"}
                            <div class="side-menu-item fancy-box dark-plain-box lazy">You Are In Combat</div>
                        {else}
                            <div class="side-menu-item fancy-box dark-plain-box lazy">Currently N/A</div>
                        {/if}
                    {/if}

                    {if $type == 'general'}
                        <a href="?id=1&amp;act=logout" class="side-menu-item side-menu-link fancy-box dark-plain-box lazy">Log out</a>
                    {/if}
                </div>
            {/foreach}
        </div>

    </div>
</div>