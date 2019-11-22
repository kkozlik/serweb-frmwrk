<?php

class OohElTextarea extends OohElCommon {

    public static $default_class;
    protected $rows;
    protected $cols;
    protected $wrap;
    protected $minlength=0;
    protected $maxlength=null;
    protected $length_err;
    protected $min_length_err;
    protected $max_length_err;
    protected $valid_regex;
    protected $valid_err;


    public function self_get($val) {

        $id =           $this->get_id();
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();
        $placeholder =  $this->get_placeholder();

        $str = "<textarea name='$this->name' id='$this->name'";


        if ($this->rows)    $str .= " rows='$this->rows'";
        if ($this->cols)    $str .= " cols='$this->cols'";
        if ($this->wrap)    $str .= " wrap='$this->wrap'";

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($placeholder)           $str .= " placeholder='".htmlspecialchars($placeholder, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($this->is_disabled())   $str .= " disabled";

        $str .= ">" . htmlspecialchars($this->value) ."</textarea>";

        return $str;
    }


    public function self_get_js() {
        $str = "";

        if ($this->length_err || $this->min_length_err || $this->max_length_err || $this->valid_err) {
            $str .= "  var val=f.elements['".$this->name."'].value.replace('\\n', '\\r\\n')\n";
        }

        if ($this->length_err) {
            $str .= "if (val.length < ".$this->minlength."\n";
            if (!is_null($this->maxlength)){
                $str .= "  ||  val.length > ".$this->maxlength;
            }
            $str .= ") {\n";
            $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->length_err))."\");\n";
            $str .= "  f.elements['".$this->name."'].focus();\n";
            $str .= "  return(false);\n}\n";
        }
        if ($this->min_length_err) {
            $str .= "if (val.length < ".$this->minlength.") {\n";
            $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->min_length_err))."\");\n";
            $str .= "  f.elements['".$this->name."'].focus();\n";
            $str .= "  return(false);\n}\n";
        }
        if ($this->max_length_err) {
            $str .= "if (val.length > ".$this->maxlength.") {\n";
            $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->max_length_err))."\");\n";
            $str .= "  f.elements['".$this->name."'].focus();\n";
            $str .= "  return(false);\n}\n";
        }
        if ($this->valid_err) {

            if (!empty($this->valid_regex_js))  $str .= "  var reg = ".$this->valid_regex_js.";\n";
            else                                $str .= "  var reg = ".$this->valid_regex.";\n";

            $str .= "  if (!reg.test(val)) {\n";
            $str .= "    alert(\"".str_replace("\n", '\n', addslashes($this->valid_err))."\");\n";
            $str .= "    f.elements['".$this->name."'].focus();\n";
            $str .= "    return(false);\n";
            $str .= "  }\n";
        }

        return $str;
    }

    public function self_validate($val) {
        if (!is_array($val)) $val = array($val);

        foreach($val as $v){
            if ($this->length_err &&
                    ((strlen($v) < $this->minlength) ||
                    (!is_null($this->maxlength) && (strlen($v) > $this->maxlength))))
                return $this->length_err;

            if ($this->min_length_err && (strlen($v) < $this->minlength))
                return $this->min_length_err;

            if ($this->max_length_err && (strlen($v) > $this->maxlength))
                return $this->max_length_err;

            if ($this->valid_err && !preg_match($this->valid_regex, $v))
                return $this->valid_err;
        }
        return false;
    }
}
