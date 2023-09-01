<form id="form1" name="form1" method="post" action="?id={$smarty.get.id}&act=submitMission" class="page-box">

    <div class="page-title">
        Monthly Mission
    </div>
    
    <div class="page-content page-column-2">
        {if $missionText != ''}
            <div class="page-sub-title-top span-2">
                {$missionText}
            </div>
        {/if}
        
        {if $didMission == NULL}
            <div class="span-2 {if $missionText == ''}page-sub-title-top{/if}">
                <b>Post an URL to your mission result</b>
            </div>
            <input type="text" name="url" class="page-text-input-fill" />
            <input name="Submit" type="submit" class="page-button-fill" value="Submit for Evaluation" />
        {elseif $didMission == "Completed"}
            <div class="span-2 {if $missionText == ''}page-sub-title-top{/if}">
                You have already performed the mission this month. Come back soon.
            </div>
        {else}
            <div class="span-2 {if $missionText == ''}page-sub-title-top{/if}">
                You have submitted following URL: <b>{$didMission}</b>. You can still change it, if you wish?
            </div>
            <input type="text" name="url" class="page-text-input-fill"/>
            <input name="Submit" type="submit" class="page-button-fill" value="Submit for Evaluation" />
        {/if}
                  
    </div>
</form>