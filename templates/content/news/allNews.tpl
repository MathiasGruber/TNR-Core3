{if $news}
    {foreach $news as $newItem}
        {strip}
        <div class="table" style="text-align:center;width:95%;">
            <div class="subHeader">{$newItem.title|stripslashes}</div>
            <div class="tdDiv" style="text-align:left;padding:15px;">{$newItem.content|stripslashes}</div>
            <div class="tdDiv" style="font-size:13px;"><br>
                {$newItem.time|date_format:"%D, %I:%M %p"}, <i>Posted by:</i> {$newItem.posted_by}
            </div>
        </div>
        {/strip}
    {/foreach}
{/if} 