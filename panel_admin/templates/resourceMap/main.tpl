<div align="center">
    <table width="95%" class="table">
        <tr>
          <td colspan="2" class="subHeader">Resource Map</td>
        </tr>
        <tr>
          <td colspan="2" class="tdTop">
              This is the resource map as it looks right now, from -100,-100 to 100,100.<br>
              Red signifies resource fields<br>
              Green signifies the map boundaries.<br>
              Yellow signifies the resource picked in the left-hand menu</td>
        </tr>
        <tr>
          <td colspan="2">
              <a href='?id={$smarty.get.id}&act=clear'>Clear Resources</a> | 
              <a href='?id={$smarty.get.id}&act=create'>Recreate Resources</a>
          </td>
        </tr>
        <tr>
          <td valign="top">
              {if isset($resources)}
                    {$subSelect="resources"}  
                    {include file="file:{$absPath}/{$resources}" title="Resources"}
                {/if}
          </td>
          <td valign="top">
            {$resourceImage}
          </td>
        </tr>
    </table>
</div>