<form  method="post" action="" class="lazy page-box">
    <div class="lazy page-title">
        TNR signature
    </div>
    <div class="lazy page-content">
        <div>
            Just copy and paste the BBcode below and help attract new users to TNR!
        </div>

        <div>
            Here's a preview of how your signature will look:
        </div>

        <div>
            <img src="{$s3}/signatures/{$smarty.session.uid}.gif" border="0">
        </div>
        

        {if (($user[0]['dynamic_signature'] < ({$smarty.now} - 10800)) && ($user[0]['dynamic_signature'] !== 0))}
            <a href="?id=4&act=specialsig&action=update">Update Signature</a>
        {else}
            <div>
                Signatures can be updated every 3 hours!					
            </div>
        {/if}

        <b>Forum Signature (BBCode):</b>

        <textarea name="copytext3" cols=55 rows=3 readonly="true" >[url=http://www.theninja-rpg.com][img]{$s3}/signatures/{$smarty.session.uid}.gif[/img][/url]</textarea>

        <b>MySpace / HTML Code:</b>

        <textarea name="copytext3" cols=55 rows=3 readonly="true" >
            <a href="http://www.theninja-rpg.com">
                <img src="{$s3}/signatures/{$smarty.session.uid}.gif" />
                <img />
            </a>
        </textarea>
        <br>
    </div>
</form>
