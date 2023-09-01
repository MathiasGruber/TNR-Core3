<div class="page-box">
    <div class="page-title">
        Resign from ANBU
    </div>
    <form name="form1" method="post" action="" class="page-content">
        <div class="page-sub-title-top">
            By leaving the anbu squad you will no longer have access to the ANBU only items.
        </div>

        {if isset($isLeader) && $isLeader == true && isset($hasMembers) && $hasMembers == true }
            <div>
                Leave the ANBU leadership to:
            </div>
            <div>
                <select name="newLeader" class="page-drop-down-fill">
                    {for $i = 1 to 9}
                        {if isset($squad["member_{$i}_uid_username"]) && $squad["member_{$i}_uid_username"] != ''}
                            <option value="{$i}">{$squad["member_{$i}_uid_username"]}</option>
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