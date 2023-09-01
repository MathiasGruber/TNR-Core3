<table width="95%" class="table">
    <tr>
        <td class="subHeader">Chuunin exam!</td>
    </tr>
    <tr>
        <td style="text-align:left;padding:15px;">
            You enter the room where you are told the chuunin exam will start. You are told that this exam will consist of 3 tests.<br><br>
            
            <b><u>1st Test</u></b><br>
            {if $resultReturn.0 == "yes"}
                You are given a general test, and you complete it with ease <br>You <b>pass</b> the first test<br><br>
                
                <b><u>2nd Test</b></u><br>
                {if $resultReturn.1 == "yes"}
                    In this test your objective is to pass through the dangerous forest and gain access to an old tower. Due to your defensive skills as a ninja in general you are able to reach the tower<br>You <b>pass</b> the second test!<br><br>
                    
                    <b><u>3rd Test</b></u><br>
                    {if $resultReturn.2 == "yes"}
                        In this test you have to fight various people from other countries in a big tournament. Due to your good offensive skills you prove yourself worthy of being a Chuunin<br>You <b>pass</b> the third test<br><br>
                       
                        <div style="width:100%;text-align:center;"><a href="?id=2">Return</a></div>
                        
                        {if isset($ceremoni) }
                            </td></tr></table>
                            <table width="95%" class="table">
                            <tr>
                                <td class="subHeader">Rankup Ceremony</td>
                            </tr>
                            <tr><td style="text-align:left;padding:15px;">
                            {$ceremoni}
                        {/if}
                    {else}
                        In this test you have to fight various people from other countries in a big tournament. Due to your not so good offensive skills you prove the you are not worthy of becoming a Chuunin! Your must have once offense points with at least 5000 points.<br> You <b>fail</b> the third test!<br><br>
                    {/if}
                {else}
                    In this test your objective is to pass through the dangerous forest and gain access to an old tower, but due to your lack of defensive ninja skills you can't reach it!<br>You <b>fail</b> the second test!<br><br>
                {/if}
            {else}
                You are given a written test, but because of your lack of general intelligence, strength etc. you cannot finish it!<br>You <b>fail</b> the first test!<br><br>
            {/if}
        </td>
    </tr>
</table>