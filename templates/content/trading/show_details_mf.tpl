<div class="page-box">
    <div class="page-title">
        Trade Details
    </div>
    <div class="page-content">

        <div class="stiff-grid stiff-column-min-left-2">
            <div>
                Username: 
            </div>
            <div>
                <a href="?id=13&page=profile&name={$trade[0]['username']}">{$trade[0]['username']}</a>
            </div>

            <div>
                Made on:
            </div>
            <div>
                {$trade[0]['time']|date_format:"%d-%m-%y, %H:%M"}
            </div>

            <div>
                Attached Message:
            </div>
            <div>
                {$trade[0]['message']}
            </div>
        </div>

        {if isset($items)}
            {$subSelect="items"}
            {include file="file:{$absPath}/{$items}" title="Items"}
        {/if}
    </div>
</div>