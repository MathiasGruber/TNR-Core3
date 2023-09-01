<div align="center">
    <form name="form1" method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td class="subHeader">Kick Member</td>
            </tr>
            <tr>
                <td>
                    <select name="memberID">
                        {for $i = 1 to 9}
                            {$squad["member_{$i}_uid_username"]}
                            {if isset($squad["member_{$i}_uid_username"]) && $squad["member_{$i}_uid_username"] != ''}
                                <option value="{$i}">{$squad["member_{$i}_uid_username"]}</option>
                            {/if}
                        {/for}
                    </select>&nbsp;
                    <input type="submit" name="Submit" style="height:22px;" value="Submit">
                </td>
            </tr>
        </table>
    </form>
</div>