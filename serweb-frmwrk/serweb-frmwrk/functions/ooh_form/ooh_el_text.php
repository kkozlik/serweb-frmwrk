<?php

class OohElText extends OohElCommon {

    public static $default_class;
    protected $maxlength;
    protected $minlength;
    protected $length_err;
    protected $valid_regex;
    protected $valid_regex_js;
    protected $valid_err;
    protected $pass = false;
    protected $size;

    public function __construct($form, $options){
        parent::__construct($form, $options);

        if ($options["type"]=="password")  $this->pass = true;
    }


    public function self_get($val) {

        $value = htmlspecialchars($this->value, ENT_QUOTES);

        $id =           $this->get_id();
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();
        $placeholder =  $this->get_placeholder();

        $name = $this->name . ($this->multiple ? "[]" : "");

        $str = "<input name='$name' value=\"$value\"";
        $str .= ($this->pass) ? " type='password'" : " type='text'";

        if ($this->maxlength)       $str .= " maxlength='$this->maxlength'";
        if ($this->size)            $str .= " size='$this->size'";

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($placeholder)           $str .= " placeholder='".htmlspecialchars($placeholder, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($this->is_disabled())   $str .= " disabled";

        $str .= " />";

        return $str;
    }


    public function self_get_js() {
        $str = "";

        if ($this->length_err) {
            $str .= "if (f.elements['".$this->name."'].value.length < $this->minlength) {\n";
            $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->length_err))."\".replace('#VALUE#', f.elements['".$this->name."'].value));\n";
            $str .= "  f.elements['".$this->name."'].focus();\n";
            $str .= "  return(false);\n}\n";
        }

        if ($this->valid_err) {
            $str .= "if (window.RegExp) {\n";

            if (!empty($this->valid_regex_js))  $str .= "  var reg = {$this->valid_regex_js};\n";
            else                                $str .= "  var reg = {$this->valid_regex};\n";

            $str .= "  if (!reg.test(f.elements['".$this->name."'].value)) {\n";
            $str .= "    alert(\"".str_replace("\n", '\n', addslashes($this->valid_err))."\".replace('#VALUE#', f.elements['".$this->name."'].value));\n";
            $str .= "    f.elements['".$this->name."'].focus();\n";
            $str .= "    return(false);\n";
            $str .= "  }\n}\n";
        }

        return $str;
    }

    public function self_validate($val) {
        if (!is_array($val)) $val = array($val);

        foreach($val as $v){

            if ($this->length_err && (strlen($v) < $this->minlength))
                return str_replace("#VALUE#", $v, $this->length_err);

            if ($this->valid_err && !preg_match($this->valid_regex, $v))
                return str_replace("#VALUE#", $v, $this->valid_err);
        }
        return false;
    }

}
