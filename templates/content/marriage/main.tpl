<div align="center"><br>
    {if $marriage == 0}
        {if isset($proposalList)}
            {$subSelect="proposalList"}  
            {include file="file:{$absPath}/{$proposalList}" title="Marriage Proposals"}
        {/if}
        
        <table width="95%" class="table">
            <tr>
                <td class="subHeader">Send a Proposal</td>
            </tr>
            <tr>
                <td>
                    <form name="Send_Proposal" method="post" action="?id={$smarty.get.id}">
                        <input type="text" style="text-align:center;" name="proposed_user">
                        <input class="input_submit_btn" type="submit" style="height:25px;" name="Proposal_Submit" value="Send Proposal">
                    </form>
                </td>
            </tr>
        </table>
    {/if}
</div>