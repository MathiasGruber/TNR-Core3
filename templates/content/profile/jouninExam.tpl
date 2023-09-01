<table width="95%" class="table">
    <tr>
        <td class="subHeader">{$newRank} exam!</td>
    </tr>
    <tr>
        <td style="text-align:left;padding:15px;">
            You enter the room where you are told the {$newRank} exam will start now. You are told that this exam will consist of 3 tests, 
            just like the chuunin exam did back in the day.<br><br>
            
            <b><u>1st Test</u></b><br>
            {if $resultReturn.0 == "yes"}
                You are given a test, and you complete it with ease<br>You <b>pass</b> the first test<br><br>
                
                <b><u>2nd Test</b></u><br>
                {if $resultReturn.1 == "yes"}
                    In this test your objective is to obtain a secret scroll from the enemy village. Because of your intense 
                    training you are able to obtain the scroll<br>You <b>pass</b> the second test!<br><br>
                    
                    <b><u>3rd Test</b></u><br>
                    {if $resultReturn.2 == "yes"}
                        In this test you have to fight various people from other countries in a big tournament. Due to your good 
                        offensive and/or defensive skills you prove yourself worthy of being a {$newRank}<br>You <b>pass</b> the third test<br><br>
                       
                        <div style="width:100%;text-align:center;"><a href="?id=2">Return</a></div>
                        
                        {if isset($ceremoni) }
                            </td></tr></table>
                            <table width="95%" class="table">
                            <tr>
                                <td class="subHeader">Rankup Gains</td>
                            </tr>
                            <tr><td style="text-align:left;padding:15px;">
                            {$ceremoni}
                        {/if}
                    {else}
                        In this test you have to fight various people from other countries in a big tournament. 
                        Due to your not so good offensive and/or defensive skills you prove the you are not worthy of becoming a 
                        {$newRank}! You must have a total offence of 350000 in one type to pass.<br>You <b>fail</b> the third test!<br><br>
                    {/if}
                {else}
                    In this test your objective is to obtain a secret scroll from the enemy village, but due to your lack of defensive 
                    ninja skills you can't reach it! Train your defensive skills, so that you won't be discovered next time<br>
                    You <b>fail</b> the second test!<br><br>
                {/if}
            {else}
                You are given a test, but because of your lack of intelligence, strength, willpower and speed you cannot finish it!<br>
                You <b>fail</b> the first test!<br><br>
            {/if}
        </td>
    </tr>
</table>