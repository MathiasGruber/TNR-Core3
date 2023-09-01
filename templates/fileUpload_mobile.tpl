<tr>
    <td>
        <headerText screenWidth="true">{$subTitle}</headerText>
        {if isset($image) && !empty($image)}
            <img height="400" src="{$image}"></img>
        {/if}
        {if isset({$description})}
            <text>{$description}
            <br>Maximum dimensions: {$dimX} x {$dimY} pixels, {$maxsize}</text>
        {/if}        
    </td>
</tr>

<tr>
    <td>
        <input type="file" name="userfile" xDim="{$dimX}" yDim="{$dimY}"></input>
        <submit type="submit" name="Submit" value="Upload"></submit>
    </td>
</tr>


{if isset($returnLink)}
    <tr><td><a href="?id={$smarty.get.id}">Return</a></td></tr>
{/if} 