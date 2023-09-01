<div class="page-box">
    <div class="page-title">
        Territory Challenge
        <span class="toggle-button-info closed" data-target="#territory-info"></span>
    </div>

    <div class="page-content">

        <div class="toggle-target closed" id="territory-info">
            <hr>
            Here you can challenge others for their territories.
            <br>
            You can not challenge any of your own allies.
            <br>
            Mobilizing the challenge costs {$challengeCost} points!
            <br>
            <hr>
        </div>

        <div class="page-sub-title-top">
            Challenge
        </div>
        <form method="post" action="" class="stiff-grid stiff-column-fr-3">
            {if isset($avail_terr) && $avail_terr !== "0 rows"}
                <select name="challenge" id="challenge" class="page-drop-down-fill span-2">
                    {foreach $avail_terr as $territory}
                        <option value="{$territory['id']}">{$territory['owner']}: {$territory['name']}</option>
                    {/foreach}
                </select>
                <input type="submit" name="Submit" id="button" value="Challenge" class="page-button-fill">
            {else}
                <div class="light-solid-box table-cell span-3">
                    None Available
                </div>
            {/if} 
        </form>

        {if isset($allianceData)}
            {include file="file:{$absPath}/templates/content/alliance/alliances_mf.tpl" title="Alliance Data"}
        {/if}
    </div>
</div>