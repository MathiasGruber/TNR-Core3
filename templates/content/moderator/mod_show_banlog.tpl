<script type="text/javascript">
{include file="./Scripts/mod_banlog_interface.js"}
</script>
<div align="center">
  	<table width="95%" class="table">
    <tr>
        <td colspan="6" class="subHeader">Currently Banned Users</td>
	</tr>
	{if $banned != "0 rows"}
		<tr>
			<td colspan="6" style="color:red;border-bottom:1px solid #000000;">For more details, check the user's details</td>
		</tr>
		<tr>
			<td width="15%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
			<td width="15%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">Moderator</td>
			<td width="40%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">Reason</td>
			<td width="10%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
			<td width="10%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
			<td width="10%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
		
		{for $i = 0 to ($banned|@count)-1}
			<tr class="{cycle values="row1, row2"}" >
				<td>{$banned[$i]['username']}</td>
				<td>{$temp[$i]['moderator']}</td>
				<td>{$temp[$i]['reason']}</td>
				<td>
				    <button class="Override" id="{$banned[$i]['username']}">Unban</button>
                </td>
				<td>
				    <button class="Reduction" id="{$banned[$i]['username']}">Reduce</button>
                </td>
				<td>
				    <button class="Extension" id="{$banned[$i]['username']}">Extend</button>
                </td>
			</tr>
			<tr id="unban{$banned[$i]['username']}" style="display:none;">
				<td colspan="6" style="padding:0px;margin:0px;">
					<div align="center">
						<form name="GameUnbanForm" method="post" action="">
							<table width="100%" style="padding:0px;margin:0px;border-collapse:collapse;">
								<tr>
									<td class="subHeader">Unban User: {$banned[$i]['username']}</td>
								</tr>
								<tr>
									<td align="center" style="font-weight:bold;">Reason for Unban</td>
								</tr>
								<tr>
									<td><textarea name="override_reason" rows="5" cols="35"></textarea></td>
								</tr>
								<tr>
									<td>
										<input type="submit" name="Submit_Unban" value="Submit">
										<input name="unban_time" type="hidden" value="{$temp[$i]['time']}">
										<input name="unban_uid" type="hidden" value="{$banned[$i]['id']}">
									</td>
								</tr>
							</table>
						</form>
					</div>
				</td>
			</tr>
			<tr id="reduce{$banned[$i]['username']}" style="display:none;">
				<td colspan="6" style="padding:0px;margin:0px;">
					<div align="center">
						<form name="GameReduceForm" method="post" action="">
							<table width="100%" style="padding:0px;margin:0px;border-collapse:collapse;">
								<tr>
									<td class="subHeader">Reduce User Ban: {$banned[$i]['username']}</td>
								</tr>
								<tr>
									<td style="text-align:center;">Reduce Ban Length To: &nbsp;
										<select name="reduce_ban_time">
											{$time_served = $smarty.now - $temp[$i]['time']}
											{if ($time_served < 3600) && ($banned[$i]['ban_time'] > ($smarty.now + 3600))}
												<option>1 Hour</option>
											{/if}
											{if ($time_served < 86400) && ($banned[$i]['ban_time'] > ($smarty.now + 86400))}
												<option>1 Day</option>
											{/if}
											{if ($time_served < 259200) && ($banned[$i]['ban_time'] > ($smarty.now + 259200))}
												<option>3 Days</option>
											{/if}
											{if ($time_served < 604800) && ($banned[$i]['ban_time'] > ($smarty.now + 604800))}
												<option>1 Week</option>
											{/if}
											{if ($time_served < 1209600) && ($banned[$i]['ban_time'] > ($smarty.now + 1209600))}
												<option>2 Weeks</option>
											{/if}
											{if ($time_served < 2419200) && ($banned[$i]['ban_time'] > ($smarty.now + 2419200))}
												<option>1 Month</option>
											{/if}
											{if ($time_served < 4838400) && ($banned[$i]['ban_time'] > ($smarty.now + 4838400))}
												<option>2 Months</option>
											{/if}
											{if ($time_served < 7257600) && ($banned[$i]['ban_time'] > ($smarty.now + 7257600))}
												<option>3 Months</option>
											{/if}
										</select>
									</td>
								</tr>
								<tr>
									<td align="center" style="font-weight:bold;">Reason for Reduction</td>
								</tr>
								<tr>
									<td>
									<textarea name="override_reason" rows="5" cols="35"></textarea></td>
								</tr>
								<tr>
									<td>
										<input type="submit" name="Submit_Reduce" value="Submit">
										<input name="reduce_time" type="hidden" value="{$temp[$i]['time']}">
										<input name="reduce_uid" type="hidden" value="{$banned[$i]['id']}">
									</td>
								</tr>
							</table>
						</form>
					</div>
				</td>
			</tr>
			<tr id="extend{$banned[$i]['username']}" style="display:none;">
				<td colspan="6" style="padding:0px;margin:0px;">
					<div align="center">
						<form name="GameExtendForm" method="post" action="">
							<table width="100%" style="padding:0px;margin:0px;border-collapse:collapse;">
								<tr>
									<td class="subHeader">Extend User Ban: {$banned[$i]['username']}</td>
								</tr>
								<tr>
									<td style="text-align:center;">Extend Ban Length By: &nbsp;
										<select name="extend_ban_time">
											<option>1 Hour</option>
											<option>12 Hours</option>
											<option>1 Day</option>
											<option>3 Days</option>
											<option>1 Week</option>
											<option>2 Weeks</option>
											<option>1 Month</option>
											<option>2 Months</option>
											<option>3 Months</option>
										</select>
									</td>
								</tr>
								<tr>
									<td align="center" style="font-weight:bold;">Reason for Extension</td>
								</tr>
								<tr>
									<td>
									<textarea name="override_reason" rows="5" cols="35"></textarea></td>
								</tr>
								<tr>
									<td>
										<input type="submit" name="Submit_Extend" value="Submit">
										<input name="extend_time" type="hidden" value="{$temp[$i]['time']}">
										<input name="extend_uid" type="hidden" value="{$banned[$i]['id']}">
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
			<td colspan="6">No users are currently banned.</td>
		</tr>
	{/if}
	<tr>
		<td colspan="6" style="border-top:1px solid #000000;font-weight:bold;">
		    <a href="?id={$smarty.get.id}">Return</a>
        </td>
	</tr>
	</table>
</div>