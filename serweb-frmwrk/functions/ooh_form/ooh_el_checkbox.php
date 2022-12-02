<?php


class OohElCheckbox extends OohElCommon {

    public static $default_class;
    protected $checked=false;

    public function self_get($val) {

        $id =           $this->get_id();
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();
        $disabled =     $this->is_disabled();

        $name = $this->name . ($this->multiple ? "[]" : "");

        if ($this->multiple and is_array($this->value)) {
            $value = $val;
            $checked = in_array($val, $this->value);
        }
        else {
            $value = $this->value;
            $checked = $this->checked;
        }

        $str = "<input type='checkbox' name='$name' value=\"".htmlspecialchars($value, ENT_QUOTES)."\" ";

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($disabled)              $str .= " disabled";
        if ($checked)               $str .= " checked";

        $str .= " />";

        return $str;
    }

    public function self_load_defaults($val) {
        if ($this->multiple)
            $this->value = $val;
        elseif (isset($val))
            $this->checked=1;
        else
            $this->checked=0;

        if (!is_null($val)) $this->value = $val;
    }

}
