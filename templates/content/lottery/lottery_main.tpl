<div>
    <table class="table"  width="95%">
        <tr>
            <td colspan="3" class="subHeader">Lottery</td>
        </tr>
        <tr>
            <td style="padding-left:15px;">
                <br>There are: <b>{$tickets}</b> tickets available <br>
                You currently own: <b>{$User_tickets}</b> tickets<br>
                <br>
                Prices:<br>Jackpot: {$Jackpot} ryo, Normal: {$Normal} ryo 
            </td>
            <td width="7%">&nbsp;</td>
            <td width="46%" valign="top" style="padding:5px;">
                <form id="form1" name="form1" method="post" action="">
                    <div align="center" style="padding-top:5px;">Buy Tickets:<input type="textfield" name="tickets" />      
                        <br>
                        <strong>Jackpot:</strong>
                        Yes:<input name="jackpot" type="radio" value="yes" />
                        No:<input name="jackpot" type="radio" value="no" />
                        <br>
                        <input type="submit" name="Submit" value="Submit" />
                    </div>
                </form>
            </td>
        </tr>
    </table>
</div>