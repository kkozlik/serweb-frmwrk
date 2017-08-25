<?php
/**
 *	Functions needed for load module
 * 
 *	@author     Karel Kozlik
 *	@version    $Id: load_modules.php,v 1.7 2008/06/26 09:52:19 kozlik Exp $
 *	@package    serweb
 *	@subpackage framework
 */ 

    /** Flag indicating whether init_modules() function has been already executed */
    $GLOBALS['__modules_initiated__'] = false;

	/**
	 * Include file "include.php" from each loaded module
	 */
	function include_modules(){
		global $_SERWEB, $config;
		
		$loaded_modules = getLoadedModules();
	
		foreach($loaded_modules as $module){
			if (file_exists($_SERWEB["modulesdir"] . $module."/include.php")){ 
				require_once ($_SERWEB["modulesdir"] . $module."/include.php");
			}
			elseif (file_exists($_SERWEB["coremodulesdir"] . $module."/include.php")){ 
				require_once ($_SERWEB["coremodulesdir"] . $module."/include.php");
			}
		}
		
		unset($loaded_modules);
	}
	
	/**
	 * Call function <module_name>_init() for each loaded module
	 */
	function init_module($module){
	    static $initiated_modules = array();

        /* if module is already initited -> exit */
        if (!empty($initiated_modules[$module])) return;
	    
        $module = str_replace("-", "_", $module);
		if (function_exists($module."_init"))
			call_user_func($module."_init");
			
		$initiated_modules[$module] = true;
	}

	/**
	 * Call function <module_name>_init() for each loaded module
	 */
	function init_modules(){
		$loaded_modules = getLoadedModules();
	
		foreach($loaded_modules as $module){
		    init_module($module);
		}

        $GLOBALS['__modules_initiated__'] = true;
		
		unset($loaded_modules);
	}
	

	/**
	 * Include additional module
	 * This function should be called before function init_modules()
	 *
	 * @param string $mod	name of module
	 * @access private
	 */	
	function include_module($mod){
		global $_SERWEB, $config;

		if (!empty($config->modules[$mod])) return;
		$config->modules[$mod] = true;

		if (file_exists($_SERWEB["modulesdir"] . $mod."/include.php")){ 
			require_once ($_SERWEB["modulesdir"] . $mod."/include.php");
		}
		elseif (file_exists($_SERWEB["coremodulesdir"] . $mod."/include.php")){ 
			require_once ($_SERWEB["coremodulesdir"] . $mod."/include.php");
		}
		
        /* if other modules has been already initiated, init module imediately */	
		if ($GLOBALS['__modules_initiated__']) init_module($mod);
	}
	
	global $config;
	/* enable required modules */	
	if (isset($GLOBALS['_required_modules'])){
		foreach((array) $GLOBALS['_required_modules'] as $m){
			$config->modules[$m] = true;
		}
	}
	
	
	include_modules();
?>
