<form id="form1" name="form1" method="post" action="">
    <div align="center" id="showTable">  
        <table width="95%" class="table">
            <tr>
                <td colspan="3" class="subHeader">Monthly Mission Submissions. Total: {$total}</td>
            </tr>
            <tr>
                <td width="20%" class="tdTop"> Username </td>
                <td width="60%" class="tdTop"> URL </td>
                <td width="20%" class="tdTop"> Popularity Points </td>
            </tr>
            {if isset({$submissions}) && {$submissions} != "0 rows"}
                {for $i = 0 to ($submissions|@count)-1}
                    <tr class="{cycle values="row1,row2"}" >
                        <td> {$submissions[$i]["username"]} </td>
                        <td> {$submissions[$i]["mission_monthly"]} <a href="{$submissions[$i]["mission_monthly"]}">Link</a> </td>
                        <td>  
                            <select name="userID:::{$submissions[$i]["id"]}:::{$submissions[$i]["mission_monthly"]}">
                                <option selected="selected" value="0">0</option>
                                <option value="no">Reject</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="13">13</option>
                                <option value="14">14</option>
                                <option value="15">15</option>
                                <option value="16">16</option>
                                <option value="17">17</option>
                                <option value="18">18</option>
                                <option value="19">19</option>
                                <option value="20">20</option>
                                <option value="31">31</option>
                                <option value="32">32</option>
                                <option value="33">33</option>
                                <option value="34">34</option>
                                <option value="35">35</option>
                                <option value="36">36</option>
                                <option value="37">37</option>
                                <option value="38">38</option>
                                <option value="39">39</option>
                                <option value="40">40</option>
                                <option value="41">41</option>
                                <option value="42">42</option>
                                <option value="43">43</option>
                                <option value="44">44</option>
                                <option value="45">45</option>
                                <option value="46">46</option>
                                <option value="47">47</option>
                                <option value="48">48</option>
                                <option value="49">49</option>
                                <option value="50">50</option>
                            </select>
                        </td>
                    </tr>
                {/for}
            {else}
                <tr><td colspan="3">No entries found in database</td></tr>
            {/if}
            <tr>
                <td colspan="3" class="tdBottom">
                    <input name="Submit" type="submit" class="button" value="Submit All" />
                </td>
            </tr>
        </table>    
        <a href="?id={$smarty.get.id}">Return</a>
    </div>
</form>