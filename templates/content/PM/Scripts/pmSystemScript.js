$(document).ready(function() {    
    
    // Location of the backend file
    var loadUrl = "./ajaxLibs/mainBackend.php";
    
    // An array of the link-classes to which we want to attach the backend
    var linkClasses = new Array( ".showTableTopLink", ".returnLink", ".showTableLink", ".prevEntries", ".nextEntries" );
    var submitClasses = new Array( "#sendForm" , "#tableParserCheckboxForm");
    
    // Setup the backend system
    setupBackend( 
        loadUrl, 
        linkClasses, 
        submitClasses, 
        "PMbackend", 
        "&token={$pageToken}&uid={$smarty.session.uid}&id={$smarty.get.id}" 
    );
    
});