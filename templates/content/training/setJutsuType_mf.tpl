<div class="page-sub-title">
    Train Jutsu
</div>

<div>
    To succeed as a ninja, knowledge and mastery of jutsu is of the greatest importance
    You can train in the following types of jutsu:
</div>

<form class="autoInput" method="post" action="" id="trainingForm">
    <div class="page-grid page-column-4">
        <div>
            <select name="jutsu_type" class="page-drop-down-fill">
                <option value="normal">Normal jutsu</option>
                <option value="special">Special jutsu</option>
                <option value="village">Village jutsu</option>
                {foreach $select1 as $key => $value}
                    <option value="{$key}">{$value}</option>
                {/foreach}
            </select>
        </div>

        <div>
            <select name="attack_type" class="page-drop-down-fill">
                <option selected value="x">All available</option>
                <option value="ninjutsu">Ninjutsu</option>
                <option value="genjutsu">Genjutsu</option>
                <option value="taijutsu">Taijutsu</option>
                <option value="weapon">Bukijutsu</option>
                {foreach $select2 as $key => $value}
                    <option value="{$key}">{$value}</option>
                {/foreach}
            </select>
        </div>

        <div>
            <select name="rank_type" class="page-drop-down-fill">
                <option selected value="x">All available</option>
                {foreach $select3 as $key => $value}
                    <option value="{$key}">{$value}</option>
                {/foreach}
            </select>
        </div>

        <div>
            <select name="element" class="page-drop-down-fill">
                <option selected value="x">All available</option>
                {foreach $select4 as $key => $value}
                    <option value="{$key}">{$value}</option>
                {/foreach}
            </select>
        </div>

    </div>

    <div>
        <input type="submit" name="Submit" value="Submit" class="page-button-fill">
        <input type="hidden" name="train" value="{$train}">
    </div>
</form>
