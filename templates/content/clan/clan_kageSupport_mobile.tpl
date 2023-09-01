{include file="file:{$absPath}/templates/content/clan/clan_info_mobile.tpl" title="Clan Information"}

<tr>
    <td>
        <headerText>Kage Position</headerText>
        <text>As the leader of your clan you can chose to either support or oppose the kage of your village. This will affect the influence of the kage, and if the influence goes into the negative, the kage will lose his position. The kage's current influence is: <b>{$kageInfluence} points</b></text>
    </td>
</tr>
<tr>
    <td>
        <submit type="submit" name="Submit" value="Support Kage"></submit>
    </td><td>
        <submit type="submit" name="Submit" value="Oppose Kage"></submit>
    </td>
</tr>

<tr><td><a href="?id={$smarty.get.id}">Return</a></td></tr>
