<script type="text/javascript" src="files/javascript/general.js"></script>

<script defer type="text/javascript">
    {include file="./Scripts/trainScripts_mf.js"}
</script>



<div class="page-box font-shrink-early">
    <div class="page-title">
        Training Overview
    </div>
    <div class="page-content page-column-min-left-2">

        <div class="span-2 page-sub-title-top">
            User Stats
        </div>

        <div>
            <img src="{$avatar}" />
        </div>

        <div class="stiff-grid stiff-column-2 page-grid-justify-stretch grid-gap-none">

            <div class="text-left table-alternate-1 table-cell-small-no-border">
                <b>Rank:</b>
            </div>

            <div class="table-alternate-1 table-cell-small-no-border">
                {$user['rank']}
            </div>

            <div class="text-left table-alternate-2 table-cell-small-no-border">
                <b>Level:</b>
            </div>

            <div class="table-alternate-2 table-cell-small-no-border">
                {$user['level']}
            </div>

            <div class="text-left table-alternate-1 table-cell-small-no-border">
                <b>Primary element:</b> 
            </div>

            <div class="table-alternate-1 table-cell-small-no-border">
                <span id="element_mastery_1">{$user['element_mastery_1']}</span>
            </div>

            <div class="text-left table-alternate-2 table-cell-small-no-border">
                <b>Secondary element:</b> 
            </div>

            <div class="table-alternate-2 table-cell-small-no-border">
                <span id="element_mastery_2">{$user['element_mastery_2']}</span>
            </div>

            <div class="text-left table-alternate-1 table-cell-small-no-border">
                <b>Strength:</b>
            </div>

            <div class="table-alternate-1 table-cell-small-no-border">
                <span id="strength">{$user['strength']}</span>
            </div>

            <div class="text-left table-alternate-2 table-cell-small-no-border">
                <b>Intelligence:</b>
            </div>

            <div class="table-alternate-2 table-cell-small-no-border">
                <span id="intelligence">{$user['intelligence']}</span>
            </div>

            <div class="text-left table-alternate-1 table-cell-small-no-border">
                <b>Willpower:</b>
            </div>

            <div class="table-alternate-1 table-cell-small-no-border">
                <span id="willpower">{$user['willpower']}</span>
            </div>

            <div class="text-left table-alternate-2 table-cell-small-no-border">
                <b>Speed:</b>
            </div>

            <div class="table-alternate-2 table-cell-small-no-border">
                <span id="speed">{$user['speed']}</span>
            </div>
        </div>

    </div>
    <div class="page-content page-grid page-column-2">

        <div class="span-2 page-grid page-column-2 page-grid-justify-stretch grid-gap-none">
            <div class="stiff-grid stiff-column-2 page-grid-justify-stretch grid-gap-none">
                <div class="text-left table-alternate-1 table-cell-small-no-border"><b>Taijutsu strength:</b></div>
                <div class="table-alternate-1 table-cell-small-no-border"><span id="tai_off">{$user['tai_off']}</span></div>

                <div class="text-left table-alternate-2 table-cell-small-no-border"><b>Ninjutsu strength:</b></div>
                <div class="table-alternate-2 table-cell-small-no-border"><span id="nin_off">{$user['nin_off']}</span></div>

                <div class="text-left table-alternate-1 table-cell-small-no-border"><b>Genjutsu strength:</b></div>
                <div class="table-alternate-1 table-cell-small-no-border"><span id="gen_off">{$user['gen_off']}</span></div>

                <div class="text-left table-alternate-2 table-cell-small-no-border"><b>Bukijutsu strength:</b></div>
                <div class="table-alternate-2 table-cell-small-no-border"><span id="weap_off">{$user['weap_off']}</span></div>
            </div>
            <div class="stiff-grid stiff-column-2 page-grid-justify-stretch grid-gap-none">
                <div class="text-left table-alternate-1 table-cell-small-no-border"><b>Taijutsu defense:</b></div>
                <div class="table-alternate-1 table-cell-small-no-border"><span id="tai_def">{$user['tai_def']}</span></div>

                <div class="text-left table-alternate-2 table-cell-small-no-border"><b>Ninjutsu defense:</b></div>
                <div class="table-alternate-2 table-cell-small-no-border"><span id="nin_def">{$user['nin_def']}</span></div>

                <div class="text-left table-alternate-1 table-cell-small-no-border"><b>Genjutsu defense:</b></div>
                <div class="table-alternate-1 table-cell-small-no-border"><span id="gen_def">{$user['gen_def']}</span></div>

                <div class="text-left table-alternate-2 table-cell-small-no-border"><b>Bukijutsu defense:</b></div>
                <div class="table-alternate-2 table-cell-small-no-border"><span id="weap_def">{$user['weap_def']}</span></div>
            </div>
        </div>

        {if isset($release)}
            <div class="span-2">
                Time until release from jail: {$release}
            </div>
        {/if}

        {if isset($wrapLoad)}
            {$input_full_page = false}
            <div class="span-2" id="pageWrapper">
                {include file="file:{$absPath}/{$wrapLoad}" title="Training options"}
            </div>
        {/if}
    </div>
</div>



