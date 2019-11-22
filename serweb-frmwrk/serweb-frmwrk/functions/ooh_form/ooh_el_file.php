<?php

class OohElFile extends OohElCommon {

    public static $default_class;
    protected $size;

    public function is_file(){
        return false;
    }

    public function self_get($val) {

        $id =           $this->get_id();
        $class =        $this->get_classes();
        $extrahtml =    $this->get_extrahtml();
        $title =        $this->get_title();
        $placeholder =  $this->get_placeholder();

        $str = "";
        if ($this->size) $str .= "<input type='hidden' name='MAX_FILE_SIZE' value=$this->size />\n";

        $str .= "<input type='file' name='$this->name'";

        if ($id)                    $str .= " id='$id'";
        if ($class)                 $str .= " class=\"$class\"";
        if ($title)                 $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        if ($placeholder)           $str .= " placeholder='".htmlspecialchars($placeholder, ENT_QUOTES)."'";
        if ($extrahtml)             $str .= " $extrahtml";
        if ($this->is_disabled())   $str .= " disabled";

        $str .= " />";

        return $str;
    }
}
