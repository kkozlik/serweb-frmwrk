<?php

class OohElSubmit extends OohElCommon {

    public static $default_class;
    protected $src;

    public function self_get($val) {

        $id =           $this->get_id();
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();

        $str = "<input name='$this->name' value=\"".htmlspecialchars($val, ENT_QUOTES)."\"";

        if ($this->src) $str .= " type='image' src='$this->src'";
        else            $str .= " type='submit'";

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($this->is_disabled())   $str .= " disabled";

        $str .= " />";

        return $str;
    }

    public function is_submit_image() {
        return (bool)$this->src;
    }
}
