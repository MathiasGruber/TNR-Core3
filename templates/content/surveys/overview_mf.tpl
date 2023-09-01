<div class="page-box">
    <div class="page-title">
        Earn Popularity Points
    </div>
    <div class="page-content">
        <div class="page-sub-title-top">
            Mini-Games for Popularity Points
        </div>
        <div>
            Popularity points can be earned through our mini-game Ninja-Farmer, 
            available on iOS&Android. Click links below to go to the respective app stores.
            See top players <a href="?id={$smarty.get.id}&act=ninjaFarmer">here</a>
        </div>
        <div style="
            height: 120px;
            background-repeat: no-repeat;
            background-position: center top;
            background-image: url(images/NinjaFarmer/800x150TNR.png);
            text-align: left;
            padding-left: 60px;
            padding-top: 10px;"
            
            class="tdBorder">
            
            <a href="https://play.google.com/store/apps/details?id=com.YAAC.YANF&hl=en_GB">
            <img style="border:0;" src="images/NinjaFarmer/android.png" alt="Android" width="150" height="49">
            </a>
            
            <a href="https://itunes.apple.com/dk/app/ninja-farmer/id783128403?mt=8">
            <img style="border:0;" src="images/NinjaFarmer/ios.png" alt="Android" width="166" height="49">
            </a>
            
        </div>
        <div>
            More to come soon! If you are interested in creating a mini-game for TNR
            through which other users will be able to earn popularity points, please
            <a href="http://www.theninja-forum.com/index.php?app=nexus&module=support">contact</a> us for more information.
        </div>

        <div class="page-sub-title">Promotion Codes</div>
        <div>
            If you've obtained a promotion code somewhere, you may enter it 
            below to claim a popularity point. Each code can only be used once, 
            so unless you're the only one with the code be quick!
        </div>
        <form id="form1" name="form1" method="post" action="?id={$smarty.get.id}&act=checkCode">
            <div class="page-grid page-column-2">
                <input type="text" name="code" class="page-text-input-fill" />
                <input name="Submit" type="submit" class="page-button-fill" value="Check Code" />
            </div>
        </form>
    </div>
</div>