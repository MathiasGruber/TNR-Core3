function readyMenu()
{
    var groups = new Array("character", "combat", "map", "general", "communication", "village", "training", "missions", "support");
    var active = "";

    // Get the active tab
    if ($.cookie('activeTab')) {
        active = $.cookie('activeTab');
    }
    else {
        active = "character";
    }

    // Page Toggler
    function togglePage(requestDiv) {

        // If active tab is shown, hide it
        if ($('#div_' + active).is(":visible") && requestDiv != active) {
            $('#div_' + active).hide();
        }

        // Toggle the request Div
        $('#div_' + requestDiv).toggle();
        active = requestDiv;

        // Set the cookie
        if ($('#div_' + requestDiv).is(":visible")) {
            console.log('Tab is now active: ' + requestDiv);
            $.cookie('activeTab', requestDiv);
        } else {
            console.log('Tab no longer active: ' + requestDiv);
            $.cookie('activeTab', false);
        }
        return false;
    }

    for (var i = 0; i < groups.length; i++) {
        // anonymous function to fix closures
        (function () {
            var index = i; // also needed to fix closures

            // Show the active one. All others already hidden by css
            if (groups[index] == active) {
                $('#div_' + groups[index]).show();
            }

            // Click Function
            $("#h_" + groups[index]).click(function () {
                togglePage(groups[index]);
            });

        })();
    }

    // If no active, set to character
    console.log('Tab now active: ' + active);
    if (active == "") {
        $('#div_character').show();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    readyMenu();
});