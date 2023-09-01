<div align="center">
    <table width="95%" class="table">
        <tr>
          <td colspan="3" class="subHeader">Time-Limited Entries</td>
        </tr>
        <tr>
          <td colspan="3" style="color:darkred;">Here you can get an overview of the current time-limited entries in the TNR system.</td>
        </tr>
        <tr>
          <td colspan="3">
            {if isset($items)}
                {$subSelect="items"}
                {include file="file:{$absPath}/{$items}" title="Timed Items"}
            {/if}
          </td>
        </tr>       
        <tr>
          <td colspan="3">
            {if isset($jutsus)}
                {$subSelect="jutsus"}  
                {include file="file:{$absPath}/{$jutsus}" title="Timed Jutsus"}
            {/if}
          </td>
        </tr>       
        <tr>
          <td colspan="3">
            {if isset($quests)}
                {$subSelect="quests"}  
                {include file="file:{$absPath}/{$quests}" title="Timed Quests"}
            {/if}
          </td>
        </tr>
    </table>
</div>