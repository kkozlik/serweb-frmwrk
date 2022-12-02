<?php
/**
 *  Emulating Disabled Options in IE
 *  Internet Explorer 6 does not implement disabled OPTION's in a SELECT. 
 *  This is workaround to deal with it.
 *
 *  Originally designed by Apptaro:
 *  http://apptaro.seesaa.net/article/21140090.html
 */

Header("Content-type: text/css");

/**  */
global $_SERWEB, $config;
require(dirname(__FILE__)."/../../functions/set_dirs.php");

require ($_SERWEB["coreconfigdir"] . "config_paths.php");

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

?>
select, option {
  behavior: url(<?php echo htmlspecialchars($config->style_src_path); ?>core/ie_select.htc);
}
