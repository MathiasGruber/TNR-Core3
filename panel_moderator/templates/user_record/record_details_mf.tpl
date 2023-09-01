<div class="page-box">
    <div class="page-title">
        {ucfirst($data[0]["action"])} Details
    </div>
    <div class="page-content">
        <div class="table-grid table-column-min-left-5">
            <div class="table-legend row-header column-1">Username</div>
            <div class="table-legend row-header column-2">Moderator</div>
            <div class="table-legend row-header column-3">Type</div>
            <div class="table-legend row-header column-4">Date</div>
            <div class="table-legend row-header column-5">Reason</div>

            <div class="table-legend-mobile table-alternate-1 row-0 column-1">
                Username
            </div>
            <div class="table-cell table-alternate-1 column-1 row-0">
                {$data[0]["username"]}
            </div>

            <div class="table-legend-mobile table-alternate-1 row-0 column-2">
                Moderator
            </div>
            <div class="table-cell table-alternate-1 column-2 row-0">
                {$data[0]["moderator"]}
            </div>

            <div class="table-legend-mobile table-alternate-1 row-0 column-3">
                Type
            </div>
            <div class="table-cell table-alternate-1 column-3 row-0">
                {$data[0]["action"]}
            </div>

            <div class="table-legend-mobile table-alternate-1 row-0 column-4">
                Date
            </div>
            <div class="table-cell table-alternate-1 column-4 row-0">
                {$data[0]['time']|date_format:"%Y-%m-%d %H:%M:%S"}
            </div>

            <div class="table-legend-mobile table-alternate-1 row-0 column-5">
                Reason
            </div>
            <div class="table-cell table-alternate-1 column-5 row-0">
                {$data[0]["reason"]}
            </div>

        </div>

        <div class="page-sub-title">
            Message
        </div>
        <div>
            {$data[0]["message"]}
        </div>

        {if isset( $data[0]['override_reason'] ) && trim($data[0]['override_reason']) != "" && $data[0]['override_reason'] != '<p></p>'}

            <div class="page-sub-title">
                Overridden (by: {$data[0]["override_by"]})
            </div>
            <div>
                {$data[0]["override_reason"]}
            </div>
        {/if}

        <div class="page-sub-title">Options</div>

        <div>
            <a href="?id={$smarty.get.id}&uid={$data[0]["uid"]}">Return to user violations</a>
            {if isset($confirmDeletion)}
                <form name="form1" method="post" action="">
                    <input type="submit" name="Submit" value="Delete Record"/>
                </form>
            {/if}
        </div>

    </div>
</div>