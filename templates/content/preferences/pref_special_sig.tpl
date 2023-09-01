<div align="center">
    <form  method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td class="subHeader">TNR signature</td>
            </tr>
            <tr>
                <td align="center" style="padding:5px;">Just copy and paste the BBcode below and help attract new users to TNR!<br>
                    Here's a preview of how your signature will look:<br>
                    <img src="{$s3}/{$smarty.session.uid}.gif" border="0"><br>
                    {if (($user[0]['dynamic_signature'] < ({$smarty.now} - 10800)) && ($user[0]['dynamic_signature'] !== 0))}
                        <a href="?id=4&act=specialsig&action=update">Update Signature</a>
                    {else}
                        Signatures can be updated every 3 hours!					
                    {/if}
                </td>
            </tr>
            <tr>
                <td>
                    <b>Forum Signature (BBCode):</b><br>
                    <textarea name="copytext3" cols=55 rows=3 readonly="true" >[url=http://www.theninja-rpg.com][img]{$s3}/signatures/{$smarty.session.uid}.gif[/img][/url]</textarea>
                    <br><br>
                    <b>MySpace / HTML Code:</b><br>
                    <textarea name="copytext3" cols=55 rows=3 readonly="true" ><a href="http://www.theninja-rpg.com"><img src="{$s3}/signatures/{$smarty.session.uid}.gif" /><img /></a></textarea><br>
			</td>
   	    </tr>
     	</table>
	</form>
        <a href="?id={$smarty.get.id}" class="returnLink">Return</a>
</div>