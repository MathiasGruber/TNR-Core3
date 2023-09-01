<div class="page-box">
	<div class="page-title">
		Occupations <span class="toggle-button-info closed" data-target="#occupations-info"/>
	</div>
	<div class="page-content">
		<div class="toggle-target closed" id="occupations-info">
            You may have both one special occupation and one normal occupation at a time.
			<br><br>
			{if $userdata['rank_id'] < 3}
				You will not gain access to special occupations until you have reached the rank of Chuunin.
				<br><br>
			{/if}
        </div>

		{if isset($heal_message)}

			<div class="page-sub-title-top">
				Heal Results
			</div>

			<div>
				{$heal_message}
			</div>

		{else if isset($special_occupations)}

  			{if $userdata['rank_id'] >= 3}
				<div class="page-sub-title-top">
					Special Occupations
				</div>

				<div class="table-grid table-column-3">
					<div class="lazy table-legend row-header column-1">
						Name
					</div>

					<div class="lazy table-legend row-header column-2">
						Description
					</div>

					<div class="lazy table-legend row-header column-3">
						Action
					</div>

					{assign var=i value=0}
					{foreach $special_occupations as $occupation}

						<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-1">
				            Name
				        </div>

						<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-1 row-{$i}">
							{$occupation['name']}
						</div>



						<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-2">
				            Description
				        </div>

						<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-2 row-{$i}">
							{$occupation['description']}
						</div>



						<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-3">
				            Action
				        </div>

						<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-3 row-{$i}">
							<a class="showTableLink" href="?id={$smarty.get.id}&act=specialgetoccupation&job={$occupation['id']}">Take!</a>
						</div>

						{$i = $i + 1}
					{/foreach}
				</div>
			{/if}

		{else if isset($users)}
			{$subSelect="users"}
			{include file="file:{$absPath}/{$users}" title="Surgeon Occupations"}
		{else if isset($bounties)}
			{$subSelect="bounties"}
			{include file="file:{$absPath}/{$bounties}" title="Interesting bounties"}
		{/if}

		{if isset($normal_occupations)}

			<div class="page-sub-title">
				Normal Occupations
			</div>

			<div class="table-grid table-column-6">
				<div class="lazy table-legend row-header column-1">
					Name
				</div>

				<div class="lazy table-legend row-header column-2">
					Supports
				</div>

				<div class="lazy table-legend row-header column-3">
					Stat 1
				</div>

				<div class="lazy table-legend row-header column-4">
					Stat 2
				</div>

				<div class="lazy table-legend row-header column-5">
					Stat 3
				</div>

				<div class="lazy table-legend row-header column-6">
					Action
				</div>

				{$i = 0}
				{foreach $normal_occupations as $occupation}

					<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-1">
			            Name
			        </div>

					<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-1 row-{$i}">
						{$occupation['name']}
					</div>



					<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-2">
			            Supports
			        </div>

					<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-2 row-{$i}">
						{$occupation['professionSupport']}
					</div>



					<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-3">
			            Stat 1
			        </div>

					<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-3 row-{$i}">
						{$occupation['gain_1']}
					</div>



					<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-4">
			            Stat 2
			        </div>

					<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-4 row-{$i}">
						{$occupation['gain_2']}
					</div>



					<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-5">
			            Stat 3
			        </div>

					<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-5 row-{$i}">
						{$occupation['gain_3']}
					</div>



					<div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-6">
			            Action
			        </div>
				    
					<div class="lazy table-cell table-alternate-{$i % 2 + 1} column-6 row-{$i}">
						{if $occupation['rankid'] == 2}
							<a class="showTableLink" href="?id={$smarty.get.id}&act=normalgetoccupation&job={$occupation['id']}">Take!</a>
						{else}
							n/a
						{/if}
					</div>

					{$i = $i + 1}
				{/foreach}
			</div>

		{else if isset($normal_occupation)}

			<div class="page-sub-title">
				{$normal_occupation['name']} lvl: {$normal_occupation['level']} <span class="toggle-button-info closed" data-target="#normal-occupation-info"/>
			</div>

			<div class="toggle-target closed" id="normal-occupation-info">

				{if $check_promotion != 'promotion' && $check_promotion != 'level_up'}
					Progress:
					<b>
						{$check_promotion}
					</b>
					<br>
					<br>
				{/if}

				Gains can be collected every 24 hours and they include the following.
				Profession Experience: <b>{$gains['profGain']}</b> (if applicable)<br>
		        Experience: <b>{$gains['expGain']}</b><br>
		        Stats: 
		        <b>
		          {$gains['statGain']} 
		          ({$gains['stats']})
		        </b>
		        <br>
		        Ryo: <b>{$gains['ryoGain']}</b><br>
			</div>

			<div>
				{if $check_promotion == 'promotion'}
					Progress:
					<b>
						<a href="?id={$smarty.get.id}&act=normalpromotion">Take Promotion!</a>
					</b>
					<br>
					<br>
				{else if $check_promotion == 'level_up'}
					Progress:
					<b>
						<a href="?id={$smarty.get.id}&act=normallevelup">Level Up!</a>
					</b>
					<br>
					<br>
				{/if}


		        Claim:
		        <b>
					{if $claim_time !== false}
						{$claim_time}
					{else}
						<a href="?id={$smarty.get.id}&act=normalcollect">Collect</a>
					{/if}
		        </b>

				<br>

		        {if isset($newJob)}
					{$newJob}
		        {/if}

		        Status:
				<b>
		          <a href="?id={$smarty.get.id}&act=normalquit">Quit job</a>
		        </b>
			</div>
		    
		{/if}

	</div>
</div>