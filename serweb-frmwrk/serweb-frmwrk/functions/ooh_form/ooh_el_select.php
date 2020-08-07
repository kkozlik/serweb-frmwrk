<?php

class OohElSelect extends OohElCommon {

    public static $default_class;
    public static $default_option_class;
    public static $optgroup_selectable_class;
    public static $optgroup_member_class;
    public static $optgroup_member_indent = "&nbsp;&nbsp;&nbsp;";

    const OPTGROUP_SELECTABLE = 'selectable';

    protected $options = [];
    protected $optgroup;
    protected $size;
    protected $valid_err;

    public function __construct($form, $options){
        parent::__construct($form, $options);

        if ($options["type"]=="select multiple")  $this->multiple = true;
    }


    public function self_get($val) {

        if ($this->multiple) {
            $name = $this->name . "[]";
            $type = "select multiple";
        } else {
            $name = $this->name;
            $type = "select";
        }

        $id =           $this->get_id();
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();

        $str = "<$type name='$name' ";

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($this->is_disabled())   $str .= " disabled";

        if ($this->size)            $str .= " size='$this->size'";

        if (is_array($this->value))     $str .= " data-oohf-raw-value='".htmlspecialchars(json_encode($this->value), ENT_QUOTES)."'";
        else                            $str .= " data-oohf-raw-value='".htmlspecialchars($this->value, ENT_QUOTES)."'";

        $str .= ">";

        if ($this->optgroup){
            foreach ($this->options as $optgroup){
                if ($this->optgroup === static::OPTGROUP_SELECTABLE){
                    $str .= $this->get_selectable_optgroup($optgroup);
                }
                else{
                    $str .= $this->get_optgroup($optgroup);
                }
            }
        }
        else{
            foreach ($this->options as $o){
                $str .= $this->get_option($o);
            }
        }
        $str .= "</select>";

        return $str;
    }

    protected function get_optgroup_attrs($optgroup){
        $attrs = [];

        if (!empty($optgroup['disabled'])){
            $attrs[] = "disabled";
        }

        if (!empty($optgroup['title'])) {
            $attrs[] = "title=\"".htmlspecialchars($optgroup['title'], ENT_QUOTES)."\"";
        }

        if (!empty($optgroup['id'])) {
            $attrs[] = "id=\"".htmlspecialchars($optgroup['id'], ENT_QUOTES)."\"";
        }

        if (!empty($optgroup['extrahtml'])) $attrs[] = $optgroup['extrahtml'];

        return $attrs;
    }

    protected function get_optgroup_class_attr($optgroup, $default_class){
        $classes = [];
        if (!empty($optgroup['class'])) {
            $classes = (array)$optgroup['class'];
        }

        if ($default_class) $classes[] = $default_class;

        if (!empty($classes)) {
            return "class=\"".implode(" ", $classes)."\"";
        }

        return '';
    }

    public function get_optgroup($optgroup){

        $str = "<optgroup ";

        $str .= " label=\"" .  htmlspecialchars($optgroup["label"], ENT_QUOTES) . "\"";
        $str .=  " ".$this->get_optgroup_class_attr($optgroup, null);
        $str .= implode(" ", $this->get_optgroup_attrs($optgroup));

        $str .= ">\n";
        foreach ($optgroup['options'] as $o){
            $str .= $this->get_option($o);
        }
        $str .= "</optgroup>\n";
        return $str;
    }

    /**
     * Renders optgroup that is clickable and selectable.
     *
     * This is implemented by simple trick: <option> html element is used
     * instead of <optgroup>. See this link: https://stackoverflow.com/a/9892421
     *
     * The application have to define proper css classes and take care of proper
     * rendering vi css.
     *
     * OohElSelect::$optgroup_selectable_class is used for the "group" option element
     * OohElSelect::$optgroup_member_class     is used for the "group member" option element
     *
     * Labels of "group member" options are intended with: OohElSelect::$optgroup_member_indent
     * It might be tricky to do the indentation via css.
     *
     * @return string
     */
    public function get_selectable_optgroup($optgroup){

        $str = "<option";

        if (isset($optgroup["value"])){
            $str .= " value=\"" .  htmlspecialchars($optgroup["value"], ENT_QUOTES) . "\"";
        }

        $str .=  " ".$this->get_optgroup_class_attr($optgroup, static::$optgroup_selectable_class);
        $str .= implode(" ", $this->get_optgroup_attrs($optgroup));

        $str .= ">" . htmlspecialchars($optgroup["label"], ENT_QUOTES) . "</option>\n";

        foreach ($optgroup['options'] as $o){
            if (empty($o['class'])) $o['class'] = Array();
            else                    $o['class'] = (array)$o['class'];
            if (static::$optgroup_member_class) $o['class'][] = static::$optgroup_member_class;
            if (static::$optgroup_member_indent) $o['label-indent'] = static::$optgroup_member_indent;

            $str .= $this->get_option($o);
        }

        return $str;
    }

    public function get_option($o){
        $str = "";

        if (is_array($o)) {
            // cast value of option to string for purpose of comparing
            $o["value"] = (string)$o["value"];

            $str .= "<option";
            $str .= " value=\"" .  htmlspecialchars($o["value"], ENT_QUOTES) . "\"";
            if (!empty($o['disabled'])){
                $str .= " disabled";
            }

            $classes = [];
            if (!empty($o['class'])) $classes = (array)$o['class'];
            if (static::$default_option_class) $classes[] = static::$default_option_class;

            if ($classes) {
                $str .= " class=\"".implode(" ", $classes)."\"";
            }

            if (!empty($o['id'])) {
                $str .= " id=\"".htmlspecialchars($o['id'], ENT_QUOTES)."\"";
            }

            if (!empty($o['title'])) {
                $str .= " title=\"".htmlspecialchars($o['title'], ENT_QUOTES)."\"";
            }

            if (!empty($o['extrahtml'])) $str .= " ".$o['extrahtml'];

            if (!$this->multiple && ((string)$this->value==$o["value"]))
                $str .= " selected";
            elseif ($this->multiple && is_array($this->value)) {
                if (in_array($o["value"], $this->value)){
                    $str .= " selected";
                }
            }

            $str .= ">";

            if (!empty($o['label-indent'])) {
                $str .= $o['label-indent'];
            }

            $str .= htmlspecialchars($o["label"], ENT_QUOTES) . "</option>\n";
        }
        else {
            // cast value of option to string for purpose of comparing
            $o = (string)$o;

            $str .= "<option";
            $str .= " value=\"" .  htmlspecialchars($o, ENT_QUOTES) . "\"";
            if (!$this->multiple && ((string)$this->value==$o))
                $str .= " selected";
            elseif ($this->multiple && is_array($this->value)) {
                if (in_array($o, $this->value)){
                    $str .= " selected";
                }
            }

            $str .= ">" . htmlspecialchars($o, ENT_QUOTES) . "</option>\n";
        }

        return $str;
    }


    public function self_get_js() {
        $str = "";

        if (!$this->multiple && $this->valid_err) {
            $str .= "if (f.$this->name.selectedIndex == 0) {\n";
            $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->valid_err))."\");\n";
            $str .= "  f.$this->name.focus();\n";
            $str .= "  return(false);\n";
            $str .= "}\n";
        }

        return $str;
    }

    public function self_validate($val) {
        if (!$this->multiple && $this->valid_err) {
            reset($this->options);
            $o = current($this->options);
            if ($val==$o["value"] || $val==$o) return $this->valid_err;
        }
        return false;
    }

}
