{if isset($people)}
    {$subSelect="people"}{include file="file:{$absPath}/{$people|replace:'.tpl':'_mobile.tpl'}" title="People"}
{/if}
{if isset($challenges)}
    {$subSelect="challenges"}{include file="file:{$absPath}/{$challenges|replace:'.tpl':'_mobile.tpl'}" title="Challenges"}
{/if}
{if isset($spars)}
    {$subSelect="spars"}{include file="file:{$absPath}/{$spars|replace:'.tpl':'_mobile.tpl'}" title="Spars"}
{/if}

<tr>
    <td>
        <headerText>Challenge Directly</headerText>
        <text>Enter username to challenge</text>
		<tr color="dim">
			<td>
				<input type="text" name="username" value="username"></input>
			</td>
		</tr>
        <submit type="submit" name="Submit" value="Submit" href="?id={$pageID}&act=challenge"></submit>
    </td>
</tr>

