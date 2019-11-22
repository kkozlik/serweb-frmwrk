<?php
/**
 * Smarty template engine customized for serweb
 *
 * @author     Karel Kozlik
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

    public function __construct() {
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
     *  Assign OOH form to smarty variable
     *
     *  @param  string  $form_name      name of smarty variable
     *  @param  object  $form           OOH Form
     *  @param  array   $start_arg      associative array of arguments of method {@link form::start() $form->start}
     *  @param  array   $finish_arg     associative array of arguments of method {@link form::finish() $form->finish}
     */
    function assign_phplib_form($form_name, OohForm $form, $start_arg = array(), $finish_arg = array()){
        global $controler;

        $controler->trigger_event("pre_form_smarty");

        /* do not leave 'action' empty */
        if (empty($start_arg['action'])) {
            $start_arg['action'] = $controler->url($_SERVER['PHP_SELF']);
        }

        /* assign default values to args */
        if (isset($start_arg['form_name']))     $form->name($start_arg['form_name']);
        if (isset($start_arg['jvs_name']))      $form->jvs_name($start_arg['jvs_name']);
        if (isset($start_arg['method']))        $form->method($start_arg['method']);
        if (isset($start_arg['action']))        $form->action($start_arg['action']);
        if (isset($start_arg['target']))        $form->target($start_arg['target']);

        if (isset($finish_arg['before']))       $form->js_before($finish_arg['before']);
        if (isset($finish_arg['after']))        $form->js_after($finish_arg['after']);


        /* create associative array of form elements and begin and end tags of form */
        $f = array();

        /* add begin tag to assoc array */
        $f['start'] =  $form->start();
        /* add end tag to assoc array */
        $f['finish'] = $form->finish();

        /* add elements of form to assoc array */
        foreach($form->get_element_names() as $name){
            $el = $form->get_element_obj($name);

            /* if element is radio we must add form elements for each radio button */
            if ($el->get_type() == 'radio'){
                $radio_values = $el->get_radio_values();
                foreach($radio_values as $val){
                    $f[$name.'_'.$val] = $form->get_element($name, $val);
                }
            }
            /* if element is submit and it is not image, we have to strip the '_x' from end of its name */
            elseif ($el->get_type() == 'submit'){
                if (!$el->is_submit_image()) $f_nm = substr($name, 0, -2);
                else                         $f_nm = $name;

                $f[$f_nm] = $form->get_element($name);
            }
            else {
                /* for all others elements simply add them to $f array */
                $f[$name] = $form->get_element($name);
            }
        }

        $this->assign($form_name, $f);
        $this->assign($form_name."obj", $form);

        $controler->trigger_event("post_form_smarty");
    }
}
