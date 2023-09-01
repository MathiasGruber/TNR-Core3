<form name="form1" method="post" action="?id=63">
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">Register an Account</td>
        </tr>
        <tr>
            <td colspan="2" style="padding-bottom:10px;">Welcome to the world of Seichi, where shinobi rule and monsters of unimaginable power aren't all that unimaginable! Before we can begin your adventure, you must create your character.</td>
        </tr>
        <tr>
            <td width="50%" style="text-align:left;">Username:</td>
            <td width="50%" style="text-align:left;">
                <input name="username" type="text" id="username" size="30" {if isset($fbName) }value="{$fbName}"{/if}>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;">E-mail:</td>
            <td style="text-align:left;"><input name="mail" type="text" id="mail" size="30" {if isset($fbEmail) }value="{$fbEmail}"{/if}></td>
        </tr>
        <tr>
            <td style="text-align:left;">Verify E-mail: </td>
            <td style="text-align:left;"><input name="mail_v" type="text" id="mail_v" size="30" {if isset($fbEmail) }value="{$fbEmail}"{/if}></td>
        </tr>
        <tr>
            <td style="text-align:left;">Password:</td>
            <td style="text-align:left;"><input name="password" type="password" id="password" size="30"></td>
        </tr>
        <tr>
            <td style="text-align:left;">Verify Password: </td>
            <td style="text-align:left;"><input name="password_v" type="password" id="password_v" size="30"></td>
        </tr>
        <tr>
            <td style="text-align:left;">Gender:</td>
            <td style="text-align:left;">
                <select name="gender" id="gender">
                    {if isset($fbGender) && $fbGender=="male"}
                        <option selected>Male</option>
                        <option>Female</option>
                    {elseif isset($fbGender) && $fbGender=="female"}
                        <option>Male</option>
                        <option selected>Female</option>
                    {else}
                        <option>Male</option>
                        <option>Female</option>
                        <option selected>Pick one</option>
                    {/if}
                </select>                    
            </td>
        </tr>
        <tr>
            <td style="text-align:left;">Starting village </td>
            <td style="text-align:left;font-weight:bold;">
                <select name="village" id="village">
                    {foreach $villageList as $village}
                        <option value="{$village}">{$village}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;">Starting clan </td>
            <td style="text-align:left;font-weight:bold;">
                <select name="clanElement" id="village">
                    {foreach $clanList as $clan}
                        <option value="{$clan}">{$clan}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td style="padding:0px;" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:left;"><input name="terms" type="checkbox" id="terms" value="1"> I have read and agree to the <a href="?id=7" target="_blank">Terms and Conditions</a></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:left;"><input name="rules" type="checkbox" id="rules" value="1"> I have read and agree to the <a href="?id=15" target="_blank">Rules</a></td>
        </tr>
        <tr>
            <td colspan="2" align="center">

                {if isset($fbID) }
                    <input type="hidden" name="Facebook" value="{$fbID}">
                {else}
                    <input type="hidden" name="Facebook" value="Nope">
                {/if}

                <input type="submit" name="Submit" value="Submit">
            </td>
        </tr>
    </table>
</form>
