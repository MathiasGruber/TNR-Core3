<div align="center">
    <table width="95%" class="table" >
        <tr>
            <td colspan="4" class="subHeader">Jutsu Details</td>
        </tr>
        <tr>
            <td style="text-align:left;padding-bottom:0px;font-weight:bolder;" width="25%">Name:</td>
            <td style="text-align:left;padding-bottom:0px;">{$data['name']}</td>
            <td style="text-align:left;padding-bottom:0px;font-weight:bolder;">Required Weapons:</td>
            <td style="text-align:left;padding-bottom:0px;" >{$data['required_weapons']}</td>
        </tr>
        <tr>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;" >Attack type:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;" width="25%">{$data['attack_type']|capitalize}</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;">Required Reagents:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;" >{$data['required_reagents']}</td>
        </tr>
        <tr>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;" >Type:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;" >{$data['jutsu_type']|capitalize}</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;">Required Rank: </td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;" >{$data['required_rank']}</td>
        </tr>
        <tr>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;" >Element:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;">{$data['element']|capitalize}</td>
            <td width="23%"  style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;">Uses / Battle:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;" width="27%">{$data['max_uses']|capitalize}</td>
        </tr>
        <tr>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;" >Experience:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;">{$data['exp']}</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;">Village:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;">{$data['village']}</td>
        </tr>
        <tr>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;" >Chakra cost:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;">{$data['cha_cost']}</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;">Level:</td>
            <td style="text-align:left;padding-top:0px;padding-bottom:0px;">{$data['level']}</td>
        </tr>
        <tr>
          <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;" >Stamina cost:</td>
          <td style="text-align:left;padding-top:0px;padding-bottom:0px;">{$data['sta_cost']}</td>
          <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;">Ryo cost to train:</td>
          <td style="text-align:left;padding-top:0px;padding-bottom:0px;">{$data['price'] + ($data['price_increment'] * $data['level'])}</td>
        </tr>
      <tr>
        <td style="text-align:left;padding-top:0px;padding-bottom:0px;font-weight:bolder;" >Targeting Type:</td>
        <td style="text-align:left;padding-top:0px;padding-bottom:0px;">{$data['targeting_type']}</td>
      </tr>
        
        <tr>
            <td colspan="4" align="center">&nbsp;</td>
        </tr>
        {if isset($data['specialNote'])}
           <tr>
                <td colspan="4" align="center">{$data['specialNote']}</td>
            </tr>
        {/if}
        <tr>
            <td colspan="4" style="text-align:left;" ><b>Description</b><br>{$data['description']}</td>
        </tr>
        <tr>
            <td colspan="4" class="tableColumns" >
                Jutsu Effects
            </td>
        </tr>
        <tr>
            <td colspan="4"  style="text-align:left;font-size:10px;">
                <ul>
                    {if strlen($effects) > 15}
                        {str_replace('-new-line-', "\r\n", $effects)}
                    {else}
                        N/A
                    {/if}
                </ul>
            </td>
        </tr>
    </table>
    {if isset($returnLink)}
        <a href="?id={$smarty.get.id}">Return</a>
    {/if} 
</div>