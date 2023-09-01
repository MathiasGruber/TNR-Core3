<div id="widget-mobile-menu" style="display:none;">
    
    <div id="mobile-menu-character"       title="Character"           class="mobile-menu-button lazy" data-menu="character"></div>
    <div id="mobile-menu-communication"   title="Communication"       class="mobile-menu-button lazy" data-menu="communication"></div>
    <div id="mobile-menu-village"         title="{$user_factionType}" class="mobile-menu-button lazy" data-menu="village"></div>
    <div id="mobile-menu-training"        title="Training & Missions" class="mobile-menu-button lazy" data-menu="training"></div>
    <div id="mobile-menu-map"             title="Map"                 class="mobile-menu-button lazy" data-menu="map"></div>
    <div id="mobile-menu-combat"          title="Combat"              class="mobile-menu-button lazy" data-menu="combat"></div>
    <div id="mobile-menu-support"         title="Support TNR"         class="mobile-menu-button lazy" data-menu="support"></div>
    <div id="mobile-menu-general"         title="Settings"            class="mobile-menu-button lazy" data-menu="general"></div>

    <div id="mobile-menu-box" class="lazy">
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
            <!--<div class="mobile-menu-type-box-title" id="mobile-menu-{$type}-box-title" {if $type != 'character'}style="display:none;"{/if}>
                {$header}
            </div>-->

            <div class="mobile-menu-type-box {if $_COOKIE['menu-open-tab'] == $type || (!isset($_COOKIE['menu-open-tab']) && $type == 'character')}active{/if}" id="mobile-menu-{$type}-box">
                {if isset($menuArray[$type][0]) || ($type == 'training' && isset($menuArray['missions'][0])) }

                    {if $type == 'training'}
                        {$menuArray[$type] = array_merge($menuArray[$type], $menuArray['missions'])}
                    {/if}

                    {foreach $menuArray[$type] as $menu_item_key => $menu_item}
                        <a class="mobile-menu-item mobile-menu-link fancy-box {if $menu_item_key is even} {else} dark-solid-box{/if} lazy" href="{$menu_item.link}">
                            {str_replace('Tool:','T:',str_replace('Moderator:','M:',str_replace('Control Panel:','CP:',$menu_item.name)))}
                        </a>
                    {/foreach}
                {else}
                    <!-- add sleeping or awake or location warning-->
                    {if $userStatus == "asleep"}
                        <a class="mobile-menu-item mobile-menu-link fancy-box lazy" onclick="loadPage(null,'all','doSleep:wakeup');">Wake Up!<br>(N/A while asleep)</a>
                    {elseif $userStatus == "combat"}
                        <div class="mobile-menu-item fancy-box lazy">You Are In Combat</div>
                    {else}
                        <div class="mobile-menu-item fancy-box lazy">Currently N/A</div>
                    {/if}
                {/if}

                {if $type == 'general'}
                    <a href="?id=1&amp;act=logout" class="mobile-menu-item mobile-menu-link fancy-box lazy">Log out</a>
                {/if}
            </div>
        {/foreach}
        <!--<div id="mobile-menu-return-button" class="mobile-menu-button"></div>-->
    </div>

</div>