<tr>
<td>
    <headerText>{$newRank} exam!</headerText>
    <text>You enter the room where you are told the {$newRank} exam will start now. You are told that this exam will consist of 3 tests, just like the chuunin exam did back in the day.</text>
    
    <headerText><br>1st Test</headerText>
    {if $resultReturn.0 == "yes"}
        <text>You are given a general test, and you complete it with ease. You <b>pass</b> the first test</text>

        <headerText><br>2nd Test</headerText>
        {if $resultReturn.1 == "yes"}
            <text>In this test your objective is to obtain a secret scroll from the enemy village. Because of your intense training you are able to obtain the scroll. You <b>pass</b> the second test!</text>

            <headerText><br>3rd Test</headerText>
            {if $resultReturn.2 == "yes"}
                <text>In this test you have to fight various people from other countries in a big tournament. Due to your good offensive and/or defensive skills you prove yourself worthy of being a {$newRank}. You <b>pass</b> the third test.</text>
                {if isset($ceremoni) }

                    <headerText><br>Rankup Ceremony</headerText>
                    <text>{$ceremoni}</text>                        
                {/if}
            {else}
                <text>In this test you have to fight various people from other countries in a big tournament. Due to your not so good offensive and/or defensive skills you prove the you are not worthy of becoming a {$newRank}! You must have a total offence of 350000 in one type to pass. You <b>fail</b> the third test!</text>
            {/if}
        {else}
            <text>In this test your objective is to obtain a secret scroll from the enemy village, but due to your lack of defensive ninja skills you can't reach it! Train your defensive skills, so that you won't be discovered next time. You <b>fail</b> the second test!</text>
        {/if}
    {else}
        <text>You are given a test, but because of your lack of intelligence, strength, willpower and speed you cannot finish it! You <b>fail</b> the first test!</text>
    {/if}
            
    <text><br></text>
    <a href="?id=2">Return</a>
</td>
</tr>