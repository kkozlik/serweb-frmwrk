<?php
/**
 * Functions for corect pick language file and load it
 *
 * @author    Karel Kozlik
 * @version   $Id: load_lang.php,v 1.13 2007/02/14 16:36:39 kozlik Exp $
 * @package   serweb
 * @subpackage framework
 */

global $_SERWEB;

/**
 *  Include configuration
 */
require_once($_SERWEB["coreconfigdir"]."config_lang.php");

/** If there is another directory with application specific config, try to
 *  load language configuration also from this directory */
if ($_SERWEB["configdir"] != $_SERWEB["coreconfigdir"] and
    file_exists($_SERWEB["configdir"] . "config_lang.php")){

    require_once($_SERWEB["configdir"]."config_lang.php");
}

/**
 * Class holding various methods for internationalization
 *
 * @package    serweb
 * @subpackage framework
 */
class Lang {

    function internationalize($str){
        global $lang_str;

        if (substr($str, 0, 1) == '@' and
            isset($lang_str[substr($str, 1)])){

            return $lang_str[substr($str, 1)];
        }

        return $str;
    }
}

/**
 * Analyzes some PHP environment variables to find the most probable language
 * that should be used
 *
 * @param   string   string to analyze
 * @param   integer  type of the PHP environment variable which value is $str
 *
 * @global  array    the list of available translations
 * @global  string   the retained translation keyword
 *
 * @access  private
 */

function lang_detect($str = '', $envType = ''){
    global $available_languages;

    foreach($available_languages AS $key => $value) {
        // $envType =  1 for the 'HTTP_ACCEPT_LANGUAGE' environment variable,
        //             2 for the 'HTTP_USER_AGENT' one
        //             3 for the user/domain/global attribute
        if (($envType == 1 && preg_match('/^(' . $value[0] . ')(;q=[0-9]\\.[0-9])?$/i', $str))
            || ($envType == 2 && preg_match('/(\(|\[|;[[:space:]])(' . $value[0] . ')(;|\]|\))/i', $str))
            || ($envType == 3 && ($value[2] == substr($str, 0, 2)))) {
            return $key;
        }
    }
    return false;
}



function determine_lang(){
    global $config, $available_languages;

    // Lang forced
    if (!empty($config->force_lang) && isset($available_languages[$config->force_lang])) {
        $_SESSION['lang'] = $config->force_lang;
    }

    // If session variable is set, obtain language from it
    if (isset($_SESSION['lang'])){
        if (isset($available_languages[$_SESSION['lang']])) return $_SESSION['lang'];
        else unset($_SESSION['lang']);
    }


    // try to findout user's language by checking cookie

    if (!empty($_COOKIE['serweb_lang']) and isset($available_languages[$_COOKIE['serweb_lang']])){
        return $_COOKIE['serweb_lang'];
    }

    // try to findout user's language by checking its HTTP_ACCEPT_LANGUAGE variable

    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $accepted    = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $acceptedCnt = count($accepted);
        for ($i = 0; $i < $acceptedCnt; $i++) {
            $lang = lang_detect($accepted[$i], 1);
            if (false != $lang) return $lang;
        }
    }

    // try to findout user's language by checking its HTTP_USER_AGENT variable

    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $lang = lang_detect($_SERVER['HTTP_USER_AGENT'], 2);
        if (false != $lang) return $lang;
    }


    if (!is_null($lang) and isset($available_languages[$lang])) return $lang;


    // Didn't catch any valid lang : we use the default settings

    return $config->default_lang;
}

/**
 *  Function load additional language file
 *
 *  This function may be used for example to loading modules purpose
 *
 *  @param  string  $ldir   path to directory which is scanned for language files
 *  @return bool            TRUE on success, FALSE when file is not found
 */
function load_another_lang($ldir){
    global $_SERWEB, $reference_language, $available_languages, $lang_str, $lang_set;

    $ldir = $_SERWEB["langdir"].$ldir."/";

    $primary_lang_file   = $ldir.$available_languages[$_SESSION['lang']][1].".php";
    $secondary_lang_file = $ldir.$available_languages[$reference_language][1].".php";

    if (file_exists($primary_lang_file)){
        require_once($primary_lang_file);
    }
    elseif(file_exists($secondary_lang_file)){
        require_once($secondary_lang_file);
    }
    else{
        ErrorHandler::log_errors(PEAR::RaiseError("Can't find requested language file",
                                 NULL, NULL, NULL,
                                 "Nor requested(".$primary_lang_file.") neither default(".$secondary_lang_file.") language file not exists"));

        return false;
    }

    return true;
}

$_SESSION['lang'] = determine_lang();


//set cookie containing selected lang
//cookie expires in one year
serwebSetCookie(
    'serweb_lang',
    $_SESSION['lang'],
    [
        'expires' => time()+31536000,
        'path'    => $config->root_path,
    ]);


/** load strings of selected language */
global $lang_set, $lang_str, $reference_language;

if (file_exists($_SERWEB["corelangdir"].$available_languages[$_SESSION['lang']][1].".php")){
    require_once($_SERWEB["corelangdir"].$available_languages[$_SESSION['lang']][1].".php");
}
else{
    require_once($_SERWEB["corelangdir"].$available_languages[$reference_language][1].".php");
}

/* set value of $lang_set[ldir] by avaiable_languages array */
$lang_set['ldir'] = $available_languages[$_SESSION['lang']][2];
$lang_set['lang_code'] = $available_languages[$_SESSION['lang']][2];

global $data;

if (!empty($config->data_sql->set_charset)){
    $data->set_db_charset($lang_set['charset'], null);
}

if (!empty($config->data_sql->collation)){
    $data->set_db_collation($config->data_sql->collation, null);
}
