<div class="page-box">
    <div class="page-title">
        Register an Account
    </div>
    <div class="page-content">
        <div class="page-sub-title-top">
            Welcome to the world of Seichi, where shinobi rule and monsters of unimaginable power aren't all that unimaginable! Before we can begin your adventure, you must create your character.
        </div>

        <form name="form1" method="post" action="?id=63" class="stiff-grid stiff-column-min-left-2 page-grid-justify-stretch no-wrap">

            <div>
                Username:
            </div>
            <input name="username" type="text" id="username" size="30" {if isset($fbName) }value="{$fbName}"{/if}>

            <div>
                E-mail:
            </div>
            <input name="mail" type="text" id="mail" size="30" {if isset($fbEmail) }value="{$fbEmail}"{/if}>

            <div>
                Verify E-mail:
            </div>
            <input name="mail_v" type="text" id="mail_v" size="30" {if isset($fbEmail) }value="{$fbEmail}"{/if}>

            <div>
                Password:
            </div>
            <input name="password" type="password" id="password" size="30">

            <div>
                Verify Password:
            </div>
            <input name="password_v" type="password" id="password_v" size="30">

            <div>
                Gender:
            </div>
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

            <div>
                Starting village:
            </div>
            <select name="village" id="village">
                {foreach $villageList as $village}
                    <option value="{$village}">{$village}</option>
                {/foreach}
            </select>

            <div>
                Starting clan:
            </div>
            <select name="clanElement" id="village">
                {foreach $clanList as $clan}
                    <option value="{$clan}">{$clan}</option>
                {/foreach}
            </select>

            <div class="span-2"></div>

            <div class="span-2">
                <input name="terms" type="checkbox" id="terms" value="1"> I have read and agree to the <a href="?id=7" target="_blank">Terms and Conditions</a>
            </div>

            <div class="span-2">
                <input name="rules" type="checkbox" id="rules" value="1"> I have read and agree to the <a href="?id=15" target="_blank">Rules</a>
            </div>

            <div class="span-2">
                {if isset($fbID) }
                    <input type="hidden" name="Facebook" value="{$fbID}">
                {else}
                    <input type="hidden" name="Facebook" value="Nope">
                {/if}

                <input type="submit" name="Submit" value="Submit">
            </div>
        </form>

    </div>
</div>