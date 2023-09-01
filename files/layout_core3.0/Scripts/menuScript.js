function readyMenu()
{
    var groups = new Array("character", "combat", "map", "general", "communication", "village", "training", "missions", "support");
    var active = "";
    if ($.cookie('activeTab')) {
        active = $.cookie('activeTab');
    } // Get the active tab
    else {
        active = "character";
    }

    function togglePage(requestDiv) { // Page Toggler
        if ($('#div_' + active).is(":visible") && requestDiv != active) {
            $('#div_' + active).hide();
        } // If active tab is shown, hide it        
        $('#menuTable').show();
        $('#div_' + requestDiv).show();
        active = requestDiv; // Toggle the request Div     
        // Set the cookie
        if ($('#div_' + requestDiv).is(":visible")) {
            console.log('Tab is now active: ' + requestDiv);
            $.cookie('activeTab', requestDiv);
        } else {
            console.log('Tab no longer active: ' + requestDiv);
            $('#menuTable').hide();
            $.cookie('activeTab', false);
        }
        return false;
    }
    for (var i = 0; i < groups.length; i++) {
        (function () { // anonymous function to fix closures
            var index = i; // also needed to fix closures
            if (groups[index] == active) {
                $('#menuTable').show();
                $('#div_' + groups[index]).show();
            } // Show the active one. All others already hidden by css
            $("#h_" + groups[index]).click(function () {
                togglePage(groups[index]);
            }); // Click Function
        })();
    }
    console.log('Tab now active: ' + active); // If no active, set to character
    if (active == "") {
        $('#div_character').show();
    }
    if (active == "false") {
        $('#menuTable').hide();
    }

    // Start at first entry
    var tutorialEntry = 1;

    function startTutorial() {
        if (tutorialEntry == 1) {

            // Set tutorial defaults
            $.fn.tooltipster('setDefaults', {
                theme: 'tooltipster-noir',
                maxWidth: 300,
                onlyOne: true,
                autoClose: false
            });

            // Capture all clicks and key presses, and advance the tutorial
            $(document).click(function (event) {
                console.log("Now advancing tutorial.");
                event.preventDefault();
                showTutorialEntry(tutorialEntry);
            });
        }
    }

    function showTutorialEntry(index) {
        console.log("Now showing tutorial entry with index: " + index)
        switch (index) {
            case 1:
                $('#mainGameTitle').tooltipster({
                    content: 'Welcome to the world of Seichi, where shinobi rule and monsters of unimaginable power are not all that unimaginable! The following will be a short introduction to how the game is played and where you will find the different things. Click on this toolbox or any button to continue the tutorial.',
                });
                $('#mainGameTitle').tooltipster('show');
                break;
            case 2:
                $('#mainUserRankWidget').tooltipster({
                    content: 'To the left of the main content we have the widget menu. The top widget shows your name, current rank and character avatar. You will be able to change the avatar as soon as you have reached the rank of Genin',
                    position: 'right'
                });
                $('#mainGameTitle').tooltipster('destroy');
                $('#mainUserRankWidget').tooltipster('show');

                break;
            case 3:
                $('#mainUserStatWidget').tooltipster({
                    content: 'The second widget has information about your current status, your current health, chakra and stamina, as well as the amount of money currently on your character.',
                    position: 'right'
                });
                $('#mainUserRankWidget').tooltipster('destroy');
                $('#mainUserStatWidget').tooltipster('show');
                break;
            case 4:
                $('#mainUserTravelWidget').tooltipster({
                    content: 'The third widget is used to travel the map. If you just started out, it is recommended that you wait a bit with travelling, this world is quite dangerous.',
                    position: 'right'
                });
                $('#mainUserStatWidget').tooltipster('destroy');
                $('#mainUserTravelWidget').tooltipster('show');
                break;
            case 5:
                $('#h_character').tooltipster({
                    content: 'To the right you have the main menu. All entries of the main menu are separated into 8 categories, as shown by the icons.',
                });
                $('#mainUserTravelWidget').tooltipster('destroy');
                $('#h_character').tooltipster('show');
                break;
            case 6:
                togglePage("character");
                $('#h_character').tooltipster('content', 'The first tab contain your main profile information as well as inventory, logbook, jutsu information and more.');
                break;
            case 7:
                togglePage("village");
                $('#h_village').tooltipster({
                    content: 'The village tab is where you can check the village hall of your faction, as well as many other things; shop for items, join a clan, check alliances, and much much more.',
                });
                $('#h_character').tooltipster('disable');
                $('#h_village').tooltipster('show');
                break;
            case 8:
                togglePage("training");
                $('#h_training').tooltipster({
                    content: 'The training tab is where you can train your character or learn new techniques. As you advance in ranks many more jutsus are unlocked and you get to specialize your character. You can also go on missions to help out your village, again, more are unlocked as you rank up.',
                });
                $('#h_village').tooltipster('destroy');
                $('#h_training').tooltipster('show');
                break;
            case 9:
                togglePage("map");
                $('#h_map').tooltipster({
                    content: 'Here you can travel the world, find & complete quests and visit other ninja villages. Once you reach a high enough rank, you will be able to get a profession by which you can mine metal ores, hunt for animals, or gather herbs on the map',
                    position: 'bottom'
                });
                $('#h_training').tooltipster('destroy');
                $('#h_map').tooltipster('show');
                break;
            case 10:
                togglePage("combat");
                $('#h_combat').tooltipster({
                    content: 'On the combat tab you can challenge other characters or fight in the battle arena to improve your fighting skills.',
                    position: 'bottom'
                });
                $('#h_map').tooltipster('destroy');
                $('#h_combat').tooltipster('show');
                break;
            case 11:
                togglePage("character");
                $('#h_character').tooltipster('enable');
                $('#h_character').tooltipster('content', 'Finally, to progress your character you need to earn experience which can be done through training & combat, and you need to complete level orders. For each level, there is an order that you must complete. Check your logbook for further information.');
                $('#h_combat').tooltipster('destroy');
                $('#h_character').tooltipster('show');
                break;
            case 12:
                tutorialEntry = 0;
                $(document).unbind("click");
                $('#h_character').tooltipster('destroy');
                break;
        }

        tutorialEntry = tutorialEntry + 1;
    }

    $("#tutorialIcon").click(function () {
        console.log("Now starting tutorial");
        startTutorial();
    });

    if ($("#autoStartTutorial").length) {
        console.log("Auto starting tutorial");
        startTutorial();
        showTutorialEntry(tutorialEntry);
    }

    // Make menu list buttons clickable
    $(document).on("click", ".menuLink", function (event) {
        console.log($(event.target).is("a") + " - " + event.target);
        if ($(event.target).is("a")) {
            // Click on link
            console.log("Clicked menu item link");
            window.location.href = $(event.target).attr("href");
        }
        else {
            // Click on list element
            console.log("Clicked menu item box");
            var $link = $(event.currentTarget).find("a");
            if (event.target === $link[0]) return false;
            window.location.href = $link.attr("href");
        }
        return false;
    });
}

document.addEventListener("DOMContentLoaded", () => {
    readyMenu();
});