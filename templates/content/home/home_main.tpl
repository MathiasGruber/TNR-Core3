<div align="center">
    <table width="90%" class="table" >
      <tr>
          <td colspan="2" class="subHeader">Home</td>
      </tr>
      <tr>
          <td width="60%" style="text-align:left;padding:15px;" align="center" valign="top">
              <b>Home:</b> {$house['name']}<br>
              <b>Comfort Rate:</b> {$house['regen']}
              <br><br>
              <b>Home options available to you:</b><br>
              {if $user['status'] == 'awake'} 
                   - <a href="?id={$smarty.get.id}&act=sleep">Sleep</a><br>
                   - <a href="?id={$smarty.get.id}&act=sell">Sell home</a>
              {elseif $user['status'] == 'asleep'}
                   - <a href="?id={$smarty.get.id}&act=wake">Wake up</a>
              {/if}
              <br>
               - <a href="?id={$smarty.get.id}&act=list">Home list</a>
              <br>
               - <a href="?id={$smarty.get.id}&act=furniture">Manage Furniture</a>
              <br>
               - <a href="?id={$smarty.get.id}&act=inventory">Home Inventory</a>
          </td>
        <td width="40%" style="padding-top:5px;" align="center" valign="top">
              {$uservil = {$user['village']|lower}}
              <img class="home-image" src="{$house_image}" alt="{$house['name']}" />            
              <br>
        </td>
      </tr>
    </table>
</div>