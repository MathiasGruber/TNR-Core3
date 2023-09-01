<div class="page-sub-title-top">
    Clan Details
</div>

<div>
    <img style="border:1px solid #000000;" src="{$signature}">
</div>

<div class="page-grid page-column-2">
    <div class="stiff-grid stiff-column-2">

        <div class="text-left">
            <b>Clan Name:</b>
        </div>
        <div>
            {$clan['name']}
        </div>



        <div class="text-left">
            <b>Clan Rank:</b>
        </div>
        <div>
            {$clan['rank']}
        </div>



        <div class="text-left">
            <b>Clan Element:</b>
        </div>
        <div>
            {$clan['element']}
        </div>



        <div class="text-left">
            <b>Kage Position:</b>
        </div>
        <div>
            {$clan['kage_vote']}
        </div>



        <div class="text-left">
            <b>Clan leader:</b>
        </div>
        <div>
            {if isset($canClaim) && $canClaim == true}
                <a href="?id=14&act2=claimLeader">Claim</a>
            {else}
                {$clan['leaderName']}
            {/if}
        </div>

    </div>
    <div class="stiff-grid stiff-column-2">

        <div class="text-left">
            <b>Activity Points:</b>
        </div>
        <div>
            {$clan['activity']}
        </div>


        <div class="text-left">
            <b>Clan Members:</b>
        </div>
        <div>
            {$clanUsers}
        </div>



        <div class="text-left">
            <b>Activity / Member:</b>
        </div>
        <div>
            {$avgPoints}
        </div>


        <div class="text-left">
            <b>Your Activity:</b>
        </div>
        <div>
            {$userPoints}
        </div>



        <div class="text-left">
            <b>Your Clan Rank:</b>
        </div>
        <div>
            {$userClanRank}
        </div>

    </div>
</div>