<!-- quick links java script goes here -->

<div id="widget-quick-links" class="widget-box lazy">
    <div id="widget-quick-links-title" class="widget-title lazy {if $_COOKIE['widget-quick-links-closed'] == true}closed{/if}">
        <a id="widget-quick-links-title-text" href="/?id=4&act=layout_settings" title="layout settings">
		    Quick Links
		</a>
    </div>
    
    <div class="widget-content lazy {$userdata['layout_quick_links_style']}" id="widget-quick-links-content" {if $_COOKIE['widget-quick-links-closed'] == true}style="display:none;"{/if}>
        {$href = [  'anbu' => 95,
                    'clan' => 94,
                    'combat' => 50,
                    'home-inventory' => '23&act=inventory',
                    'inbox' => 3,
                    'inventory' => 11,
                    'jutsu' => 12,
                    'marriage' => 17,
                    'mission' => 121,
                    'occupation' => 36,
                    'preferences' => 4,
                    'profession' => 86,
                    'profile' => 2,
                    'quest' => 120,
                    'rob' => 49,
                    'scout' => 30,
                    'tavern' => 24,
                    'travel' => 8]}

        {if $userdata['village'] != 'Syndicate'}
            {$href['errands'] = 21}
            {$href['bank'] = 20}
        {else}
            {$href['errands'] = 22}
            {$href['bank'] = 45}
        {/if}

        {if $userdata['village'] != 'Syndicate' && $userdata['village'] == $userdata['location']}
            {$href['ramen'] = 25}
        {else if $userdata['village'] != 'Syndicate' && $userdata['village'] != $userdata['location']}
            {$href['ramen'] = 57}
        {else}
            {$href['ramen'] = 97}
        {/if}

        {if $userdata['rank_id'] == 1}
            {$href['training'] = 18}
        {else if $userdata['rank_id'] == 2}
            {$href['training'] = 29}
        {else if $userdata['rank_id'] >= 3}
            {$href['training'] = 39}
        {/if}

        {$quick_links = json_decode($userdata['layout_quick_links'], true)}

        {for $i=1 to 9}
            {if $userdata['layout_quick_links_style'] == 'text'}
                <a href="/?id={$href[$quick_links['quick-link-'|cat:$i]]}" class="widget-quick-links-link widget-quick-link-{$quick_links['quick-link-'|cat:$i]} fancy-box {if !($i % 2)}dark-plain-box{else}dark-solid-box{/if}" id="widget-quick-link-{$quick_links['quick-link-'|cat:$i]}">{$quick_links['quick-link-'|cat:$i]}</a>
            {else}
                <a href="/?id={$href[$quick_links['quick-link-'|cat:$i]]}" class="widget-quick-links-link widget-quick-link-{$quick_links['quick-link-'|cat:$i]} ui-button" id="widget-quick-link-{$quick_links['quick-link-'|cat:$i]}" title="{$quick_links['quick-link-'|cat:$i]}"></a>
            {/if}
        {{/for}}
    </div>
</div>