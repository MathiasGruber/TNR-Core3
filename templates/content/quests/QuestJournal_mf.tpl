<div class="page-box">
    <div class="page-title stiff-grid stiff-column-3">
        <div width="36px">
        </div>

        <div>
            Quest Journal - 
            <select  onchange="window.location.href = '?id={$smarty.get.id}&filter='+this.options[this.selectedIndex].value;" style="border:none;background:none;font:inherit;color:black;-webkit-appearance: none;-moz-appearance:none;text-indent:1px;text-overflow:'';font-weight:900;">
                <option {if $smarty.get.filter == ""}selected{/if}          value=""         >Default  </option>
                <option {if $smarty.get.filter == "known"}selected{/if}     value="known"    >Known    </option>
                <option {if $smarty.get.filter == "active"}selected{/if}    value="active"   >Active   </option>
                <option {if $smarty.get.filter == "completed"}selected{/if} value="completed">Completed</option>
                <option {if $smarty.get.filter == "closed"}selected{/if}    value="closed"   >Closed   </option>
                <option {if $smarty.get.filter == "dead"}selected{/if}      value="dead"     >Dead     </option>
                <option {if $smarty.get.filter == "failed"}selected{/if}    value="failed"   >Failed   </option>
                <option {if $smarty.get.filter == "order"}selected{/if}     value="order"    >Orders   </option>
                <option {if $smarty.get.filter == "story"}selected{/if}     value="story"    >Stories  </option>
                <option {if $smarty.get.filter == "event"}selected{/if}     value="event"    >Events   </option>
                <option {if $smarty.get.filter == "misc"}selected{/if}      value="misc"     >Misc.</option>
                <option {if $smarty.get.filter == "mission"}selected{/if}   value="mission"  >Missions</option>
                <option {if $smarty.get.filter == "crime"}selected{/if}     value="crime"    >Crimes</option>
                <option {if $smarty.get.filter == "elemental mastery"}selected{/if}     value="elemental mastery"    >Elemental Mastery</option>
                <option {if $smarty.get.filter == "forbidden"}selected{/if}     value="forbidden"    >Forbidden</option>
            </select>
        </div>
        <div class="self-end">
            <a href="{rtrim(rtrim($smarty.server.REQUEST_URI,'&questing_mode=alert'),'questing_mode=quiet')}&questing_mode={$questing_mode}">
                <img border="0" alt="{$questing_mode}" src="./images/{$questing_mode}.png" width="31" height="32" title="In {$questing_mode} mode.">
            </a>
        </div>
    </div>
    <div class="page-content">
        <div class="page-sub-title-top">
             Active - ({$active}/10)
        </div>

        <div class="table-grid table-column-{if $quest_widget == 'yes'}6{else}5{/if}">
            {if $quest_widget == 'yes'}
                <div class="lazy table-legend row-header column-1">
                    Track
                </div>
            {/if}

            <div class="lazy table-legend row-header column-{if $quest_widget == 'yes'}2{else}1{/if}">
                Name
            </div>
            <div class="lazy table-legend row-header column-{if $quest_widget == 'yes'}3{else}2{/if}">
                Status
            </div>
            <div class="lazy table-legend row-header column-{if $quest_widget == 'yes'}4{else}3{/if}">
                Type
            </div>
            <div class="lazy table-legend row-header column-{if $quest_widget == 'yes'}5{else}4{/if}">
                Level
            </div>
            <div class="lazy table-legend row-header column-{if $quest_widget == 'yes'}6{else}5{/if}">
                Actions
            </div>

            {assign var=i value=0}
            {foreach $quests as $qid => $quest}
                {if ($smarty.get.filter == '' && $statuses[$quest->status] != 'closed' && $statuses[$quest->status] != 'dead') ||  $statuses[$quest->status] == $smarty.get.filter || $quest->category == $smarty.get.filter || ( $smarty.get.filter == 'failed' && $quest->failed )}
                    {if $quest_widget == 'yes'}
                        <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-1">
                            Track
                        </div>
                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-1">
                            {if $quest->status != 3 && $quest->status != 4}
                                <input id="track_{$qid}" type="checkbox" {if $quest->track}checked{/if} class="track-check-box" onChange=
                                "{
                                    temp = $(this).prop('checked');
                                	$('.track-check-box').prop('checked', false);
                                    $(this).prop('checked', temp);
                                	$('.track-check-box').prop('disabled', true);
                                	$.get
                                	(
                                		'?id={$smarty.get.id}&track={$qid}', 
                                		function( data )
                                		{
                                			$('.track-check-box').prop('disabled', false);
                                            $('#widget-quests').html( $(data).find('#widget-quests') );
                                		}
                                	);
                                }">
                            {/if}
                        </div>
                    {/if}


                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}2{else}1{/if}">
                        Name
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}2{else}1{/if}">
                        <a href="?id={$smarty.get.id}&details={$qid}" >
                            {$quest->name}
                        </a>
                    </div>

                    

                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}3{else}2{/if}">
                        Status
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}3{else}2{/if}">
                        {if $quest->status == 0 and $quest->time_gap_requirement_text != ''}
                            Repeatable in {$quest->time_gap_requirement_text}
                        {else}
                            {ucwords($statuses[$quest->status])}{if $quest->failed} (<span style="color:darkorange">Failed</span>){elseif $quest->failable}(<span style="color:grey">Failable</span>){/if}
                        {/if}
                    </div>



                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}4{else}3{/if}">
                        Type
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}4{else}3{/if}">
                        {if $quest->category_skin == ''}
                            {ucwords($quest->category)}
                        {else}
                            {$quest->category_skin}
                        {/if}
                    </div>



                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}5{else}4{/if}">
                        Level
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}5{else}4{/if}">
                        {$quest->level}
                    </div>



                    <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}6{else}5{/if}">
                        Actions
                    </div>

                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-{if $quest_widget == 'yes'}6{else}5{/if}">
                        {if isset($quest->actions) && count($quest->actions) > 1}

                            <select  onchange="window.location.href = this.options[this.selectedIndex].value;" style="border:none;background:none;font:inherit;color:black;-webkit-appearance: none;-moz-appearance:none;text-indent:1px;text-overflow:'';font-weight:900;">
                                <option value=""         >Actions</option>
                                {foreach $quest->actions as $action}
                                    <option value="{$action['link']}">{$action['text']}</option>
                                {/foreach}
                            </select>

                        {elseif isset($quest->actions) && count($quest->actions) == 1}

                            <a href="{$quest->actions[0]['link']}">{$quest->actions[0]['text']}</a>

                        {else}

                            N/A

                        {/if}
                    </div>
                {/if}
                {$i = $i + 1}
            {/foreach}

			{if count($quests) < 1}
				<div class="span-all bold table-cell table-alternate-1">
					You have no quests at this moment.
				</div>
			{/if}

        </div>

        <!--{literal}
        <table align="center" width="100%">
            <tr>
                <td class="subHeader" align="center">
                    quest data dump for debugging.
                </td>
            </tr>
            <tr>
                <td style="text-align:left;">
                    <br/>
                    {foreach $quests as $qid => $quest}
                        <details style="text-align:left;">
                            <summary>{$qid}</summary>
                                <pre>
                                    {var_dump($quest)}
                                </pre>
                        </details>
                        <br/>
                    {/foreach}
                </td>
            </tr>
        </table>
        {/literal}-->
    </div>
</div>


