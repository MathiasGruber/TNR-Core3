<div align="center">
    <table class="table" width="95%" >
        <tr>
            <td class="subHeader">Estimates User Worth</td>
        </tr>
        <tr>
            <td>
                <form id="analysisForm" name="analysisForm" method="post" action="">
                <table border="0" style="border-collapse: collapse; border: 0px solid #000000;">
                    <tr>
                        <td>
                            <i>Days to analyze</i>
                        </td>
                        <td style="padding-left:10px;">
                            <i>Only new users</i>                            
                        </td>
                        <td style="padding-left:10px;">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>    
                            <select name="days" >
                              <option {if isset($smarty.post.days) && $smarty.post.days == 1} selected {/if} value="1">1</option>
                              <option {if isset($smarty.post.days) && $smarty.post.days == 7} selected {/if} value="7">7</option>
                              <option {if isset($smarty.post.days) && $smarty.post.days == 14} selected {/if} value="14">14</option>
                              <option {if isset($smarty.post.days) && $smarty.post.days == 30} selected {/if} value="30">30</option>
                              <option {if isset($smarty.post.days) && $smarty.post.days == 365} selected {/if} value="365">365</option>
                              <option {if isset($smarty.post.days) && $smarty.post.days == "all"} selected {/if} value="all">All</option>
                            </select>
                        </td>
                        <td style="padding-left:10px;">
                            <input type="checkbox" name="newUsers" value="new" {if isset($smarty.post.newUsers) && $smarty.post.newUsers  == "new"} checked {/if}>
                        </td>
                        <td style="padding-left:10px;">
                            <input type="submit"  style="height: 25px; width: 150px;" class="button" name="Update" value="Update" />
                        </td>
                    </tr>
                </table>
                </form>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;">
                <table width="350px" class="table">
                    <tr>                        
                      <td class="subHeader">Paypal Income</td>
                    </tr>
                    <tr class="row1" >
                      <td>
                        Income without fees: {$income}                            
                      </td>  
                    </tr>
                    <tr class="row2" >
                      <td>
                        Fees paid to paypal: {$fee}                            
                      </td>  
                    </tr>
                    <tr class="row1" >
                      <td>
                        Income with fees: {$income-$fee}                            
                      </td>  
                    </tr>
                    <tr>                        
                      <td class="subHeader">Lvl 1 Signups</td>
                    </tr>
                    <tr class="row1" >
                      <td>
                        Users signed up during the period: {$users_o}
                      </td>  
                    </tr>
                    <tr class="row2" >
                      <td>
                        Income from new users: {$newIncome}
                      </td>  
                    </tr>
                    <tr class="row1" >
                      <td>
                        Fees paid to paypal: {$newFee}
                      </td>  
                    </tr>
                    <tr class="row2" >
                      <td>
                        Income with fees: {$newIncome-$newFee}
                      </td>  
                    </tr>
                    <tr>                        
                      <td class="subHeader">Lvl 2 Signups</td>
                    </tr>
                    <tr class="row1" >
                      <td>
                        Users signed up during the period: {$lvlusers_o}
                      </td>  
                    </tr>
                    <tr class="row2" >
                      <td>
                        Income from new users: {$lvlnewIncome}
                      </td>  
                    </tr>
                    <tr class="row1" >
                      <td>
                        Fees paid to paypal: {$lvlnewFee}
                      </td>  
                    </tr>
                    <tr class="row2" >
                      <td>
                        Income with fees: {$lvlnewIncome-$lvlnewFee}
                      </td>  
                    </tr>
                    <tr>                        
                      <td class="subHeader">Facebook signups</td>
                    </tr>
                    <tr class="row1" >
                      <td>
                        Facebook Users signed up during the period: {$facebookPeriod}
                      </td>  
                    </tr>
                    <tr class="row2" >
                      <td>
                        Facebook Users in Total: {$facebookTotal}
                      </td>  
                    </tr>
                    <tr>                        
                      <td class="subHeader">Summary</td>
                    </tr>
                    <tr class="row1" >
                      <td>
                        Income / new user the last {$smarty.post.days} days: 
                        {if $users_o > 0}
                            {($newIncome-$newFee)/$users_o}
                        {else}
                            0
                        {/if}
                      </td>  
                    </tr>
                    <tr class="row2" >
                      <td>
                        Income / new lvl2+ user the last {$smarty.post.days} days: 
                        {if $lvlusers_o > 0} 
                            {($lvlnewIncome-$lvlnewFee)/$lvlusers_o} 
                        {else}
                            0
                        {/if}
                      </td>  
                    </tr>            
                </table>
            </td>
        </tr>
    </table>
</div>
 



