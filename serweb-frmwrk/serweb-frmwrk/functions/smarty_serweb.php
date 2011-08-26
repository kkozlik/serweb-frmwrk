<?php
/**
 * Smarty template engine customized for serweb
 * 
 * @author     Karel Kozlik
 * @version    $Id: smarty_serweb.php,v 1.9 2007/02/14 16:46:32 kozlik Exp $
 * @package    serweb
 * @subpackage framework
 */ 

/**
 *  include the smarty engine
 */
global $_SERWEB;
define ('SMARTY_DIR', $_SERWEB["smartydir"]);
require($_SERWEB["smartydir"].'Smarty.class.php');


/**
 * Smarty template engine customized for serweb
 * 
 * @package    serweb
 * @subpackage framework
 */ 
class Smarty_Serweb extends Smarty {

    function Smarty_Serweb() {
        global $config, $_SERWEB;
        
        // Smarty does not support "magic_quotes_runtime" enabled
        // So make sure to disable it.
        ini_set("magic_quotes_runtime", 0);
    
        // Class Constructor. These automatically get set with each new instance.
        parent::__construct();

        //set smarty directories
        $this->template_dir = array($_SERWEB["templatesdir"]);
        if ($_SERWEB["templatesdir"] != $_SERWEB["coretemplatesdir"]){
            $this->template_dir[] = $_SERWEB["coretemplatesdir"];
        }
        $this->config_dir =   $_SERWEB["templatesdir"].'configs/';
        $this->cache_dir =    $_SERWEB["templatesdir"].'cache/';

        $this->plugins_dir = array();
        if (!empty($_SERWEB["smartypluginsdir"])){
            $this->plugins_dir[] = $_SERWEB["smartypluginsdir"];
        }
        $this->plugins_dir[] = $_SERWEB["corefunctionsdir"]."smarty_plugins/";
        $this->plugins_dir[] = $_SERWEB["smartydir"]."plugins/";

        if (!empty($config->smarty_compile_dir)){
            RecursiveMkdir($config->smarty_compile_dir);
            $this->compile_dir =  $config->smarty_compile_dir;
        }
        else{
            $this->compile_dir =  $_SERWEB["templatesdir"].'templates_c/';
        }

    }


    /**
     *  Assign PHPLib form to smarty variable
     *  
     *  @param  string  $name           name of smarty variable
     *  @param  object  $form           phplib form
     *  @param  array   $start_arg      associative array of arguments of method {@link form::start() $form->start}
     *  @param  array   $finish_arg     associative array of arguments of method {@link form::finish() $form->finish}
     */
    
    function assign_phplib_form($name, $form, $start_arg = array(), $finish_arg = array()){
        global $controler;

        /* assign default values to args */     
        if (!isset($start_arg['jvs_name']))     $start_arg['jvs_name']="";
        if (!isset($start_arg['method']))       $start_arg['method']="";
        if (!isset($start_arg['action']))       $start_arg['action']="";
        if (!isset($start_arg['target']))       $start_arg['target']="";
        if (!isset($start_arg['form_name']))    $start_arg['form_name']="";
        
        if (!isset($finish_arg['after']))   $finish_arg['after']="";
        if (!isset($finish_arg['before']))  $finish_arg['before']="";

        /* do not leave 'action' empty */
        if (!$start_arg['action']) {
            $start_arg['action'] = $controler->url($_SERVER['PHP_SELF']);
        }

        /* create associative array of form elements and begin and end tags of form */

        /* add begin tag to assoc array */
        $f['start']=$form->get_start($start_arg['jvs_name'], $start_arg['method'], $start_arg['action'], $start_arg['target'], $start_arg['form_name']);

        /* add elements of form to assoc array */
        if (is_array($form->elements)){
            foreach($form->elements as $nm => $el){
                /* if element is radio we must add form elements for each radio button */
                if (get_class($el['ob']) == 'of_radio'){
                    /* values of radio buttos shold be stored in 'options' property, if they are not -> ignore this element */
                    if (isset($el['ob']->options) and is_array($el['ob']->options)){
                        foreach($el['ob']->options as $opt){
                            $f[$nm.'_'.$opt['value']] = $form->get_element($nm, $opt['value']);
                        }
                    }
                }
                /* if element is submit and it is not image, we have to strip the '_x' from end of its name */
                elseif (get_class($el['ob']) == 'of_submit'){
                    if (!$el['ob']->src) $f_nm = substr($nm, 0, -2);
                    else                 $f_nm = $nm;
                    
                    $f[$f_nm] = $form->get_element($nm);
                }
                else {
                    /* for all others elements simply add them to $f array */
                    $f[$nm] = $form->get_element($nm);
                }
            }
        }

        /* add end tag to assoc array */
        $f['finish']=$form->get_finish($finish_arg['after'], $finish_arg['before']);

        $this->assign($name, $f);   
    }
    
}

?>
