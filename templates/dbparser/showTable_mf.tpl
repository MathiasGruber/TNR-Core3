{if isset({$hideOptions_{$subSelect}}) && {$hideOptions_{$subSelect}} == true}
    <script type="text/javascript">
        $(document).ready(function() {
            // Target Groups
            $('.showTableEntry').click(function() {
                console.log('Toggling hidden class ');
                $(this).parent("tr").next("tr").toggleClass('jsHide');
            });

            // Hover effect 1
            $("table.row1").hover(
                function () {
                  $(this).addClass("row2");
                },
                function () {
                  $(this).removeClass("row2");
                }
            );

			$("table.sortable").hover(
                function () {
                  $(this).addClass("row2");
                },
                function () {
                  $(this).removeClass("row2");
                }
            );
        });
    </script>
{/if}

{if isset( $checkBoxFormLink )}
    <form id="tableParserCheckboxForm" action="{$checkBoxFormLink}" method="post">     
{/if}

{if isset( $topInfo_{$subSelect}['hidden'] ) && $topInfo_{$subSelect}['hidden'] == "yes"}
    {$hidden = true}
{else}
    {$hidden = false}
{/if}

{if $full_page}<div class="lazy page-box">{/if}

    {if {$subHeader_{$subSelect}} != ''}
        <div class="lazy {if $full_page}page-title{elseif $first}page-sub-title-top{else}page-sub-title{/if}">
            {$subHeader_{$subSelect}}  {if $hidden}<span class="toggle-button-info closed" data-target="#show-table-info"></span>{/if}
        </div>
    {/if}

    <div class="{if $full_page}page-content{/if}">

        {if isset( {$topInfo_{$subSelect}} ) && {$topInfo_{$subSelect}} != ""}
            <div class="{if {$subHeader_{$subSelect}} == ''}page-sub-title-top{/if}{if $hidden}toggle-target closed{/if}" id="show-table-info">
                {if is_array($topInfo_{$subSelect}) && isset( $topInfo_{$subSelect}['message'] ) && strlen($topInfo_{$subSelect}['message']) > 1 }
                    {$topInfo_{$subSelect}['message']}
                {else}
                    {$topInfo_{$subSelect}}
                {/if}
            </div>
        {/if}

        {if isset( {$topSearchFields_{$subSelect}} ) && {$topSearchFields_{$subSelect}} != ""}
            {foreach $topSearchFields_{$subSelect} as $entry}
                <form action="{$entry['href']}" method="post" class="table-grid table-column-2">
                    <div class="span-2">
                        {$entry['infoText']}
                    </div>
                    <input class="lazy page-text-input" name="{$entry['postField']}" type="text">
                    <input class="lazy page-button-fill" type="submit" name="{$entry['postIdentifier']}" value="{$entry['inputName']}">
                </form>
            {/foreach}
        {/if}
        {if isset( {$topOptions_{$subSelect}} ) && !empty({$topOptions_{$subSelect}}) }
            {$rows = 0}
            {foreach $topOptions_{$subSelect} as $entry}
                {if isset($entry["type"]) && $entry["type"] == "text"}
                    {$rows = $rows + 1}
                {/if}
            {/foreach}
            <div class="page-sub-title-no-margin page-grid page-grid-justify-stretch {if $rows > 1}page-column-{count($topOptions_{$subSelect})/$rows}{else}grid-fill-columns{/if}">
                {foreach $topOptions_{$subSelect} as $entry}
                    {if isset($entry["type"]) && $entry["type"] == "text"}
                        {$entry["name"]}
                    {else}
                        <a href="{$entry["href"]}" {if isset($entry["onclick"]) && strlen($entry["onclick"]) > 1} onclick="{$entry["onclick"]}return false;"{/if} >{$entry["name"]}</a>
                    {/if}
                {/foreach}
            </div>
        {/if}

        <div class="table-grid table-column-{$nColumns_{$subSelect}}">
            {if {$data_{$subSelect}} }
                {for $foo=0 to $nColumns_{$subSelect}-1}
                    <div class="table-legend row-header column-{$foo+1} {if $foo == 0}table-legend-first{elseif $foo == $nColumns_{$subSelect}-1}table-legend-last{/if}">
                        {$data_{$subSelect}[0].$foo} 
                    </div>
                {/for}
                
                {$no_content = true}
                {for $i = 1 to ($data_{$subSelect}|@count)-1}
                    {if array_key_exists( "TP_subtitle", $data_{$subSelect}[$i])}
                        <div class="table-legend table-span-{$nColumns_{$subSelect}} table-alternate-{$i % 2 + 1} row-sub-header column-{$i}">
                            {$data_{$subSelect}[$i].TP_subtitle}
                        </div>
                    {else}

                        {$no_content = false}
                        
                        {for $foo=0 to $nColumns_{$subSelect}-1}
                            {if isset($data_{$subSelect}[$i].$foo)}

                                <div class="table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-{$foo+1}">
                                    {$data_{$subSelect}[0][$foo % {$nColumns_{$subSelect}}]}
                                    {if array_key_exists($data_{$subSelect}[$foo%{$nColumns_{$subSelect}}])}</br>{$data_{$subSelect}[$foo%{$nColumns_{$subSelect}}].TP_subtitle}{/if}
                                </div>

                                {if $data_{$subSelect}[0].$foo|strstr:"Detailed Time"}

                                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}" >
                                        {$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y, %H:%M:%S"}
                                    </div>

                                {elseif $data_{$subSelect}[0].$foo|strstr:"Time"}

                                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}">{$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y, %H:%M"} </div>

                                {elseif $data_{$subSelect}[0].$foo|strstr:"Date"}

                                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}">
                                        {$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y"}
                                    </div>

                                {elseif $data_{$subSelect}[0].$foo|stristr:"Activity"}
                                    {assign var="timePassed" value=$serverTime-$data_{$subSelect}[$i].$foo}
                                    {if $timePassed == 0}

                                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}">
                                            Now
                                        </div>

                                    {else}

                                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}">
                                            {if $timePassed <= 60}
                                                {$timePassed} seconds
                                            {else if $timePassed % 60 != 0}
                                                {floor($timePassed / 60)}m : {$timePassed % 60}s
                                            {else}
                                                {floor($timePassed / 60)} minutes
                                            {/if}
                                        </div>

                                    {/if}
                                {elseif $data_{$subSelect}[0].$foo|strstr:"Online Status"}
                                    {if $data_{$subSelect}[$i].$foo < ($smarty.now - 600) }

                                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}" style="color:red;">
                                            <b>Offline</b>
                                        </div>

                                    {else}

                                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}" style="color:green;">
                                            <b>Online</b>
                                        </div>

                                    {/if}
                                {else}

                                    <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}">
                                        {$data_{$subSelect}[$i].$foo}
                                    </div>

                                {/if}
                            {else}

                                <div class="lazy table-cell table-alternate-{$i % 2 + 1} column-{$foo+1} row-{$i}">
                                    <i>NULL</i>
                                </div>

                            {/if}
                        {/for}
                    {/if}
                    
                    {if isset({$hideOptions_{$subSelect}}) && {$hideOptions_{$subSelect}} == true}
                        <div class="lazy table-alternate-{$i % 2 + 1} table-span-{$nColumns_{$subSelect}} page-grid-justify-stretch page-grid page-column-{count($dataHidden_{$subSelect}[$i-1])}">
                            {for $foo=0 to count($dataHidden_{$subSelect}[$i-1])-1}
                                <form style="padding-bottom:8px;" action="{$dataHidden_{$subSelect}[$i][$foo]["href"]}" method="post">
                                    <input class="lazy page-button-fill table-cell" type="submit" value="{$dataHidden_{$subSelect}[$i][$foo]["name"]}" />
                                </form>
                            {/for}
                        </div>
                    {/if}
                {/for}

                {if $no_content}
                    <div class="table-legend-mobile table-alternate-1 row-0 column-0" style="width:0px;margin:0px;padding:0px;border:none;">
                    </div>
                    <div class="lazy table-cell table-alternate-1 column-1 row-1 table-span-{$nColumns_{$subSelect}} bold">
                        Empty
                    </div>
                {/if}

                {if isset($newerLink_{$subSelect})}
                    <div class="page-pages table-span-{$nColumns_{$subSelect}}">
                        <a onclick="loadPage('{$newerLink_{$subSelect}}','all');">&laquo; Newer</a> - 
                        <a onclick="loadPage('{$olderLink_{$subSelect}}','all');">Older &raquo;</a>
                    </div>
                {/if} 
            {else}
                <div>No entries found in database</div>
            {/if}

        </div>

        {if isset( $checkBoxFormLink )}
            <div>
                <input class="page-button-fill" type="submit" value="{$checkBoxFormSubmit}" />
            </div>
        {/if}

    </div>
{if $full_page}</div>{/if}

{if isset( $checkBoxFormLink )}
    </form>        
{/if}