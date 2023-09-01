{if isset($timers)}
    {foreach $timers as $timer}
        <div>{$timer.name}:</div>
        <div>{$timer.time}</div>
    {/foreach}
{/if}
