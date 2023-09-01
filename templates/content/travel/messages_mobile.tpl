{if isset( $travelMessages[0] )}   
    {foreach $travelMessages as $entry}
        {strip}
        <tr color="{cycle values="clear,dim"}" >
          <td>
              <text>{$entry.text}</text>
              {if isset($entry.linkText)}<a href="{$entry.href}">{$entry.linkText}</a>{/if}
          </td>
        </tr>
        {/strip}
    {/foreach}
{/if}