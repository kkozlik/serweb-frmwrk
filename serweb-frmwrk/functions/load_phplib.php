<?php
/**
 *	Load all files needed by phplib
 *
 *	@author     Karel Kozlik
 *	@version    $Id: load_phplib.php,v 1.12 2007/02/14 16:36:39 kozlik Exp $
 *	@package    serweb
 *	@subpackage framework
 */

global $_SERWEB;

if (isset($GLOBALS['_phplib_page_open'])){
    if (isset($GLOBALS['_phplib_page_open']['sess'])) require_once($_SERWEB["phplibdir"] . "session4.1.php");
    if (isset($GLOBALS['_phplib_page_open']['auth'])) require_once($_SERWEB["phplibdir"] . "auth4.1.php");
    if (isset($GLOBALS['_phplib_page_open']['perm'])) require_once($_SERWEB["phplibdir"] . "perm4.1.php");

    require_once($_SERWEB["phplibdir"] . "page4.1.php");      /* Required, contains the page management functions. */

    if (isset($_SERWEB["phpliblocalconfig"])){                /* Require local configuration. */
        require_once($_SERWEB["phpliblocalconfig"]);
    }
    else{
        require_once($_SERWEB["phplibdir"] . "local/local.php");
    }
}

class PHPlib{
    public static $session = null;
    public static $auth = null;
    public static $perm = null;
}

function phplib_load($features = null){

    if (is_null($features)) $features = array('sess', 'auth', 'perm');
    if (is_string($features)) $features = array($features);

    if (isset($GLOBALS['_phplib_page_open'])){
        if (in_array('sess', $features)) put_headers();

        page_open ($GLOBALS['_phplib_page_open'], $features);
    }

}
