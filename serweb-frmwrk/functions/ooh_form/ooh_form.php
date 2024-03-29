<?php

class OohForm {
    private $elements = [];
    private $isfile=false;

    private $id;
    private $name    = "oohform";
    private $jvs_name;
    private $classes = [];
    private $method  = "POST";
    private $target  = "_self";
    private $action;
    private $js_before;
    private $js_after;
    private $js = "";

    private $hidden = array();
    private $hidden_submits = array();
    private $hidden_cancels = array();
    private $form_cancels = array();

    protected $ignore_default_class = false;
    public static $default_class = "";

    private $options = [
        'id_prefix' => '',
        'generate_ids_by_names' => true,
    ];

    public function __construct(){
        global $config;

        if (!empty($_SERVER['PHP_SELF']))    $this->action = $_SERVER['PHP_SELF'];

        if (isset($config->ooh_form_cfg)){
            foreach ($config->ooh_form_cfg as $opt_name => $opt_value){
                if (!isset($this->options[$opt_name])){
                    sw_log(__CLASS__."::".__FUNCTION__."(): Config variable \$config->ooh_form_cfg contain unknown option '$opt_name'");
                    continue;
                }

                $this->options[$opt_name] = $opt_value;
            }
        }

        $this->add_element(array("type"=>"hidden",
                                 "name"=>"form_cancels",
                                 "value"=>""));
    }

    public function __toString(){
        // Using setter metods (which return $this) in template shall not generate any output
        return "";
    }

    public function __clone()
    {
        foreach($this->elements as &$el){
            // Make copy of elements when cloning
            $el['ob'] = clone $el['ob'];
            $el['ob']->set_form($this);
        }
    }

    /** Clone self - to be used in smarty templates */
    public function cp(){
        return clone $this;
    }

    public function id($val){
        $this->id = $val;
        return $this;
    }
    public function name($val){
        $this->name = $val;
        return $this;
    }

    public function jvs_name($val){
        $this->jvs_name = $val;
        return $this;
    }

    public function method($val){
        $this->method = $val;
        return $this;
    }
    public function action($val){
        $this->action = $val;
        return $this;
    }
    public function target($val){
        $this->target = $val;
        return $this;
    }
    public function add_class($val){
        $this->classes[] = $val;
        return $this;
    }
    public function ignore_default_class($val){
        $this->ignore_default_class = (bool)$val;
        return $this;
    }

    public function js_before($val){
        $this->js_before = $val;
        return $this;
    }
    public function js_after($val){
        $this->js_after = $val;
        return $this;
    }

    /**
     * Additional javascript code inserted after the form
     *
     * @param string $val
     * @return void
     */
    public function add_js(string $val) : void{
        $this->js .= $val;
    }

    public function option($name, $val=null){
        $this->options[$name] = $val;
        return $this;
    }

    public function get_option($name){
        return $this->options[$name];
    }

    public function get_id(){
        $id = $this->id;
        if (!$id and $this->options['generate_ids_by_names']) $id = $this->name;
        if ($id and $this->options['id_prefix']) $id = $this->options['id_prefix'].$id;
        return $id;
    }

    protected function get_classes(){
        $classes = $this->classes;
        if (!$this->ignore_default_class and static::$default_class) $classes[] = static::$default_class;
        return implode(" ", $classes);
    }

    public function start() {
        $str = "<form name='{$this->name}'";
        $id = $this->get_id();
        $class = $this->get_classes();
        if ($id)    $str .= " id='".htmlspecialchars($id, ENT_QUOTES)."'";
        if ($class) $str .= " class='$class'";

        $method = $this->method;
        if ($this->isfile) {
            $str .= " enctype='multipart/form-data'";
            $method = "POST";
        }
        $str .= " method='".strtolower($method)."'";
        $str .= " action='".htmlspecialchars($this->action, ENT_QUOTES)."'";
        $str .= " target='$this->target'";
        $str .= ">";

        return $str;
    }

    public function finish(){
        $str = "";
        if ($this->hidden) {
            foreach($this->hidden as $elname) $str .= $this->get_element($elname);
        }
        $str .= "</form>";
        return $str.$this->js_validator();
    }

    /**
     * Generate javascript code registering event handlers to the form elements
     *
     * @return string
     */
    private function js_register_events() : string{
        $js = "";
        $form_id = $this->get_id();

        foreach($this->elements as $elrec){
            $el = $elrec["ob"];
            $event_handlers = $el->get_event_handlers();
            if (!$event_handlers) continue;

            foreach($event_handlers as $event_handler){
                $el_name = $el->get_dom_name();
                if (!$form_id and !$this->name) throw new Exception("Cannot register event handler. Neither 'name' nor 'id' is set for the form.");
                if (!isset($event_handler['event']))    throw new Exception("'event' property of event handler of {$el_name} is not set.");
                if (!isset($event_handler['handler']))  throw new Exception("'handler' property of event handler of {$el_name} is not set.");

                if ($form_id) $selector = "#$form_id [name=\"$el_name\"]";
                else          $selector = "form[name=\"{$this->name}\"] [name=\"$el_name\"]";

                $js .= "document.querySelectorAll('$selector').forEach(".
                            "function(element){element.addEventListener(".
                                "'{$event_handler['event']}', {$event_handler['handler']});});\n";
            }
        }

        if (!$js) return "";

        return "
            document.addEventListener('DOMContentLoaded', function(event) {
                $js
            });
        ";
    }

    private function js_validator_get_fn($name){
        $validator_str = "";

        if ($this->js_before) $validator_str .= "{$this->js_before}\n";

        foreach($this->elements as $elrec){
            $el = $elrec["ob"];

            if ($el->get_js_trim_value()){
                $validator_str .= "phplib_ctl.trim(document.getElementById('".$el->get_name()."'));\n";
            }

            if ($el->do_js_validation()) $validator_str .= $el->self_get_js();
        }

        if ($this->js_after) $validator_str .= "$this->js_after\n";

        if (!$validator_str) return "";

        $str  = "function ${name}(f) {\n";
        $str .= $validator_str;
        $str .= "return true;\n";
        $str .= "}\n";

        return $str;
    }

    public function js_validator(){

        if (!$this->jvs_name) return "";

        $str = "";
        $js_str = $this->js;

        if ($js_str) $js_str .= "\n";
        $js_str .= $this->js_register_events();

        $validator_name = "{$this->jvs_name}_Validator";
        $validator_fn = $this->js_validator_get_fn($validator_name);

        foreach($this->elements as $elrec){
            $el = $elrec["ob"];

            if ($el->get_js_trim_value()){
                $js_str .= "phplib_ctl.add_event(document.getElementById('".$el->get_id()."'), 'blur', phplib_ctl.oh_trim);\n";
            }
        }

        if ($validator_fn){
            $form_id = $this->get_id();

            if (!$form_id){
                throw new Exception("Cannot get ID of the HTML form: ".json_encode($this));
            }

            $js_str .= "\n";
            $js_str .= $validator_fn;

            $js_str .= "document.getElementById('".$form_id."').onsubmit= $validator_name;\n";
        }


        if (!$js_str) return;

        $str .= "\n<script type='text/javascript' nonce='{$GLOBALS['controler']->get_nonce()}'>\n<!--\n";

        $str .= $js_str;
        $str .= "\n";
        $str .= "//-->\n</script>";

        return $str;
    }


    /**
     *  add submit element to from
     *  @param array $submit - associative array describing submit element
     *
     *   Keys of $submit array:
     *       ['type']  - type of submit element 'hidden', 'button', 'image'
     *       ['text']  - text on button on alt on image
     *       ['src']   - source of image
     *       ['disabled'] - button is disabled
     *       ['class'] - CSS class
     *       ['extrahtml'] - extra paramaters
     */
    public function add_submit($submit){
        $this->add_extra_submit("okey", $submit);
    }

    public function add_cancel($submit){
        $this->form_cancels["cancel"] = true;
        $this->get_element_obj('form_cancels')->value(implode(" ", array_keys($this->form_cancels)));
        $this->add_extra_submit("cancel", $submit);
    }

    public function add_extra_cancel($name, $submit){
        $this->form_cancels[$name] = true;
        $this->get_element_obj('form_cancels')->value(implode(" ", array_keys($this->form_cancels)));
        $this->add_extra_submit($name, $submit);
    }

    public function add_extra_submit($name, $submit){
        if (! empty($submit['class'])) $class = $submit['class'];
        else $class = null;

        if (! empty($submit['extrahtml'])) $extrahtml = $submit['extrahtml'];
        else $extrahtml = '';

        switch ($submit['type']){
        case "image":
            $element = array("type"=>"submit",
                             "name"=>$name,
                             "src"=>$submit['src'],
                             "disabled"=>!empty($submit['disabled']),
                             "class"=>$class,
                             "extrahtml"=>"alt='".$submit['text']."' ".$extrahtml);

            /* if it is a cancel button, disable form validation */
            if (isset($this->form_cancels[$name])){
                $element['events'] = [[
                    "event" => "click",
                    "handler" => "function(){ this.form.onsubmit=null; }"
                ]];
            }
            break;

        case "button":
            $element = array("type"=>"submit",
                             "name"=>$name."_x",
                             "value"=>$submit['text'],
                             "disabled"=>!empty($submit['disabled']),
                             "class"=>$class,
                             "extrahtml"=>$extrahtml);

            /* if it is a cancel button, disable form validation */
            if (isset($this->form_cancels[$name])){
                $element['events'] = [[
                    "event" => "click",
                    "handler" => "function(){ this.form.onsubmit=null; }"
                ]];
            }
            break;

        case "hidden":
        default:
            $element = array("type"=>"hidden",
                             "name"=>$name."_x",
                             "value"=>'0',
                             "class"=>$class,
                             "extrahtml"=>$extrahtml);

            if (isset($this->form_cancels[$name])) $this->hidden_cancels = $name."_x";
            else $this->hidden_submits[] = $name."_x";
        }

        // Add event handlers to the element
        if (isset($submit['events']) and is_array($submit['events'])){
            foreach($submit['events'] as $event){
                $element['events'][] = $event;
            }
        }

        $this->add_element($element);

    }

    public function add_element($el) {

        if (!is_array($el)) throw new UnexpectedValueException("Element is not an associative array");

        $cv_tab = array("select multiple"=>"select", "image"=>"submit");

        if (isset($cv_tab[$el["type"]])) $type = $cv_tab[$el["type"]];
        else                             $type = $el["type"];

        // translate names like $foo[int] to $foo{int} so that they can cause no
        // harm in $this->elements
        // if (preg_match("/([a-zA-Z_]+)\[([0-9]+)\]/", $el["name"], $regs)) {
        //     $el["name"] = sprintf("%s{%s}", $regs[1], $regs[2]);
        //     $el["multiple"] = true;
        // }

        $elclass = "OohEl".ucfirst($type);

        $el = new $elclass($this, $el);

        $this->elements[$el->get_name()]["ob"] = $el;

        if ($el->is_file())   $this->isfile = true;
        if ($el->is_hidden()) $this->hidden[] = $el->get_name();
    }

    public function get_element_names(){
        return array_keys($this->elements);
    }

    public function get_element_obj($name){
        if (!isset($this->elements[$name])) return null;
        return $this->elements[$name]["ob"];
    }
    /**
     * Shorthand for the above function for use in templates.
     */
    public function el($name){return $this->get_element_obj($name);}

    public function get_element($name, $value=null) {
        $str = "";

        // see add_element: translate $foo[int] to $foo{int}
        // $flag_nametranslation = false;
        // if (preg_match("/([a-zA-Z_]+)\[([0-9]+)\]/", $name, $regs)) {
        //     $orig_name = $name;
        //     $name = sprintf("%s{%s}", $regs[1], $regs[2]);
        //     $flag_nametranslation = true;
        // }

        if (!isset($this->elements[$name])) return false;

        $el = $this->elements[$name]["ob"];
        if (is_null($value)) $value = $el->get_value();

        // if (true == $flag_nametranslation) $el->get_name() = $orig_name;

        $str .= $el->self_get($value);

        return $str;
    }

    /**
     *  Return names of all hidden elements in the form
     *
     *  Elements used internaly by this class are skipped (hidden submits,
     *  hidden cancels and 'form_cancels')
     *
     *  @return array
     */
    protected function get_hidden_el_names(){
        return array_diff((array)$this->hidden,
                    array_merge($this->hidden_cancels,
                                $this->hidden_submits,
                                array("form_cancels")));
    }

    /**
     *  Return all hidden elements in the form as string
     *
     *  @return string
     */
    public function get_hidden_els_as_string(){
        $str = "";
        $els = $this->get_hidden_el_names();

        foreach($els as $v){
            $str .= $this->get_element($v);
        }

        return $str;
    }

    public function validate($default=false) {

        reset($this->elements);
        $elrec = current($this->elements);

        $err = [];
        foreach($this->elements as $elrec){
            $el = $elrec["ob"];
            if ($el->do_php_validation()){
                if ($res = $el->marshal_dispatch($this->method, "self_validate")){
                    $err[] = $res;
                }
            }
        }
        return $err ? $err : $default;
    }


    public function load_defaults($deflist="") {

        foreach($this->elements as &$elrec){
            $el = $elrec["ob"];

            //if default value of this element should be loaded
            if ($el->do_load_default()) {
                $el->marshal_dispatch($this->method, "self_load_defaults");
            }
        }
    }
}

require("ooh_el_common.php");
require("ooh_el_button.php");
require("ooh_el_file.php");
require("ooh_el_hidden.php");
require("ooh_el_checkbox.php");
require("ooh_el_radio.php");
require("ooh_el_reset.php");
require("ooh_el_select.php");
require("ooh_el_submit.php");
require("ooh_el_textarea.php");
require("ooh_el_text.php");

