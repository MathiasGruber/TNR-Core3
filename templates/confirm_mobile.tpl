<tr>
    <td>
        <headerText>{$subHeader}</headerText>
        <text>{$msg}<br></text>
        {if $storage_name_1 != 'n/a' && $storave_value_1 != 'n/a'}
            <hidden type="hidden" name="{$storage_name_1}" value="{$storage_value_1}"></hidden>
            {if $storage_name_2 != 'n/a' && $storave_value_2 != 'n/a'}
                <hidden type="hidden" name="{$storage_name_2}" value="{$storage_value_2}"></hidden>
            {/if}
        {/if}
		<tr>
			<td>
			</td>
			<td>
				<submit type="submit" name="Submit"  value="{$returnLink}"></submit>
			</td>
			<td>
			</td>
		</tr>
    </td>
</tr>