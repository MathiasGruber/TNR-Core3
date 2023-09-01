<tr>
    <td>
        <headerText>Train Jutsu</headerText>
    </td>
</tr>
<tr>
    <td align="center"> 
To succeed as a ninja, knowledge and mastery of jutsu is of the greatest importance<br>
You can train in the following types of jutsu:
    </td>
</tr>
<tr>
    <td>
        <select name="jutsu_type">
            <option value="normal">Normal jutsu</option>
            <option value="special">Special jutsu</option>
            <option value="village">Village jutsu</option>
            {foreach $select1 as $key => $value}
                <option value="{$key}">{$value}</option>
            {/foreach}
        </select>
    </td>
</tr>
<tr>
    <td>
        <select name="attack_type">
            <option value="x">All available</option>
            <option value="ninjutsu">Ninjutsu</option>
            <option value="genjutsu">Genjutsu</option>
            <option value="taijutsu">Taijutsu</option>
            <option value="weapon">Bukijutsu</option>
            {foreach $select2 as $key => $value}
                <option value="{$key}">{$value}</option>
            {/foreach}
        </select>
    </td>
</tr>
<tr>
    <td>
        <select name="rank_type">
            <option value="x">All available</option>
            {foreach $select3 as $key => $value}
                <option value="{$key}">{$value}</option>
            {/foreach}
        </select>
    </td>
</tr>
<tr>
    <td>
        <select name="element">
            <option value="x">All available</option>
            {foreach $select4 as $key => $value}
                <option value="{$key}">{$value}</option>
            {/foreach}
        </select>
    </td>
</tr>
<tr>
    <td>
        <submit type="submit" name="Submit" value="Submit"></submit>
    </td>
</tr>
<tr>
    <td>
        <hidden type="hidden" name="train" value="{$train}"></hidden>
    </td>
</tr>

{if isset($returnLink)}    
    {if $returnLink === true}
        <tr><td><button href="?id={$smarty.get.id}">Return</button></td></tr>
    {elseif $returnLink !== false}
        <tr><td><button href="{$returnLink}">Return</button></td></tr>
    {/if}    
{/if} 