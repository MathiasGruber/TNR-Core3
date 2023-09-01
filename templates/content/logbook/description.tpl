<table class="table" style="width:95%;" >
    <tr>
        <td class="subHeader">Task Information</td>
    </tr>
    <tr>
        <td class="tableColumns">{$entry['name']}</td>
    </tr>
    <tr>
        <td style="text-align:left;padding:15px;">
            {$entry['description']|nl2br}<br>
        </td>
    </tr>
    {if $simpleGuide["req"]}
        <tr>
            <td class="tableColumns tdBorder">
                Requirements to Complete
            </td>
        </tr>
        {foreach $simpleGuide["req"] as $guideline}
            {if $guideline !== ""}
                <tr class="{cycle values="row1,row2"}" >
                  <td>{$guideline}</td>
                </tr>
            {/if}
        {/foreach}
    {/if}
    {if $simpleGuide["rew"]}
        <tr>
            <td style="border-top:1px solid #000000;">
                &nbsp;
            </td>
        </tr>
        <tr>
            <td class="tableColumns tdBorder">
                Rewards for Completing
            </td>
        </tr>
        {foreach $simpleGuide["rew"] as $guideline}
            {if $guideline !== ""}
                <tr class="{cycle values="row1,row2"}" >
                  <td>{$guideline}</td>
                </tr>
            {/if}
        {/foreach}
    {/if}
    <tr>
        <td style="border-top:1px solid #000000;">
            <a href="?id={$smarty.get.id}">Return</a> 
            {if isset($quitLink)}
                - <a href="?id={$smarty.get.id}{$quitLink}">Quit Mission</a>
            {/if}
        </td>
    </tr>
</table>    
        
        
