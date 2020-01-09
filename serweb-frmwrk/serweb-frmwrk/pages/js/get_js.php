<?php
/*
 *	This file providing access to the javascript files specifing for an module
 *	$Id: get_js.php,v 1.1 2005/08/22 14:35:12 kozlik Exp $
 */

Header("content-type: text/js");

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
    echo 'alert("file: '.htmlspecialchars($_GET['js']).' not found in module: '.htmlspecialchars($_GET['mod']).'");';
}
