<div align="center">
      <table border="0" cellpadding="0" cellspacing="0" class="table" width="95%" >
    <tr>
        <td align="center" colspan="4" style="border-top:none;" class="subHeader">Facebook Friends Playing</td>
    </tr>
    <tr>
      <td width="100" class="tableColumns tdBorder" style="border-right:none;"><b>Real Name</b></td>
      <td width="100" class="tableColumns tdBorder" style="border-left:none;border-right:none;"><b>Username</b></td>
      <td width="100" class="tableColumns tdBorder" style="border-left:none;border-right:none;"><b>Village</b></td>
      <td width="100" class="tableColumns tdBorder" style="border-left:none;"><b>Profile Link</b></td>
    </tr>
        
    {if $DATA}
        {foreach $DATA as $entry}
            {strip}
            <tr class="{cycle values="row1,row2"}" >
              <td>{$entry.name}</td>
              <td>{$entry.username}</td>
              <td>{$entry.village}</td>
              <td><a href="?id=13&amp;page=profile&amp;name={$entry.username}">Public Profile</a></td>
            </tr>
            {/strip}
        {/foreach}
    {else}
        <tr><td colspan="4">No users found</td></tr>
    {/if}         
</table></div>

<a href="?id=82&amp;act=seeFriends&amp;min={$newminm}">&laquo; Previous</a> - 
<a href="?id=82&amp;act=seeFriends&amp;min={$newmini}">Next &raquo;</a><br>
<a href="?id=82">&laquo; Return to Facebook Panel &raquo;</a><br>
