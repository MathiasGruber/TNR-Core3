{$rank_to_letter = [5 => 'A', 4 => 'B', 3 => 'C']}

{if $userdata['village'] != 'Syndicate'}
    {$rank_to_letter[2] = 'D'}
{/if}

<table class="table" style="width:95%">
    <tr>
        <td colspan="2" class="subHeader">{if $userdata['village'] != "Syndicate"}Missions{else}Crimes{/if} {$mission_count}/{$mission_count_per_day}</td>
    </tr>
    <tr>
        <td>
            {if $userdata['village'] != "Syndicate"}
                Missions tutorial text here.
            {else}
                Crimes tutorial text here.
            {/if}
            <br>
        </td>
    </tr>

    {foreach $rank_to_letter as $rank => $letter}
        {if $userdata['rank_id'] >= $rank}
            <tr>
                <td>

                    <details {if $userdata['rank_id'] == $rank}open{/if}>
                        <summary class="tdTop" style="margin-left:-5px;margin-right:-5px;padding:5px;font-size:16px;">{$letter} Rank</summary>
                        {foreach $missions[$letter] as $mission}
                            <br>
                            <a href="?id={$_GET['id']}&qid={$mission->qid}" class="page-button-fill">{$mission->name}</a>
                            <br>
                        {/foreach}
                        <br>
                    </details>

                </td>
            </tr>
        {/if}
    {/foreach}

</table>
