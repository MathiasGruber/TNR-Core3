{if isset($memberList)}
    {$subSelect="memberList"}
    {if file_exists($absPath|cat:'/'|cat:str_replace('.tpl','_mf.tpl',$memberList)) && $mf=='yes'}
        {include file="file:{$absPath}/{str_replace('.tpl','_mf.tpl',$memberList)}" title="MemberList"}       
    {else}
        {$full_page=true}
        {include file="file:{$absPath}/{$memberList}" title="MemberList"}       
    {/if}
    <div class="page-content-addon">  
        <div class="table-grid table-column-6 page-sub-title-top">
            <div>Sort by Rank:</div>                                     
            <a href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=1"  >Student</a>
            <a href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=2"  >Genin</a>
            <a href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=3"  >Chuunin</a>
            <a href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=4"  >Jounin</a>
            <a href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=5"  >Elite Jounin</a>
        </div>
    </div>
    <div class="page-content-addon">
        {include file="file:{$absPath}/templates/content/facebook/inviteTable.tpl" title="FB Menu"} 
    </div>
{/if}
