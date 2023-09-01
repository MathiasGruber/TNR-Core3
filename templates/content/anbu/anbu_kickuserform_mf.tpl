<div class="page-box">
    <div class="page-title">
        Kick Member
    </div>
    <form name="form1" method="post" action="">
        <div class="page-content">
            <div>
                <select name="memberID" class="page-drop-down-fill">
                    {for $i = 1 to 9}
                        {$squad["member_{$i}_uid_username"]}
                        {if isset($squad["member_{$i}_uid_username"]) && $squad["member_{$i}_uid_username"] != ''}
                            <option value="{$i}">{$squad["member_{$i}_uid_username"]}</option>
                        {/if}
                    {/for}
                </select>
            </div>
            <div>
                <input type="submit" name="Submit" class="page-button-fill" value="Submit">
            </div>
        </div>
    </form>
</div>