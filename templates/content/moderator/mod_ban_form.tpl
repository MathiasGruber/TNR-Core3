<div align="center" style="display:none;" id="div_ban">
	<form name="GameBanForm" method="post" action="">
		<table width="95%" class="table">
			<tr>
				<td colspan="2" class="subHeader">Ban User</td>
			</tr>
			<tr>
				<td width="30%" style="text-align:right;">Username:</td>
				<td width="70%" style="text-align:left;"><input name="ban_username" type="text" id="user_ban"></td>
			</tr>
			<tr>
				<td style="text-align:right;">Length:</td>
				<td style="text-align:left;">
					<select name="game_ban_time">
						<option>1 Hour</option>
						<option>1 Day</option>
						<option>3 Days</option>
						<option>1 Week</option>
						<option>2 Weeks</option>
					</select>
				</td>
			</tr>
			<tr>
				<td style="text-align:right;">Reason:</td>
				<td style="text-align:left;">
				<input name="ban_reason" type="text" id="ban_reason" size="35"></td>
			</tr>
			<tr>
				<td colspan="2" style="font-weight:bold;">The message below will be stored and shown to the user</td>
			</tr>
			<tr>
				<td colspan="2" class="subHeader">Message</td>
			</tr>
			<tr>
				<td colspan="2">
					<textarea name="ban_message" id="ban_message" rows="8" cols="35"></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center" >
					<input type="submit" name="Ban_User" id="ban_submit" value="Submit">
				</td>
			</tr>
    	</table>
	</form>
</div>