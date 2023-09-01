<div class="page-box">
    <div class="page-title">
        Scout
    </div>

    <div class="page-content">
        {$first = true}
        {if isset($users)}
            {$subSelect="users"}
            {include file="file:{$absPath}/{$users}" title="Nearby Users"}
        {/if}
        {$first = false}
        {if isset($resources)}
            {$subSelect="resources"}
            {include file="file:{$absPath}/{$resources}" title="Nearby resources"}
        {/if}
    </div>
</div>