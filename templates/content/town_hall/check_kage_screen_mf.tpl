<div class="page-box">
    <div class="page-title">
        {$kageInfo.village}
    </div>
    <div class="page-content">
        <div class="page-grid page-column-2">

            <div>
                <img src="{$kageInfo.avatar}"/>
            </div>

            <div class="page-grid page-column-2">

                <div class="text-left">
                    Username:
                </div>

                <div>
                    {$kageInfo.username}
                </div>



                <div class="text-left">
                    User Rank:
                </div>

                <div>
                    {$kageInfo.rank}
                </div>



                <div class="text-left">
                    Bloodline:
                </div>

                <div>
                    {$kageInfo.bloodline}
                    <br/>
                    <br/>
                </div>

            </div>
        </div>

        <div class="span-2">
                {if $kageInfo.rank != "Guardian"}
                    <a href="?id=13&page=profile&name={$kageInfo.username}">View {$kageInfo.username}'s Profile</a><br>
                {else}
                    &nbsp;
                {/if}
                {if $kageInfo.challenge == "yes"} 
                    <a href="?id={$smarty.get.id}&act={$smarty.get.act}&doChallenge={$kageInfo.username}{if isset($pvpCode)}&code={$pvpCode}{/if}">Challenge the kage!</a><br> 
                {else}
                     {$kageInfo.challenge}
                {/if}
            </div>
    </div>
</div>