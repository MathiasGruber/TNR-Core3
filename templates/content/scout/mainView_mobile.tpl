{if isset($resources)}
    {$subSelect="resources"}
    {include file="file:{$absPath}/{$resources|replace:'.tpl':'_mobile.tpl'}" title="Nearby resources"}
{/if}

{if isset($users)}
    {$subSelect="users"}
    {include file="file:{$absPath}/{$users|replace:'.tpl':'_mobile.tpl'}" title="Nearby Users"}
{/if}
