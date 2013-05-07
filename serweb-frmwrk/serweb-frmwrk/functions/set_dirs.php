<?php
/**
 *  File describing directory structure (where the others files could be found)
 * 
 *  @author     Karel Kozlik
 *  @version    $Id: set_dirs.php,v 1.2 2007/02/14 16:36:39 kozlik Exp $
 *  @package    serweb
 *  @subpackage framework
 */ 

global $_SERWEB;

if (!isset($_SERWEB))   $_SERWEB = array();

$dir = realpath(dirname(__FILE__)."/..");
$_SERWEB["serwebdir"] =         $dir."/";
$_SERWEB["datadir"] =           $dir."/data_layer/";
$_SERWEB["appdir"] =            $dir."/application_layer/";
$_SERWEB["corefunctionsdir"] =  $dir."/functions/";
$_SERWEB["coreconfigdir"] =     $dir."/config/";
$_SERWEB["corelangdir"] =       $dir."/lang/";
$_SERWEB["coremodulesdir"] =    $dir."/modules/";
$_SERWEB["coretemplatesdir"] =  $dir."/templates/";
$_SERWEB["smartydir"] =         $dir."/smarty/";
$_SERWEB["phplibdir"] =         $dir."/phplib/";

// default paths to application directories
$_SERWEB["pagesdir"] =     $dir."/pages/";
$_SERWEB["templatesdir"] = $_SERWEB["coretemplatesdir"];
$_SERWEB["modulesdir"] =   $_SERWEB["coremodulesdir"];
$_SERWEB["langdir"] =      $_SERWEB["corelangdir"];
$_SERWEB["configdir"] =    $_SERWEB["coreconfigdir"];
$_SERWEB["smartypluginsdir"] = null;

unset ($dir);


// configure paths to application directories
if (file_exists(getenv('SERWEB_SET_DIRS'))) {
    require_once(getenv('SERWEB_SET_DIRS'));
}
elseif (getenv('SERWEB_SET_DIRS')) {
    die("Application directory configuration file configured in ".
        "'SERWEB_SET_DIRS' environment variable, does not exists: ". 
        getenv('SERWEB_SET_DIRS'));
}
else {
    die("Environment variable 'SERWEB_SET_DIRS' is not set");
} 
?>
