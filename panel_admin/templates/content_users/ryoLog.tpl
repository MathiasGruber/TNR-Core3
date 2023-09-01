<div align="center">
    <table width="95%" class="table">
        <tr>
          <td colspan="3" class="subHeader">Ryo Log: {$username}</td>
        </tr>
        <tr>
          <td colspan="3" style="color:darkred;">Here all ryo sendings from and to {$username}. Only sendings above 10 million are recorded..</td>
        </tr>
        <tr>
          <td colspan="3">
            {if isset($sendingsFrom)}
                {$subSelect="sendingsFrom"}
                {include file="file:{$absPath}/{$sendingsFrom}" title="Sending Ryo"}
            {/if}
          </td>
        </tr>
        <tr>
          <td colspan="3">
            {if isset($sendingsTo)}
                {$subSelect="sendingsTo"}
                {include file="file:{$absPath}/{$sendingsTo}" title="Sending Ryo"}
            {/if}
          </td>
        </tr>
    </table>
    <a href="?id={$smarty.get.id}">Return</a>   
</div>
