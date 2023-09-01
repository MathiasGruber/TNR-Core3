<div align="center"><br><table width="75%" border="0" class="table" cellspacing="0" cellpadding="0">
    <tr><td colspan="2" align="center" style="border-top:none;" class="subHeader">Statistics:</td></tr>
    <tr><td width="50%" align="center">Users online: </td>
    	<td width="50%" align="center">{$users_count}</td></tr>
    <tr><td align="center">Most users ever online: </td>
        <td align="center">{$max_users}</td></tr>
    <tr><td align="center">Moderators online: </td>
        <td align="center">{$mod_count}</td></tr></table><br></div>
<div align="center"><table border="0" cellpadding="0" cellspacing="0" class="table" width="75%" id="AutoNumber1">
    <tr><td colspan="2" style="border-top:none;text-align:center;" class="subHeader">Users Online</td></tr>
    {if $user != '0 rows'}
        {for $i = 0 to ($user|@count)-1}
            <tr class="row{($i % 2) + 1}"><td width="100" colspan="2" align="center">
                <a href="?id=13&page=profile&name={$user[$i]['username']}">{$user[$i]['username']}</a></td></tr>
        {/for}
    {else}<tr><td colspan="2" align="center">No users found</td></tr>{/if}
    <tr><td align="center" style="border-top:1px solid #000000;">
            <a href="?id={$smarty.get.id}&act=users&min={$newminm}">&laquo; Previous</a></td>
        <td align="center" style="border-top:1px solid #000000;">
            <a href="?id={$smarty.get.id}&act=users&min={$newmini}">Next &raquo;</a></td></tr></table></div>