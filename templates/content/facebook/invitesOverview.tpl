
<div align="center">
      <table border="0" cellpadding="0" cellspacing="0" class="table" width="95%" >
    <tr>
        <td align="center" colspan="2" style="border-top:none;" class="subHeader">Facebook Invites</td>
    </tr>
    <tr>
      <td width="100" class="tableColumns tdBorder" style="border-right:none;"><b>Real Name</b></td>
      <td width="100" class="tableColumns tdBorder" style="border-left:none;border-right:none;"><b>Invite Status</b></td>
    </tr>

    {if $friends}
        {foreach $friends as $friend}
            {strip}
            <tr class="{cycle values="row1,row2"}" >
              <td>{$friend.name}</td>
              <td>
                  {if $friend.status == ""}
                      Pending Signup
                  {elseif $friend.status == "Rewarded"}
                      Accepted, 2 pop points rewarded!
                  {elseif $friend.status == "IpDeny"}
                      Identical IPs = no reward
                  {elseif $friend.status == "Error"}
                      Error, no points uploaded
                  {/if}
              </td>
            </tr>
            {/strip}
        {/foreach}
    {else}
        <tr><td colspan="2">No facebook invites found. You cannot invite people who have already connect Facebook to TNR.</td></tr>
    {/if}  
</table></div>
<a href="?id=82&amp;act=overView&amp;min={$newminm}">&laquo; Previous</a> - 
<a href="?id=82&amp;act=overView&amp;min={$newmini}">Next &raquo;</a><br>
<a href="?id=82">&laquo; Return to Facebook Panel &raquo;</a><br>
