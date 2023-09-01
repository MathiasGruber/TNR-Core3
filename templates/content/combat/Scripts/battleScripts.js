$(document).ready(function() {

    function setupBattleOptions() {

        var actionGroups = new Array("standardAction", "itemAction", "jutsuAction");
        var targetGroups = new Array("friendTarget", "enemyTarget");
        var activeAction = "";
        var activeTarget = "";
        
        // Get the active action tab
        activeAction = $.cookie('activeActionTab') ? $.cookie('activeActionTab') : "standardAction";
        activeTarget = $.cookie('activeTargetTab') ? $.cookie('activeTargetTab') : "enemyTarget";
        console.log("Active Action:" + activeAction);
        console.log("Active Target:" + activeTarget);
        
        // Action Page Toggler
        function toggleActionPage(requestDiv) {

            // If active tab is shown, hide it
            if ($('.' + activeAction).is(":visible") && requestDiv != activeAction) {
                $('.' + activeAction).hide();
            }

            // Toggle the request Div
            $('.' + requestDiv).show();
            activeAction = requestDiv;
            // Set the cookie
            if ($('.' + requestDiv).is(":visible")) {
                console.log('Tab is now active: ' + requestDiv);
                $.cookie('activeActionTab', requestDiv);
            } else {
                console.log('Tab is now no longer active: ' + requestDiv);
                $.cookie('activeActionTab', false);
            }
            return false;
        }

        for (var i = 0; i < actionGroups.length; i++) {
            // anonymous function to fix closures
            (function() {
                var index = i; // also needed to fix closures

                // Show the active one. All others already hidden by css
                if (actionGroups[index] == activeAction) {
                    $('.' + actionGroups[index]).show();
                }

                // Click Function
                $("#h_" + actionGroups[index]).click(function() {
                    toggleActionPage(actionGroups[index]);
                });
            })();
        }


        // Target Page Toggler
        function toggleTargetPage(requestDiv) {

            // If active tab is shown, hide it
            if ($('.' + activeTarget).is(":visible") && requestDiv != activeTarget) {
                $('.' + activeTarget).hide();
            }

            // Toggle the request Div
            $('.' + requestDiv).show();
            activeTarget = requestDiv;
            // Set the cookie
            if ($('.' + requestDiv).is(":visible")) {
                console.log('Tab is now active: ' + requestDiv);
                $.cookie('activeTargetTab', requestDiv);
            } else {
                console.log('Tab is now no longer active: ' + requestDiv);
                $.cookie('activeTargetTab', false);
            }
            return false;
        }

        for (var i = 0; i < targetGroups.length; i++) {
            // anonymous function to fix closures
            (function() {
                var index = i; // also needed to fix closures

                // Show the active one. All others already hidden by css
                if (targetGroups[index] == activeTarget) {
                    $('.' + targetGroups[index]).show();
                }

                // Click Function
                $("#h_" + targetGroups[index]).click(function() {
                    toggleTargetPage(targetGroups[index]);
                });
            })();
        }

        // Target Groups
        $('.logEntry').click(function() {
            console.log('Toggling hidden class ');
            $(this).parent("tr").next("tr").toggleClass('hidden');
        });
        $('.logMain').click(function() {
            console.log('Toggling hidden2 class ');
            $(this).parent("tr").next("tr").toggleClass('hidden2');
        });
        // If no active, set to character
        console.log('Action Tab starting: ' + activeAction);
        if (activeAction == "") {
            $('.standardAction').show();
        }

        // If no active, set to character
        console.log('Target Tab starting: ' + activeTarget);
        if (activeTarget == "") {
            $('.enemyTarget').show();
        }
    }

    // On load, setup the battle options
    setupBattleOptions();

    // Function for handling responses from combatBackend
    function handleSuccess(json) {
        setupBattleOptions();
    }
    
    // Location of the backend file
    var loadUrl = "./ajaxLibs/mainBackend.php";
    
    // An array of the link-classes to which we want to attach the backend
    var linkClasses = new Array( ".refreshButton" ); //
    var submitClasses = new Array( '.battleForm' );
    
    // Setup the backend system
    setupBackend( 
        loadUrl, 
        linkClasses, 
        submitClasses, 
        "combatBackend", 
        "&token={$pageToken}&uid={$smarty.session.uid}&id={$smarty.get.id}" ,
        function(){
            setupBattleOptions();
        }
    ); 
});