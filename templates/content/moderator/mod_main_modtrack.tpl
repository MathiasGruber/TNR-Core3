<div align="center" style="display:none;" id="div_modtrack">
	<form name="ModTrackForm" method="post" action="">
		<table width="95%" class="table">
		<tr>
			<td class="subHeader">Track Moderator Activity</td>
		</tr>
		<tr>
			<td>
                Using this panel you can keep track of moderator's activity like the number of reports handled, number of bans, as well as links to complete lists of acts by this particular moderator.<br>
			    <div style="color:darkred;">This feature is currently under development!</div>
            </td>
		</tr>
		<tr>
			<td>
			    <select name="moderator_track" id="track_moderator">
			    {if $modtrack != '0 rows'}
				    {for $i = 0 to ($modtrack|@count)-1}
					    <option value="{$modtrack[$i]['username']}">{$modtrack[$i]['username']}</option>
				    {/for}
			    {/if}
			    </select>
			</td>
		</tr>
		<tr>
			<td align="center">
			<input type="submit" name="Track_Mod" id="track_mod_submit" value="Submit"></td>
		</tr>
		</table>
	</form>
</div>