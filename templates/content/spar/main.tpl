{if isset($people)}{$subSelect="people"}{include file="file:{$absPath}/{$people}" title="People"}{/if}
{if isset($challenges)}{$subSelect="challenges"}{include file="file:{$absPath}/{$challenges}" title="Challenges"}{/if}
{if isset($spars)}{$subSelect="spars"}{include file="file:{$absPath}/{$spars}" title="Spars"}{/if}
<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    <tr><td colspan="2" align="center" style="border-top:none;" class="subHeader">Challenge a user </td></tr>
    <tr><td width="50%" align="center" style="padding-top:5px;"><form name="form1" method="post" action="?id={$pageID}&act=challenge">
        <input type="text" name="username">&nbsp;<input type="submit" name="Submit" value="Submit"></form></td></tr></table>