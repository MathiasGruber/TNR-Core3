<table width="95%" class="table">
    <tr>
        <td class="subHeader">Genin exam!</td>
    </tr>
    <tr>
        <td style="text-align:left;padding:15px;">
            You enter the classroom as the genin exam starts. The exam consists of four tests which decide whether you graduate or not!<br><br>
            
            <b><u>1st Test</u></b><br>
            {if $resultReturn.0 == "yes"}
                You go to your sensei and perform a perfect Bunshin no Jutsu<br>You <b>pass</b> the first test!<br><br>
                
                <b><u>2nd Test</b></u><br>
                {if $resultReturn.1 == "yes"}
                    You go to your sensei and perform a perfect Henge no Jutsu <br>You <b>pass</b> the second test!<br><br>
                    
                    <b><u>3rd Test</b></u><br>
                    {if $resultReturn.2 == "yes"}
                        You go to your sensei and perform a perfect Kawarimi no Jutsu <br>You <b>pass</b> the third test!<br><br>
                        
                        <u><b>4th Test</b></u><br>
                        {if $resultReturn.3 == "yes"}
                            You are given a written test, and you complete it with ease<br>You <b>pass</b> the fourth test<br><br>
                            
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
                            You are given a written test, but because of your lack of intelligence you cannot finish it!<br>You <b>fail</b> the fourth test!<br><br>
                        {/if}
                    {else}
                        You go to your sensei and you try perform the Kawarimi no Jutsu , but can't do it well enough!<br>You <b>fail</b> the third test!<br><br>
                    {/if}
                {else}
                    You go to your sensei and you try perform the Henge no Jutsu, but can't do it well enough!<br>You <b>fail</b> the second test!<br><br>
                {/if}
            {else}
                You go to your sensei and you try perform the Bunshin no Jutsu, but can't do it well enough!<br>You <b>fail</b> the first test!<br><br>
            {/if}
        </td>
    </tr>
</table>
<div style="width:100%;text-align:center;">
    <a href="?id=2">Return</a>
</div>