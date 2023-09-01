<div style="border: 1px solid black;position:relative;top:-10px;" width="100%">
	<table align="center" width="100%">
		<tr>
			<td align="center" class="subHeader" width="100%">
				Artisans
			</td>
		</tr>
		<tr>
			<td>
        Here you can find the people who are active in their profession, as well as their level.
      </td>
		</tr>
	</table>

	<table class="sortable" style="border-left:none;border-right:none;" width="100%">
		<thead>
			<tr>
				<td class="tdTop" width="20%">
					Username
				</td>

				<td class="tdTop" width="20%">
					Profession
				</td>

				<td class="tdTop" width="20%">
					Level
				</td>

				<td class="tdTop" width="20%">
					User Rank
				</td>

				<td class="tdTop" width="20%">
					Days Offline
				</td>
			</tr>
		</thead>

		<tbody>
			{foreach $artisan_list as $artisan}
				<tr>
					<td width="20%">
						<a class="showTableLink" href="?id=13&page=profile&name={$artisan['username']}">
							{$artisan['username']}
						</a>
					</td>

					<td width="20%">
						{$artisan['name']}
					</td>

					<td width="20%">
						{$artisan['profession_exp']}
					</td>

					<td width="20%">
						{$artisan['rank']}
					</td>

					<td width="20%">
						{$artisan['days_since_login']}
					</td>

				</tr>
			{/foreach}
		</tbody>
	</table>



</div>
<div align ="center">
  <a href="?id=9">Return</a>
</div>