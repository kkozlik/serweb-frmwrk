<?php
/**
 * The main parent of all application units
 *
 * @author    Karel Kozlik
 * @version   $Id: apu_base_class.php,v 1.13 2007/05/11 07:46:54 kozlik Exp $
 * @package   serweb
 * @subpackage framework
 */

/**
 *  The main parent of all application units
 *
 *  <pre>
 *  Configuration:
 *  --------------
 *  instance_id         unique identificator of instance of application unit
 *  form_submit         assotiative array describe submit element of form.
 *                      for details see description of method add_submit in class OohForm
 *  </pre>
 *
 *  @package   serweb
 *  @subpackage framework
 */

class apu_base_class{
    /** associative array of application unit options */
    var $opt=array();

    var $action;
    /** unified number of instance of this class */
    var $instance;
    /** auth info of user with which setting we are workig. Usualy is same as $_SESSION['auth']->get_logged_user(), only admin can change it */
    var $user_id;
    /** html form */
    var $f;
    /** name of html form when multiple shared html forms is used, variable is set by controler */
    var $form_name = null;
    /** reference to page_controller */
    var $controler;
    /** custom function called after determine action */
    var $custom_post_determine_action;
    /** javascript executed before HTML for is validated */
    var $js_before = "";
    /** javascript executed after HTML for is validated */
    var $js_after  = "";

    protected $session;

    /* constructor */
    function apu_base_class(){
        global $lang_str;
        $this->action="";
        /* set instance id for identification this object when multiple instances is used */
        $this->opt['instance_id']=get_class($this).apu_base_class::get_Instance();

        /* form */
        $this->opt['smarty_form'] =         'form';
        /* name of html form */
        $this->opt['form_name'] =           'data_form';

        $this->opt['form_submit']=array('type' => 'button',
                                        'text' => $lang_str['b_submit']);

        $this->opt['form_cancel']=array('type' => 'button',
                                        'text' => $lang_str['b_cancel']);
    }

    /* static method - generate instance number */
    function get_Instance(){
        static $instance_counter = 0;
        $instance_counter++;
        return $instance_counter;
    }

    /* return instance ID */
    function get_instance_id(){
        return $this->opt['instance_id'];
    }

    /* return required data layer methods - static class */
    function get_required_data_layer_methods(){
        return array();
    }

    /* return array of strings - required javascript files */
    function get_required_javascript(){
        return array();
    }

    /* set option $opt_name to value $val */
    function set_opt($opt_name, $val){
        $this->opt[$opt_name]=$val;
    }

    function set_custom_post_determine_action($fn){
        $this->custom_post_determine_action = $fn;
    }

    /* this metod is called always at begining */
    function init(){
        /* if html form is common for more APUs, reference this->f to common form */
        if ($this->controler->shared_html_form){
            /* if html form was not assignet to this APU, assign default */
            if (is_null($this->form_name)){
                sw_log("Html form was not assigned to APU ".$this->opt['instance_id'].".  Useing default.", PEAR_LOG_DEBUG);
                $this->controler->assign_form_name('default', $this);
            }

            $this->f = &$this->controler->f[$this->form_name]['form'];
        }
        /* else create own form object */
        else{
            $this->f = new OohForm();
        }

        if (PHPlib::$session) PHPlib::$session->register_and_call_init_fn([$this, "session_init"]);
    }

    public function session_init(){
        $classname = get_class($this);
        if (!isset($_SESSION[$classname][$this->opt['instance_id']])){
            $_SESSION[$classname][$this->opt['instance_id']] = array();
        }

        $this->session = &$_SESSION[$classname][$this->opt['instance_id']];
    }

    function action_default(){
        return true;
    }

    /* check _get and _post arrays and determine what we will do */
    function determine_action(){
        $this->action=array('action'=>"default",
                            'validate_form'=>false,
                            'reload'=>false);
    }

    /* check _get and _post arrays and determine what we will do */
    function post_determine_action(){
        if (!is_null($this->custom_post_determine_action)){
            call_user_func_array($this->custom_post_determine_action, array(&$this));
        }
    }

    /* create html form */
    function create_html_form(){
        /* if html form is shared by more APUs, add insatance_id to controler->form_apu_names array */
        if ($this->controler->shared_html_form)
            $this->controler->f[$this->form_name]['apu_names'][] = $this->opt['instance_id'];
        else{
        /* otherways form isn't shared - add hidden element to it */
            $this->f->add_element(array("type"=>"hidden",
                                         "name"=>"apu_name",
                                         "value"=>$this->opt['instance_id']));
        /* and also add submit and cancel element */
            $this->f->add_submit($this->opt['form_submit']);
            $this->f->add_cancel($this->opt['form_cancel']);
        }
    }

    /* validate html form */
    function validate_form(){
        /* if html form isn't shared validate it, otherwise it do controler */
        if (!$this->controler->shared_html_form){
            if ($err = $this->f->validate()) {          // Is the data valid?
                ErrorHandler::add_error($err);          // No!
                return false;
            }
        }
        return true;
    }

    /* callback function when some html form is invalid */
    function form_invalid(){
    }

    /* check if form of this APU was submited */
    function was_form_submited(){

        /* check if is set $_POST['apu_name'] anf if it contains
           instance_id of this APU
         */

        if (isset($_POST['apu_name']) and
            (is_array($_POST['apu_name'])?
                in_array($this->opt['instance_id'], $_POST['apu_name']):
                $_POST['apu_name']==$this->opt['instance_id'])){

                    /* check if form has been submited with cancel button */
                    if (isset($_POST['form_cancels'])){
                        /* get list of cancel buttons */
                        $cancels = explode(" ", $_POST['form_cancels']);
                        /* check all cancel buttons */
                        foreach($cancels as $v){
                            if (!empty($_POST[$v."_x"])) return false;
                        }
                    }

                    return true;
        }
        else return false;

    }

    /* check if form of this APU was submited with cancel button */
    function was_form_canceled(){

        /* check if is set $_POST['apu_name'] anf if it contains
           instance_id of this APU
         */

        if (isset($_POST['apu_name']) and
            (is_array($_POST['apu_name'])?
                in_array($this->opt['instance_id'], $_POST['apu_name']):
                $_POST['apu_name']==$this->opt['instance_id'])){

                    /* check if form has been submited with cancel button */
                    if (isset($_POST['form_cancels'])){
                        /* get list of cancel buttons */
                        $cancels = explode(" ", $_POST['form_cancels']);
                        /* check all cancel buttons */
                        foreach($cancels as $v){
                            if (!empty($_POST[$v."_x"])) return true;
                        }
                    }

                    return false;
        }
        else return false;

    }

    /* add messages to given array */
    function return_messages(&$msgs){
    }

    /* assign variables to smarty */
    function pass_values_to_html(){
    }

    /* return info need to assign html form to smarty */
    function pass_form_to_html(){
        return array('smarty_name' => $this->opt['smarty_form'],
                     'form_name'   => $this->opt['form_name'],
                     'after'       => $this->js_before,
                     'before'      => $this->js_after);
    }
}
