
{if (($result[0]['visibility'] == 'All') || ({$smarty.session.user_rank} == 'Admin') || (($result[0]['visibility'] == 'Supermod') && ({$smarty.session.user_rank} == 'Supermod')))}
	<div align="center">
        <form name="form1" method="post" action="">
            <table width="95%" class="table">
			<tr>
				<td colspan="2" class="subHeader">View Note</td>
			</tr>
			<tr class="row1">
				<td style="font-weight:bold;">Title</td>
				<td>{$result[0]['title']}</td>
			</tr>
			<tr class="row2">
				<td style="font-weight:bold;" width="26%">Posted by</td>
				<td width="74%">{$result[0]['posted_by']}</td>
			</tr>
			<tr class="row1">
				<td style="font-weight:bold;">Visibility</td>
				<td>{$result[0]['visibility']}</td>
			</tr>
			<tr class="row2">
				<td style="font-weight:bold;">Date</td>
				<td>{$result[0]['time']|date_format:"%H:%M:%S %d-%m-%Y"}</td>
			</tr>
			<tr>
				<td colspan="2" class="subHeader">Contents</td>
			</tr>
			<tr>
				<td colspan="2">{$result[0]['text']}</td>
			</tr>
            </table>
        </form>
        <a href="?id={$smarty.get.id}">Return</a>
    </div>
{else} 
	<div align="center">You are not allowed to view this note<br>
    <a href="?id={$smarty.get.id}">Return</a></div>
{/if} 
