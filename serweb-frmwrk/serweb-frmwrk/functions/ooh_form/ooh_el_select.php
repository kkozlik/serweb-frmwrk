<?php

class OohElSelect extends OohElCommon {

    public static $default_class;
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
                $str .= $this->get_optgroup($optgroup);
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

    public function get_optgroup($optgroup){
        $str = "<optgroup ";

        $str .= " label=\"" .  htmlspecialchars($optgroup["label"], ENT_QUOTES) . "\"";
        if (!empty($optgroup['disabled'])){
            $str .= " disabled";
        }

        if (!empty($optgroup['title'])) {
            $str .= " title=\"".htmlspecialchars($optgroup['title'], ENT_QUOTES)."\"";
        }

        if (!empty($optgroup['class'])) {
            $str .= " class=\"".implode(" ", (array)$optgroup['class'])."\"";
        }

        if (!empty($optgroup['id'])) {
            $str .= " id=\"".htmlspecialchars($optgroup['id'], ENT_QUOTES)."\"";
        }

        if (!empty($optgroup['extrahtml'])) $str .= " ".$optgroup['extrahtml'];

        $str .= ">\n";
        foreach ($optgroup['options'] as $o){
            $str .= $this->get_option($o);
        }
        $str .= "</optgroup>\n";
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

            if (!empty($o['class'])) {
                $str .= " class=\"".implode(" ", (array)$o['class'])."\"";
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

            $str .= ">" . htmlspecialchars($o["label"], ENT_QUOTES) . "</option>\n";
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
