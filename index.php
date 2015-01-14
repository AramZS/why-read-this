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
function does_it_json($file){
$obj_file = json_decode($file);
 #var_dump($array_file);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            #echo ' - No errors';
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
    return $obj_file;
}

$obj_file = does_it_json($file);

$url = 'https://wdq.wmflabs.org/';
$file = 'api';
$vars = array(
            'action' => 'wbsearchentities',
            'search' => 'Moby Dick',
            'language' => 'en',
            'continue' => 7,
            'format' => 'json'
            );
#$vars = 'action=wbgetentities&sites=frwiki&titles=France&languages=zh-hans|zh-hant|fr&props=sitelinks|labels|aliases|descriptions&format=json';
$s = get_wikidata_url($vars);
$j = does_it_json($s);
var_dump($j); 

function get_wiki_data_obj($vars){
    $vars['format'] = 'json';
    #$vars = 'action=wbgetentities&sites=frwiki&titles=France&languages=zh-hans|zh-hant|fr&props=sitelinks|labels|aliases|descriptions&format=json';
    $s = get_wikidata_url($vars);
    $j = does_it_json($s);
    return $j;
}

function wb_authors_finder($obj, $obj_id){
    #Q174596
    if (isset($obj->entities->$obj_id->claims->P50)){
        $author = $obj->entities->$obj_id->claims->P50;
        $author = $author[0];
        $id_holder = 'numeric-id';
        $id = $author->mainsnak->datavalue->value->$id_holder;
        $id = 'Q'.$id;
        $sarray = array(
                'action' => 'wbgetentities',
                'ids' => $id#Q4985#'Q174596'#$r->id
            );
        $author_object = get_wiki_data_obj($sarray);
        $author_aliases = $author_object->entities->$id->aliases->en;
        return $author_aliases;
        #var_dump($author_object->entities->$id->aliases->en); die();
    } else {
        return false;
    }
}

function check_author_aliases_against($authors_obj, $author){
    foreach ($authors_obj as $obj) {
        var_dump($obj->value);
        var_dump('Checking: '.$author);
        $author_is = $obj->value;
        if ( $author == $author_is){
            return true;
        }
    }
    return false;
}

function do_results_have_author($json_as_obj, $check_author = "Hermann Melville"){
    foreach ($json_as_obj->search as $r){
        $sarray = array(
                'action' => 'wbgetentities',
                'ids' => $r->id
            );
        $id_obj = $r->id;
        $obj = get_wiki_data_obj($sarray);
        if (!isset($obj->entities->$id_obj->claims->P50)){

        } else {
            $author = $obj->entities->$id_obj->claims->P50;
            $author = $author[0];
            $id_holder = 'numeric-id';
            $authors = wb_authors_finder($obj, $id_obj); 
            if (!empty($authors) && check_author_aliases_against($authors, $check_author)){
                var_dump($r->id); die();#'Q174596');
            }
        }
        
        #P50 = author
    }
    return false;
}

$continue = 0;
$do_they = do_results_have_author($j);
while (!$do_they) {
    $continue += 7;
    $vars['continue'] = $continue;
    $s = get_wikidata_url($vars);
    $j = does_it_json($s);
    $do_they = do_results_have_author($j);
}

die();
foreach ($obj_file->files as $book) {
    ?><p><?php
    echo $book->title;
    echo '<br/>';
    #$info_url = 'http://en.wikipedia.org/w/api.php?format=json&action=query&';
    #file_get_contents($info);
    ?></p><?php
}