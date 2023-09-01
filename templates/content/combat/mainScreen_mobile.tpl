<tr>
    <td contentFit="true">
        
        {if $userList}
            {foreach $userList as $entry}
                <tr>
                    <td>
                        {if $entry['avatar'] == "AI"}
                            <text><b>AI</b></text>
                        {else}
                            <img src="{$entry['avatar']}"></img>
                        {/if}
                    </td>
                    <td>

                        <!-- Set the texts -->
                        <text><b>{$entry['name']['text']}</b>
                        <br>{$entry['rank']}{if !isset($entry['chaperc']) && !isset($entry['staperc'])}, {$entry['village']}{/if}
                        </text>

                        {if isset($entry['lifeperc'])} <bar curVal="{$entry.cur_health}" maxVal="{$entry.max_health}" title="" showValues="false" color="red" height="40"></bar> {/if}
                        {if isset($entry['chaperc'])} <bar curVal="{$entry.cur_cha}" maxVal="{$entry.max_cha}" title="" showValues="false" color="blue" height="40"></bar> {/if}
                        {if isset($entry['staperc'])} <bar curVal="{$entry.cur_sta}" maxVal="{$entry.max_sta}" title="" showValues="false" color="green" height="40"></bar> {/if}

                    </td>
                </tr>
            {/foreach}
        {else}
            <tr><td><text>Nobody Active</text></td></tr>
        {/if} 
        
    </td>  
    
    <td contentFit="true">
        
        {if $opponentList}
            {foreach $opponentList as $entry}
                <tr>
                    <td>
                        {if $entry['avatar'] == "AI"}
                            <text><b>AI</b></text>
                        {else}
                            <img src="{$entry['avatar']}"></img>
                        {/if}
                    </td>
                    <td>

                        <!-- Set the texts -->
                        <text><b>{$entry['name']['text']}</b>
                        <br>{$entry['rank']}{if !isset($entry['chaperc']) && !isset($entry['staperc'])}, {$entry['village']}{/if}
                        </text>
                        
                        {if isset($entry['lifeperc'])} <bar curVal="{$entry.cur_health}" maxVal="{$entry.max_health}" title="" showValues="false" color="red" height="40"></bar> {/if}
                        {if isset($entry['chaperc'])} <bar curVal="{$entry.cur_cha}" maxVal="{$entry.max_cha}" title="" showValues="false" color="blue" height="40"></bar> {/if}
                        {if isset($entry['staperc'])} <bar curVal="{$entry.cur_sta}" maxVal="{$entry.max_sta}" title="" showValues="false" color="green" height="40"></bar> {/if}
                        
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr><td><text>Nobody Active</text></td></tr>
        {/if} 
        
    </td>
</tr>
