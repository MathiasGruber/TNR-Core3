{if $marriage == 0}

    {if isset($proposalList)}
        {$subSelect="proposalList"}  
        {include file="file:{$absPath}/{$proposalList|replace:'.tpl':'_mobile.tpl'}" title="Marriage Proposals"}
    {/if}

    <tr>
        <td>
            <headerText>Send a Proposal</headerText>
        </td>
    </tr>
    <tr>
        <td>
            <text>Enter the name of the one you want to marry</text>
            <input name="proposed_user" type="text" value="{$entry['inputFieldValue']}"></input>
            <submit name="Proposal_Submit" value="Send Proposal" type="submit"></submit>
        </td>
    </tr>
{/if}
