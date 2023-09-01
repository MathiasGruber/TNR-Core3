<div class="page-box">
    <div class="page-title">
        User Record
    </div> 
    <div class="page-content">

        <div class="page-sub-title-top">
            User Information
        </div>
        <div>
            {if isset($extraNotices) && extraNotices != ""}
                {$extraNotices}
            {/if}
            <a href="?id={$smarty.get.id}&act=ryoLog&uid={$userid}">Ryo log</a>
        </div>

        {if isset($modLogData)}
            {$subSelect="modLogData"}
            {include file="file:{$absPath}/{$modLogData}" title="modLogData"}
        {/if}

        {if isset($userReports)}
            {$subSelect="userReports"}
            {include file="file:{$absPath}/{$userReports}" title="userReports"}
        {/if}

        {if isset($changes)}
            {$subSelect="changes"}
            {include file="file:{$absPath}/{$changes}" title="Name changes"}
        {/if}

        <div class="page-sub-title">
            Moderator discussion about user
        </div>

        <div class="table-grid table-column-4">

            <div class="table-legend row-header column-1">Date</div>
            <div class="table-legend row-header column-2">Moderator</div>
            <div class="table-legend row-header column-3">Message</div>
            <div class="table-legend row-header column-3">Action</div>

            {if $tavern != '0 rows'}
                {for $i = 0 to ($tavern|@count)-1} 

                    <div class="table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-1">
                        Date
                    </div>

                    <div class="table-cell table-alternate-{$i % 2 + 1} column-1 row-{$i}">
                        {$tavern[$i]['time']|date_format:"%Y-%m-%d"}
                    </div>

                    <div class="table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-2">
                        Moderator
                    </div>

                    <div class="table-cell table-alternate-{$i % 2 + 1} column-2 row-{$i}">
                        {$tavern[$i]["moderator"]}
                    </div>

                    <div class="table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-3">
                        Message
                    </div>

                    <div class="table-cell table-alternate-{$i % 2 + 1} column-3 row-{$i}">
                        <hr/>
                        {$tavern[$i]["message"]}
                        <hr/>
                    </div>

                    <div class="table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-3">
                        Action
                    </div>

                    <div class="table-cell table-alternate-{$i % 2 + 1} column-3 row-{$i}">
                        {if isset($canDeleteStuff) && $canDeleteStuff == true}
                            <a href='?id={$smarty.get.id}&act=userNotes&postID={$tavern[$i]['id']}&perform=deletepost'>Delete_Comment</a>
                        {/if}
                    </div>
                {/for}
            {else}
                <div class="span-4">No comments on this user</div>
            {/if}
        </div>
        <form id="form1" name="form1" method="post" action="?id={$smarty.get.id}&act=userNotes&uid={$userid}">
            <textarea name="message" cols="35" rows="3" id="message"></textarea><br>
            <input type="submit" name="POSTCOMMENT" value="Upload Comment" />
        </form>
    </div>
</div>