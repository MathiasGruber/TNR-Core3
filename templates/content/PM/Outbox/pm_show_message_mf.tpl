<div class="page-box">
    <div class="page-title">
        Outbox System
    </div>
    <div class="page-content">
        <div class="stiff-grid stiff-column-min-left-3 page-grid-justify-stretch page-sub-title-top">
            <div class="text-left">
                To:
            </div>
            <div class="text-left">
                <a href="?id=13&page=profile&name={$message[0]['receiver']}">{$message[0]['receiver_color']}</a>
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
                &nbsp;
            </div>
            <div class="text-right">
                <a href="?id={$smarty.get.id}&act=delete&pmid={$smarty.get.pmid}">Delete message</a>
            </div>
        </div>

    </div>
</div>