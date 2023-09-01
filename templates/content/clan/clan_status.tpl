<div align="center">
    {include file="file:{$absPath}/templates/content/clan/clan_info.tpl" title="Clan Information"}
   
    {if isset($clanMembers)}
        {$subSelect="clanMembers"}
        {include file="file:{$absPath}/{$clanMembers}" title="Clan Members"}
    {/if}
</div>