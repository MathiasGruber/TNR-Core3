<div class="page-box">
    <div class="page-title">
        Spar
    </div>
    <div class="page-content">
        {$first = true}
        {if isset($people)}{$subSelect="people"}{include file="file:{$absPath}/{$people}" title="People"}{/if}
        {$first = false}
        {if isset($challenges)}{$subSelect="challenges"}{include file="file:{$absPath}/{$challenges}" title="Challenges"}{/if}
        {if isset($spars)}{$subSelect="spars"}{include file="file:{$absPath}/{$spars}" title="Spars"}{/if}
        <div colspan="2" align="center" style="border-top:none;" class="subHeader">Challenge a user </div>
        <div>
            <form name="form1" method="post" action="?id={$pageID}&act=challenge">
                <input type="text" name="username">&nbsp;
                <input type="submit" name="Submit" value="Submit">
            </form>
        </div>
    </div>
</div>