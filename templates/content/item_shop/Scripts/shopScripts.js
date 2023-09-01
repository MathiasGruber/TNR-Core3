$(document).ready(function() {    
    
    // Location of the backend file
    var loadUrl = "./ajaxLibs/mainBackend.php";
    
    // An array of the link-classes to which we want to attach the backend
    var linkClasses = new Array( ".showTableLink",".showTableTopLink",".showTableOrderLink",".nextEntries",".prevEntries", ".returnLink" );
    var submitClasses = new Array();
    
    // Setup the backend system
    setupBackend( 
        loadUrl, 
        linkClasses, 
        submitClasses, 
        "ShopBackend", 
        "&token={$pageToken}&shopToken={$shopToken}&setupData={$setupData}&uid={$smarty.session.uid}&id={$smarty.get.id}" 
    );    
});