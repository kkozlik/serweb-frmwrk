<?php

class Growable_Forms{

    /** HTML form */
    var $f;
    /** Callback function creating entries in HTML form for one item */
    var $add_item_to_form_fn;

    /** Name of form element containing list of created IDs */
    var $new_items_form_el =    "df_new_items";
    /** Name of form element containing list of deleted IDs */
    var $del_items_form_el =    "df_del_items";
    /** Name of form element containing list of current IDs */
    var $db_items_form_el =     "df_db_items";
    /** Prefix of form element (it's just a name without ID) containing ordering value */
    var $item_order_form_el;
    /** name attribute of the html form */
    var $form_name;
    /** URL that is accessed via AJAX when new item should be added to the form. 
     *  It should invoke add_item_handler() method */
    var $add_item_url;
    /** URL param containing ID of new item */
    var $add_item_url_id_param = "new_item_id";
    /** URL param containing first row flag */
    var $add_item_url_fr_param = "first_row";

    /** filename of smarty template for one item */
    var $smarty_template;
    /** name of smarty variable containing html form */
    var $smarty_form_param = "form";
    /** name of smarty variable containing ID of item */
    var $smarty_id_param = "item_id";
    /** name of smarty variable containing strings */
    var $smarty_lang_param = "lang_str";
    /** name of smarty variable containing control links */
    var $smarty_links_param = "links";
    /** name of smarty variable containing first row flag */
    var $smarty_fr_param = "first_row";
    /** name of smarty variable containing item as returnet by its 'to_smarty' method */
    var $smarty_item_param = "item";
    /** assoc array containing extra params provided to smarty. Should be set 
     *  before add_item_handler() method is invoked */
    var $smarty_extra_params = array();

    /** max constraint for number of items. Zero means no constraint. */
    var $max_items = 0;
    /** min constraint for number of items */
    var $min_items = 0;
    /** if true, do not allow do add, del or change order of items */
    var $view_only = false;


    /** name of PHP class representing one item */
    var $item_class = "";
    /** name of static function of the class that create one instance */
    var $item_creator = "create";
    /** name of variable of the class that holds ID of item */
    var $item_id_var = "id";
    /** name of variable of the class that holds ordering of the item */
    var $item_order_var = null;
    /** name of method of the class that that converts item for smarty */
    var $item_to_smarty_fn = "to_smarty";
    

    /** name of javascript variable holding the javascript object */
    var $js_ctl = "Growable_Forms";

    /** name of custom javascript function called on js object initialization */
    var $custom_init_js_fn = null;    

    /** */
    var $max_new_id_used = 0;
    
    /** array of item IDs to be created */
    var $ins_ids = array();
    /** array of item IDs to be deleted */
    var $del_ids = array();
    /** array of item IDs existed in DB at the moment the page has been generated */
    var $db_ids = array();
    /** array of already existing items */
    var $items = array();
    /** array containing IDs of items in the DB */
    var $items_ids = array();
    /** array of items beeing updated - its same as $this->items, 
     *  but the items of $this->del_ids are removed */
    var $upd_items = null;
    /** */
    var $smarty_items = array();
    
    /** list of item IDs to be created */
    var $new_item_elements = "";
    

    /**
     *  Constructor
     *  
     *  @param  form_ext    $form                   HTML form used by the invoking APU
     *  @param  string      $form_name              name attribute of the html form
     *  @param  function    $add_item_to_form_fn    callback function creating form 
     *                                              entries for one item
     *  @param  string      $item_clas              name of PHP class representing one item
     *  @param  string      $add_item_url           URL that is accessed via AJAX 
     *                                              when new item should be added 
     *                                              to the form. It should invoke 
     *                                              add_item_handler() method
     *  @param  string      $smarty_template        filename of smarty template 
     *                                              for one item
     */         
    function Growable_Forms(&$form, $form_name, $add_item_to_form_fn, 
                            $item_class, $add_item_url, $smarty_template){

        $this->f =                      &$form;
        $this->form_name =              $form_name; 
        $this->add_item_to_form_fn =    $add_item_to_form_fn;
        $this->item_class =             $item_class;
        $this->add_item_url =           $add_item_url;
        $this->smarty_template =        $smarty_template;
    }

    /**
     *  Init function should be called when all variables are set     
     */    
    function init(){
        $this->get_ins_del_items();
    }

    /**
     *  Activate ordering of items
     *  
     *  @param  string  $item_order_form_el     Prefix of form element (it's just 
     *                                          a name without ID) containing 
     *                                          ordering value
     *  @param  string  $item_order_var         name of variable of the class 
     *                                          that holds ordering of the item
     */
    function activate_ordering($item_order_form_el, $item_order_var){
                               
        $this->item_order_form_el =    $item_order_form_el;
        $this->item_order_var =        $item_order_var;
    }

    /**
     *  Generate ID for next item based on list of new items: $this->new_item_elements
     */
    function get_new_item_id(){
        $ids = explode(";", $this->new_item_elements);

        $new_id = 0;
        foreach($ids as $v){
            if (substr($v, 0, 1)=='x' and (substr($v, 1) >= $new_id)) 
                $new_id = substr($v, 1)+1;
        }

        if ($new_id < $this->max_new_id_used) $new_id = $this->max_new_id_used;
        return $new_id;
    }

    /**
     *  Return javascript code initializing the JS controler
     *  
     *  @param  bool    $controls_init_call     specifies whether call of controls_init() 
     *                                          function should be included. It's useful
     *                                          to set it to FALSE and postpone call of 
     *                                          the method if js_ctl object will 
     *                                          be modified later.    
     */         
    function get_init_js($controls_init_call=true){
        $js = "
            var {$this->js_ctl};
            {$this->js_ctl} = new Growable_Forms_ctl('{$this->js_ctl}', '{$this->new_items_form_el}', 
                                    '{$this->del_items_form_el}');
            {$this->js_ctl}.add_item_url = '".js_escape($this->add_item_url)."';
            {$this->js_ctl}.add_item_url_id_param = '".js_escape($this->add_item_url_id_param)."';
            {$this->js_ctl}.add_item_url_fr_param = '".js_escape($this->add_item_url_fr_param)."';
            {$this->js_ctl}.max_items = {$this->max_items};
            {$this->js_ctl}.min_items = {$this->min_items};
            {$this->js_ctl}.new_item_id = ".$this->get_new_item_id().";            
            {$this->js_ctl}.view = ".($this->view_only?"true":"false").";            
            ";

        if ($this->custom_init_js_fn)
            $js .= "{$this->js_ctl}.custom_init_fn = '".$this->custom_init_js_fn."';\n";

        if ($this->item_order_form_el)
            $js .= "{$this->js_ctl}.ordering_form_el = '".$this->item_order_form_el."';\n";

    
        $js .= "
            {$this->js_ctl}.init('{$this->form_name}');
            ";
            
        if ($controls_init_call) $js .= $this->get_controls_init_js_call();

        return $js;
    }

    /**
     *  Return javascript for call of controls_init() function
     */         
    function get_controls_init_js_call(){

        $js = "
            {$this->js_ctl}.controls_init();
            ";

        return $js;
    }
    
    /**
     *  Return structure that is passed to smarty template
     */         
    function get_smarty_var(){
        return array("add_item_url" => "javascript:".rawurlencode($this->js_ctl.".add_item();"),
                     "items" => $this->smarty_items,
                     "template" => $this->smarty_template);
    }

    /**
     *  Method get IDs of inserted and deleted items.
     *  The IDs are stored in following variables:
     *
     *     - $this->ins_ids
     *     - $this->del_ids
     */
    function get_ins_del_items(){

        if (!isset($_POST[$this->new_items_form_el]) or $_POST[$this->new_items_form_el] == "")
            $ins_ids = array();
        else
            $ins_ids = explode(";", $_POST[$this->new_items_form_el]);

        if (!isset($_POST[$this->del_items_form_el]) or $_POST[$this->del_items_form_el] == "")
            $del_ids = array();
        else
            $del_ids = explode(";", $_POST[$this->del_items_form_el]);
        
        if (!isset($_POST[$this->db_items_form_el]) or $_POST[$this->db_items_form_el] == "")
            $db_ids = array();
        else
            $db_ids = explode(";", $_POST[$this->db_items_form_el]);
        
        /* walk throught new and deleted IDs to be sure we will not 
           generate duplicated IDs*/
        foreach($ins_ids as $v){
            if (substr($v, 0, 1)=='x' and (substr($v, 1) >= $this->max_new_id_used)) 
                $this->max_new_id_used = substr($v, 1)+1;
        }

        foreach($del_ids as $v){
            if (substr($v, 0, 1)=='x' and (substr($v, 1) >= $this->max_new_id_used)) 
                $this->max_new_id_used = substr($v, 1)+1;
        }
        
        //array of IDs that were inserted and imediately deleted (without insert to DB)
        $del_ins_ids = array_intersect($ins_ids, $del_ids);

        $this->ins_ids = array_diff($ins_ids, $del_ins_ids);
        $this->del_ids = array_diff($del_ids, $del_ins_ids);
        $this->db_ids = $db_ids;
    }

    /**
     *  Set list of items
     */         
    function set_items($items){
        $this->items = $items;

        // create list of item IDs present in the DB
        $this->items_ids = array();
        $new_ids = explode(";", $this->new_item_elements);

        foreach ($this->items as $k => $v){
            // skip the items from "new_item_elements"
            if (in_array($v->{$this->item_id_var}, $new_ids)) continue;
            $this->items_ids[] = $v->{$this->item_id_var};
        }
    }


    /**
     *  Duplicate items.
     *         
     *  This method should be called when 'copy' action is invoked. It change 
     *  IDs of items and the list of IDs to new_item_elements list.
     */         
    function duplicate_items(){
        $i=0;
        foreach($this->items as $k => $v){
            if ($i != 0) $this->new_item_elements .= ";";
            $this->items[$k]->id = "x".$i;
            $this->new_item_elements .= "x".$i;
            $i++;
        }
    }

    /**
     *  Return list of items to be updated.
     *  
     *  From list of currently configured items the function remove items
     *  that has been deleted.                   
     */    
    function get_updated_items(){

        // if method has been already called do not make the list again but 
        // return stored value
        if (!is_null($this->upd_items)) return $this->upd_items;

        // otherwise make the list of updated items
        $items = $this->items;
    
        //remove inserted and deleted items
        foreach($items as $k => $v){
            if (in_array($v->{$this->item_id_var}, $this->del_ids)) unset($items[$k]);
            if (in_array($v->{$this->item_id_var}, $this->ins_ids)) unset($items[$k]);
        }
        
        $this->upd_items = $items;
        return $this->upd_items;
    }

    /**
     *  Add item ID to the DEL list (and remove it from INS or UPD list 
     *  if necessary)
     *  
     *  @param  string  $id          
     */         
    function del_item($id){
        if (!in_array($id, $this->del_ids)) $this->del_ids[] = $id;
        if (false !== $key = array_search($id, $this->ins_ids)) unset($this->ins_ids[$key]);
        $this->get_updated_items();
        if (isset($this->upd_items[$id])) unset($this->upd_items[$id]);
    }

    /**
     *  Return URLs that will be used in control links for one item
     */         
    function get_control_links($id){
        $links = array();
        $links["url_del_item"] =  "javascript:".rawurlencode($this->js_ctl.".del_item('".$id."');");
        $links["url_item_up"] =   "javascript:".rawurlencode($this->js_ctl.".item_up('".$id."');");
        $links["url_item_down"] = "javascript:".rawurlencode($this->js_ctl.".item_down('".$id."');");
        return $links;
    }



    /**
     *  If there were conrurent changes in the list of items this functions 
     *  should deal with them.
     *  
     *  If there are items in the DB that did not exist at the time the page 
     *  has been generated, they are added to "del_ids" list to delete them.
     *  
     *  If some items has been deleted from the DB in the meantime, they are 
     *  added to the "ins_ids" list to recreate them.                     
     */         
    function solve_concurent_changes(){
        foreach($this->items as $k=>$v){
            if (!in_array($v->{$this->item_id_var}, $this->db_ids))
                $this->del_ids[] = $v->{$this->item_id_var};
        }

        foreach($this->db_ids as $k=>$v){
            if (!in_array($v, $this->items_ids)) $this->ins_ids[] = $v;
        }
    }

    /**
     *  Add inserted items (to be created) into internal list of items ($this->items)
     */         
    function add_items_tbc(){

        /* add inserted items to array of items to be added to form */
        foreach($this->ins_ids as $v){
            $this->items[$v] = call_user_func(array($this->item_class, $this->item_creator));
            $this->items[$v]->{$this->item_id_var} = $v;
        }

        /* if order variables are specified, update ordering value by the _POST vars */
        if ($this->item_order_form_el and $this->item_order_var and !$this->view_only){
            foreach($this->items as $k => $v){
                if (isset($_POST[$this->item_order_form_el.$v->{$this->item_id_var}])){
                    $this->items[$k]->{$this->item_order_var} = $_POST[$this->item_order_form_el.$v->{$this->item_id_var}];
                }
            }
        }
    }

    /**
     *  Create html form elements for all items
     */         
    function add_items_to_form(){
        /* sort items by the ordering */
        if ($this->item_order_var){
            uasort($this->items, create_function('$a, $b', 
                'if ($a->'.$this->item_order_var.' == $b->'.$this->item_order_var.') return 0;
                 return ($a->'.$this->item_order_var.' < $b->'.$this->item_order_var.') ? -1 : 1;'));
        }

        foreach ($this->items as $k => $v){
            // skip items that should be deleted
            if (in_array($v->{$this->item_id_var}, $this->del_ids)) continue;

            // create form elements calling user funct    
            call_user_func_array($this->add_item_to_form_fn, array(&$this->f, $v));    
            
            // create record to be provided to smarty template
            $smarty_item = array();
            $smarty_item['id'] = $v->{$this->item_id_var};
            $smarty_item['links'] = $this->get_control_links($v->{$this->item_id_var});

            if (method_exists($v, $this->item_to_smarty_fn)){
                $smarty_item['item'] = $v->{$this->item_to_smarty_fn}();
            }

            $this->smarty_items[$v->{$this->item_id_var}] = $smarty_item;
        }
        

        $this->f->add_element(array("type"=>"hidden",
                                    "name"=>$this->new_items_form_el,
                                    "value"=>$this->new_item_elements));
                                    
        $this->f->add_element(array("type"=>"hidden",
                                    "name"=>$this->del_items_form_el,
                                    "value"=>""));
                
        $this->f->add_element(array("type"=>"hidden",
                                    "name"=>$this->db_items_form_el,
                                    "value"=>implode(";", $this->items_ids)));
                
    }
    
    /**
     *  Handler of action "add item"
     *  
     *  Generates html code for one item and return it
     */         
    function add_item_handler(){
        global $lang_str, $smarty;
        
        $id = $_GET[$this->add_item_url_id_param];
        $first_row = $_GET[$this->add_item_url_fr_param];
        $links = $this->get_control_links($id);
        
        $f = new form_ext();
        $sm = new Smarty_Serweb();

        // create instance of one item
        $item = call_user_func(array($this->item_class, $this->item_creator));
        $item->{$this->item_id_var} = $id;
        
        // create html for for the item
        call_user_func_array($this->add_item_to_form_fn, array(&$f, $item));    

        // copy already set template vars to the new smarty object 
        $tpl_vars = &$smarty->getTemplateVars();
        foreach($tpl_vars as $k => $v){
            $sm->assign($k, $tpl_vars[$k]);
        }

        // assign smarty templates    
        $sm->assign_phplib_form($this->smarty_form_param, 
                                $f, 
                                array(),
                                array());

        $sm->assign($this->smarty_id_param,    $id);
        $sm->assign($this->smarty_lang_param,  $lang_str);
        $sm->assign($this->smarty_links_param, $links);
        $sm->assign($this->smarty_fr_param,    $first_row);

        if (method_exists($item, $this->item_to_smarty_fn)){
            $sm->assign($this->smarty_item_param,     $item->{$this->item_to_smarty_fn}());
        }


        foreach($this->smarty_extra_params as $k => $v){
            $sm->assign($k, $v);
        }

        // generate object to be returned to browser as response to AJAX request
        $response = $this->get_empty_response();
        $response->tableRows = str_replace(array("\n", "\r"), array('', ''), $sm->fetch($this->smarty_template));
        $response->formElements = $f->get_hidden_els_as_string();

        return $response;    
    }

    /**
     *  This method should be used when server needs to generate more items 
     *  on one request from client. In this case the add_item_handler() needs 
     *  to be called multiple times and between each call of add_item_handler(),
     *  this method should be called.
     *  
     *  Client specify ID for first item as "xN" where N is some number. This
     *  method generates another IDs as "yNpM" where M is 1 on first call and
     *  is increased with each call of this method.                                   
     */         
    function generate_id_for_another_item(){
        static $base_num = null;
        static $cnt = 0;

        // initialize the $base_num on first call
        if (is_null($base_num)) $base_num = substr($_GET[$this->add_item_url_id_param], 1);

        // generate new ID
        $cnt++;
        $_GET[$this->add_item_url_id_param] = "y".$base_num."p".$cnt;

        // if generating ID for another row, clear the "first row" flag
        $_GET[$this->add_item_url_fr_param] = 0;
    }
    
    /**
     *  Returns same response as add_item_handler(), but empty.
     */         
    function get_empty_response(){
        $response = new stdClass();
        $response->tableRows = "";
        $response->formElements = "";
        
        return $response;
    }
    
    /**
     *  Joins two responses of add_item_handler() together. It's useful
     *  when add_item_handler() is called multiple times;
     */         
    function join_responses($resp1, $resp2){
        $resp1->tableRows    .= $resp2->tableRows;
        $resp1->formElements .= $resp2->formElements;
        return $resp1;
    }
}
?>
