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

/** Create default object holding configuration */
if (!isset($config)) $config = new stdclass();

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

if (isset($_SERWEB["onconfigload"]) and is_callable($_SERWEB["onconfigload"])){
    call_user_func($_SERWEB["onconfigload"]);
}

/** create log instance */
if ($config->enable_logging){
    require_once 'Log.php';

    function enable_logging(){
        global $config;

        $handler = "file";
        $name = $config->log_file;
        $ident = isset($config->log_ident) ? $config->log_ident : "serweb";
        $conf = array();
        $level = null;

        if (is_int($config->log_level)) $level = $config->log_level;
        elseif (is_string($config->log_level)){
            $level = constant($config->log_level); // convert the constant name to its value
        }
        else{
            die("Invalid log level specified in config:". $config->log_level);
        }

        if (substr($config->log_file, 0 , 6) == 'syslog'){
            $handler = "syslog";
            $name = LOG_LOCAL0; // facility
            if (strlen($config->log_file) > 7){
                // if the $config->log_file contain also facility, read it
                $name = substr($config->log_file, 7);
                $name = constant($name); // convert the constant name to its value
            }
            $conf['reopen'] = true;
        }
        elseif (substr($config->log_file, 0 , 7) == 'console'){
            // log to console
            $handler = "console";
            $name = "";
            if (strlen($config->log_file) > 8){
                // if the $config->log_file contain also stream, read it
                $conf = array('stream' => constant(substr($config->log_file, 8)));
            }
        }
        elseif (substr($config->log_file, 0 , 9) == 'error_log'){
            $handler = "error_log";
            $name = PEAR_LOG_TYPE_SYSTEM;
            if (strlen($config->log_file) > 10){
                // if the $config->log_file contain also log type, read it
                $name = substr($config->log_file, 10);
                $name = constant($name); // convert the constant name to its value
            }
        }

        if (isset($config->log_options)){
            $conf = array_merge($conf, $config->log_options);
        }

        $GLOBALS['serwebLog'] = Log::singleton($handler, $name, $ident, $conf, $level);
    }
    enable_logging();

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


if (isset($_SERVER["HTTP_HOST"]) and isset($_SERVER["REQUEST_URI"])){
    // When running from cli, the variables bellow are not set
    sw_log('*** BOOTSTRAP: New request: '.$_SERVER['REQUEST_METHOD'].':'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], PEAR_LOG_DEBUG);
}
elseif(php_sapi_name() == "cli" and isset($_SERVER['argv'])){
    sw_log('*** BOOTSTRAP: Script executed: '.implode(" ", $_SERVER['argv']), PEAR_LOG_DEBUG);
}

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

if (!empty($_SERWEB["hookpreauth"])){
    call_user_func($_SERWEB["hookpreauth"]);
}

phplib_load(array("auth", "perm"));

require_once ($_SERWEB["corefunctionsdir"] . "load_apu.php");
$GLOBALS['controler']->add_required_javascript('core/phplib.js');

init_modules();
