<?php
/**
 * Application unit filter 
 * 
 * @author    Karel Kozlik
 * @version   $Id: apu_filter.php,v 1.6 2008/01/09 15:25:58 kozlik Exp $
 * @package   serweb
 * @subpackage framework
 */ 

/**
 *  Application unit filter 
 *
 *  This application unit is used for display filter form
 *     
 *  <pre>
 *  Configuration:
 *  --------------
 *  
 *  'form_name'                 (string) default: ''
 *   name of html form
 *  
 *  'form_submit'               (assoc)
 *   assotiative array describe submit element of form. For details see description 
 *   of method add_submit in class form_ext
 *  
 *  'smarty_form'               name of smarty variable - see below
 *  
 *  Exported smarty variables:
 *  --------------------------
 *  opt['smarty_form']          (form)          
 *   phplib html form
 *   
 *  </pre>
 *  
 *  @package   serweb
 *  @subpackage framework
 */

class apu_filter extends apu_base_class{
    var $form_elements;
    var $labels = array();
    var $get_params = array();
    var $filter_applied = false;
    

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
        return array("module:widgets:filter.js");
    }
    
    /**
     *  constructor 
     *  
     *  initialize internal variables
     */
    function apu_filter(){
        global $lang_str;
        parent::apu_base_class();

        /* set default values to $this->opt */      
        $this->opt['filter_name'] =         '';

        $this->opt['on_change_callback'] =          '';

        /* match any value containing the filter value */
        $this->opt['partial_match'] =           true;
        
        /*** names of variables assigned to smarty ***/
        /* form */
        $this->opt['smarty_form'] =         'filter_form';
        /* name of html form */
        $this->opt['form_name'] =           'filter_form';

        $this->opt['smarty_form_label'] =   'filter_label';
        $this->opt['smarty_filter_applied'] =   'filter_applied';
        $this->opt['smarty_filter_values'] =   'filter_values';


        $this->opt['form_submit']=array('type' => 'button',
                                        'text' => $lang_str['b_search']);
        
        $this->opt['form_clear']=array('type' => 'button',
                                        'text' => $lang_str['b_clear_filter']);
        
        
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

        /* set form name if it is not set */
        if (!$this->opt['form_name']) 
            $this->opt['form_name'] = "form_".$this->opt['instance_id'];

        $session_name = empty($this->opt['filter_name'])?
                        $this->opt['instance_id']:
                        $this->opt['filter_name'];

        if (!isset($_SESSION['apu_filter'][$session_name])){
            $_SESSION['apu_filter'][$session_name] = array();
        }
        
        $this->session = &$_SESSION['apu_filter'][$session_name];

        if (!isset($this->session['f_values'])){
            $this->session['f_values'] = array();
        }

        if (!isset($this->session['act_row'])){
            $this->session['act_row'] = 0;
        }
        
        if (isset($_GET['act_row'])){
            $this->session['act_row'] = $_GET['act_row'];
        }

    }

    function get_form_elements(){
        if (!isset($this->form_elements)){
            $this->form_elements = $this->base_apu->get_filter_form();
        }
    }
    
    /**
     *  Method perform action update
     *
     *  @return array           return array of $_GET params fo redirect or FALSE on failure
     */

    function action_update(){
        foreach ($this->form_elements as $k=>$v){
            if ($v['type'] == "checkbox"){
                $this->session['f_values'][$v['name']] = !empty($_POST[$v['name']."_hidden"]);
                if (!empty($v['3state'])){
                    $this->session['f_spec'][$v['name']]['enabled'] = !empty($_POST[$v['name']."_en"]);
                }
            }
            else{
                if (isset($_POST[$v['name']])){
                    $this->session['f_values'][$v['name']] = $_POST[$v['name']];
                }
                if (!empty($v['multiple']) and !isset($_POST[$v['name']])){
                    $this->session['f_values'][$v['name']] = array();
                }
            }
        }

        $this->session['act_row'] = 0;

        if (!empty($this->opt['on_change_callback'])){
            call_user_func($this->opt['on_change_callback']);
        }

        if (!empty($this->session['get_param'])) {
            return (array)$this->session['get_param'];
        }

        return $this->get_params;
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
        
        $this->get_form_elements();

        $js_elements = array();
        
        foreach ($this->form_elements as $k => $v){
            
            if (!isset($this->session['f_values'][$v['name']])){
                if (isset($v['initial'])) $this->session['f_values'][$v['name']] = $v['initial'];
                else                      $this->session['f_values'][$v['name']] = null;
            }

            /* pre set the value */
            if ($v['type'] == "checkbox"){
                $v['checked'] = $this->session['f_values'][$v['name']];
                if (empty($v['value'])) $v['value'] = 1;    //if value is not set
            }
            else{           
                $v['value'] = $this->session['f_values'][$v['name']];
            }

            $js_el = array();
            $js_el["name"] = $v['name'];
            $js_el["type"] = $v['type'];

            /* do specific actions for each type */
            switch ($v['type']){
            case "text":
                if (!isset($v['maxlength'])){
                    $v['maxlength'] = 32;
                }
                if ($v['value']) $this->filter_applied = true;
                break;
            case "checkbox":
                /* add the hidden element in order to it not depend 
                   if checkbox is displayed by the template or not */
                $this->f->add_element(array(
                        "name" => $v['name']."_hidden",
                        "type" => "hidden" ,
                        "value" => $v['checked'] ? 1 : 0
                    ));

                $onclick = "if (this.checked) this.form.".$v['name']."_hidden".".value=1; else this.form.".$v['name']."_hidden".".value=0;";
                
                if (!empty($v['3state'])){
                    $js_el["three_state"] = true;

                    $v['disabled'] = empty($this->session['f_spec'][$v['name']]['enabled']);
                
                    /* add the chcekbox element enabling or disabling the first one */
                    $this->f->add_element(array(
                            "name" => $v['name']."_en",
                            "type" => "checkbox",
                            "value" => 1,
                            "checked" => !$v['disabled'],
                            "extrahtml" => "title='enable filtering by this flag' onclick='if (this.checked) this.form.".$v['name'].".disabled=false; else this.form.".$v['name'].".disabled=true;'"
                        ));

//                  $onchange .= "if (this.checked) this.form.".$v['name'].".disable=false; else this.form.".$v['name'].".disable=true;";

                    if (!$v['disabled']) $this->filter_applied = true;
                }
                else{
                    if ($v['checked']) $this->filter_applied = true;
                }

                if (empty($v['extrahtml'])) $v['extrahtml'] = "";
                $v['extrahtml'] .= " onclick='".$onclick."'";
                
                break;
            }


            $this->f->add_element($v);
            $this->form_elements[$k] = $v;

            if (isset($v['label'])) $this->labels[$v['name']] = $v['label'];
            
            $js_elements[] = $js_el;
        }
        
        $this->opt['form_clear']['extra_html'] = "onclick='filter_form_ctl.filter_clear();'";
        $this->f->add_extra_submit("f_clear", $this->opt['form_clear']);

        $onload_js = "
            filter_form_ctl = new Filter_Form('".$this->opt['form_name']."', ".my_JSON_encode($js_elements).");
        ";
        $this->controler->set_onload_js($onload_js);
    }

        
    /**
     *  assign variables to smarty 
     */
    function pass_values_to_html(){
        global $smarty;
        $smarty->assign($this->opt['smarty_form_label'], $this->labels);

        $smarty->assign($this->opt['smarty_filter_applied'], $this->filter_applied);
        $smarty->assign($this->opt['smarty_filter_values'], $this->session['f_values']);
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
    
    /**
     *  @deprec
     */
    function get_filter_values(){
        return $this->session['f_values'];
    }

    function get_filter(){
        $this->get_form_elements();

        /* shortcut to form_elemetns */
        $fv = &$this->session['f_values'];

        $f_ops = array();

        foreach($this->form_elements as $v){
            /* skip unset values */
            if (!isset($fv[$v['name']])) continue;

            /* do not add disabled chcekboxes to filter */
            if ($v['type'] == 'checkbox' and
                !empty($v['3state']) and
                empty($this->session['f_spec'][$v['name']]['enabled'])) continue;

            if (!empty($v['multiple'])){
                if (!count($fv[$v['name']])) continue;
                $op = "in";
            }
            else{
                /* do not include empty values to filter*/
                if ($fv[$v['name']] === "") continue;
                $op = "like";
            }

            $f_ops[$v['name']] = new Filter($v['name'], $fv[$v['name']], $op, $this->opt['partial_match']);  
        }


        return $f_ops;
    }
    
    function set_get_param_for_redirect($str){
        $this->session['get_param']=$str;
    }

    function is_form_submited(){
        return ($this->action['action'] == "update");
    }
    
}


?>
