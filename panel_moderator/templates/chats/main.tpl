<div align='center' width ='95%'>
  <form action="?id={$smarty.get.id}" method="post">
    <table>
      <tr>
        <td>
          <input list="village_options" name="village_option">
            <datalist id="village_options">
              {$village_options}
            </datalist>
        </td>
        <td>
          <input list="clan_options" name="clan_option">
            <datalist id="clan_options">
              {$clan_options}
            </datalist>
        </td>
        <td>
          <input list="anbu_options" name="anbu_option">
            <datalist id="anbu_options">
              {$anbu_options}
            </datalist>
        </td>
        <td>
          <input list="marriage_options" name="marriage_option">
            <datalist id="marriage_options">
              {$marriage_options}
            </datalist>
        </td>
        <td>
          kage chat
        </td>
      </tr>
        
      <tr>
        <td>
          <input type="submit" value="village_submit" name="submit">
        </td>
        <td>
          <input type="submit" value="clan_submit" name="submit">
        </td>
        <td>
          <input type="submit" value="anbu_submit" name="submit">
        </td>
        <td>
          <input type="submit" value="marriage_submit" name="submit">
        </td>
        <td>
          <a href='?id={$smarty.get.id}&act=kage'>submit</a>
        </td>
      </tr>
    </table>
  </form>
</div>