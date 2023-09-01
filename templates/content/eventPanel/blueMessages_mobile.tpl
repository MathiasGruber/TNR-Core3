<tr>
<td>
    <headerText>Notification Message</headerText>
    <text>Use this feature responsibly, too much joking around with this feature will not be tolerated.</text>
    <input name="message" type="textarea" value="message"></input>
    <select name="mask">
        <option value="All">All</option>
        <option value="Konoki">Konoki</option>
        <option value="Shroud">Shroud</option>
        <option value="Shine">Shine</option>
        <option value="Samui">Samui</option>
        <option value="Silence">Silence</option>
        <option value="Syndicate">Syndicate</option>
    </select>
    <submit type="submit" name="Submit" value="Submit"></submit>
</td>
</tr>

{* Show the log *}
{if isset($log)}
    {$subSelect="log"} 
    {include file="file:{$absPath}/{$log|replace:'.tpl':'_mobile.tpl'}" title="AI log"}
{/if}
