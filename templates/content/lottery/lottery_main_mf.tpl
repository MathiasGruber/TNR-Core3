<div class="page-box">
    <div class="page-title">
        Lottery
    </div>
    <div class="page-content">
        <div class="page-grid">
            <div class="page-sub-title-top">
                Ticket Overview
            </div>
            <div class="page-grid page-column-4">
                <div>
                    Available: <b>{$tickets}</b>
                </div>
                <div>
                    Owned: <b>{$User_tickets}</b>
                </div>
                <div>
                    Jackpot Price: {$Jackpot}
                </div>
                <div>
                    Normal Price: {$Normal}
                </div>
            </div>
            <div class="page-sub-title">
                Purchase Tickets
            </div>
            <form id="form1" name="form1" method="post" action="" class="page-grid">
                <div class="page-grid page-column-2">
                    <div>
                        <input type="text" name="tickets" placeholder="amount" class="page-text-input-fill"/>      
                    </div>
                    <div>
                        <strong>Jackpot:</strong> <input name="jackpot" type="radio" value="yes" />Yes / No<input name="jackpot" type="radio" value="no" />
                    </div>
                </div>
                <div>
                    <input type="submit" name="Submit" value="Submit" class="page-button-fill" />
                </div>
            </form>
        </div>
    </div>
</div>