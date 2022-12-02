<?php

class OohElSubmit extends OohElCommon {

    public static $default_class;
    protected $src;

    public function get_name(){
        // strip the '_x' from end of the name
        if (substr($this->name, -2) == '_x') return substr($this->name, 0, -2);
        else return $this->name;
    }

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

    public function self_load_defaults($val) {
        // SUBMIT will not change its value
    }

    public function is_submit_image() {
        return (bool)$this->src;
    }
}
