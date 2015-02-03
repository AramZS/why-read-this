<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors',1); 
echo "Let's get some JSON<pre><code>";

/**
* Send takes an array dimension from a backtrace and puts it in log format
*
* As part of the effort to create the most informative log we want to auto
* include the information about what function is adding to the log.
*
* @param array $caller The sub-array from a step in a debug_backtrace
*/
function pf_function_auto_logger($caller){
    if (isset($caller['class'])){
        $func_statement = '[ ' . $caller['class'] . '->' . $caller['function'] . ' ] ';
    } else {
        $func_statement = '[ ' . $caller['function'] . ' ] ';
    }
    return $func_statement;
}

function dump_log(){
    $trace=debug_backtrace();
    foreach ($trace as $key=>$call) {
        if ( in_array( $call['function'], array('call_user_func_array','do_action','apply_filter', 'call_user_func', 'do_action_ref_array', 'require_once') ) ){
            unset($trace[$key]);
        }
    }
    reset($trace);
    $first_call = next($trace);
    if (!empty($first_call)){
        $func_statement = pf_function_auto_logger( $first_call );
    } else {
        $func_statement = '[ ? ] ';
    }
    $second_call = next($trace);
    if ( !empty($second_call) ){
        if ( ('call_user_func_array' == $second_call['function']) ){
            $third_call = next($trace);
            if ( !empty($third_call) ) {
                $upper_func_statement = pf_function_auto_logger($third_call);
            } else {
                $upper_func_statement = '[ ? ] ';
            }
        } else {
            $upper_func_statement = pf_function_auto_logger($second_call);
        }
        $func_statement = $upper_func_statement . $func_statement;
    }
    var_dump($func_statement);

}


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
    #var_dump($base_url.$file.$url_qs); #die();
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

function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
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

function get_wikipedia_url($query_array){

    #https://www.wikidata.org/w/api.php?action=help&modules=query
    $base_url = "http://en.wikipedia.org/";
    $file = "w/api.php";
    $query = $query_array;
    $r = get_url($base_url,$file,$query,'','POST');
    return $r;
}

#var_dump($file);
function does_it_json($file){
   # var_dump($file);
   # dump_log();
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

function search_wikidata($args){
    $vars = array(
            'action'    =>  'wbsearchentities',
            'language'  =>  'en',
            'format'    =>  'json'
        );
    $array = array_merge($vars, $args);
    $s = get_wikidata_url($array);
    $j = does_it_json($s);
    #var_dump($j);
    return $j;
}

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
        #var_dump($obj->value);
        #var_dump('Checking: '.$author);
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
                return $r->id;#'Q174596');
            }
        }
        
        #P50 = author
    }
    return false;
}

function crawl_wikidata_for_title_and_author($j, $vars, $author){
    $continue = 0;
    $do_they = do_results_have_author($j);
    while (!$do_they && !empty($j->search)) {
        $continue += 7;
        $vars['continue'] = $continue;
        $j = search_wikidata($vars);
        $do_they = do_results_have_author($j);
    }

    $do_they_again = false;
    $vars['search'] = str_replace(' ', '-', $vars['search']);
    $continue = 0;
    $vars['continue'] = $continue;
    $j = search_wikidata($vars);
    $do_they = do_results_have_author($j);
    while (!$do_they && !empty($j->search)) {
        $vars['search'] = str_replace(' ', '-', $vars['search']);
        $continue += 7;
        $vars['continue'] = $continue;
        $j = search_wikidata($vars);
        $do_they = do_results_have_author($j);
    }
    #var_dump($do_they);
    return $do_they;
    
}

function get_wikipedia_title_from_wikidata($id){
        $vars = array(
            'action' => 'wbgetentities',
            'ids' => $id,
            'format' => 'json'
            );
    #Fun fact P18 is title images!
    #$vars = 'action=wbgetentities&sites=frwiki&titles=France&languages=zh-hans|zh-hant|fr&props=sitelinks|labels|aliases|descriptions&format=json';
    $j = search_wikidata($vars);
    #var_dump($j->entities->$id->sitelinks->enwiki->title);
    return $j->entities->$id->sitelinks->enwiki->title;

}

function get_plot_from_wikipedia_article($string){
    $plot = get_string_between($string, '==Plot==', '==');
    var_dump($plot);
    return $plot;
}

function get_wikipedia_description($title){
    $vars = array(
            'action' => 'query',
            'titles' => $title,
            'format' => 'json',
            'prop'   => 'revisions',
            'rvprop' => 'content',
            #'rawcontinue' => ''
        );
    #Fun fact P18 is title images!
    #$vars = 'action=wbgetentities&sites=frwiki&titles=France&languages=zh-hans|zh-hant|fr&props=sitelinks|labels|aliases|descriptions&format=json';
    $s = get_wikipedia_url($vars);
    #var_dump($s);
    $j = does_it_json($s); 
    #WHO THE HELL PUTS AN OBJECT ID INSIDE AN OBJECT THAT CAN ONLY BE ACCESSED BY ID?!
    foreach($j->query->pages as $page){
        $rs = $page->revisions;
        $array_key = "*";
        $string = $rs[0]->$array_key;
        $plot = get_plot_from_wikipedia_article($string);
    }
    if (false != $plot){
        return $plot;
    }
}

function get_book_description($title = 'Moby Dick', $author = 'Hermann Melville'){
    $vars = array(
            'search' => $title
            );
    $j = search_wikidata($vars);
    $author = $author;
    $do_they = crawl_wikidata_for_title_and_author($j, $vars, $author);

    if (false != $do_they ) {
        $title = get_wikipedia_title_from_wikidata($do_they);
        $description = get_wikipedia_description($title);
        var_dump($description);
        return $description;
        #var_dump(get_string_between($j->query->pages->1->revisions, '==Plot==', '=='));
    }
}

function some_books_with_descriptions($file_contents){
    $obj_file = does_it_json($file_contents);
    foreach ($obj_file->files as $book) {
        ?><p><?php
        echo $book->title;
        echo '<br/>';
        foreach ($book->authors as $authors_obj) {
            var_dump($authors_obj->display_name);
            $descrip = get_book_description($book->title, $authors_obj->display_name);
            if (false != $descrip){
                return $descrip;
            }
        }
        #$info_url = 'http://en.wikipedia.org/w/api.php?format=json&action=query&';
        #file_get_contents($info);
        ?></p><?php
    }
}


$file = file_get_contents('small_metadata.json');
#some_books_with_descriptions($file);

$description = get_book_description('Moby Dick', 'Hermann Melville');

var_dump($description);

die();

