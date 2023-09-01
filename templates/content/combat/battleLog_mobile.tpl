{if isset($battleLog[1])}  
    <tr>
        <td>
            {if isset($battleLogTitle)}
                <a contentControl="battleLog">{$battleLogTitle}</a>
            {else}
                <a contentControl="battleLog">Battle Log</a>
            {/if}    
        </td>
    </tr>

    <contentElement name="battleLog">              
        <tr color="yellow">
            <td>
                <text>{if isset( $battleLog[1][0]['time'] )}{$battleLog[1][0]['time']|date_format:"h:i A"} - {/if}<b>Round {count($battleLog)}</b></text>
            </td>
        </tr>
        {foreach $battleLog[count($battleLog)] as $entry}
            {if $entry['type'] == "main"}

                <tr color="darkgrey">
                    <td>
                        <text>{$entry['message']}</text>
                    </td>
                </tr>

                {if $entry['tempLongMessage'] !== ""}
                    <tr color="grey">
                        <td>
                            <text><b>Description: </b> <i>{$entry['tempLongMessage']}</i></text>
                        </td>
                    </tr>
                {/if}
            {elseif $entry['type'] == "subEntry"}
                <tr color="{$entry['cssClass']}">
                    <td>
                        <text>- {$entry['message']}</text>
                    </td>
                </tr>
            {/if}
        {/foreach}
    </contentElement>
{/if}