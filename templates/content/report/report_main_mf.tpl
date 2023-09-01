<div class="page-box">

    <div class="page-title">
        Report Form
    </div>

    <form name="form1" method="post" action="">
        <div class="page-content">
            {include file="file:{$absPath}/templates/content/report/report_header_mf.tpl" title="Report header"}

            <div class="bold">
                Reporting User
            </div>

            <div class="stiff-grid stiff-column-min-left-2">
                <div class="text-left">
                    Username: 
                </div>
                <div class="text-left">
                    {$message[0]["user"]}
                </div>
            </div>

            <div class="stiff-grid stiff-column-min-left-2">
                <div class="text-left">
                    Reporter: 
                </div>
                <div class="text-left">
                    {$reportBy}
                </div>
            </div>

            <div class="stiff-grid stiff-column-min-left-2 page-grid-justify-stretch">
                <div class="text-left">
                    Reason: 
                </div>
                <div class="text-left">
                    <select name="reason" id="reason" class="page-drop-down-fill">
                        <option>Harassment</option><option>Foul language</option><option>Spamming</option>
                        <option>Selling / Buying accounts</option><option value="other">Other (enter below)</option>
                    </select>
                </div>
            </div>

            <div class="stiff-grid stiff-column-min-left-2 page-grid-justify-stretch">
                <div class="text-left">
                    Details:
                </div>
                <div class="text-left">
                    <div class="textAreaWrapper" style="width:100%">
                        <div class='textAreaCounter'></div>
                        <textarea class="page-text-area-fill" name="reason_text" id="reason_text" maxlength="500" rows="{ceil(500/100)}" form="form1"></textarea>
                    </div>
                </div>
            </div>

            {if isset($message[0]["type"]) && $message[0]["type"] != ""}
                <div class="page-sub-title">
                    {$message[0]["type"]}
                </div>
                <div class="text-left">
                    {$message[0]['message']}
                </div>
            {/if}

            <div>
                <input type="submit" name="Submit" value="Submit" class="page-button-fill"/>
            </div>

        </div>
    </form>

</div>