<tr>
<td>
    <headerText>Chuunin exam!</headerText>
    <text>You enter the room where you are told the chuunin exam will start. You are told that this exam will consist of 3 tests.</text>
    
    <headerText><br>1st Test</headerText>
    {if $resultReturn.0 == "yes"}
        <text>You are given a general test, and you complete it with ease. You <b>pass</b> the first test</text>

        <headerText><br>2nd Test</headerText>
        {if $resultReturn.1 == "yes"}
            <text>In this test your objective is to pass through the dangerous forest and gain access to an old tower. Due to your defensive skills as a ninja in general you are able to reach the tower. You <b>pass</b> the second test!</text>

            <headerText><br>3rd Test</headerText>
            {if $resultReturn.2 == "yes"}
                <text>In this test you have to fight various people from other countries in a big tournament. Due to your good offensive skills you prove yourself worthy of being a Chuunin. You <b>pass</b> the third test</text>
                {if isset($ceremoni) }

                    <headerText><br>Rankup Ceremony</headerText>
                    <text>{$ceremoni}</text>                        
                {/if}
            {else}
                <text>In this test you have to fight various people from other countries in a big tournament. Due to your not so good offensive skills you prove the you are not worthy of becoming a Chuunin! Your must have one offense with at least 5000 points. You <b>fail</b> the third test!</text>
            {/if}
        {else}
            <text>In this test your objective is to pass through the dangerous forest and gain access to an old tower, but due to your lack of defensive ninja skills you can't reach it!You <b>fail</b> the second test!</text>
        {/if}
    {else}
        <text>You are given a written test, but because of your lack of general intelligence, strength etc. you cannot finish it!You <b>fail</b> the first test!</text>
    {/if}
            
    <text><br></text>
    <a href="?id=2">Return</a>
</td>
</tr>