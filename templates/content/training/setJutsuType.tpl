<div align="center">
    <table class="table" width="95%">
        <tr>
            <td class="subHeader">Train Jutsu</td>
        </tr>
        <tr>
            <td align="center"> 
                To succeed as a ninja, knowledge and mastery of jutsu is of the greatest importance<br>
                You can train in the following types of jutsu:
            </td>
        </tr>
        <tr>
            <td align="center">
                <form class="autoInput" method="post" action="" id="trainingForm">
                    <select name="jutsu_type">
                        <option value="normal">Normal jutsu</option>
                        <option value="special">Special jutsu</option>
                        <option value="village">Village jutsu</option>
                        {foreach $select1 as $key => $value}
                            <option value="{$key}">{$value}</option>
                        {/foreach}
                    </select>
                    &nbsp;
                    <select name="attack_type">
                        <option selected value="x">All available</option>
                        <option value="ninjutsu">Ninjutsu</option>
                        <option value="genjutsu">Genjutsu</option>
                        <option value="taijutsu">Taijutsu</option>
                        <option value="weapon">Bukijutsu</option>
                        {foreach $select2 as $key => $value}
                            <option value="{$key}">{$value}</option>
                        {/foreach}
                    </select>
                    &nbsp;
                    <select name="rank_type">
                        <option selected value="x">All available</option>
                        {foreach $select3 as $key => $value}
                            <option value="{$key}">{$value}</option>
                        {/foreach}
                    </select>
                    &nbsp;
                    <select name="element">
                        <option selected value="x">All available</option>
                        {foreach $select4 as $key => $value}
                            <option value="{$key}">{$value}</option>
                        {/foreach}
                    </select>
                    <input type="submit" name="Submit" value="Submit">
                    <input type="hidden" name="train" value="{$train}">
                </form>
            </td>
        </tr>
    </table>
    <a href="?id={$smarty.get.id}" class="returnLink">Return</a>
</div>
