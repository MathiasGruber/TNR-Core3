<tr>
    <td>
        <headerText>War Control</headerText>        
        {if isset($otherVillages)}
            <text>With this form you may declare war on other villages or surrender active wars. Beware, wars cost village points to sustain and may break alliances, so be sure to check out war information on the different villages before doing anything rash.</text>
            <select name="wardeclaration">
                {foreach $otherVillages as $village}
                    <option value="{$village['name']}">{$village['name']}</option>
                {/foreach}
            </select>
            <submit type="submit" name="Submit" value="Declare War" />
        {/if}  
        {if !empty($userEnemies)}   
            <select name="surrenderrequest">
                {foreach $userEnemies as $village}
                    <option value="{$village}">{$village}</option>
                {/foreach}
            </select>
            <submit type="submit" name="Submit" value="Request Surrender" />
        {/if} 
    </td>
</tr>

<tr>
    <td>
        <headerText>Alliance Control</headerText>        
        <text>With this form you may request or break alliances with other villages. Forming alliances or breaking certain alliances may have consequences in regards to other alliances, so be careful.</text>
        <select name="requestalliance">
            {foreach $otherVillages as $village}
                <option value="{$village['name']}">{$village['name']}</option>
            {/foreach}
        </select>
        <submit type="submit" name="Submit" value="Request Alliance" />
        
        {if !empty($userAllies)}  
            <select name="breakalliance">
                {foreach $userAllies as $village}
                    <option value="{$village}">{$village}</option>
                {/foreach}
            </select>
            <submit type="submit" name="Submit" value="Break Alliance" />
        {/if}  
    </td>
</tr>
   
{if isset($requests)}
    {$subSelect="requests"}
    {include file="file:{$absPath}/{$requests|replace:'.tpl':'_mobile.tpl'}" title="Requests"}
{/if}     

{if isset($allianceData)}
    {include file="file:{$absPath}/templates/content/alliance/alliances_mobile.tpl" title="Alliance Data"}
{/if}

<tr>
    <td>
        <a href="?id={$smarty.get.id}&act={$smarty.get.act}">Return</a>
    </td>
</tr>
    
