<?php 
 /* ============== LICENSE INFO START ==============
  * 2005 - 2016 Studie-Tech ApS, All Rights Reserved
  * 
  * This file is part of the project www.TheNinja-RPG.com.
  * Dissemination of this information or reproduction of this material
  * is strictly forbidden unless prior written permission is obtained
  * from Studie-Tech ApS.
  * ============== LICENSE INFO END ============== */ 
?>
<?php
function BBCode2Html($text) {
	$text = trim($text);
            
    // BBCode [code]
    if (!function_exists('escape')) {
        function escape($s) {
            global $text;
            $text = strip_tags($text);
            $code = $s[1];
            $code = htmlspecialchars($code);
            $code = str_replace("[", "&#91;", $code);
            $code = str_replace("]", "&#93;", $code);
            return '<code>'.$code.'</code>';
        }    
    }
    $text = preg_replace_callback('/\[code\](.*?)\[\/code\]/ms', "escape", $text);
    $text = str_replace("url=www", "url=http://www", $text);  
    
    // BBCode to find...
    $in = array(      '/\[b\](.*?)\[\/b\]/ms',    
                     '/\[i\](.*?)\[\/i\]/ms',
                     '/\[u\](.*?)\[\/u\]/ms',
                     '/\[email\](.*?)\[\/email\]/ms',
                     '/\[url\="?(.*?)"?\](.*?)\[\/url\]/ms',
                     '/\[size\="?(.*?)"?\](.*?)\[\/size\]/ms',
                     //'/\[quote](.*?)\[\/quote\]/ms',
                     '/\[list\=(.*?)\](.*?)\[\/list\]/ms',
                     '/\[list\](.*?)\[\/list\]/ms',
                     '/\[\*\]\s?(.*?)\n/ms',
                     '/\[br\]/ms'
    );
    // And replace them by...
    $out = array(     '<b>\1</b>',
                     '<i>\1</i>',
                     '<u>\1</u>',
                     '<a href="mailto:\1">\1</a>',
                     '<a href="\1">\2</a>',
                     '<span style="font-size:\1%">\2</span>',
                     //'<blockquote><span>\1</span></blockquote>',
                     '<ol start="\1">\2</ol>',
                     '<ul>\1</ul>',
                     '<li>\1</li>',
                     '<br>'
    );
    $text = str_replace("PHPSESSID", "&nbsp;", $text);  
    $text = str_replace("javascript:document", "&nbsp;", $text);  
    $text = str_replace("document.cookie", "&nbsp;", $text);          
    
    $text = preg_replace($in, $out, $text);
    
    // paragraphs
    $text = str_replace("\r", "", $text);
    $text = "<p>".preg_replace("/(\n){2,}/", "</p><p>", $text)."</p>";
    $text = nl2br($text);
    
    // clean some tags to remain strict
    // not very elegant, but it works. No time to do better ;)
    if (!function_exists('removeBr')) {
        function removeBr($s) {
            return str_replace("<br>", "", $s[0]);
        }
    }    
    $text = preg_replace_callback('/<pre>(.*?)<\/pre>/ms', "removeBr", $text);
    $text = preg_replace('/<p><pre>(.*?)<\/pre><\/p>/ms', "<pre>\\1</pre>", $text);
    
    $text = preg_replace_callback('/<ul>(.*?)<\/ul>/ms', "removeBr", $text);
    $text = preg_replace('/<p><ul>(.*?)<\/ul><\/p>/ms', "<ul>\\1</ul>", $text);
    
    return $text;
}

// Echo data if some is sent in POST
if( isset( $_POST['data']) ){
    echo stripslashes(BBCode2Html( $_POST['data'] ));  
}