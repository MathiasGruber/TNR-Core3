<tr>
    <td>
        <headerText>Monthly Mission</headerText>
        <text>{$missionText}</text>
        {if $didMission == NULL}
            <text><b>Post an URL to your mission result</b></text>
            <tr color="dim">
              <td>
                <input type="text" name="url" /><br>
              </td>
            </tr>
            <submit name="Submit" type="submit" href="?id={$smarty.get.id}&act=submitMission" value="Submit for Evaluation" />
        {elseif $didMission == "Completed"}
            <text>You have already performed the mission this month. Come back soon.</text>
        {else}
            <text>You have submitted following URL:<br>{$didMission}<br><br>You can still change it, if you wish?</text>
            <input type="text" name="url" /><br>
            <submit name="Submit" type="submit" href="?id={$smarty.get.id}&act=submitMission" value="Submit for Evaluation" />
        {/if}
    </td>
</tr>