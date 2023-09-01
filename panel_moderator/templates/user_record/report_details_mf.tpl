<div class="page-box">
    <div class="page-title">
        User Report
    </div>
    <div class="page-content">
        <div class="table-grid table-column-7">

            <div class="lazy table-legend row-header column-1">
                User
            </div>

            <div class="lazy table-legend row-header column-2">
                Reporter
            </div>

            <div class="lazy table-legend row-header column-3">
                Date
            </div>

            <div class="lazy table-legend row-header column-4">
                Type
            </div>

            <div class="lazy table-legend row-header column-5">
                Issue Date
            </div>

            <div class="lazy table-legend row-header column-6">
                Status
            </div>

            <div class="lazy table-legend row-header column-7">
                Processor
            </div>



            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-1">
                User
            </div>
            <div class="lazy table-cell table-alternate-1 row-1 column-1">
		        {$name}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-2">
                Reporter
            </div>
            <div class="lazy table-cell table-alternate-1 row-1 column-2">
		        {$rname}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-3">
                Date
            </div>
            <div class="lazy table-cell table-alternate-1 row-1 column-3">
		        {$report[0]['time']|date_format:"%Y-%m-%d %H:%M:%S"}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-4">
                Type
            </div>
            <div class="lazy table-cell table-alternate-1 row-1 column-4">
		        {$report[0]['type']}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-5">
                Issue Date
            </div>
            <div class="lazy table-cell table-alternate-1 row-1 column-5">
		        {$report[0]['mt']|date_format:"%Y-%m-%d %H:%M:%S"}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-6">
                Status
            </div>
            <div class="lazy table-cell table-alternate-1 row-1 column-6">
		        {$report[0]['status']}
            </div>

            <div class="lazy table-legend-mobile table-alternate-1 row-1 column-7">
                Processor
            </div>
            <div class="lazy table-cell table-alternate-1 row-1 column-7">
		        {$processed}
            </div>

        </div>

        {if $can_take_control || $report[0]['uid'] != 0}
            <div class="page-sub-title"">
                Actions
            </div>
            <div>
                {if $report[0]['uid'] != 0}
                    <a href="?id={$smarty.get.id}&amp;uid={$report[0]['uid']}">User's Record</a>
                    {if $can_take_control}
                        <br>
                    {/if}
                {/if}

                {if $can_take_control}
                    <a href='?id={$smarty.get.id}&act=take_control&eid={$smarty.get.eid}'>Take Control!</a>
                    <br>
                    <a href='?id={$smarty.get.id}&act=delete&eid={$smarty.get.eid}'>Delete!</a>
                {/if}
            </div>
        {/if}

        <div class="page-sub-title">
            Reason:
        </div>
        <div>
            {$report[0]['reason']}
        </div>
    
        <div class="page-sub-title">
            Reported Issue:
        </div>
        <div>
            {$message}
        </div>

        {if isset($handleReport) && $handleReport == true }
            <div class="page-sub-title">
                Alter Report Status
            </div>
            <div>
                Altering the report status will designate you as the processing moderator, any questions regarding this matter, and it's    processing can and will be directed at you.<br>
                <br>Once you have set the status to anything other than &quot;unviewed&quot; no other moderator or admin can alter the  report's status, unless you set it back to &quot;unviewed&quot;<br>
                <br>you cannot reset &quot;ungrounded&quot; or &quot;handled&quot; reports to unviewed.
            </div>
            <form method="post" action="?id={$smarty.get.id}&act=reportDetails&eid={$smarty.get.eid}">
                <select name="status">
                    <option>unviewed</option>
                    <option>in progress</option>
                    <option>ungrounded</option>
                    <option>handled</option>
                </select></td>
                <input type="submit" name="Submit" value="Submit">
            </form>
        {/if}
    </div>
</div>