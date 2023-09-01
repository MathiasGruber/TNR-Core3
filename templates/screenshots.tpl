<div class="menuContainer">
    <div class="menuBg1 menuBg1-left">
        <div class="menuPerforationVertical menuPerforationVerticalLeft">
            <div class="perforationMenuHorizontal perforationMenuHorizontalTop"></div>		 
            <div class="menuBg2 menuBg2-right">
                <div class="wrapper_outer">
                    <div class="wrapper_inner" style="padding-bottom:0px;">
                        {if isset($topPlayers)}
                            {foreach $topPlayers as $key => $item}
                                <div class="topPlayersRow {cycle values="row1,row2"}">
                                    <div class="toplistNumbers">#{($key+1)}</div>
                                    <b> {$item.username}</b><br>
                                    <i>{$item.rank}</i><br>
                                    <i>{$item.pvp_experience} PvP Exp</i>
                                </div>
                            {/foreach}
                        {/if}
                    </div>
                </div>
            </div>    
            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom"></div>
        </div>
        <div class="ribbonStats ribbonStats-right">
            <div class="ribbonText">Top Players &nbsp;</div>
        </div>
    </div>      
</div>                        

<div class="menuContainer">
    <div class="menuBg1 menuBg1-left">
        <div class="menuPerforationVertical menuPerforationVerticalLeft">
            <div class="perforationMenuHorizontal perforationMenuHorizontalTop"></div>		 
            <div class="menuBg2 menuBg2-right">
                <div class="wrapper_outer">
                    <div class="wrapper_inner">
                        <table width="100%" style="padding:0px;border:0px solid #580000;margin-bottom: 5px;border-spacing:0px;border-collapse: collapse;" >
                            <tr>
                                <td>
                                    <a href="./images/core3screens/battle.jpg">
                                        <img border="2" width="70px" height="40px" 
                                             src="./images/core3screens/thumbnails/battle.jpg" alt="Screenshot 1"></img></a>
                                </td>
                                <td>
                                    <a href="./images/core3screens/inventory.jpg">
                                        <img border="2" width="70px" height="40px" 
                                             src="./images/core3screens/thumbnails/inventory.jpg" alt="Screenshot 2"></img></a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="./images/core3screens/logbook.png">
                                        <img border="2" width="70px" height="40px" 
                                             src="./images/core3screens/thumbnails/logbook.jpg" alt="Screenshot 3"></img></a>
                                </td>
                                <td>
                                    <a href="./images/core3screens/myJutsu.png">
                                        <img border="2" width="70px" height="40px" 
                                             src="./images/core3screens/thumbnails/myJutsu.jpg" alt="Screenshot 4"></img></a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="./images/core3screens/profile.jpg">
                                        <img border="2" width="70px" height="40px" 
                                             src="./images/core3screens/thumbnails/profile.jpg" alt="Screenshot 5"></img></a>
                                </td>
                                <td>
                                    <a href="./images/core3screens/training.png">
                                        <img border="2" width="70px" height="40px" 
                                             src="./images/core3screens/thumbnails/training.jpg" alt="Screenshot 6"></img></a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="./images/core3screens/travel.png">
                                        <img border="2" width="70px" height="40px" 
                                             src="./images/core3screens/thumbnails/travel.png" alt="Screenshot 7"></img></a>
                                </td>
                                <td>
                                    <a href="./images/core3screens/villageHall.png">
                                        <img border="2" width="70px" height="40px" 
                                             src="./images/core3screens/thumbnails/villageHall.png" alt="Screenshot 8"></img></a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>    
            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom"></div>
        </div>
        <div class="ribbonStats ribbonStats-right">
            <div class="ribbonText">screenshots &nbsp;</div>
        </div>
    </div>      
</div>                        