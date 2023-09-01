<div width="95%" style="border: 1px solid black;position:relative;top:-10px;">

    <table class="table" style="border:none;margin:0px;" width="100%">
        <tr>
            <td class="subHeader" width="10%">
            </td>
            <td class="subHeader" style="text-align:center;padding-right:0px;">
                QuestJournal -
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
            </td>
            <td class="subHeader" style="text-align:right;padding:5px;" width="10%">
                <a href="{rtrim(rtrim($smarty.server.REQUEST_URI,'&questing_mode=alert'),'questing_mode=quiet')}&questing_mode={$questing_mode}">
                    <img border="0" alt="{$questing_mode}" src="./images/{$questing_mode}.png" width="31" height="32" title="In {$questing_mode} mode.">
                </a>
            </td>
        </tr>
        <tr>
            <td style="font-family:sakura;font-size:15px;position:relative;top:-2px;"  colspan="3">
                Active - ({$active}/10)
            </td>
        </tr>
    </table>

    <table align="center" width="100%" class="sortable" style="position:relative;">
        <tr>
            {if $quest_widget == 'yes'}
                <td class="tableColumns sorttable_nosort" style="text-align:left;" width="41px">
                    Track
                </td>
            {/if}
            <td class="tableColumns" style="text-align:left;">
                Name
            </td>
            <td class="tableColumns" align="center">
                Status
            </td>
            <td class="tableColumns" align="center">
                Type
            </td>
            <td class="tableColumns" align="center">
                Level
            </td>
            <td class="tableColumns sorttable_nosort" align="center">
                Actions
            </td>
        </tr>
        {foreach $quests as $qid => $quest}
            {if ($smarty.get.filter == '' && $statuses[$quest->status] != 'closed' && $statuses[$quest->status] != 'dead') || $statuses[$quest->status] == $smarty.get.filter || $quest->category == $smarty.get.filter || ( $smarty.get.filter == 'failed' && $quest->failed )}
                <tr>
                    {if $quest_widget == 'yes'}
                        <td>
                            {if $quest->status != 3 && $quest->status != 4}
                                <input style="margin-right:15px;" id="track_{$qid}" type="checkbox" {if $quest->track}checked{/if} class="track-check-box" onChange=
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
                                            $('#quest_widget').html( $(data).find('#quest_widget') );
                                		}
                                	);
                                }">
                            {/if}
                        </td>
                    {/if}
                    <td style="text-align:left;">
                        <a href="?id={$smarty.get.id}&details={$qid}" >
                            {$quest->name}
                        </a>
                    </td>

                    <td>
                        {if $quest->status == 0 and $quest->time_gap_requirement_text != ''}
                            Repeatable in {$quest->time_gap_requirement_text}
                        {else}
                            {ucwords($statuses[$quest->status])}{if $quest->failed} (<span style="color:darkorange">Failed</span>){elseif $quest->failable}(<span style="color:grey">Failable</span>){/if}
                        {/if}
                    </td>

                    <td>
                        {if $quest->category_skin == ''}
                            {ucwords($quest->category)}
                        {else}
                            {$quest->category_skin}
                        {/if}
                    </td>

                    <td>
                        {$quest->level}
                    </td>

                    <td>
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
                    </td>
                </tr>
            {/if}
        {/foreach}
    </table>

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
    </table>{/literal}-->
</div>