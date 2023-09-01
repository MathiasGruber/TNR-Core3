{if isset($memberList)}
    {$subSelect="memberList"}
    {include file="file:{$absPath}/{$memberList|replace:'.tpl':'_mobile.tpl'}" title="MemberList"}
{/if}
