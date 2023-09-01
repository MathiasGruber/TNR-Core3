<div align="center" style="display:none;" id="div_tavernban">
	<form name="TavernBanForm" method="post" action="">
    	<table width="95%" class="table">
			<tr>
				<td class="subHeader" colspan="2">Tavern Ban User</td>
			</tr>
			<tr>
				<td width="30%" style="text-align:right;">Username:</td>
				<td width="70%" style="text-align:left;"><input name="user_tban" type="text" id="user_tban"></td>
			</tr>
			<tr>
				<td style="text-align:right;">Length:</td>
				<td style="text-align:left;">
					<select name="tavern_ban_time">
						<option>1 Hour</option>
						<option>12 Hours</option>
						<option>1 Day</option>
						<option>3 Days</option>
						<option>1 Week</option>
						<option>2 Weeks</option>
						<option>Permanent</option>
					</select>
				</td>
			</tr>
			<tr>
				<td style="text-align:right;">Reason:</td>
				<td style="text-align:left;">
					<input name="tban_reason" type="text" id="tban_reason" size="35">
				</td>
			</tr>
			<tr>
				<td colspan="2" style="font-weight:bold;">The message below will be stored and sent to the user</td>
			</tr>
			<tr>
				<td colspan="2" class="subHeader">Message</td>
			</tr>
			<tr>
				<td colspan="2">
					<textarea name="tban_message" id="tban_message" rows="8" cols="35"></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" name="TavernBan_User" id="tban_submit" value="Submit">
				</td>
			</tr>
		</table>
	</form>
</div>