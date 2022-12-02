<?php

class OohElReset extends OohElCommon {

    public static $default_class;

    public function self_get($val) {

        $id =           $this->get_id();
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();

        $str = "<input name='$this->name' type='reset' value=\"".htmlspecialchars($val, ENT_QUOTES)."\"";

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($this->is_disabled())   $str .= " disabled";

        $str .= " />";

        return $str;
    }
}