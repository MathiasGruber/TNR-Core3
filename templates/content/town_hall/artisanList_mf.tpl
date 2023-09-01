<div class="page-box">
    <div class="page-title">
        Artisans <span class="toggle-button-info closed" data-target="#artisans-info"/>
    </div>
    <div class="page-content">
        <div class=" toggle-target closed" id="artisans-info">
            Here you can find the people who are active in their profession, as well as their level.
        </div>

        <div class="table-grid table-column-5">
            <div class="lazy table-legend row-header column-1">
				Username
            </div>

		    <div class="lazy table-legend row-header column-2">
                Profession
            </div>

		    <div class="lazy table-legend row-header column-3">
                Level
            </div>

            <div class="lazy table-legend row-header column-4">
                User Rank
            </div>

            <div class="lazy table-legend row-header column-5">
                Days Offline
            </div>

			{foreach $artisan_list as $key => $artisan}

                <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-1">
                    Username
                </div>

				<div class="lazy table-cell table-alternate-{$key % 2 + 1} column-1 row-{$key}">
					<a href="?id=13&page=profile&name={$artisan['username']}">
						{$artisan['username']}
					</a>
				</div>



				<div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-2">
                    Profession
                </div>
            
                <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-2 row-{$key}">
					{$artisan['name']}
				</div>

                

                <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-3">
                    Level
                </div>

				<div class="lazy table-cell table-alternate-{$key % 2 + 1} column-3 row-{$key}">
					{$artisan['profession_exp']}
				</div>



                <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-4">
                    User Rank
                </div>

				<div class="lazy table-cell table-alternate-{$key % 2 + 1} column-4 row-{$key}">
					{$artisan['rank']}
				</div>



                <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-5">
                    Days Offline
                </div>

				<div class="lazy table-cell table-alternate-{$key % 2 + 1} column-5 row-{$key}">
					{$artisan['days_since_login']}
				</div>

			{/foreach}

        </div>
    </div>
</div>