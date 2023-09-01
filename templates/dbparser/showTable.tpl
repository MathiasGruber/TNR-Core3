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

<div align="center" id="showTable">  
    <table width="95%" class="table">
        <tr>
            <td colspan="{$nColumns_{$subSelect}}" class="subHeader">{$subHeader_{$subSelect}}</td>
        </tr>
        {if isset( {$topInfo_{$subSelect}} ) && {$topInfo_{$subSelect}} != ""}
            <tr>
                <td colspan="{$nColumns_{$subSelect}}" style="border-bottom:1px solid #000000;">
                    {if is_array($topInfo_{$subSelect}) && isset( $topInfo_{$subSelect}['message'] ) && strlen($topInfo_{$subSelect}['message']) > 1}
                        {$topInfo_{$subSelect}['message']}
                    {else}
                        {$topInfo_{$subSelect}}
                    {/if}
                </td>
            </tr>
        {/if}
        {if isset( {$topSearchFields_{$subSelect}} ) && {$topSearchFields_{$subSelect}} != ""}
            {foreach $topSearchFields_{$subSelect} as $entry}
                <tr>
                    <td colspan="{$nColumns_{$subSelect}}" style="border-bottom:1px solid #000000;">
                        {$entry['infoText']}: &#160;&#160;&#160;
                        <form style="display: inline;" action="{$entry['href']}" method="post">
                            <input name="{$entry['postField']}" type="text" size="15">&#160;&#160;&#160;
                            <input class="input_submit_btn" style="line-height:15px;" type="submit" name="{$entry['postIdentifier']}" value="{$entry['inputName']}">
                        </form>
                    </td>
                </tr>
            {/foreach}
        {/if}
        {if isset( {$topOptions_{$subSelect}} ) && !empty({$topOptions_{$subSelect}}) }
            <tr>
                <td colspan="{$nColumns_{$subSelect}}" style="border-bottom:1px solid #000000;">
                    {foreach $topOptions_{$subSelect} as $entry}
                        {if isset($entry["type"]) && $entry["type"] == "text"}
                            {$entry["name"]}
                        {else}
                            <a class="showTableTopLink" href="{$entry["href"]}" {if isset($entry["onclick"]) && strlen($entry["onclick"]) > 1} onclick="{$entry["onclick"]}return false;"{/if} >{$entry["name"]}</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        {/if}
                         
                    {/foreach}
                </td>
            </tr>
        {/if}
		</table>

		<table width="100%" class="table sortable" style="border-left:none;border-right:none">
        {if {$data_{$subSelect}} }
			<thead>
            <tr>
                {for $foo=0 to $nColumns_{$subSelect}-1}
                    <td class="tdTop">
                        {$data_{$subSelect}[0].$foo} 
                    </td>
                {/for}
            </tr>
			</thead>
            <tbody>
            {for $i = 1 to ($data_{$subSelect}|@count)-1}
                <tr class="{cycle values="row1,row2"}" >
                    {if array_key_exists( "TP_subtitle", $data_{$subSelect}[$i])}
                        <td class="tdTop" colspan="{$nColumns_{$subSelect}}">{$data_{$subSelect}[$i].TP_subtitle}</td>
                    {else}
                        
                        {for $foo=0 to $nColumns_{$subSelect}-1}
                            {if isset($data_{$subSelect}[$i].$foo)}
                                {if $data_{$subSelect}[0].$foo|strstr:"Detailed Time"}
                                    <td class="showTableEntry" style="width:150px;">{$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y, %H:%M:%S"} </td>
                                {elseif $data_{$subSelect}[0].$foo|strstr:"Time"}
                                    <td class="showTableEntry" style="width:75px;">{$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y, %H:%M"} </td>
                                {elseif $data_{$subSelect}[0].$foo|strstr:"Date"}
                                    <td class="showTableEntry" style="width:75px;">{$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y"} </td>
                                {elseif $data_{$subSelect}[0].$foo|stristr:"Activity"}
                                    {assign var="timePassed" value=$serverTime-$data_{$subSelect}[$i].$foo}
                                    {if $timePassed == 0}
                                        <td class="showTableEntry">Now</td>
                                    {else}
                                        <td class="showTableEntry">
                                            {if $timePassed <= 60}
                                                {$timePassed} seconds
                                            {else if $timePassed % 60 != 0}
                                                {floor($timePassed / 60)}m : {$timePassed % 60}s
                                            {else}
                                                {floor($timePassed / 60)} minutes
                                            {/if} 
                                        </td>
                                    {/if}
                                {elseif $data_{$subSelect}[0].$foo|strstr:"Online Status"}
                                    {if $data_{$subSelect}[$i].$foo < ($smarty.now - 600) }
                                        <td class="showTableEntry" style="color:red;"><b>Offline</b></td>
                                    {else}
                                        <td class="showTableEntry" style="color:green;"><b>Online</b></td>
                                    {/if}
                                {else}
                                    <td class="showTableEntry"> {$data_{$subSelect}[$i].$foo} </td>
                                {/if}
                            {else}
                                <td class="showTableEntry"><i>NULL</i></td>
                            {/if}
                        {/for}
                    {/if}
                </tr>
                
                {if isset({$hideOptions_{$subSelect}}) && {$hideOptions_{$subSelect}} == true}
                    <tr class="{cycle values="row1,row2"} jsHide" >
                        <td colspan="{$nColumns_{$subSelect}}">
                            {for $foo=0 to count($dataHidden_{$subSelect}[$i-1])-1}
                                <form style="display: inline;" action="{$dataHidden_{$subSelect}[$i][$foo]["href"]}" method="post">
                                    <input class="input_submit_btn" style="width:150px; line-height:30px;" type="submit" value="{$dataHidden_{$subSelect}[$i][$foo]["name"]}" />
                                </form>
                            {/for}
                        </td>
                    </tr>
                {/if}
            {/for}
			</tbody>

            {if isset($newerLink_{$subSelect})}
                <tr>
                    <td colspan="{$nColumns_{$subSelect}}" style="border-top:1px solid #000000;">
                        <a class="prevEntries" href="{$newerLink_{$subSelect}}">&laquo; Newer</a> - 
                        <a class="nextEntries" href="{$olderLink_{$subSelect}}">Older &raquo;</a>
                    </td>
                </tr>
            {/if} 
        {else}
            <tr><td colspan="{$nColumns_{$subSelect}}">No entries found in database</td></tr>
        {/if}
    </table>
    {if !isset($hideReturnLink)}
        {if isset($returnLink)}
            {if $returnLink === true}
                <a href="?id={$smarty.get.id}" class="returnLink">Return</a>
            {else}
                <a href="{$returnLink}" class="returnLink">Return</a>
            {/if}
        {/if}
    {/if}

</div>

{if isset( $checkBoxFormLink )}
        <input class="input_submit_btn" type="submit" value="{$checkBoxFormSubmit}" />
    </form>        
{/if}