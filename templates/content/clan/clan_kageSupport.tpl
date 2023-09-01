<div align="center">
    
    <!-- Information about the clan -->
    {include file="file:{$absPath}/templates/content/clan/clan_info.tpl" title="Clan Information"}
    
    <!-- Information about kage support -->
    <table width="95%" class="table">
        <tr>
            <td class="subHeader">Kage Position</td>
        </tr>
        <tr>
            <td>
               As the leader of your clan you can chose to either support or oppose the kage of your village. 
               This will affect the influence of the kage, and if the influence goes into the negative, the 
               kage will lose his position. The kage's current influence is: <b>{$kageInfluence} points</b>.<br>
               
               <form action="" method="post">
                   <input class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Support Kage">
                   &#160;&#160;&#160;
                   <input class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="Submit" value="Oppose Kage">
               </form>
            </td>
        </tr>
    </table>
    
    <a href="?id={$smarty.get.id}">Return</a>
</div>