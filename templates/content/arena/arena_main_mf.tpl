<div class="page-box">
    <div class="page-title">
        The Arena
    </div> 
    <div class="page-content">
        <div class="page-sub-title-top">
            Battle Arena <span class="toggle-button-info closed" data-target="#normal-arena"/>
        </div>
        <div class="toggle-target closed" id="normal-arena">
            Located in every village, the battle arena offers a chance to fight for prize money.<br>
            You will fight a random opponent around your level, most of the time. <br>
            Until now there's been {$village[0]['arena_fights']} arena fights in your village. <br><br>
            You walk up to the door. Will you enter and fight, or flee like a baby?
        </div>
        <div class="battle_arena_button_glow no-hover">
            {$optionsStandard}
        </div>

        <div class="page-sub-title">
            Mirror Arena <span class="toggle-button-info closed" data-target="#mirror-arena"/>
        </div>
        <div class="toggle-target closed" id="mirror-arena">
            Face one single extremely tough opponent for amazing rewards.
        </div>
        <div class="battle_arena_button_glow no-hover">
            {$optionsMirror}
        </div>

        <div class="page-sub-title">
            Torn Battle Arena <span class="toggle-button-info closed" data-target="#torn-arena"/>
        </div>
        <div class="toggle-target closed" id="torn-arena">
            Ready to put yourself to the ultimate test? <br/>
            Battle a series of opponents and see how far you can go without breaks in this special version of the battle arena.
        </div>
        <div class="battle_arena_button_glow no-hover">
            {$optionsTorn}
        </div>
    </div>
</div>