<div class="page-box">
    <div class="page-title">
        Ryo Log: {$username}
    </div>
    <div class="page-content">
        <div class="page-sub-title-top">
          Here all ryo sendings from and to {$username}. Only sendings above 10 million are recorded.
        </div>
        {if isset($sendingsFrom)}
            {$subSelect="sendingsFrom"}
            {include file="file:{$absPath}/{$sendingsFrom}" title="Sending Ryo"}
        {/if}
        {if isset($sendingsTo)}
            {$subSelect="sendingsTo"}
            {include file="file:{$absPath}/{$sendingsTo}" title="Sending Ryo"}
        {/if}
    </div>
</div>
