<div class="page-box">
    <div class="page-title">
        Clan Status
    </div>
    <div class="page-content">
        {include file="file:{$absPath}/templates/content/clan/clan_info_mf.tpl" title="Clan Information"}
   
        {if isset($clanMembers)}
            {$subSelect="clanMembers"}
            {include file="file:{$absPath}/{$clanMembers}" title="Clan Members"}
        {/if}
    </div>
</div>
