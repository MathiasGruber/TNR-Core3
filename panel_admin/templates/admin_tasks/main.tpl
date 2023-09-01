<script type="text/javascript">
    {include file="./Scripts/taskInterface.js"}
</script>
<div align="center">
    <table width="95%" class="table">
        <tr>
          <td colspan="5" class="subHeader">Tasks, Quests & Achievements Admin</td>
        </tr>
        <tr>
          <td colspan="5" style="color:darkred;text-align:left;padding:10px;">
            This Core 3 enables you to create tasks (required to level, or optional), quests (one active at a time),
            and achievements (both TNR badges & facebook achievements). <br>
            Check instructions for more info, and always remember the following:
            <br><br>
            <ul>
                <li>Achievements can only be enabled if a picture is set for the task.</li>
                <li>Score is to indicate the difficulty of getting the achievement done. </li>
                <li>Total score right now is: {$totalScore}. This value MUST NOT exceed 1000.
            </ul>
          </td>
        </tr>
        <tr>
          <td id="newEntry" class="tdBorder" width="20%">
              <a id="a_newEntry"  href="?id={$smarty.get.id}&act=new">New Entry</a>
          </td>
          <td id="instructions" class="tdBorder" width="20%">
              <a id="a_instructions" href="?id={$smarty.get.id}&act=instructions">Instructions</a>
          </td>
          <td id="currentList" class="tdBorder" width="20%">
              <a id="a_currentList" href="?id={$smarty.get.id}&act=current">List of Entries</a>
          </td>
          <td id="search" class="tdBorder" width="20%">
              <a id="a_search" href="?id={$smarty.get.id}&act=clear">Search Entries</a>
          </td>
          <td id="facebookAchievements" class="tdBorder" width="20%">
              <a id="a_facebookAchievements" href="?id={$smarty.get.id}&act=clear">See Facebook Achivements</a>
          </td>
        </tr>
        <tr>
          <td colspan="5" style="padding-top:20px;">
              
              <!-- New task -->
              <div align="center" style="display:none;" id="div_newEntry">
                {if isset($NewForm)}
                    {include file="file:{$absPath}/{$NewForm}" title="New Entry"}
                {/if}
             </div>
              
              <!-- List of current tasks -->
              <div align="center" style="display:none;" id="div_currentList">
                {if isset($tasksAndQuests)}
                    {$subSelect="tasksAndQuests"}
                    {include file="file:{$absPath}/{$tasksAndQuests}" title="Admin tasksAndQuests"}
                {/if}
             </div>
             
             <!-- Instructions -->
              <div align="center" style="display:none;" id="div_instructions">
                {include file="./instructions.tpl"}
             </div>
             
             <!-- Search -->
              <div align="center" style="display:none;" id="div_search">
                {include file="./search.tpl"}
             </div>
             
             <!-- Search -->
              <div align="center" style="display:none;" id="div_facebookAchievements">
                {include file="./fbAchievements.tpl"}
             </div>
             
          </td>
        </tr>
        
    </table>
</div>