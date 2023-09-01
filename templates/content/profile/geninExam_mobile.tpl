<tr>
<td>
    <headerText>Genin exam!</headerText>
    <text>You enter the classroom as the genin exam starts. The exam consists of four tests which decide whether you graduate or not!</text>
    
    <headerText><br>1st Test</headerText>
    {if $resultReturn.0 == "yes"}
        <text>You go to your sensei and perform a perfect Bunshin no Jutsu. You <b>pass</b> the first test!</text>
        
        <headerText><br>2nd Test</headerText>
        {if $resultReturn.1 == "yes"}
            <text>You go to your sensei and perform a perfect Henge no Jutsu. You <b>pass</b> the second test!</text>
            
            <headerText><br>3rd Test</headerText>
            {if $resultReturn.2 == "yes"}
                <text>You go to your sensei and perform a perfect Kawarimi no Jutsu. You <b>pass</b> the third test!</text>
                
                <headerText><br>4th Test</headerText>
                {if $resultReturn.3 == "yes"}
                    <text>You are given a written test, and you complete it with ease. You <b>pass</b> the fourth test</text>
                    {if isset($ceremoni) }
                        
                        <headerText><br>Rankup Ceremony</headerText>
                        <text>{$ceremoni}</text>                        
                    {/if}
                {else}
                    <text>You are given a written test, but because of your lack of intelligence you cannot finish it! You <b>fail</b> the fourth test!</text>
                {/if}
            {else}
                <text>You go to your sensei and you try perform the Kawarimi no Jutsu , but can't do it well enough! You <b>fail</b> the third test!</text>
            {/if}
        {else}
            <text>You go to your sensei and you try perform the Henge no Jutsu, but can't do it well enough! You <b>fail</b> the second test!</text>
        {/if}
    {else}
        <text>You go to your sensei and you try perform the Bunshin no Jutsu, but can't do it well enough! You <b>fail</b> the first test!</text>
    {/if}
    <text><br></text>
    <a href="?id=2">Return</a>
</td>
</tr>