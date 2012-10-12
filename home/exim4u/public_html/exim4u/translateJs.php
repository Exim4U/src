<?php

/** 
 * The goal is to translate items inside in a javascript file.
 *
 * Items to translate starts with 2 underscores, examples are:
 * '__Hello world'
 * '__Goodbye'
 *
 * This is a very light and simple solution to put translatable
 * words in a js file but still using gettext from PHP.
 * Javascript Gettext is not used.
 **/

// debug : to test translation without the full appContext :
//     putenv('LC_ALL=de_DE');
//     setlocale(LC_ALL, 'de_DE');
//     bindtextdomain('messages', './locale');
//     textdomain('messages');
//     print _("-- domain disabled (please see your administrator).");

include_once("appContext.php");

// security: we allow only known js files to be processed
$names = array("group.js");
$name = $_GET["name"];
if (!in_array($name, $names)) {
    throw new Exception("'$name' is an unregistered file");
}
pushAndTranslateJsFile($name);

function translateLine($line) {
    return preg_replace_callback(
        '/(\'__)(.*\')/',
        create_function(
            '$matches',
            'return "\'"._(substr($matches[2],0,strlen($matches[2])-1))."\'";'
        ),
        $line
    );
}
function pushAndTranslateJsFile($name) {
    $lastmod = gmdate('r', filemtime($name));
    $etag = md5(getenv('LC_ALL').$lastmod);

    // we produced headers that can be used by the http client
    // to cache the generated page
    header("Last-Modified: $lastmod");
    header("ETag: \"$etag\"");
    header('Content-Type: application/javascript');

    $fd = fopen($name, "rb");
    while (($line = fgets($fd, 512)) != FALSE) {
        // protection against infinite loop
        // (it can be tricky to test/understand regexp)
        $i = 10;
        do {
            $newLine = translateLine($line);
            $mustContinue = strcmp($newLine, $line) != 0;
            $line = $newLine;
            $i--;
            
        } while ($mustContinue && $i > 0);
        print $newLine;
    }
    fclose($fd);
}
