{if !isset($preventStretch)}
    <doStretch></doStretch>
{/if}
<tr>
    <td>
        <headerText>{$subHeader_{$subSelect}}</headerText>
    </td>
</tr>

<!-- Text to show at top of page -->
{if isset( {$topInfo_{$subSelect}} ) && {$topInfo_{$subSelect}} != ""}
    <td>
        <text color="black">{$topInfo_{$subSelect}}</text>
    </td>
{/if}

<!-- TODO: Test and Implement -->
{if isset( {$topSearchFields_{$subSelect}} ) && {$topSearchFields_{$subSelect}} != ""}
    {foreach $topSearchFields_{$subSelect} as $entry}
        <td>
            {if isset($entry['infoText']) && !empty($entry['infoText'])}<text>{$entry['infoText']}</text>{/if}
			<tr color="dim">
				<td>
					<input name="{$entry['postField']}" type="text"></input>
				</td>
			</tr>
            <submit type="submit" name="{$entry['postIdentifier']}" value="{$entry['inputName']}" href="{$entry['href']}"></submit>
        </td>
    {/foreach}
{/if}    

<!-- Links at the top of the table -->
{if isset( {$topOptions_{$subSelect}} ) && !empty({$topOptions_{$subSelect}}) }
    <tr>
        <td>
            <buttonList>
                {foreach $topOptions_{$subSelect} as $entry}
                    {if isset($entry["type"]) && $entry["type"] == "text"}
                        </buttonList><text>{$entry["name"]}</text><buttonList>
                    {else}
                        <buttonListButton href="{$entry["href"]}">{$entry["name"]}</buttonListButton>
                    {/if}                         
                {/foreach}
            </buttonList>
        </td>
    </tr>
{/if}

<!-- Column Names -->
<tr color="fadedblack">
    {for $foo=0 to $nColumns_{$subSelect}-1}
        <td>
            <text color="white"><b>{$data_{$subSelect}[0].$foo}</b></text>
        </td>
    {/for}
</tr>

<!-- Contents -->
{if {$data_{$subSelect}} && count($data_{$subSelect}) > 1}
    {for $i = 1 to ($data_{$subSelect}|@count)-1}
        <tr color="{cycle values="dim,clear"}">
            {if array_key_exists( "TP_subtitle", $data_{$subSelect}[$i])}
                <td><text>{$data_{$subSelect}[$i].TP_subtitle}</text></td>
            {else}

                {for $foo=0 to $nColumns_{$subSelect}-1}
                    {if isset($data_{$subSelect}[$i].$foo)}
                        {if $data_{$subSelect}[0].$foo|strstr:"Detailed Time"}
                            <td><text>{$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y, %H:%M:%S"}</text></td>
                        {elseif $data_{$subSelect}[0].$foo|strstr:"Time"}
                            <td><text>{$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y, %H:%M"}</text></td>
                        {elseif $data_{$subSelect}[0].$foo|strstr:"Date"}
                            <td><text>{$data_{$subSelect}[$i].$foo|date_format:"%d-%m-%y"}</text></td>
                        {elseif $data_{$subSelect}[0].$foo|stristr:"Activity"}
                            {assign var="timePassed" value=$serverTime-$data_{$subSelect}[$i].$foo}
                            {if $timePassed == 0}
                                <td><text>Now</text></td>
                            {else}
                                <td><text>{$timePassed} seconds</text></td>
                            {/if}
                        {elseif $data_{$subSelect}[0].$foo|strstr:"Online Status"}
                            {if $data_{$subSelect}[$i].$foo < ($smarty.now - 600) }
                                <td><text><b>Offline</b></text></td>
                            {else}
                                <td><text><b>Online</b></text></td>
                            {/if}
                        {else}
                            <td>
                                {if strstr({$data_{$subSelect}[$i].$foo}, "</a") || strstr({$data_{$subSelect}[$i].$foo}, "</text")}
                                    {$data_{$subSelect}[$i].$foo}
                                {else}
                                    <text>{$data_{$subSelect}[$i].$foo}</text>
                                {/if}
                            </td>
                        {/if}
                    {else}
                        <td><text><i>NULL</i></text></td>
                    {/if}
                {/for}
            {/if}
        </tr>
    {/for}
    
    <!-- Newer / Older Links -->
    {if isset($newerLink_{$subSelect})}
        <tr>
            <td><a href="{$olderLink_{$subSelect}}">Older</a></td>
            <td><a href="{$newerLink_{$subSelect}}">Newer</a></td>                
        </tr>
    {/if} 
    
{else}
    <tr color="dim"><td><text><i>No entries found in database</i></text></td></tr>
{/if}

{if isset( $checkBoxFormLink )}
    <tr><td><submit name="Submit" type="submit" value="{$checkBoxFormSubmit}" href="{$checkBoxFormLink}"></submit></td></tr>
{/if}


{if !isset($hideReturnLink)}
    {if isset($returnLink)}
        <tr><td>
        {if $returnLink === true}
            <a href="?id={$smarty.get.id}">Return</a>
        {else}
            <a href="{$returnLink}">Return</a>
        {/if}
        </td></tr>
    {/if}
{/if}