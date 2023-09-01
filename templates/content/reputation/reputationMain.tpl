<div align="center">
    {if isset($reputation)}
        {$subSelect="reputation"}
        {include file="file:{$absPath}/{$reputation}" title="User Reputation"}
    {/if}
    
    <table class="table" style="width:95%;">
      <tr>
        <td colspan="6" class="subHeader">Members / Village</td>
      </tr>
      <tr>
          <td class="tableColumns tdBorder"><b></b></td>
          <td class="tableColumns tdBorder"><b>Konoki</b></td>
          <td class="tableColumns tdBorder"><b>Silence</b></td>
          <td class="tableColumns tdBorder"><b>Samui</b></td>
          <td class="tableColumns tdBorder"><b>Shine</b></td>
          <td class="tableColumns tdBorder"><b>Shroud</b></td>
      </tr>
      <tr>
          <td class="row1"><b>Total</b></td>
          <td class="row1"><b>{$memberCount.konoki}</b></td>
          <td class="row1"><b>{$memberCount.silence}</b></td>
          <td class="row1"><b>{$memberCount.samui}</b></td>
          <td class="row1"><b>{$memberCount.shine}</b></td>
          <td class="row1"><b>{$memberCount.shroud}</b></td>
      </tr>
      <tr>
          <td class="row2"><b>Active</b></td>
          <td class="row2"><b>{$memberCount.akonoki}</b></td>
          <td class="row2"><b>{$memberCount.asilence}</b></td>
          <td class="row2"><b>{$memberCount.asamui}</b></td>
          <td class="row2"><b>{$memberCount.ashine}</b></td>
          <td class="row2"><b>{$memberCount.ashroud}</b></td>
      </tr>
    </table>

    {include file="file:{$absPath}/templates/message.tpl" title="User Reputation"}
</div>
