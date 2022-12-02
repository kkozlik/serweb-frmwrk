<?php

class OohElRadio extends OohElCommon {

    public static $default_class;
    protected $valid_err;
    protected $options = [];

    public function self_get($val) {

        $id =           $this->get_id($val);
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();
        $disabled =     $this->is_disabled();

        if (isset($this->options) and is_array($this->options)){
            foreach ($this->options as $opt){
                if ($opt['value'] == $val){
                    if (!empty($opt['extrahtml'])) $extrahtml .= " ".$opt['extrahtml'];
                    if (isset($opt['disabled']))   $disabled = $opt['disabled'];
                    if (!empty($opt['title']))     $title = $opt['title'];
                    break;
                }
            }
        }

        $str = "<input type='radio' name='{$this->name}' value=\"".htmlspecialchars($val, ENT_QUOTES)."\"";

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($disabled)              $str .= " disabled";

        if ($this->value == $val)   $str .= " checked";

        $str .= " />";

        return $str;
    }

    public function get_radio_values(){
        $out = array();

        if (isset($this->options) and is_array($this->options)){
            foreach ($this->options as $opt){
                $out[] = $opt['value'];
            }
        }

        return $out;
    }

    public function self_get_js() {
        $str = "";

        if ($this->valid_err) {
            $str .= "var l = f.{$this->name}.length;\n";
            $str .= "var radioOK = false;\n";
            $str .= "for (i=0; i<l; i++)\n";
            $str .= "  if (f.{$this->name}[i].checked) {\n";
            $str .= "    radioOK = true;\n";
            $str .= "    break;\n";
            $str .= "  }\n";
            $str .= "if (!radioOK) {\n";
            $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->valid_err))."\");\n";
            $str .= "  return(false);\n";
            $str .= "}\n";
        }
    }

    function self_validate($val) {
        if ($this->valid_err && !isset($val)) return $this->valid_err;
        return false;
    }

}

