$(document).ready(function(){  
    
    // Location of the backend file
    var loadUrl = "./ajaxLibs/mainBackend.php";

    $.each(["#Tform", ".deletePost"], function (key, value) {
        $(document).off("submit", value);
        $(document).on("submit", value, function (event) {
            event.preventDefault();
            absURL = $(event.currentTarget).attr('action');
            formUrl = "";
            if (absURL !== "") {
                formUrl = "&" + absURL.split("?")[1];
            }
            pageURL = $(event.currentTarget).serialize() + formUrl;

            //pageURL = pageURL.replace("'", "'");

            $.ajax({
                url: loadUrl,
                type: 'GET',
                data: pageURL + "&backend=" + 'ChatBackend' + "&token={$pageToken}&chatToken={$chatToken}&setupData={$setupData}&uid={$smarty.session.uid}&id={$smarty.get.id}&mf=yes",
                dataType: 'json', // json later
                success: function (json) {
                    //getting elements from page
                    current = $('.tavern-message-box');
                    $('.tavern-message-box').detach();

                    //checking for errors
                    var error = $(json.mainContent).find('.tavern-error');
                    if( error[0] )
                        alert(error[0]['innerHTML']);

                    //getting response elements
                    response = $(json.mainContent).find('div.tavern-message-box');


                    //building a new array of sorted elements from current and response with duplicates removed.
                    composit = {};
                    $([response, current]).each(function (key_1, value_1) {
                        $.each(value_1, function (key_2, value_2) {
                            composit[value_2.id] = value_2;
                        });
                    });

                    //putting elements on page
                    $(Object.keys(composit)).each(function(key_index,key){
                        var value = composit[key];
                        $(value).appendTo($('.tavern-top-box'));
                    });

                    if (value == '#Tform') {
                        $('.tavern-top-box').scrollTop($('.tavern-top-box').get(0).scrollHeight - $('.tavern-top-box').get(0).clientHeight);
                        $('.post-box').val("");
                    }

                    if (value == '.deletePost')
                    {
                        $(event.currentTarget.parentElement.parentElement.parentElement.parentElement).remove();
                    }
                }
            });
        });
    });


    if (autoUpdateChat) {
        var autoChatTimer = 0;
        setInterval(function() { 
            
            // Increment timer
            autoChatTimer += 1;
            var reloadFreq = 5;
            var modulus = reloadFreq - autoChatTimer % reloadFreq
            
            $('#quickRefreshButton').val( "Quick Refresh ("+modulus+")" ) 
            
            if( modulus == 1 ){
                chatRefresh(loadUrl, "ChatBackend", "&token={$pageToken}&chatToken={$chatToken}&setupData={$setupData}&uid={$smarty.session.uid}&id={$smarty.get.id}"); 
            }
        }
        , 1000);
    }

    function chatRefresh (loadUrl, backendSystem, pageData) {

        old_scroll_top_last_time = scroll_top_last_time;
        scroll_top_last_time = 9999999999;
        previous_height = $('.tavern-top-box').get(0).scrollHeight;

        $.ajax({
            url: loadUrl,
            type: 'GET',
            data: "backend=" + backendSystem + pageData + '&refresh=true' + '&mf=yes',
            dataType: 'json', // json later
            async: true,
            success: function(json) {
                if (json.mainContent) {
                    //getting elements from page
                    current = $('.tavern-message-box');
                    $('.tavern-message-box').detach();

                    //checking for errors
                    var error = $(json.mainContent).find('.tavern-error');
                    if( error[0] )
                        alert(error[0]['innerHTML']);

                    //getting response elements
                    response = $(json.mainContent).filter('div.tavern-message-box');

                    //building a new array of sorted elements from current and response with duplicates removed.
                    composit = {};
                    $([response, current]).each(function (key_1, value_1) {
                        $.each(value_1, function (key_2, value_2) {
                            composit[value_2.id] = value_2;
                        });
                    });

                    //putting elements on page
                    $(Object.keys(composit)).each(function (key_index, key) {
                        var value = composit[key];
                        $(value).appendTo($('.tavern-top-box'));
                    });

                    //updating last_min and scroll_top_last_time
                    scroll_top_last_time = old_scroll_top_last_time;
                    $('.tavern-top-box').scrollTop($('.tavern-top-box').get(0).scrollTop + $('.tavern-top-box').get(0).scrollHeight - previous_height);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(((jqXHR.status == 0) ? "2. The page could not be retrieved. Please check your internet connection. Status: "+textStatus+". Error: "+errorThrown : jqXHR.responseText));
                location.reload();
            }
        });
    }

    var scroll_top_last_time = 0;
    var last_min = 25;
    $('.tavern-top-box').scroll(function (event) {
        if (event.target.scrollTop == 0 && scroll_top_last_time < event.timeStamp) {
            scroll_top_last_time = event.timeStamp + 5000;
            previous_height = $('.tavern-top-box').get(0).scrollHeight;

            $('.tavern-top-box').css({
                overflow: 'hidden',
                height: '100%',
                paddingRight: '18px'
            });

            $.ajax({
                url: loadUrl,
                type: 'GET',
                data: "min=" + last_min + "&mf=yes&backend=ChatBackend&token={$pageToken}&chatToken={$chatToken}&setupData={$setupData}&uid={$smarty.session.uid}&id={$smarty.get.id}&refresh=true",
                dataType: 'json', // json later
                success: function (json) {
                    //getting elements from page
                    current = $('.tavern-message-box');
                    $('.tavern-message-box').detach();

                    //checking for errors
                    var error = $(json.mainContent).find('.tavern-error');
                    if( error[0] )
                        alert(error[0]['innerHTML']);

                    //getting response elements
                    response = $(json.mainContent).filter('div.tavern-message-box');

                    //building a new array of sorted elements from current and response with duplicates removed.
                    composit = {};
                    $([response, current]).each(function (key_1, value_1) {
                        $.each(value_1, function (key_2, value_2) {
                            composit[value_2.id] = value_2;
                        });
                    });

                    //putting elements on page
                    //$(composit).each(function (key, value) {
                    $(Object.keys(composit)).each(function (key_index, key) {
                        var value = composit[key];
                        $(value).appendTo($('.tavern-top-box'));
                    });

                    //updating last_min and scroll_top_last_time
                    last_min = last_min + 25;
                    scroll_top_last_time = event.timeStamp + 1500;

                    $('.tavern-top-box').scrollTop($('.tavern-top-box').get(0).scrollHeight - previous_height - 50);

                    $('.tavern-top-box').css({
                        overflow: 'auto',
                        height: 'auto',
                        paddingRight: '0px'
                    });
                },
                error: function()
                {
                    $('.tavern-top-box').css({
                        overflow: 'auto',
                        height: 'auto',
                        paddingRight: '0px',
                    });

                    if(!$('.tavern-top-box').html().replace(/\s/g,'').startsWith('<divclass="tavern-message-boxlazy"id="0">'))
                        $('<div class="tavern-message-box lazy" id="0"><div class="tavern-message-info"></div><div class="stiff-grid stiff-column-min-right-2 page-grid-justify-stretch"><div class="tavern-message" style="text-align: center;"><p>NO MORE MESSAGES</p></div></div></div>').prependTo($('.tavern-top-box'));
                }

            });
        }
    })

    $.each($('.scroll-to-bottom'), function (key, value) { $(value).scrollTop($(value)[0].scrollHeight - $(value)[0].clientHeight); });
});








