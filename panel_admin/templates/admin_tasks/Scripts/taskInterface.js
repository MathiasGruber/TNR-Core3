$(document).ready(function() {
    
    $('#div_newEntry').hide();
    $('#div_currentList').hide();
    $('#div_instructions').hide();
    $('#div_search').hide();
    $('#div_facebookAchievements').hide();
    
    $("#currentList").data('clicked', false);
    $("#instructions").data('clicked', false);
    $("#search").data('clicked', false);
    $("#newEntry").data('clicked', false);
    $("#facebookAchievements").data('clicked', false);
    
    function setActive( div ) {
        console.log('setActive: '+div)
        div == "newEntry"               ? $("#newEntry").data('clicked', true)              : $("#newEntry").data('clicked', false);
        div == "currentList"            ? $("#currentList").data('clicked', true)           : $("#currentList").data('clicked', false);
        div == "instructions"           ? $("#instructions").data('clicked', true)          : $("#instructions").data('clicked', false);
        div == "search"                 ? $("#search").data('clicked', true)                : $("#search").data('clicked', false);
        div == "facebookAchievements"   ? $("#facebookAchievements").data('clicked', true)  : $("#facebookAchievements").data('clicked', false);
    }
    
    function getClicked(){
        if( $("#currentList").data('clicked') ){
            return "currentList";
        }
        else if( $("#instructions").data('clicked') ){
            return "instructions";
        }
        else if( $("#search").data('clicked') ){
            return "search";
        }
        else if( $("#newEntry").data('clicked') ){
            return "newEntry";
        }
        else if( $("#facebookAchievements").data('clicked') ){
            return "facebookAchievements";
        }
        else {
            return false;
        }
    }
    
    function showPage( requestDiv ){
        var clicked = getClicked();
        if( clicked && clicked != requestDiv )
        {
            setActive( requestDiv )
            $('#div_'+clicked).fadeOut('fast', function() {
                $('#div_'+requestDiv).fadeToggle('fast');
                return false;
            });
        }
        else
        {
            if( !clicked )
            {
                $("#"+requestDiv).data('clicked', true);
                $('#div_'+requestDiv).fadeToggle('fast');
                return false;
            }
            else
            {
                $("#"+requestDiv).data('clicked', false);
                $('#div_'+requestDiv).fadeToggle('fast');
                return false;
            }
        }
        return false;     
    }
    
    
    $("#instructions,#a_instructions").click(function(e) {
        e.preventDefault();
        showPage( "instructions" );
        return false;
    });
    
    $("#currentList,#a_currentList").click(function(e) {
        e.preventDefault();
        showPage( "currentList" );
        return false;
    });
	
    $("#newEntry,#a_newEntry").click(function(e) {
        e.preventDefault();
        showPage( "newEntry" );
        return false;
    });
    
    $("#search,#a_search").click(function(e) {
        e.preventDefault();
        showPage( "search" );
        return false;
    });
    
    $("#facebookAchievements,#a_facebookAchievements").click(function(e) {
        e.preventDefault();
        showPage( "facebookAchievements" );
        return false;
    });
	
    
	
});