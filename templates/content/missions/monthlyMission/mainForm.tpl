<form id="form1" name="form1" method="post" action="?id={$smarty.get.id}&act=submitMission">
    <table width="95%" class="table">
        <tr>
            <td class="subHeader">Monthly Mission</td>
        </tr>
        <tr>
            <td class="tableColumns tdBorder" style="padding:20px;">
                {$missionText}
            </td>
        </tr>
        <tr>
            <td>
                {if $didMission == NULL}
                    <b>Post an URL to your mission result</b><br>
                    <input type="text" name="url" class="textfield" style="margin:5px;width:200px;" /><br>
                    <input name="Submit" type="submit" class="input_submit_btn" value="Submit for Evaluation" />
                {elseif $didMission == "Completed"}
                    You have already performed the mission this month. Come back soon.
                {else}
                    You have submitted following URL: <b>{$didMission}</b>. You can still change it, if you wish?<br>
                    <input type="text" name="url" class="textfield" style="margin:5px;width:200px;" /><br>
                    <input name="Submit" type="submit" class="input_submit_btn" value="Submit for Evaluation" />
                {/if}
                      
            </td>
        </tr>
    </table>
</form>