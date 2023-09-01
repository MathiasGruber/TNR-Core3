<div class="page-box">
    <div class="page-title">
        Kage Support
    </div>
    <div class="page-content">
        <!-- Information about the clan -->
        {include file="file:{$absPath}/templates/content/clan/clan_info.tpl" title="Clan Information"}

        <!-- Information about kage support -->
        <div class="page-sub-title">
            Kage Position
        </div>
        <div>
            As the leader of your clan you can chose to either support or oppose the kage of your village. 
            This will affect the influence of the kage, and if the influence goes into the negative, the 
            kage will lose his position. The kage's current influence is: <b>{$kageInfluence} points</b>.<br>
        </div>
        <form action="" method="post" class="stiff-grid stiff-column-fr-2">
            <input class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Support Kage" class="button-fill">
            <input class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Oppose Kage" class="button-fill">
        </form>
    </div>
</div>