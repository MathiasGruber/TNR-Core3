<div align="center">
    <table width="95%" class="table">
        <tr>
          <td colspan="9" class="subHeader">Tag Checker Admin</td>
        </tr>
        <tr>
          <td colspan="9" style="color:darkred;">
              This system will check content tags in the system, to make sure they adapt the correct format. 
              We will strive to keep this database constantly updated, so if you find anything not working,
              please contact one of the coders to get it fixed.</td>
        </tr>
        <tr>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=jutsu">Jutsu Tags</a></td>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=bloodline">Bloodline Tags</a></td>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=ai">AI Traits & Acts</a></td>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=item">Item Tags</a></td>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=armor">Armor Tags</a></td>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=artifact">Artifact Tags</a></td>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=weapon">Weapon Tags</a></td>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=objectives">Objectives</a></td>
          <td style="border-top:1px solid #000000;"><a href="?id={$smarty.get.id}&act=itemLinks">Item Links</a></td>
        </tr>
    </table>
</div>

{if isset($tags)}
    {$subSelect="tags"}  
    {include file="file:{$absPath}/{$tags}" title="Broken Tags"}
{/if}

<br><br>

{if isset($availableTags)}
    {$subSelect="availableTags"}  
    {include file="file:{$absPath}/{$availableTags}" title="Available Tags"}
{/if}