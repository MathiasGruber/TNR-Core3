<div align="center">
    <table class="table" width="95%" >
        <tr>
            <td class="subHeader">List of Payments</td>
        </tr>
        <tr>
            <td style="text-align:left;">
                <b>Notes:</b><br>
                <i>
                    -- Following payments are for the period between {$smarty.post.startDate} and {$smarty.post.endDate} <br>
                    -- At the end of this site a summary of all the payments can be found.<br>
                    -- Income is listed without pyapal fees!
                </i>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;">
                {if isset($data) && $data != "0 rows"}
                    <font size='+2'>
                        Paypal fees are not included in the following!
                    </font>
                    <br>
                    {foreach from=$countries key=key item=value}
                        {$key} contributes: {$value} USD. <br>
                        - VAT Perc: {$VAT[ $key ]}%<br>
                        - VAT Amount: {((1/(($VAT[ $key ]+100)/$VAT[ $key ]))*$value)|string_format:"%.2f"} USD<br>
                        <br>
                    {/foreach}
                    <br>
                {/if}

            </td>
        </tr>
        <tr>
            <td style="text-align:left;">
                <div align='left'>
                  <table border='1' cellspacing='1' style='border-collapse: collapse; border-style: double; border-width: 3' bordercolor='#111111' width='500px' >
                    <tr>
                        <td  class="subHeader">Dato</td>
                        <td  class="subHeader">Land</td>
                        <td  class="subHeader">Genstand</td>
                        <td  class="subHeader">USD</td>
                    </tr>
                    {if isset($data) && $data != "0 rows"}
                        {foreach $data as $entry}
                            <tr class="{cycle values="row1,row2"}" >
                                <td >{$entry["date"]}</td>
                                <td >{$entry["country"]}</td>
                                <td >{$entry["item"]}</td>
                                <td >{$entry["price"]}</td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr><td colspan="4">No transactions found</td></tr>
                    {/if}
                  </table>
                </div>
            </td>
        </tr>
    </table>
</div>
 



