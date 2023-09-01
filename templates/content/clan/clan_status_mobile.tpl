{include file="file:{$absPath}/templates/content/clan/clan_info_mobile.tpl" title="Clan Information"}

{if isset($clanMembers)}
    {$subSelect="clanMembers"}
    {include file="file:{$absPath}/{$clanMembers|replace:'.tpl':'_mobile.tpl'}" title="Clan Members"}
{/if}