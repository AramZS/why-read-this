<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors',1); 
echo "Let's get some JSON<pre><code>";

function make_array_url_q($array){
    $s = '?';
    foreach ($array as $k=>$q){
        #$k = str_replace(' ', '%20', $k);
        #$q = str_replace(' ', '%20', $q);
        $s .= rawurlencode($k).'='.rawurlencode($q);
        $s .= '&';
    }
    return $s;
}


# Going to sub in my own cURL stuff here. 
function get_url($base_url, $file, $vars, $headers = array('Api-User-Agent: Example/1.0'), $method = 'GET'){

    if (is_array($vars)){ $url_qs = make_array_url_q($vars); } else { $url_qs = '?'.$vars; }
 #   $ch = curl_init();
    #$fp = fopen($file.$url_qs, "w");
    var_dump($base_url.$file.$url_qs);
#    var_dump(get_headers($base_url.$file.$url_qs));
#    curl_setopt($ch, CURLOPT_FILE, $fp);
#    curl_setopt($ch, CURLOPT_HEADER, 0);
#    curl_setopt($ch, CURLOPT_USERAGENT, 'MyCoolTool/1.1 (http://example.com/MyCoolTool/; MyCoolTool@example.com) BasedOnSuperLib/1.4');
#    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
#    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
#    curl_setopt($ch,CURLOPT_URL,$base_url.$file.$url_qs);
    #curl_setopt($ch,CURLOPT_HTTPHEADER,$headers); #curl_setopt($ch,CURLOPT_HTTPHEADER,array('HeaderName: HeaderValue'));

#    $r = curl_exec($ch);
    $r = file_get_contents($base_url.$file.$url_qs);
#    curl_close($ch);
#    fclose($fp);
    return $r;

}

function get_wikidata_url($query_array){

    #https://www.wikidata.org/w/api.php?action=help&modules=query
    $base_url = "http://wikidata.org/";
    $file = "w/api.php";
    $query = $query_array;
    $un = "nypl-zs";
    $pw = "npyppplp-pzpsp";
    $login_q = array(
                    'action' => 'login',
                    'lgname' => $un,
                    'lgpassword' => $pw
                );
    #$l = get_url("http://mediawiki.org/",$file,$login_q,'','POST');
    $r = get_url($base_url,$file,$query,'','POST');
    return $r;
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
$vars = array(
            'action' => 'wbsearchentities',
            'search' => 'Moby Dick',
            'language' => 'en',
            'format' => 'json'
            );
#$vars = 'action=wbgetentities&sites=frwiki&titles=France&languages=zh-hans|zh-hant|fr&props=sitelinks|labels|aliases|descriptions&format=json';
var_dump(get_wikidata_url($vars)); die();

foreach ($obj_file->files as $book) {
    ?><p><?php
    echo $book->title;
    echo '<br/>';
    #$info_url = 'http://en.wikipedia.org/w/api.php?format=json&action=query&';
    #file_get_contents($info);
    ?></p><?php
}