<table width="95%" class="table">
    <tr>
        <td class="subHeader">{$newRank} exam!</td>
    </tr>
    <tr>
        <td style="text-align:left;padding:15px;">
            Your outstanding leadership during missions is considered by the council.
            You are first given a written exam to test the knowledge and experience you have gained throughout your career. 
            <br><br>
            
            <b><u>1st Test</u></b><br>
            {if $resultReturn.0 == "yes"}
                You complete the test with ease<br>
                You <b>pass</b> the first test<br><br>
                
                <b><u>2nd Test</b></u><br>
                You are taken to a special arena where you are assaulted from all sides by the elite ninja of your village. 
                Only by surviving an entire day of this onslaught can you earn the right to advance, though you are not to harm your attackers.
                {if $resultReturn.1 == "yes"}
                    Because of your intense training you are able to survive<br>You <b>pass</b> the second test!<br><br>
                    
                    <b><u>3rd Test</b></u><br>
                    You are brought to the strongest waterfall in the land. In order to test the limits of your chakra you are made 
                    to split the water until the graduation ceremony is completed in its entirety. 
                    {if $resultReturn.2 == "yes"}
                        Due to your good offensive and/or defensive skills you complete the test<br>
                        You <b>pass</b> the third test<br><br>
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
                        Due to lack of training you cannot complete this test!<br>You <b>fail</b> the third test!<br><br>
                    {/if}
                {else}
                    Due to your lack of defensive ninja skills you fail to pass this test<br>
                    You <b>fail</b> the second test!<br><br>
                {/if}
            {else}
                Because of your lack of general strengths and knowledge you cannot finish the test!<br>
                You <b>fail</b> the first test!<br><br>
            {/if}
        </td>
    </tr>
</table>