<div id="widget-popup-quick-links-wrapper">
    <div id="widget-popup-quick-links">

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

        {$mobile_quick_links = json_decode($userdata['layout_mobile_quick_links'], true)}

        {for $i=1 to 5}
            <a href="/?id={$href[$mobile_quick_links['mobile-quick-link-'|cat:$i]]}" class="widget-popup-quick-link ui-button widget-popup-quick-link-{$mobile_quick_links['mobile-quick-link-'|cat:$i]}" id="widget-popup-quick-link-{$i}"></a>
        {{/for}}
    </div>
</div>