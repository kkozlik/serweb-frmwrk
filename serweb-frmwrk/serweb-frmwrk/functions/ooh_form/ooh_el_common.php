<?php

class OohElCommon {

    // copy the $default_class definition to every child class otherwise the inheritance will not work
    public static $default_class;

    protected $id;
    protected $name;
    protected $type;
    protected $classes = [];
    protected $value;
    protected $multiple = false;
    protected $extrahtml = '';
    protected $title;
    protected $placeholder;
    protected $disabled = false;

    /**
     * List of event handlers. It's array in format:
     *
     * ```
     * [
     *   [
     *       "event"    => "click"
     *       "handler"  => "function(e){...}"
     *   ],
     *   ...
     * ]
     * ```
     * The `event` is name of the event and the `handler` is javascript code
     * directly inserted into `addEventListener()` function.
     *
     * @var array
     */
    protected $events = [];

    protected $js_validate = true;
    protected $php_validate = true;
    protected $skip_validation = false;
    protected $skip_load_default = false;
    protected $generate_ids_by_names = null;

    protected $form;          //reference to the html form (set during element creation)

    protected $js_trim_value = false; // register javascript event handler trimming
                                    // whitespaces at beginning and end of value

    protected $ignore_default_class = false;

    /**
     *  Transform associative array to array of options for select or radio element
     *
     *  @param  array   $arr
     *  @return array
     */
    public static function array_to_opt($arr){
        $options = array();
        foreach ($arr as $k=>$v){
            $options[] = array("value"=>$k, "label"=>$v);
        }
        return $options;
    }

    public function __construct($form, $options){
        $this->form = $form;
        $this->setup_element($options);
    }

    public function set_form($form){
        $this->form = $form;
    }

    public function __toString(){
        return $this->self_get($this->value);
    }

    public function get_name(){
        return $this->name;
    }

    /**
     * Return name of the element used in DOM. This is usualy same as
     * what function `get_name()` returns, but it might differ for some elements
     * e.g. the submit one.
     *
     * @return string
     */
    public function get_dom_name() : string{
        return $this->name;
    }

    public function get_type(){
        return $this->type;
    }

    public function get_value(){
        return $this->value;
    }

    public function get_js_trim_value(){
        return $this->js_trim_value;
    }

    public function get_event_handlers(){
        return $this->events;
    }

    public function do_js_validation(){
        return (!$this->skip_validation and $this->js_validate);
    }

    public function do_php_validation(){
        return (!$this->skip_validation and $this->php_validate);
    }

    public function do_load_default(){
        return !$this->skip_load_default;
    }

    public function is_hidden(){
        return false;
    }

    public function is_file(){
        return false;
    }

    public function id($val){
        $this->id = $val;
        return $this;
    }
    public function name($val){
        $this->name = $val;
        return $this;
    }
    public function value($val){
        $this->value = $val;
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
    public function add_extrahtml($val){
        $this->extrahtml .= " ".$val;
        return $this;
    }

    /**
     * Set whether element ID shall be generated from value of the element name
     *
     * If set tu NULL the value of form option 'generate_ids_by_names' is used
     *
     * @param bool|null $val
     * @return OohElCommon
     */
    public function id_name($val){
        $this->generate_ids_by_names = $val;
        return $this;
    }

    /**
     * Using setter metods (which return $this) in template and you do not want to generate any output, use this function;
     * Example:
     *    {$form->el('foo')->add_class('bar')->mute()}
     *
     * @return string
     */
    public function mute(){
        return "";
    }

    public function marshal_dispatch($m, $func) {
        $vname = $this->name;

        if (0 == strcasecmp($m, 'post'))     $val = isset($_POST[$vname])    ? $_POST[$vname]    : null;
        elseif (0 == strcasecmp($m, 'get'))  $val = isset($_GET[$vname])     ? $_GET[$vname]     : null;
        else                                 $val = isset($_REQUEST[$vname]) ? $_REQUEST[$vname] : null;

        return $this->$func($val);
    }

    public function self_get($val) {
        return "";
    }

    public function self_validate($val) {
        return false;
    }

    public function self_get_js() {
    }

    public function self_load_defaults($val) {
        $this->value = $val;
    }

    // Helper function for compatibility
    protected function setup_element($a) {
        $cv_tab = array(
                    "min_l"      => "minlength",
                    "max_l"      => "maxlength",
                    "extra_html" => "extrahtml"
                  );

        foreach($a as $k => $v){
            if (isset($cv_tab[$k])){
                $k = $cv_tab[$k];
            }

            if ($k == "class"){
                $classes = explode(" ", $v);
                foreach($classes as $class) {
                    $class = trim($class);
                    if ($class) $this->add_class($class);
                }
                continue;
            }

            if ($k == "type") $v = strtolower($v);

            $this->$k = $v;
        }

        if (empty($a['name'])) throw new Exception('Name of the form element is not set');
    }

    public function is_disabled(){
        return $this->disabled;
    }

    public function get_id($key=null){

        $id = $this->id;

        $generate_ids_by_names = $this->generate_ids_by_names;
        if (is_null($generate_ids_by_names)) $generate_ids_by_names = $this->form->get_option('generate_ids_by_names');

        if (!$id and $generate_ids_by_names) {
            // strip '[]' from the end of the name
            $id = preg_replace("/(^[^][]+)(.*)/", "\\1", $this->name);
        }
        if ($id and $this->form->get_option('id_prefix')) $id = $this->form->get_option('id_prefix').$id;

        if ($key) return $id = $id."_".$key;

        return $id;

    }

    private function get_default_class(){
        // implementing inheritance of static::$default_class property
        $c = get_called_class();
        while($c and ($p = get_parent_class($c)) and is_null($c::$default_class)) $c = $p;
        return $c::$default_class;
   }

    public function get_classes(){
        $classes = $this->classes;

        if (!$this->ignore_default_class and $default_class=$this->get_default_class()) $classes[] = $default_class;
        return implode(" ", $classes);
    }

    public function get_extrahtml(){
        return $this->extrahtml;
    }

    public function get_title(){
        return $this->title;
    }

    public function get_placeholder(){
        return str_replace(array("\n", "\r"),
                           array("", ""),
                           $this->placeholder);
    }

}
