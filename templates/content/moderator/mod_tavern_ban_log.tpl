<script type="text/javascript">
$(document).ready(function() {
	$('#info'+$(this).attr('id')).hide();
	$(".T_Override").click(function(){
		$('#info'+$(this).attr('id')).slideToggle('fast');
	});
});
</script>

<div align="center">
	<table class="table" width="95%">
	    <tr>
            <td colspan="5" class="subHeader">Tavern Banned Users</td>
	    </tr>
	    {if $users != "0 rows"} 
		    <tr>
			    <td width="15%" style="padding-left:5px;border-bottom:1px solid #000000;"><b>Username</b></td>
			    <td width="15%" style="border-bottom:1px solid #000000;"><b>Banned by:</b></td>
			    <td width="15%" style="border-bottom:1px solid #000000;"><b>Date:</b></td>
			    <td width="35%" style="border-bottom:1px solid #000000;"><b>Reason:</b></td>
			    <td width="20%" style="border-bottom:1px solid #000000;">&nbsp;</td>
		    </tr>
		    {for $i = 0 to ($users|@count)-1} 
			    <tr class="{cycle values="row1,row2"}" >
				    <td>{$users[$i]['username']}</td>
				    <td>{$temp[$i][0]['moderator']}</td>
				    <td>{$temp[$i][0]['time']|date_format:"%Y-%m-%d"}</td>
				    <td>{$temp[$i][0]['reason']}</td>
				    <td>
						<button class="T_Override" id="{$users[$i]['username']}">Unban Form</button>
					</td>
			    </tr>
				<tr id="info{$users[$i]['username']}" style="display:none;">
					<td colspan="5" style="padding:0px;margin:0px;">			
						<div align="center">
							<form name="TavernUnbanForm" method="post" action="">
								<table width="100%" style="padding:0px;margin:0px;border-collapse:collapse;">
								<tr>
									<td class="subHeader">Unban User: {$users[$i]['username']}</td>
								</tr>
								<tr>
									<td style="font-weight:bold;">Reason for unbanning: </td>
								</tr>
								<tr>
									<td>
									<textarea name="override_reason" rows="5" cols="35"></textarea></td>
								</tr>
								<tr>
									<td>
										<input type="submit" name="Submit_Untban" value="Unban">
										<input name="untban_time" type="hidden" value="{$temp[$i][0]['time']}">
										<input name="untban_uid" type="hidden" value="{$users[$i]['id']}">
									</td>
								</tr>
								</table>
							</form>
						</div>   
					</td>
				</tr>
		    {/for}
	    {else} 
		    <tr>
			    <td colspan="5">No users found.</td>
		    </tr>
	    {/if}
	    <tr>
		    <td colspan="2" style="border-top:1px solid #000000;text-align:left;">
			    <a href="?id={$smarty.get.id}&amp;act=tbanlog&amp;min={$newminm}">&laquo; Previous</a>
		    </td>
		    <td colspan="3" style="border-top:1px solid #000000;text-align:right;">
			    <a href="?id={$smarty.get.id}&amp;act=tbanlog&amp;min={$newmini}">Next &raquo;</a>
		    </td>
	    </tr>
        <tr>
            <td colspan="5" class="subHeader">Search for Username</td>
        </tr>
	    <tr>
		    <td colspan="5"><br>
		        <form name="Search_Tban_User" method="post" action="">
			        <input name="tban_search" type="usersearch" class="textfield" id="usersearch" />
			        <input type="submit" name="Tban_Submit" value="Search Username">
		        </form>
		    </td>
	    </tr>
	    <tr>
		    <td colspan="5">
			    <a href="?id={$smarty.get.id}">Return</a>
		    </td>
	    </tr>
	</table>
</div>