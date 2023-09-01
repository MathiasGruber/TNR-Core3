<div class="page-box">
    <div class="page-title">
        News
    </div>
    <div class="page-content">
        {if $news}
            {foreach $news as $key => $newItem}
                <div class="page-sub-title{if $key == 0}-top{/if} stiff-grid stiff-column-fr-3 font-shrink-early page-grid-justify-stretch">
                    <div class="dim anti-bold text-left">
                        <i>Posted by:</i> {$newItem.posted_by}
                    </div>

                    <div style="width:100%">
                        {$newItem.title|stripslashes}
                    </div>

                    <div class="dim anti-bold text-right">
                        {$newItem.time|date_format:"%D, %I:%M %p"} 
                    </div>
                </div>

                <div>
                    {$newItem.content|stripslashes}
                </div>
            {/foreach}
        {/if}
    </div>
</div>

