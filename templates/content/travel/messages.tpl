{if isset( $travelMessages[0] )}
    <table width="100%" border="0" style="border-collapse:collapse;">
        {foreach $travelMessages as $entry}
            {strip}
            <tr class="{cycle values="row1,row2"}" >
              <td style="border-bottom:1px solid #000000;">
                  {$entry.text}{if isset($entry.linkText)}<br><a href="{$entry.href}">{$entry.linkText}</a>{/if}
              </td>
            </tr>
            {/strip}
        {/foreach}
    </table>
{/if}