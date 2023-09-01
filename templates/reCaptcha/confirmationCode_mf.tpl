<div class="page-box">
    <div class="page-title">
        Anti-bot Captcha
    </div>
    <form method='post' action=''>
        <div class="page-content">
            <div class="page-sub-title-top">
                {$msg}
            </div>
            <div>
                <center>{$reCaptcha}</center>
                {$loginInfo}
            </div>
            <div>
                <input name="Submit" type="submit" class="page-button-fill" id="Submit" value="Submit"/>
            </div>
            <div class="font-small">
                <br/>
                In case of issues: If you're using an extension that blocks scripts, ads, or trackers in some way, then you need to make an exception on the site or the captcha page. Alternatively, you may need to turn off data compression in your browser for the captcha to function correctly.
            </div>
        </div>
    </form>
</div>