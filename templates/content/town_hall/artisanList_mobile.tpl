<tr>
	<td>
		<headerText>Artisans</headerText>
	</td>
</tr>
<tr>
	<td>
    <text>Here you can find the people who are active in their profession, as well as their level.</text>
  </td>
</tr>

<tr>
	<td>
		<text>Username</text>
	</td>

	<td>
    <text>Profession</text>
	</td>

	<td>
		<text>Level</text>
	</td>

	<td>
    <text>User Rank</text>
	</td>

	<td>
    <text>Days Offline</text>
	</td>
</tr>

{foreach $artisan_list as $artisan}
	<tr>
		<td>
			<a class="showTableLink" href="?id=13&page=profile&name={$artisan['username']}">
				{$artisan['username']}
			</a>
		</td>

		<td>
      <text>{$artisan['name']}</text>
		</td>

		<td>
      <text>{$artisan['profession_exp']}</text>
		</td>

		<td>
      <text>{$artisan['rank']}</text>
		</td>

		<td>
      <text>{$artisan['days_since_login']}</text>
		</td>

	</tr>
{/foreach}



<a href="?id=9">Return</a>
