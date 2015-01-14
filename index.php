<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors',1); 
echo "Let's get some JSON<pre><code>";

$file = file_get_contents('small_metadata.json');

#var_dump($file);
$obj_file = json_decode($file);
#var_dump($array_file);
/**    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            echo ' - No errors';
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }

**/
foreach ($obj_file->files as $book) {
    ?><p><?php
    echo $book->title;
    echo '<br/>';
    $info_url = 'http://en.wikipedia.org/w/api.php?format=json&action=query&';
    file_get_contents($info);
    ?></p><?php
}