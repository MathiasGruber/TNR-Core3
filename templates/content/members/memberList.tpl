{if isset($memberList)}
    <div align="center">
        {$subSelect="memberList"}
        {include file="file:{$absPath}/{$memberList}" title="MemberList"}       
        Sort by Rank:                                                  
        <a class="showTableTopLink" href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=1"  >Student</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a class="showTableTopLink" href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=2"  >Genin</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a class="showTableTopLink" href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=3"  >Chuunin</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a class="showTableTopLink" href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=4"  >Jounin</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a class="showTableTopLink" href="?id={$smarty.get.id}&amp;act={$smarty.get.act}&amp;rank=5"  >Elite Jounin</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </div><br><br>
{/if}

{include file="file:{$absPath}/templates/content/facebook/inviteTable.tpl" title="FB Menu"} 