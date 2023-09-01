$(document).ready(function () {

    // Location of the backend file
    var loadUrl = "./ajaxLibs/mainBackend.php";

    // An array of the link-classes to which we want to attach the backend
    var linkClasses = new Array(".returnLink"); //
    var submitClasses = new Array('#trainingForm');

    // Setup the backend system
    setupBackend(
        loadUrl,
        linkClasses,
        submitClasses,
        "trainingBackend",
        "&mf=yes&token={$pageToken}&trainToken={$trainToken}&setupData={$setupData}&uid={$smarty.session.uid}&id={$smarty.get.id}"
    );
});