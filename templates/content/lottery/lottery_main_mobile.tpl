<tr>
    <td>
        <headerText screenWidth="true">Lottery</headerText>
    </td>
</tr>

<tr>
    <td>
        <text>There are: <b>{$tickets}</b> tickets available. 
        <br>You currently own: <b>{$User_tickets}</b> tickets.</text>    
        <input type="number" name="tickets"></input>
    </td>
</tr>

<tr><td><text><b>Ticket prices:</b> Jackpot: {$Jackpot} ryo, Normal: {$Normal} ryo </text></td></tr>

<tr>
    <td>
        <submit type="submit" name="Submit" value="Submit" extraKeyValue="jackpot:yes">Jackpot Ticket</submit>
    </td>
    <td>
        <submit type="submit" name="Submit" value="Submit" extraKeyValue="jackpot:no">Normal Ticket</submit>
    </td>
</tr>