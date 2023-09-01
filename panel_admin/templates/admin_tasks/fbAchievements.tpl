<div align="center">
      <table border="0" cellpadding="0" cellspacing="0" class="table" width="95%" >
    <tr>
        <td align="center" colspan="5" style="border-top:none;" class="subHeader">Achievements Registered on Facebook</td>
    </tr>
    <tr>
      <td width="20%" class="tableColumns tdBorder" ><b>Picture</b></td>
      <td width="20%" class="tableColumns tdBorder" ><b>Name</b></td>
      <td width="20%" class="tableColumns tdBorder" ><b>Score</b></td>
      <td width="20%" class="tableColumns tdBorder" ><b>Link</b></td>
      <td width="20%" class="tableColumns tdBorder" ><b>Delete</b></td>
    </tr>
        
    {if $fbAchievements}
        {foreach $fbAchievements as $entry}
            {strip}
            <tr class="{cycle values="row1,row2"}" >
              <td>
                  <img src="{$entry['image'][0]['url']}" />
              </td>
              <td>{$entry['title']}</td>
              <td>{$entry.data.points}</td>
              <td><a href="{$entry.url}">Achievement URL</a></td>
              <td><a href="?id={$smarty.get.id}&act=deleteFBachievement&oid={$entry['title']}">Delete</a></td>
            </tr>
            {/strip}
        {/foreach}
    {else}
        <tr><td colspan="5">No achievements found</td></tr>
    {/if}         

</table></div>
