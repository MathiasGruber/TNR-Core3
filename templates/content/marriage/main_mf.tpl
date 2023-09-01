<div class="page-box">
    <div class="page-title">
        Marriage
    </div>
    <div class="page-content">
        {if $marriage == 0}
            {if isset($proposalList)}
                {$subSelect="proposalList"}  
                {include file="file:{$absPath}/{$proposalList}" title="Marriage Proposals"}
            {/if}

            <div class="{if !isset($proposalList)}page-sub-title-top{else}page-sub-title{/if}">
                Send a Proposal
            </div>

            <form name="Send_Proposal" method="post" action="?id={$smarty.get.id}">
                <div class="stiff-grid stiff-column-min-left-2 page-grid-justify-stretch">
                    <div class="text-left">
                        <input class="page-button-fill" type="submit" name="Proposal_Submit" value="Send Proposal">
                    </div>
                    <div class="text-left">
                        <input class="page-text-input-fill" type="text" name="proposed_user">
                    </div>
                </div>
            </form>
        {/if}
    </div>
</div>