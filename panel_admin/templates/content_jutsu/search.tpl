<form id="form1" name="form1" method="post" action="">
    <table width="95%" class="table">
      <tr><td colspan="4" class="subHeader">::Jutsu admin:: </td></tr>
      <tr>
            <td>Jutsu ID:</td>
            <td><input name="jutsuID" type="text" class="textfield" id="jutsuID" /></td>
            <td></td>
            <td></td>
        </tr>
      <tr>
        <td>Name:</td>
          <td><input name="name" type="text" class="textfield" id="name" /></td>
          <td>Rank: </td>
          <td>
            <select name="rank_type" class="listbox" id="rank_type">
                  <option value="&lt;">&lt;</option><option value="&gt;">&gt;</option>
                  <option value="&gt;=">&gt;=</option><option value="&lt;=">&lt;=</option>
                  <option value="=">=</option>
            </select>
            <select name="rank" class="listbox" id="rank">
                  <option value="1">Academy Student</option><option value="2">Genin</option>
                  <option value="3">Chuunin</option><option value="4">Jounin</option>
                  <option value="5">Elite Jounin</option><option value="0" selected="selected">All</option>
            </select>
          </td>
      </tr><tr>
        <td width="21%">Attack Type: </td>
        <td width="27%" >
            <select name="attack_type" class="listbox" id="attack_type">
                  <option value="ninjutsu">Ninjutsu</option><option value="genjutsu">Genjutsu</option>
                  <option value="taijutsu">Taijutsu</option><option value="weapon">Weapon</option>
                  <option value="all" selected="selected">All</option>
            </select>
        </td>
        <td width="13%">Village:</td>
        <td width="39%">
            <select name="village" class="listbox" id="village">
                  <option value="glacier">Glacier</option><option value="current">Current</option>
                  <option value="shroud">Shroud</option><option value="shine">Shine</option>
                  <option value="silence">Silence</option><option value="horizon">Horizon</option>
                  <option value="any" selected="selected">Any</option>
            </select>
        </td>
      </tr><tr>
        <td>Bloodline:</td>
        <td >
            <input name="bloodline" type="text" class="textfield" id="bloodline" />
        </td>
        <td>Jutsu type: </td>
        <td>
            <select name="jutsu_type" class="listbox" id="jutsu_type">
                  <option value="normal">Normal</option><option value="special">Special</option>
                  <option value="forbidden">Forbidden</option><option value="any" selected>Any</option>
            </select>
        </td>
      </tr><tr>
        <td colspan="2" >Order by: </td>
        <td colspan="2">
            <select name="order_by" class="listbox" id="order_by">
                  <option value="name">Name</option><option value="jutsu_type">Jutsu type</option>
                  <option value="attack_type">Attack type</option><option value="required_rank">Rank</option>
            </select>&nbsp;
            <select name="order_type" class="listbox" id="order_type">
                  <option value="ASC">Ascending</option><option value="DESC">Descending</option>
            </select>
        </td>
      </tr><tr>
        <td colspan="4" align="center" style="padding:5px;"><input name="Submit" type="submit" class="button" value="Search" /></td>
      </tr>
    </table>
</form>