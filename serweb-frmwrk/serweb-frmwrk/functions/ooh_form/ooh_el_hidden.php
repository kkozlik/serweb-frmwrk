<?php

class OohElHidden extends OohElCommon {

    public static $default_class;

    public function is_hidden(){
        return true;
    }

    public function self_get($val) {

        if (is_array($this->value)){
            $is_array = true;
            $value = $this->value;
        }
        else{
            $is_array = false;
            $value = array($this->value);
        }

        $name = $this->name . ($this->multiple ? "[]" : "");
        $extrahtml = $this->get_extrahtml();

        $str = "";
        foreach($value as $key => $tv){
            $id = $this->get_id($is_array ? $key : null);
            $str .= "<input type='hidden' name='$name' value=\"".htmlspecialchars($tv, ENT_QUOTES)."\"";
            if ($id) $str .=" id='$id'";
            if ($extrahtml) $str .=" $extrahtml";
            $str .= " />";
        }

        return $str;
    }
}