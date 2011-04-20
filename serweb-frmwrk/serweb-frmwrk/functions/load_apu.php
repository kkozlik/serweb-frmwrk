<?php
/**
 *  Functions for loading APUs
 * 
 *  @author     Karel Kozlik
 *  @version    $Id: load_apu.php,v 1.6 2007/02/14 16:36:39 kozlik Exp $
 *  @package    serweb
 *  @subpackage framework
 */ 

/**
 *  Include files with APUs and load required data layer methods
 *
 *  @param  array   $_required_apu      list of APU names
 *  @param  bool    $add_controler_dl   load also data layer methods required by page controler?
 *  @return none
 *  @access private
 */ 
function _apu_require($_required_apu, $add_controler_dl = true){
    global $data, $_SERWEB;
    static $_loaded_apu = array();

    $required_data_layer = array();

    $loaded_modules = getLoadedModules();

    
    foreach($_required_apu as $item){
        if (false ===  array_search($item, $_loaded_apu)){ //if required apu isn't loaded yet, load it

            $file_found = false;

            //try found apu in loaded modules
            foreach($loaded_modules as $module){
                if (file_exists($_SERWEB["modulesdir"] . $module."/".$item.".php")){ 
                    require_once ($_SERWEB["modulesdir"] . $module."/".$item.".php");
                    $file_found = true;
                    break;
                }
                elseif (file_exists($_SERWEB["coremodulesdir"] . $module."/".$item.".php")){ 
                    require_once ($_SERWEB["coremodulesdir"] . $module."/".$item.".php");
                    $file_found = true;
                    break;
                }
            }

            // if apu was not found in modules, requere the one from 'application_layer' directory
            if (!$file_found){
                //require application unit
                require_once ($_SERWEB["appdir"] . $item.".php");   
            }
                
            $_loaded_apu[] = $item;
            $required_data_layer = array_merge($required_data_layer, call_user_func(array($item, 'get_required_data_layer_methods')));  
        }
    }
    
    if ($add_controler_dl){
        $page_ctl_class = isset($_SERWEB['_page_controller_classname']) ?
                                $_SERWEB['_page_controller_classname'] :
                                'page_conroler';
    
        $required_data_layer = array_merge($required_data_layer, call_user_func(array($page_ctl_class, 'get_required_data_layer_methods')));    
    }

    $data->add_method($required_data_layer);
} 

function load_apu($apu){
    $apu = array($apu);
    _apu_require($apu, false);
}

global $_SERWEB;

require_once ($_SERWEB["corefunctionsdir"] . "oohform_ext.php");
require_once ($_SERWEB["appdir"] . "apu_base_class.php");
require_once ($_SERWEB["appdir"] . "page_controler.php");

if (!empty($_SERWEB['_page_controller_filename'])){
    require_once ($_SERWEB['_page_controller_filename']);
}


if (isset($_SERWEB['_page_controller_classname'])){
    $GLOBALS['controler'] = new $_SERWEB['_page_controller_classname']();
}
else{
    $GLOBALS['controler'] = new page_conroler();
}

if (!isset($GLOBALS['_required_apu']) or !is_array($GLOBALS['_required_apu'])) 
    $GLOBALS['_required_apu'] = array();
_apu_require($GLOBALS['_required_apu']);

?>
