
//adding durability sorter to script
$.tablesorter.addParser
    ({
        id: 'durability',
        is: function (s, table, cell, $cell) { return false; },
        format: function (s, table, cell, cellIndex) {
            if (s == 'n/a')
                return 0;

            else {
                var working_string;
                working_string = s.replace('(', '');
                working_string = working_string.replace(')', '');
                var durability = working_string.split('/');
                return durability[1] / durability[2];
            }
        }
    });

//adding durability sorter to html
$(function () {
    $("table").tablesorter({
        headers: {
            2: {
                sorter: 'durability'
            }
        }
    });
});
