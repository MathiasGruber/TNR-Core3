{$rank_to_letter = [5 => 'A', 4 => 'B', 3 => 'C']}

{if $userdata['village'] != 'Syndicate'}
    {$rank_to_letter[2] = 'D'}
{/if}

<div class="page-box">
    <div class="page-title">
        {if $userdata['village'] != "Syndicate"}Missions{else}Crimes{/if}<span class="toggle-button-info closed" data-target="missions-info"></span> {$mission_count}/{$mission_count_per_day}
    </div>
    <div class="page-content">

        <div class="toggle-target closed" id="missions-info">
            {if $userdata['village'] != "Syndicate"}
                Missions tutorial text here.
            {else}
                Crimes tutorial text here.
            {/if}
            <br/>
            <br/>
        </div>

        {foreach $rank_to_letter as $rank => $letter}
            {if $userdata['rank_id'] >= $rank}
                <div class="page-sub-title{if $userdata['rank_id'] == $rank}-top{else}-no-margin closed {/if} toggle-button-drop" data-target="#{$letter}-rank-quests">
                    {$letter} Rank
                </div>

                <div id="{$letter}-rank-quests" class="toggle-target page-grid{if $userdata['rank_id'] != $rank} closed{/if}">
                    {foreach $missions[$letter] as $mission}
                        <a href="?id={$_GET['id']}&qid={$mission->qid}" class="page-button-fill">{$mission->name}</a>
                    {/foreach}
                </div>
            {/if}
        {/foreach}
    
    </div>
</div>

