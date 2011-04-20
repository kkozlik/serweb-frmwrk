<?php
/**
 *	This file providing access to the css files stored in templates directory
 */

Header("content-type: text/css");

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
