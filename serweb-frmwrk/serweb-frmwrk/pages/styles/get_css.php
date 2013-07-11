<?php
/**
 *	This file providing access to the css files stored in templates directory
 */

Header("content-type: text/css");

/* Load the file specified in SERWEB_AUTO_PREPEND environment variable */
if (getenv('SERWEB_AUTO_PREPEND')) {
    if (!file_exists(getenv('SERWEB_AUTO_PREPEND'))) {
        $err = "Auto prepend file configured in ".
               "'SERWEB_AUTO_PREPEND' environment variable, does not exists: ". 
               getenv('SERWEB_AUTO_PREPEND'); 
        trigger_error($err, E_USER_ERROR);
        die($err);
    }

    require_once(getenv('SERWEB_AUTO_PREPEND'));
}

/**
 *  Do not allow to get file with ".." in their name
 *  This is for security reasons
 */
if (false !== strpos($_GET['css'], ".."))  die ("Prohibited file name");

global $_SERWEB;
require(dirname(__FILE__)."/../../functions/set_dirs.php");

if (file_exists($_SERWEB["templatesdir"].$_GET['css'])){
    require($_SERWEB["templatesdir"].$_GET['css']);
}
elseif (file_exists($_SERWEB["coretemplatesdir"].$_GET['css'])){
    require($_SERWEB["coretemplatesdir"].$_GET['css']);
}
else{
    echo "CSS file has not been found: ".$_GET['css'];
}

?>
