<div class="page-box">
    <div class="page-title">
        Resign from Clan
    </div>
    <form name="form1" method="post" action="" class="page-content">
        <div class="page-sub-title-top">
            By leaving the clan you will no longer have access to any of the clans benefits.
        </div>
        {if isset($isLeader) && $isLeader == true && isset($hasMembers) && $hasMembers == true }
            <div>
                Leave the Clan leadership to:
                <select name="newLeader" class="page-drop-down-fill">
                    {for $i = 1 to 5}
                        {if isset($clan["coleader{$i}_uid_username"]) && $clan["coleader{$i}_uid_username"] != ''}
                            <option value="{$i}">{$clan["coleader{$i}_uid_username"]}</option>
                        {/if}
                    {/for}
                </select>
            </div>
        {/if}
        <div>
            <input type="submit" name="Submit" class="page-button-fill" value="Submit">
        </div>
    </form>
</div>