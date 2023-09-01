<table width="100%" cellpadding="0" cellspacing="0" border="0">
    {if isset($timers)}
        {foreach $timers as $timer}
            <tr>
                <td style="text-align: right; width: 50%; font-weight: 700;">{$timer.name}:</td>
                <td style="text-align: left">{$timer.time}</td>
            </tr>
        {/foreach}
    {/if}
</table>