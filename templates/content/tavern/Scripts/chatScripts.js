$(document).ready(function(){  
    
    // Location of the backend file
    var loadUrl = "./ajaxLibs/mainBackend.php";

    // An array of the link-classes to which we want to attach the backend
    var linkClasses = new Array(); //
    var submitClasses = new Array( "#Tform" , "#prevPosts", "#nextPosts", "#refresh", ".deletePost" );

    if (autoUpdateChat) {
        var autoChatTimer = 0;
        setInterval(function() { 
            
            // Increment timer
            autoChatTimer += 1;
            var reloadFreq = 20;
            var modulus = reloadFreq - autoChatTimer % reloadFreq
            
            console.log( modulus )
            $('#quickRefreshButton').val( "Quick Refresh ("+modulus+")" ) 
            
            if( modulus == 1 ){
                chatRefresh(loadUrl, "ChatBackend", "&token={$pageToken}&chatToken={$chatToken}&setupData={$setupData}&uid={$smarty.session.uid}&id={$smarty.get.id}"); 
            }
        }
        , 1000);
    }

    // Setup the backend system       
    setupBackend( 
        loadUrl, 
        linkClasses, 
        submitClasses, 
        "ChatBackend", 
        "&token={$pageToken}&chatToken={$chatToken}&setupData={$setupData}&uid={$smarty.session.uid}&id={$smarty.get.id}" 
    );
    
    function chatRefresh (loadUrl, backendSystem, pageData) {
        
        var curOffset = ($('#nextPosts').find('input[name="min"]').val() <= 10) ? 0 
            :  $('#nextPosts').find('input[name="min"]').val() - 
                0.5 * ($('#nextPosts').find('input[name="min"]').val() - $('#prevPosts').find('input[name="min"]').val());
          
        $.ajax({
            url: loadUrl,
            type: 'GET',
            data: 'min=' + curOffset + "&backend=" + backendSystem + pageData + '&refresh=true',
            dataType: 'json', // json later
            async: true,
            success: function(json) {
                if (json.mainContent) {
                    $("#tavernMessage").html(json.mainContent);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(((jqXHR.status == 0) ? "2. The page could not be retrieved. Please check your internet connection. Status: "+textStatus+". Error: "+errorThrown : jqXHR.responseText));
                location.reload();
            }
        });
    }
    
});








