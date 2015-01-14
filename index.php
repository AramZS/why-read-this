<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors',1); 
echo "Let's get some JSON<pre><code>";

function make_array_url_q($array){
    $s = '?';
    foreach ($array as $k=>$q){
        $s .= $k.'='.$q;
        $s .= '&';
    }
    return $s;
}


# Going to sub in my own cURL stuff here. 
function get_url($base_url, $file, $vars, $headers, $method = 'GET'){

    $url_qs = make_array_url_q($vars);
    $ch = curl_init();
    #$fp = fopen($file.$url_qs, "w");
    var_dump($base_url.$file.$url_qs);
#    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_URL,$base_url.$file.$url_qs);
    #curl_setopt($ch,CURLOPT_HTTPHEADER,$headers); #curl_setopt($ch,CURLOPT_HTTPHEADER,array('HeaderName: HeaderValue'));

    $r = curl_exec($ch);
    curl_close($ch);
#    fclose($fp);
    return $r;

}

function get_wikidata_url($query_array){

    $base_url = "http://wikidata.org/";
    $file = "w/api.php";
    $query = $query_array;

    $r = get_url($base_url,$file,$query);
}

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
$url = 'https://wdq.wmflabs.org/';
$file = 'api';
$vars = array('q' => 'CLAIM[31:188451]');
var_dump(get_url($url,$file,$vars,'')); die();

foreach ($obj_file->files as $book) {
    ?><p><?php
    echo $book->title;
    echo '<br/>';
    #$info_url = 'http://en.wikipedia.org/w/api.php?format=json&action=query&';
    #file_get_contents($info);
    ?></p><?php
}