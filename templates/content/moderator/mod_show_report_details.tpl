<div >
	<table width="95%" class="table">
	<tr>
		<td colspan="4" class="subHeader">User Report</td>
	</tr>
	<tr>
		<td width="25%" style="font-weight:bold;">User:</td>
		<td width="25%"><a href="?id=13&amp;page=profile&amp;name={$name}">{$name}</a></td>
		<td width="25%" style="font-weight:bold;">Reported by:</td>
		<td width="25%"><a href="?id=13&amp;page=profile&amp;name={$rname}">{$rname}</a></td>
	</tr>
	<tr>
		<td style="font-weight:bold;">Date:</td>
		<td >{$report[0]['time']|date_format:"%Y-%m-%d %H:%M:%S"}</td>
		<td style="font-weight:bold;">Type:</td>
		<td >{$report[0]['type']}</td>
	</tr>
	<tr>
		<td style="font-weight:bold;">Status:</td>
		<td >{$report[0]['status']}</td>
		<td style="font-weight:bold;">Processed by:</td>
		<td >{$processed}</td>
	</tr>
	<tr>
		<td align="left" style="font-weight:bold;">Reason:</td>
		<td colspan="3" >{$report[0]['reason']}</td>
	</tr>
	{if isset($message) && $message != ""}
       <tr><td colspan="4" class="subHeader">Reported Message</td></tr>
       <tr><td colspan="4" style='padding:10px;'>{$report_message}</td></tr>
    {/if} 
	<tr>
		<td colspan="4" >&nbsp;</td>
	</tr>
	</table>
</div>
<br>
<div >
	<table width="95%" border="0" class="table" >
	<tr>
		<td width="100%" class="subHeader">Options</td>
	</tr>
	<tr>
		<td style="padding:2px;">
		{if $report[0]['type'] == 'tavern'}
			{if (($report[0]['village'] != 'N/A') && ($report[0]['village'] != 'rumors'))} 
				<form name="JumpForm" method="post" action="?id={$smarty.get.id}">
					<input name="village_choice" type="hidden" id="village" value="{$report[0]['village']|capitalize}">
					<input type="submit" name="Jump_Village" value="Jump to {$report[0]['village']|capitalize}">
				</form>
			{elseif $report[0]['village'] == 'rumors'}
				<form name="JumpForm" method="post" action="?id={$smarty.get.id}">
					<input name="village_choice" type="hidden" id="village" value="Syndicate">
					<input type="submit" name="Jump_Village" value="Jump to Syndicate">
				</form>
			{else}
				<div >The report cannot determine the village.<br>
				<a href="?id={$smarty.get.id}">Return</a></div>
			{/if}
		{/if}
		<a href="?id={$smarty.get.id}&amp;act=check_user&amp;uid={$report[0]['uid']}">Check user's record</a></td>
	</tr>
	</table>
</div>
{if (($report[0]['processed_by'] == '') || ($report[0]['processed_by'] == $sessionUser))}
	{if (($report[0]['status'] != 'handled') && ($report[0]['status'] != 'ungrounded'))}
		<div >
			<form name="form1" method="post" action=""><br>
				<table width="95%" class="table">
				<tr>
					<td style="border-top:none;" class="subHeader" >Alter Report Status</td>
				</tr>
				<tr>
					<td style="padding:10px;color:darkred;">Altering the report status will designate you as the processing moderator, any questions regarding this matter, and it's processing can and will be directed at you.<br>
					<br>Once you have set the status to anything other than &quot;unviewed&quot; no other moderator or admin can alter the report's status, unless you set it back to &quot;unviewed&quot;<br>
					<br>you cannot reset &quot;ungrounded&quot; or &quot;handled&quot; reports to unviewed.</td>
				</tr>
				<tr>
					<td style="padding:5px;">
					<select name="status">
						<option>unviewed</option>
						<option>in progress</option>
						<option>ungrounded</option>
						<option>handled</option>
					</select></td>
				</tr>
				<tr>
					<td style="padding:5px;">
					<input type="submit" name="Submit" value="Submit"></td>
				</tr>
				</table><br>
			</form>
		</div>
	{else}
		<div >The report has already been handled or ungrounded.<br>
		<a href="?id={$smarty.get.id}">Return</a></div>
	{/if}
{/if}
