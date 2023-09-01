<tr>
<td>
    <headerText>Task Information</headerText>
    <text>{$entry['description']|nl2br}</text>   
</td>
</tr>

{if $simpleGuide["req"]}
    <tr>
        <td>
            <headerText>Requirements to Complete</headerText>
        </td>
    </tr>
    {foreach $simpleGuide["req"] as $guideline}
        {if $guideline !== ""}
            <tr>
                <td><text>-- {$guideline}</text></td>
            </tr>
        {/if}
    {/foreach}
{/if}
{if $simpleGuide["rew"]}
    <tr>
        <td>
            <headerText>Rewards for Completing</headerText>
        </td>
    </tr>
    {foreach $simpleGuide["rew"] as $guideline}
        {if $guideline !== ""}
            <tr>
                <td><text>-- {$guideline}</text></td>
            </tr>
        {/if}
    {/foreach}
{/if}
<text><br></text>

<tr><td><a href="?id={if isset($smarty.get.returnID)}{$smarty.get.returnID}{else}{$smarty.get.id}{/if}">Return</a></td></tr>
{if isset($quitLink)}
    <tr><td><a href="?id={$smarty.get.id}{$quitLink}">Quit Mission</a></td></tr>
{/if}
        
