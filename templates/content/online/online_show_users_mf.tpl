<div class="page-box">
	<div class="page-title">
		Online
	</div>
	<div class="page-content">
		<div class="page-sub-title-top">
			Statistics
		</div>

		<div class="page-grid page-column-2">
			<div>
				Users online:
			</div>
			<div>
				{$users_count}
			</div>
			<div>
				Most users ever online:
			</div>
			<div>
				{$max_users}
			</div>
			<div>
				Moderators online:
			</div>
			<div>
				{$mod_count}
			</div>
		</div>

		<div class="page-sub-title">
			Users
		</div>

		{if $user != '0 rows'}
			{for $i = 0 to ($user|@count)-1}
				<a class="table-alternate-{$i%2 + 1} table-cell" href="?id=13&page=profile&name={$user[$i]['username']}">
					{$user[$i]['username']}
				</a>
			{/for}
		{else}
			<div>
				No users found.
			</div>
		{/if}

		<div class="page-grid page-column-fr-2">
			<a class="page-button-fill" href="?id={$smarty.get.id}&act=users&min={$newminm}">&laquo; Previous</a>
			<a class="page-button-fill" href="?id={$smarty.get.id}&act=users&min={$newmini}">Next &raquo;</a>
		</div>

	</div>
</div>