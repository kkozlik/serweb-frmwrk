<?php
/** 
 *  OOHForms: radio
 *
 *  @author   Copyright (c) 1998 by Jay Bloodworth
 *  @author   Karel Kozlik
 *  @version  $Id: of_radio.inc,v 1.10 2008/01/09 15:26:00 kozlik Exp $
 *  @package  PHPLib
 */

/**
 *  @package  PHPLib
 */

class of_radio extends of_element {

    var $valid_e;

    // Constructor
    function of_radio($a) {
        $this->setup_element($a);
    }

    function self_get($val, $which, &$count) {
        $str = "";
        $extrahtml = "";
        $disabled = false;
        $title = "";

        if (!empty($this->title)){
            $title = $this->title;
        }

        if (isset($this->options) and is_array($this->options)){
            foreach ($this->options as $opt){
                if ($opt['value'] == $val){
                    if (!empty($opt['extrahtml'])) $extrahtml = " ".$opt['extrahtml'];
                    if (!empty($opt['disabled']))  $disabled = true;
                    if (!empty($opt['title']))     $title = $opt['title'];
                    break;
                }
            }
        }

        $str .= "<input type='radio' name='$this->name' id='".$this->name."_$val' value=\"".htmlspecialchars($val, ENT_QUOTES)."\"";
        if ($this->extrahtml) 
            $str .= " $this->extrahtml";
        if ($this->value==$val) 
            $str .= " checked";
        $str .= " class=\"inpRadio";
        if (!empty($this->class)){
            $str .= " ".implode(" ", (array)$this->class);
        }
        $str .= "\"";

        if (!empty($this->disabled) or $disabled){
            $str .= " disabled";
        }

        if ($title){
            $str .= " title='".htmlspecialchars($title, ENT_QUOTES)."'";
        }

        $str .= " ".$extrahtml;

        $str .= " />";

        $count = 1;
        return $str;
    }

    function self_get_frozen($val,$which, &$count) {
        $str = "";
    
        $x = 0;
        if ($this->value==$val) {
            $x = 1;
            $str .= "<input type='hidden' name='$this->name' value=\"".htmlspecialchars($val, ENT_QUOTES)."\" />\n";
            $str .= "<table border=1 bgcolor=#333333>";
        } else {
            $str .= "<table border=1>";
        }
        $str .= "<tr><td>&nbsp</tr></td></table>\n";
    
        $count = $x;
        return $str;
    }
  
    function self_get_js($ndx_array) {
        $str = "";
    
        if ($this->valid_e) {
            $n = $this->name;
            $str .= "var l = f.${n}.length;\n";
            $str .= "var radioOK = false;\n";
            $str .= "for (i=0; i<l; i++)\n";
            $str .= "  if (f.${n}[i].checked) {\n";
            $str .= "    radioOK = true;\n";
            $str .= "    break;\n";
            $str .= "  }\n";
            $str .= "if (!radioOK) {\n";
            $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->valid_e))."\");\n";
            $str .= "  return(false);\n";
            $str .= "}\n";
        }
    }

    function self_validate($val) {
        if ($this->valid_e && !isset($val)) return $this->valid_e;
        return false;
    }

} // end RADIO

?>
