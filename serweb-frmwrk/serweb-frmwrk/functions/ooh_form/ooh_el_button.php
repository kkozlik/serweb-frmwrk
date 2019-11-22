<?php

class OohElButton extends OohElCommon {

    public static $default_class;
    protected $src;
    protected $content;
    protected $button_type="submit";


    public function self_get($val) {

        $id =           $this->get_id();
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();

        $str = "<button name='$this->name' value=\"".htmlspecialchars($val, ENT_QUOTES)."\"";

        switch ($this->button_type){
            case "reset":   $str .= ' type="reset"';    break;
            case "button":  $str .= ' type="button"';   break;
            default:        $str .= ' type="submit"';
        }

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($this->is_disabled())   $str .= " disabled";

        $str .= " >";
        $str .= !empty($this->content) ? $this->content : $this->value;
        $str .= "</button>";

        return $str;
    }
}
