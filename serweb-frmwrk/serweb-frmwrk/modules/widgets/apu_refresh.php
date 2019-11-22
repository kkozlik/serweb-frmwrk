<?php
/**
 * Application unit refresh
 *
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   serweb
 */

/**
 *  Application unit refresh
 *
 *
 *  This application unit is used for display refresh form and refresh page
 *  after timeout exceeded
 *
 *  Configuration:
 *  --------------
 *
 *  'form_name'                 (string) default: ''
 *   name of html form
 *
 *  'form_submit'               (assoc)
 *   assotiative array describe submit element of form. For details see description
 *   of method add_submit in class OohForm
 *
 *  'smarty_form'               name of smarty variable - see below
 *
 *  Exported smarty variables:
 *  --------------------------
 *  opt['smarty_form']          (form)
 *   phplib html form
 *
 *
 */

class apu_refresh extends apu_base_class{
    var $form_elements;
    var $get_params = array();
    var $base_apu = null;


    /**
     *  return required data layer methods - static class
     *
     *  @return array   array of required data layer methods
     */
    function get_required_data_layer_methods(){
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
    function apu_refresh(){
        global $lang_str;
        parent::apu_base_class();

        /* set default values to $this->opt */
        $this->opt['refresh_name'] =         '';

        $this->opt['on_change_callback'] =          '';
        $this->opt['timeouts'] = array('15'  => '15',
                                       '30'  => '30',
                                       'off' => 'Off');
        $this->opt['default_timeout'] = 'off';
        $this->opt['reset_to_default'] = true;


        /*** names of variables assigned to smarty ***/
        /* form */
        $this->opt['smarty_form'] =         'form';
        /* name of html form */
        $this->opt['form_name'] =           '';

        $this->opt['form_submit']=array('type' => 'button',
                                        'text' => $lang_str['b_ok']);


        $this->opt['smarty_url_refresh_arr'] =  "refresh_urls";
        $this->opt['smarty_refresh_timeout'] =  "refresh_timeout";

    }

    function set_base_apu(&$apu){
        $this->base_apu = &$apu;
    }

    function set_get_params($get_params){
        $this->get_params = array_merge($this->get_params, $get_params);
    }

    /**
     *  this metod is called always at begining - initialize variables
     */
    function init(){
        parent::init();

        $session_name = empty($this->opt['refresh_name'])?
                        $this->opt['instance_id']:
                        $this->opt['refresh_name'];

        if (!isset($_SESSION['apu_refresh'][$session_name])){
            $_SESSION['apu_refresh'][$session_name] = array();
        }

        $this->session = &$_SESSION['apu_refresh'][$session_name];

        $clean_refresh = $this->opt['reset_to_default'];
        if (isset($_GET['refresh_updated'])) $clean_refresh = false;
        if (isset($_POST['refresh_updated'])) $clean_refresh = false;
        if (isset($_GET['filter_updated'])) $clean_refresh = false;
        if (isset($_POST['filter_updated'])) $clean_refresh = false;
        if (isset($_GET['sorter_updated'])) $clean_refresh = false;
        if (isset($_GET['act_row']))        $clean_refresh = false;

        foreach($_GET as $k => $v){
            if (substr($k, 0, 7) == "u_sort_") {
                $clean_refresh = false;
                break;
            }
        }

        if ($clean_refresh) $this->session = array();

        if (!isset($this->session['timeout']))  $this->session['timeout'] = $this->opt['default_timeout'];
    }

    /**
     *  Method perform action update
     *
     *  @return array           return array of $_GET params fo redirect or FALSE on failure
     */

    function action_update(){

        if (isset($_GET['refresh_timeout'])){
            $this->session['timeout']   = $_GET['refresh_timeout'];
        }
        else{
            $this->session['timeout']   = $_POST['refresh_timeout'];
        }

        if (!empty($this->opt['on_change_callback'])){
            call_user_func($this->opt['on_change_callback']);
        }

        if (isset($this->base_apu->opt['screen_name'])){
            action_log($this->base_apu->opt['screen_name'], "refresh", "refresh changed to: ".$this->opt['timeouts'][$this->session['timeout']]);
        }

        if (!empty($this->session['get_param'])) {
            return (array)$this->session['get_param'];
        }


        $get = $this->get_params;
        $get[] = 'refresh_updated='.RawURLEncode($this->opt['instance_id']);
        return $get;
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
        elseif (isset($_GET['refresh_updated']) and isset($_GET['refresh_timeout'])){
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

        $f_options = $this->f->array_to_opt($this->opt['timeouts']);

        $this->f->add_element(array("type"=>"select",
                                     "name"=>"refresh_timeout",
                                     "value"=>$this->session['timeout'],
                                     "size"=>1,
                                     "options"=>$f_options,
                                     "extra_html"=>"onchange='this.form.submit();'"));

        $this->f->add_element(array("type"=>"hidden",
                                     "name"=>"refresh_updated",
                                     "value"=>1));

        if (is_numeric($this->session['timeout']) and (int)$this->session['timeout'] > 0){
            $onload_js = "
                setTimeout('location.reload(true);', ".(1000*(int)$this->session['timeout']).");
            ";
            $this->controler->set_onload_js($onload_js);
        }
    }

    /**
     *  assign variables to smarty
     */
    function pass_values_to_html(){
        global $smarty;

        $refresh_urls = array();
        foreach($this->opt['timeouts'] as $k=>$v){
            $refresh_urls[] = array(
                "url" => $this->controler->url($_SERVER['PHP_SELF']."?refresh_updated=1&refresh_timeout=".RawURLEncode($k)),
                "label" => $v,
                "timeout" => $k);
        }

        $smarty->assign($this->opt['smarty_url_refresh_arr'], $refresh_urls);
        $smarty->assign($this->opt['smarty_refresh_timeout'], $this->session['timeout']);

    }

    /**
     *  return info need to assign html form to smarty
     */
    function pass_form_to_html(){
        return array('smarty_name' => $this->opt['smarty_form'],
                     'form_name'   => $this->opt['form_name'],
                     'after'       => '',
                     'before'      => '',
                     'get_param'   => $this->get_params);
    }
}
