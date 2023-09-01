<div class="page-box">
    <div class="page-title">
        {$newRank} exam!
    </div>
    <div class="page-content">
        <div>
            You enter the room where you are told the exam will take place. You are told that this exam will consist of 3 tests, just like the other exams did back in the time.<br><br>
            
            <b><u>1st Test</u></b><br>
            {if $resultReturn.0 == "yes"}
                You are given a test, and you complete it with ease<br>You <b>pass</b> the first test<br><br>
                
                <b><u>2nd Test</b></u><br>
                {if $resultReturn.1 == "yes"}
                    In this test your objective is to obtain a secret scroll from the enemy village. Because of your intense training you are able to obtain the scroll<br>
                    You <b>pass</b> the second test!<br><br>
                    
                    <b><u>3rd Test</b></u><br>
                    {if $resultReturn.2 == "yes"}
                        In this test you have to fight various people from other countries in a big tournament. Due to your good offensive and/or defensive skills you prove yourself 
                        worthy of being a Elite Jounin<br>You <b>pass</b> the third test<br><br>
                        
                        {if isset($ceremoni) }
                            <div class="page-sub-title">
                                Rankup Gains
                            </div>
                            <div>
                                {$ceremoni}
                            </div>
                        {/if}
                    {else}
                        In this test you have to fight various people from other countries in a big tournament. Due to your not so good offensive skills you 
                        prove the you are not worthy of becoming a Jounin!<br>You <b>fail</b> the third test!<br><br>
                    {/if}
                {else}
                    In this test your objective is to obtain a secret scroll from the enemy village, but due to your lack of defensive ninja skills you can't reach it! 
                    Train your defensive skills, so that you won't be discovered next time<br>You <b>fail</b> the second test!<br><br>
                {/if}
            {else}
                You are given a test, but because of your lack of intelligence, strength, willpower and speed you cannot finish it!<br>
                You <b>fail</b> the first test!<br><br>
            {/if}
        </div>
    </div>
</div>