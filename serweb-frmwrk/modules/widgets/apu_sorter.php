<?php
/**
 * Application unit sorter
 *
 * @author    Karel Kozlik
 * @version   $Id: apu_sorter.php,v 1.6 2008/01/09 15:25:59 kozlik Exp $
 * @package   serweb
 * @subpackage framework
 */

/**
 *  Application unit sorter
 *
 *  This application unit is used for display filter form
 *
 *  <pre>
 *  Configuration:
 *  --------------
 *
 *  'default_sort_col'          (string) default: none
 *   Name of column, the result is initialy sorted by. If is not specified,
 *   the first column from column list is used.
 *
 *  'desc_order_by_default'     (bool) default: false
 *   If true, the result is initialy sorted in descending order
 *
 *
 *  Exported smarty variables:
 *  --------------------------
 *  opt['smarty_vars']          (url_sort)
 *  opt['smarty_order']         (sorter_order_by)
 *  opt['smarty_dir']           (sorter_dir)
 *
 *  </pre>
 *
 *  @package   serweb
 *  @subpackage framework
 */

class apu_sorter extends apu_base_class{
    var $form_elements;
    var $col_to_sort = null;
    var $get_params = array();
    protected $base_apu = null;
    protected $sort_columns;


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
    function apu_sorter(){
        parent::apu_base_class();

        /* set default values to $this->opt */
        $this->opt['sorter_name'] =         '';

        $this->opt['default_sort_col'] =    '';
        $this->opt['desc_order_by_default'] =   false;

        $this->opt['on_change_callback'] =          '';


        /*** names of variables assigned to smarty ***/
        $this->opt['smarty_vars'] =         'url_sort';
        $this->opt['smarty_order'] =        'sorter_order_by';
        $this->opt['smarty_dir'] =          'sorter_dir';


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

        $session_name = empty($this->opt['sorter_name'])?
                        md5($_SERVER["PHP_SELF"]):
                        $this->opt['sorter_name'];

        if (!isset($_SESSION['apu_sorter'][$session_name])){
            $_SESSION['apu_sorter'][$session_name] = array();
        }

        $this->session = &$_SESSION['apu_sorter'][$session_name];

        if (!isset($this->session['reverse_order'])){
            $this->session['reverse_order'] = $this->opt['desc_order_by_default'];
        }
    }

    private function init_session_sort_col(){
        if ($this->opt['default_sort_col']){
            $this->session['sort_col'] = $this->opt['default_sort_col'];
        }
        else{
            $this->sort_columns = $this->base_apu->get_sorter_columns();
            $this->session['sort_col'] = reset($this->sort_columns);
        }
    }

    /**
     *  Method perform action update
     *
     *  @return array           return array of $_GET params fo redirect or FALSE on failure
     */

    function action_update(){

        if ($this->session['sort_col'] == $this->col_to_sort){
            $this->session['reverse_order'] = !$this->session['reverse_order'];
        }
        else{
            $this->session['sort_col'] = $this->col_to_sort;
            $this->session['reverse_order'] = false;
        }

        if (!empty($this->opt['on_change_callback'])){
            call_user_func($this->opt['on_change_callback']);
        }

        if (isset($this->base_apu->opt['screen_name'])){
            $msg = "Sorting order changed to sort entries by '".$this->col_to_sort."'";
            if ($this->session['reverse_order']) $msg .= " in reverse order";

            action_log($this->base_apu->opt['screen_name'], $this->action, $msg);
        }

        if (!empty($this->session['get_param'])) {
            return (array)$this->session['get_param'];
        }

        $get = array('sorter_updated='.RawURLEncode($this->opt['instance_id']));
        $get = array_merge($get, $this->get_params);
        return $get;
    }

    /**
     *  check _get and _post arrays and determine what we will do
     */
    function determine_action(){

        $this->sort_columns = $this->base_apu->get_sorter_columns();
        if (!isset($this->session['sort_col'])) $this->init_session_sort_col();

        foreach($this->sort_columns as $v){
            if (isset($_GET['u_sort_'.$v])){
                $this->col_to_sort = $v;
                $this->action=array('action'=>"update",
                                    'validate_form'=>false,
                                    'reload'=>true);
                return;
            }
        }

        $this->action=array('action'=>"default",
                            'validate_form'=>false,
                            'reload'=>false);
    }


    /**
     *  assign variables to smarty
     */
    function pass_values_to_html(){
        global $smarty;

        $sort_urls = array();

        $get_params = implode("&", $this->get_params);
        if ($get_params) $get_params = "&".$get_params;

        foreach($this->sort_columns as $v){
            $sort_urls[$v] = $this->controler->url($_SERVER['PHP_SELF']."?u_sort_".$v."=1".$get_params);
            $smarty->assign($this->opt['smarty_vars']."_".$v, $sort_urls[$v]);
        }

        $smarty->assign($this->opt['smarty_vars'], $sort_urls);
        $smarty->assign($this->opt['smarty_order'], $this->get_sort_col());
        $smarty->assign($this->opt['smarty_dir'], $this->get_sort_dir());
    }

   	function pass_form_to_html(){
        return false;
    }


    function get_sort_col(){
        if (!isset($this->session['sort_col'])) $this->init_session_sort_col();
        return $this->session['sort_col'];
    }

    function set_sort_col($col){
        $this->session['sort_col'] = $col;
    }

    /**
     * return true for descending
     *        false for ascending sorting
     */
    function get_sort_dir(){
        return $this->session['reverse_order'];
    }

    function set_reverse_order($dir){
        $this->session['reverse_order'] = $dir;
    }

    function set_get_param_for_redirect($str){
        $this->session['get_param']=$str;
    }

/*
    function is_form_submited(){
        return ($this->action['action'] == "update");
    }
*/
}
