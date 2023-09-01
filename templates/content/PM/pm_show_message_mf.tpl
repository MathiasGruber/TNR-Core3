<div class="page-box">
    <div class="page-title">
        Inbox System
    </div>
    <div class="page-content">

        <div class="stiff-grid stiff-column-min-left-3 page-grid-justify-stretch page-sub-title-top">
            <div class="text-left">
                From:
            </div>
            <div class="text-left">
                <a href="?id=13&page=profile&name={$message[0]['sender']}">{$message[0]['sender_color']}</a>
                <span> - </span>
                <a href="?id=53&act=pm&pmid={$message[0]['time']}&uid={$message[0]['sender_uid']}">
                    <img src="./images/report.gif" style="border:none;" />
                </a>
            </div>
            <div class="text-right">
                {{$message[0]['parsed_time']}} ({$message[0]['parsed_pm_time']})
            </div>
        </div>

        <div>
            <div class="bold">
                <hr>
                <hr>
                {$message[0]['subject']}
            </div>
        </div>

        <div class="text-left page-box page-content">
            {$message[0]['message']}
        </div>

        <div class="stiff-grid stiff-column-2 page-grid-justify-stretch">
            <div class="text-left">
                {if $message[0]['sender'] != 'System'}
                    <a href="?id={$smarty.get.id}&act=reply&pmid={$smarty.get.pmid}">Reply</a>
                {else}
                    &nbsp;
                {/if}
            </div>
            <div class="text-right">
                <a href="?id={$smarty.get.id}&act=delete&pmid={$smarty.get.pmid}">Delete message</a>
            </div>
        </div>

        <div>
            <font size="1">TheNinja-RPG staff will <b>NEVER</b> ask for your password!</font><br>
        </div>

    </div>
</div>