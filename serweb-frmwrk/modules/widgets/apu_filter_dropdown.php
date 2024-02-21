<?php
/**
 * Application unit filter - dropdown
 *
 *
 * @author    Karel Kozlik
 * @version   $Id: apu_filter.php,v 1.1 2006/07/20 16:06:15 kozlik Exp $
 * @package   serweb
 */

/**
 *  Application unit filter - dropdown
 *
 *
 *  This application unit is used for display filter form
 *
 *  Configuration:
 *  --------------
 *
 *  'filter_items'              (array)  default: none
 *  if is set, the list of fields which could be used as filter criteria is
 *  limited to specified fields
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

class apu_filter_dropdown extends apu_base_class{
    var $form_elements;
    var $get_params = array();
    var $filter_applied = false;
    protected $base_apu = null;


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
    public function __construct(){
        global $lang_str;
        parent::apu_base_class();

        /* set default values to $this->opt */
        $this->opt['filter_name'] =         '';

        $this->opt['on_change_callback'] =          '';
        $this->opt['filter_items'] = array();
        $this->opt['exclude_filter_items'] = array();


        /*** names of variables assigned to smarty ***/
        /* form */
        $this->opt['smarty_form'] =         'filter_form';
        /* name of html form */
        $this->opt['form_name'] =           'filter_form';
        $this->opt['smarty_filter_applied'] =   'filter_applied';

        $this->opt['form_submit']=array('type' => 'button',
                                        'text' => $lang_str['b_ok']);
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

        $session_name = empty($this->opt['filter_name'])?
                        $this->opt['instance_id']:
                        $this->opt['filter_name'];

        if (!isset($_SESSION['apu_filter_dropdown'][$session_name])){
            $_SESSION['apu_filter_dropdown'][$session_name] = array();
        }

        $this->session = &$_SESSION['apu_filter_dropdown'][$session_name];

        $clean_filter = true;
        if (isset($_GET['refresh_updated'])) $clean_filter = false;
        if (isset($_POST['refresh_updated'])) $clean_filter = false;
        if (isset($_GET['filter_updated'])) $clean_filter = false;
        if (isset($_POST['filter_updated'])) $clean_filter = false;
        if (isset($_GET['sorter_updated'])) $clean_filter = false;
        if (isset($_GET['act_row']))        $clean_filter = false;

        foreach($_GET as $k => $v){
            if (substr($k, 0, 7) == "u_sort_") {
                $clean_filter = false;
                break;
            }
        }


        if ($clean_filter) $this->session = array();


        if (!isset($this->session['f_field']))  $this->session['f_field'] = null;
        if (!isset($this->session['f_val']))    $this->session['f_val'] = null;
        if (!isset($this->session['f_op']))     $this->session['f_op'] = "=";


        if (!isset($this->session['act_row'])){
            $this->session['act_row'] = 0;
        }

        if (isset($_GET['act_row'])){
            $this->session['act_row'] = $_GET['act_row'];
        }

    }

    /**
     *  Method perform action update
     *
     *  @return array           return array of $_GET params fo redirect or FALSE on failure
     */

    function action_update(){

        if (!isset($_POST['filter_val']))   $_POST['filter_val'] = null;
        if (!isset($_POST['filter_op']))    $_POST['filter_op'] = null;

        $this->session['f_field']   = $_POST['filter_field'];
        $this->session['f_val']     = $_POST['filter_val'];
        $this->session['f_op']      = $_POST['filter_op'];


        $this->session['act_row'] = 0;

        if (!empty($this->opt['on_change_callback'])){
            call_user_func($this->opt['on_change_callback']);
        }

        if (isset($this->base_apu->opt['screen_name'])){
            if (!$this->session['f_field']) $msg = "None";
            else{
                $label = "";
                foreach ($this->form_elements as $v){
                    if ($v['name'] == $this->session['f_field']) {
                        $label = isset($v['label']) ? $v['label'] : $v['name'];
                        break;
                    }
        		}
        		if (!$label) $label = $this->session['f_field'];

                $msg = $label." ".$this->session['f_op']." '".$this->session['f_val']."'";
            }

            action_log($this->base_apu->opt['screen_name'], "filter", "new filter criteria:".$msg);
        }

        if (!empty($this->session['get_param'])) {
            return (array)$this->session['get_param'];
        }


        $get = $this->get_params;
        $get[] = 'filter_updated='.RawURLEncode($this->opt['instance_id']);
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
        else $this->action=array('action'=>"default",
                                 'validate_form'=>false,
                                 'reload'=>false);
    }

    function post_determine_action(){
        if (isset($_GET['act_row'])){ //if pagination have been done, log action
            if (isset($this->base_apu->opt['screen_name'])){
                action_log($this->base_apu->opt['screen_name'], "paging", "Viewing [First|Prev|Next|Last] with offset of [".$_GET['act_row']."]");
            }
        }
    }

    /**
     *  create html form
     *
     *  @return null            FALSE on failure
     */
    function create_html_form(){
        parent::create_html_form();

        global $lang_str;

        $this->form_elements = $this->base_apu->get_filter_form();

        /* if option 'filter_items' is set,
           limit list of form elements to only items specified by this option */
        if ($this->opt['filter_items']){
            foreach ($this->form_elements as $k=>$v){
                if (!in_array($this->form_elements[$k]['name'], $this->opt['filter_items']))
                    unset($this->form_elements[$k]);
            }
        }

        /* if option 'exclude_filter_items' is set,
           remove items specified by this option from the list of form elements */
        if ($this->opt['exclude_filter_items']){
            foreach ($this->form_elements as $k=>$v){
                if (in_array($this->form_elements[$k]['name'], $this->opt['exclude_filter_items']))
                    unset($this->form_elements[$k]);
            }
        }

        if ($this->session['f_field']) $this->filter_applied = true;

        $f_options = array(array("value" => "",
                                 "label" => "- None -"));

        foreach ($this->form_elements as $k => $v){
            $f_options[] = array("value" => $v['name'],
                                 "label" => isset($v['label']) ? $v['label'] : $v['name']);
        }

        $op_options = array(
                        array("value"=>"=",       "label"=>"="),
                        array("value"=>"!=",      "label"=>"!="),
                        array("value"=>">",       "label"=>">"),
                        array("value"=>">=",      "label"=>">="),
                        array("value"=>"<",       "label"=>"<"),
                        array("value"=>"<=",      "label"=>"<="),
                        array("value"=>"like",    "label"=>"LIKE"),
                        array("value"=>"is_null", "label"=>"IS NULL")
                    );

        $this->f->add_element(array("type"=>"select",
                                     "name"=>"filter_field",
                                     "value"=>$this->session['f_field'],
                                     "size"=>1,
                                     "options"=>$f_options,
                                     "events" => [[
                                         "event" => "change",
                                         "handler" => "function(){ if (this.selectedIndex==0) {this.form.filter_op.disabled=true;this.form.filter_val.disabled=true; if (typeof(this.form.filter_reset) != \"undefined\") this.form.filter_reset.disabled=true;} else {this.form.filter_op.disabled=false;this.form.filter_val.disabled=false; if (typeof(this.form.filter_reset) != \"undefined\") this.form.filter_reset.disabled=false;}}"
                                     ]]));

        $this->f->add_element(array("type"=>"select",
                                     "name"=>"filter_op",
                                     "value"=>$this->session['f_op'],
                                     "size"=>1,
                                     "options"=>$op_options,
                                     "disabled"=>!(bool)$this->session['f_field']));

        $this->f->add_element(array("type"=>"text",
                                     "name"=>"filter_val",
                                     "value"=>$this->session['f_val'],
                                     "disabled"=>!(bool)$this->session['f_field']));

        $this->f->add_element(array("type"=>"button",
                                     "name"=>"filter_reset",
                                     "button_type"=>"button",
                                     "content"=>$lang_str['b_reset'],
                                     "disabled"=>!(bool)$this->session['f_field'],
                                     "events" => [[
                                         "event" => "click",
                                         "handler" => "function(){ var e = new Event('change'); this.form.filter_field.selectedIndex=0; this.form.filter_field.dispatchEvent(e); this.form.submit(); }"
                                     ]]));

        $this->f->add_element(array("type"=>"hidden",
                                     "name"=>"filter_updated",
                                     "value"=>1));
    }

    /**
     *  assign variables to smarty
     */
    function pass_values_to_html(){
        global $smarty;

        $smarty->assign($this->opt['smarty_filter_applied'], $this->filter_applied);
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

    function get_act_row(){
        return $this->session['act_row'];
    }

    function set_act_row($v){
        $this->session['act_row'] = $v;
    }

    function get_filter_values(){
        $f_values = array();
        if ($this->session['f_field'])
            $f_values[$this->session['f_field']] = $this->session['f_val'];

        return $f_values;
    }

    function get_filter(){
        $f_ops = array();
        if ($this->session['f_field'])
            $f_ops[$this->session['f_field']] = new Filter($this->session['f_field'],
                                                           $this->session['f_val'],
                                                           $this->session['f_op'],
                                                           false);
        return $f_ops;
    }

    function set_get_param_for_redirect($str){
        $this->session['get_param']=$str;
    }

    function is_form_submited(){
        return ($this->action['action'] == "update");
    }

}

