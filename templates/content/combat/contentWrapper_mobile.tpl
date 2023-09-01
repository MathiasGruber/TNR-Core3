{if isset($topScreen) && !empty($topScreen)}
    {include file="file:{$absPath}/{$topScreen|replace:'.tpl':'_mobile.tpl'}" title="Main Screen"}
 {/if}

{if isset($optionalScreen) && !empty($optionalScreen)}
    {include file="file:{$absPath}/{$optionalScreen|replace:'.tpl':'_mobile.tpl'}" title="Optional Screen"}
 {/if}

 <bg color="white">
{if isset($tertiaryScreen) && !empty($tertiaryScreen)}
    {include file="file:{$absPath}/{$tertiaryScreen|replace:'.tpl':'_mobile.tpl'}" title="Tertiary Screen"}
{/if}
{if isset($secondaryScreen) && !empty($secondaryScreen)}
    {include file="file:{$absPath}/{$secondaryScreen|replace:'.tpl':'_mobile.tpl'}" title="Secondary Screen"}
{/if}
</bg>