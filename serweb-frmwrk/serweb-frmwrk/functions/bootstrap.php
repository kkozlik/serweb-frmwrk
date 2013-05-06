<?php
/**
 *  File loading configs, functions, class definitions etc.
 * 
 *  @author     Karel Kozlik
 *  @version    $Id: main_prepend.php,v 1.16 2007/10/04 21:34:16 kozlik Exp $
 *  @package    serweb
 *  @subpackage framework
 */ 

global $config;
global $serwebLog;
global $smarty;
global $_SERWEB;

require_once(dirname(__FILE__) . "/set_dirs.php");

/** require class defintions */
require_once ($_SERWEB["corefunctionsdir"] . "class_definitions.php");

/** require paths configuration */
require_once ($_SERWEB["coreconfigdir"] . "config_paths.php");

/** set $config->domain */
require_once ($_SERWEB["coreconfigdir"] . "set_domain.php");

/** require sql access configuration and table names */
require_once ($_SERWEB["coreconfigdir"] . "config_data_layer.php");

/** require other configuration */
require_once ($_SERWEB["coreconfigdir"] . "config.php");

/** if config.developer is present, replace default config by developer config */
if (file_exists($_SERWEB["coreconfigdir"] . "config.developer.php")){
    require_once ($_SERWEB["coreconfigdir"] . "config.developer.php");
}

/** if application specific config directory is different than core config
 *  directory, try to load application specific config 
 */
if ($_SERWEB["configdir"] != $_SERWEB["coreconfigdir"]){
    if (file_exists($_SERWEB["configdir"] . "config.php")){
        require_once ($_SERWEB["configdir"] . "config.php");
    }
    
    /** if config.developer is present, replace default config by developer config */
    if (file_exists($_SERWEB["configdir"] . "config.developer.php")){
        require_once ($_SERWEB["configdir"] . "config.developer.php");
    }
}

/** require database abstraction layer */
if ($config->data_sql->abstraction_layer=="MDB2")   require_once 'MDB2.php';
else                                                require_once 'DB.php';

if ($config->use_rpc){
    /** require PEAR XML_RPC class */
    require_once 'XML/RPC.php';
    require_once ($_SERWEB["corefunctionsdir"] . "xml_rpc_patch.php");
}

/** create log instance */
if ($config->enable_logging){
    require_once 'Log.php';
    eval('$serwebLog  = &Log::singleton("file", $config->log_file, "serweb", array(), '.$config->log_level.');');
}
else{
    $serwebLog  = NULL;

    /* 
     * define constants used by logging to avoid errors reported by php
     */
     
    /** System is unusable */
    define('PEAR_LOG_EMERG',    0);
    /** Immediate action required */     
    define('PEAR_LOG_ALERT',    1);
    /** Critical conditions */
    define('PEAR_LOG_CRIT',     2);
    /** Error conditions */
    define('PEAR_LOG_ERR',      3);
    /** Warning conditions */
    define('PEAR_LOG_WARNING',  4);
    /** Normal but significant */
    define('PEAR_LOG_NOTICE',   5);
    /** Informational */
    define('PEAR_LOG_INFO',     6);
    /** Debug-level messages */
    define('PEAR_LOG_DEBUG',    7);
}

/** require functions */
require_once ($_SERWEB["corefunctionsdir"] . "functions.php");

require_once ($_SERWEB["corefunctionsdir"] . "exceptions.php");

/** require Smarty and create Smarty instance */
require($_SERWEB["corefunctionsdir"]."smarty_serweb.php");
$smarty = new Smarty_Serweb;

/** require data layer for work with data store and create instance of it */
require_once ($_SERWEB["corefunctionsdir"] . "data_layer.php");

/** require modules */
require_once ($_SERWEB["corefunctionsdir"] . "load_modules.php");


/* 
 *  create instance of data_layer binded to proxy where is stored account  
 *  of currently authenticated user 
 */
$GLOBALS['data_auth'] = CData_Layer::singleton("auth_user");
/*  reference $data to $data_auth */
$GLOBALS['data'] = &$GLOBALS['data_auth'];

/** require page layout */
require_once ($_SERWEB["corefunctionsdir"] . "page.php");

require_once ($_SERWEB["corefunctionsdir"] . "load_phplib.php");
phplib_load("sess");
require_once ($_SERWEB["corefunctionsdir"] . "load_lang.php");
phplib_load(array("auth", "perm"));

require_once ($_SERWEB["corefunctionsdir"] . "load_apu.php");
$GLOBALS['controler']->add_required_javascript('core/phplib.js');

init_modules();

?>
