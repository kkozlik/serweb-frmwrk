<?php
/**
 * Application unit hello world
 *
 * @author    Karel Kozlik
 * @version   $Id: application_layer_cz,v 1.10 2007/09/17 18:56:31 kozlik Exp $
 * @package   serweb
 */


/**
 *  Application unit hello world
 *
 *
 *  This application unit is used for display and edit LB Proxies
 *
 *  Configuration:
 *  --------------
 *
 *  'msg_update'                    default: $lang_str['msg_changes_saved_s'] and $lang_str['msg_changes_saved_l']
 *   message which should be showed on attributes update - assoc array with keys 'short' and 'long'
 *
 *  'form_name'                     (string) default: ''
 *   name of html form
 *
 *  'form_submit'               (assoc)
 *   assotiative array describe submit element of form. For details see description
 *   of method add_submit in class form_ext
 *
 *  'smarty_form'               name of smarty variable - see below
 *  'smarty_action'                 name of smarty variable - see below
 *
 *  Exported smarty variables:
 *  --------------------------
 *  opt['smarty_form']              (form)
 *   phplib html form
 *
 *  opt['smarty_action']            (action)
 *    tells what should smarty display. Values:
 *    'default' -
 *    'was_updated' - when user submited form and data was succefully stored
 *
 */

class apu_hello_world extends apu_base_class{

    var $js_before = "";
    var $js_after = "";

    /**
     *  return required data layer methods - static class
     *
     *  @return array   array of required data layer methods
     */
    public static function get_required_data_layer_methods(){
        return array();
    }

    /**
     *  return array of strings - required javascript files
     *
     *  @return array   array of required javascript files
     */
    function get_required_javascript(){
        return array();
    }

    /**
     *  constructor
     *
     *  initialize internal variables
     */
    function apu_hello_world(){
        global $lang_str;
        parent::apu_base_class();


        $this->opt['screen_name'] = "Hello world";


        /* message on attributes update */
        $this->opt['msg_update']['long']  =     &$lang_str['msg_changes_saved'];

        /*** names of variables assigned to smarty ***/
        /* form */
        $this->opt['smarty_form'] =         'form';
        /* smarty action */
        $this->opt['smarty_action'] =       'action';
        /* name of html form */
        $this->opt['form_name'] =           '';
        $this->opt['smarty_name'] =         'name';
    }

    /**
     *  this metod is called always at begining - initialize variables
     */
    function init(){
        parent::init();

        if (!isset($_SESSION['apu_hello_world'][$this->opt['instance_id']])){
            $_SESSION['apu_hello_world'][$this->opt['instance_id']] = array();
        }

        $this->session = &$_SESSION['apu_hello_world'][$this->opt['instance_id']];

        if (!isset($this->session['smarty_action'])){
            $this->session['smarty_action'] = 'default';
        }
        if (!isset($this->session['name'])){
            $this->session['name'] = '';
        }
    }



    /**
     *  Method perform action update
     *
     *  @return array           return array of $_GET params fo redirect or FALSE on failure
     */

    function action_update(){

        $this->session['name'] = $_POST['hello_world_name'];

        action_log($this->opt['screen_name'], $this->action, "update name to: ".$this->session['name']);

        $get = array('hello_world_updated='.RawURLEncode($this->opt['instance_id']));
        return $get;
    }


    /**
     *  Method perform action default
     *
     *  @return array           return array of $_GET params fo redirect or FALSE on failure
     */

    function action_default(){
        $this->session['smarty_action'] = 'default';

        action_log($this->opt['screen_name'], $this->action, "View hello world screen");
        return true;
    }

    /**
     *  check _get and _post arrays and determine what we will do
     */
    function determine_action(){
        if ($this->was_form_submited()){    // Is there data to process?
            $this->action=array('action'=>"update",
                                'validate_form'=>true,
                                'reload'=>true);
        }
        else $this->action=array('action'=>"default",
                                 'validate_form'=>false,
                                 'reload'=>false);
    }

    /**
     *  create html form
     *
     *  @return null            FALSE on failure
     */
    function create_html_form(){
        parent::create_html_form();

        $this->f->add_element(array("type"=>"text",
                                     "name"=>"hello_world_name",
                                     "value"=>$this->session['name'],
                                     "js_trim_value" => true,
                                     "js_validate" => false,
                                     "maxlength"=>64));
    }

    function form_invalid(){
        if ($this->action['action'] == "update"){
            action_log($this->opt['screen_name'], $this->action, "Update action failed", false, array("errors"=>$this->controler->errors));
        }
    }

    /**
     *  validate html form
     *
     *  @return bool            TRUE if given values of form are OK, FALSE otherwise
     */
    function validate_form(){
        $form_ok = true;
        if (false === parent::validate_form()) $form_ok = false;

        if (empty($_POST['hello_world_name'])){
            ErrorHandler::add_error("The name can't be empty");
            $form_ok = false;
        }

        return $form_ok;
    }


    /**
     *  add messages to given array
     *
     *  @param array $msgs  array of messages
     */
    function return_messages(&$msgs){
        if (isset($_GET['hello_world_updated']) and $_GET['hello_world_updated'] == $this->opt['instance_id']){
            $msgs[]=&$this->opt['msg_update'];
        }
    }

    /**
     *  assign variables to smarty
     */
    function pass_values_to_html(){
        global $smarty;

        $smarty->assign($this->opt['smarty_action'], $this->session['smarty_action']);
        $smarty->assign($this->opt['smarty_name'], $this->session['name']);
    }

    /**
     *  return info need to assign html form to smarty
     */
    function pass_form_to_html(){
        return array('smarty_name' => $this->opt['smarty_form'],
                     'form_name'   => $this->opt['form_name'],
                     'after'       => $this->js_after,
                     'before'      => $this->js_before);
    }
}


?>
