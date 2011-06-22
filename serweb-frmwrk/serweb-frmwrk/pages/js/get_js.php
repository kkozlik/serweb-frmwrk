<?php
/*
 *	This file providing access to the javascript files specifing for an module
 *	$Id: get_js.php,v 1.1 2005/08/22 14:35:12 kozlik Exp $
 */

Header("content-type: text/js");

/**
 *  Do not allow to get file with ".." in their name
 *  This is for security reasons
 */
if (false !== strpos($_GET['js'], ".."))  die ("Prohibited file name");
if (false !== strpos($_GET['mod'], "..")) die ("Prohibited module name");

global $_SERWEB;
require(dirname(__FILE__)."/../../functions/set_dirs.php");

if (file_exists($_SERWEB["modulesdir"].$_GET['mod']."/".$_GET['js'])){
    require($_SERWEB["modulesdir"].$_GET['mod']."/".$_GET['js']);
}
elseif (file_exists($_SERWEB["coremodulesdir"].$_GET['mod']."/".$_GET['js'])){
    require($_SERWEB["coremodulesdir"].$_GET['mod']."/".$_GET['js']);
}
else{
    echo 'alert("file: '.$_GET['js'].' not found in module: '.$_GET['mod'].'");';
}

?>
