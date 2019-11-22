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

require_once($_SERWEB["phplibdir"] . "session4.1.php");   /* Required for everything below.      */
require_once($_SERWEB["phplibdir"] . "auth4.1.php");      /* Disable this, if you are not using authentication. */
require_once($_SERWEB["phplibdir"] . "perm4.1.php");      /* Disable this, if you are not using permission checks. */


require_once($_SERWEB["phplibdir"] . "local/local.php");     /* Required, contains your local configuration. */
require_once($_SERWEB["phplibdir"] . "page4.1.php");      /* Required, contains the page management functions. */

function phplib_load($features = null){

	if (is_null($features)) $features = array('sess', 'auth', 'perm');
	if (is_string($features)) $features = array($features);

	if (isset($GLOBALS['_phplib_page_open'])){
		if (in_array('sess', $features)) put_headers();

		page_open ($GLOBALS['_phplib_page_open'], $features);
	}

}
