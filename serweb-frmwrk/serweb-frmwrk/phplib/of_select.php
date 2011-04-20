<?php
/** 
 *  OOHForms: select
 *
 *  @author   Copyright (c) 1998 by Jay Bloodworth
 *  @author   Karel Kozlik
 *  @version  $Id: of_select.inc,v 1.13 2008/01/09 15:26:00 kozlik Exp $
 *  @package  PHPLib
 */

/**
 *  @package  PHPLib
 */

class of_select extends of_element {

    var $options;
    var $size;
    var $valid_e;

    // Constructor
    function of_select($a) {
        $this->setup_element($a);
        if ($a["type"]=="select multiple") $this->multiple=1;
    }
  
    function self_get($val,$which, &$count) {
        $str = "";
        
        if ($this->multiple) {
            $n = $this->name . "[]";
            $t = "select multiple";
        } else {
            $n = $this->name;
            $t = "select";
        }
    
        /* id is same as name without [] on the end */
        $id = ereg_replace("(^[^][]+)(.*)", "\\1", $n);
        
        $str .= "<$t name='$n' id='$id'";
        if ($this->size)      $str .= " size='$this->size'";
        if ($this->extrahtml) $str .= " $this->extrahtml";

        if (!empty($this->class)){
            $str .= " class=\"".implode(" ", (array)$this->class)."\"";
        }

        if (!empty($this->disabled)){
            $str .= " disabled";
        }

        if (!empty($this->title)){
            $str .= " title='".htmlspecialchars($this->title, ENT_QUOTES)."'";
        }
        $str .= ">";

        reset($this->options);
        foreach ($this->options as $o){

            if (is_array($o)) {
                // cast value of option to string for purpose of comparing
                $o["value"] = (string)$o["value"];
    
                $str .= "<option";
                $str .= " value=\"" .  htmlspecialchars($o["value"], ENT_QUOTES) . "\"";
                if (!empty($o['disabled'])){
                    $str .= " disabled";
                }
                if (!empty($o['extrahtml'])) $str .= " ".$o['extrahtml'];
    
                if (!$this->multiple && ((string)$this->value==$o["value"]))
                    $str .= " selected";
                elseif ($this->multiple && is_array($this->value)) {
                    if (in_array($o["value"], $this->value)){
                        $str .= " selected"; 
                    }
                }
    
                $str .= ">" . htmlspecialchars($o["label"], ENT_QUOTES) . "</option>\n";
            }
            else {
                // cast value of option to string for purpose of comparing
                $o = (string)$o;
    
                $str .= "<option";
                if (!$this->multiple && ((string)$this->value==$o))
                    $str .= " selected";
                elseif ($this->multiple && is_array($this->value)) {
                    if (in_array($o, $this->value)){
                        $str .= " selected"; 
                    }
                }
    
                $str .= ">" . htmlspecialchars($o, ENT_QUOTES) . "</option>\n";
            }
        }
        $str .= "</select>";
        
        $count = 1;
        return $str;
    }

    function self_get_frozen($val,$which, &$count) {
        $str = "";
        
        $x = 0;
        $n = $this->name . ($this->multiple ? "[]" : "");
        $v_array = (is_array($this->value) ? $this->value : array($this->value));
        $str .= "<table border=1>\n";
        reset($v_array);
        while (list($tk,$tv) = each($v_array)) {
            reset($this->options);
            while (list($k,$v) = each($this->options)) {
                if ((is_array($v) && 
                        (($tmp=$v["value"])==$tv || $v["label"]==$tv))
                     || ($tmp=$v)==$tv) {
                    
                    $x++;
                    $str .= "<input type='hidden' name='$n' value=\"".htmlspecialchars($tmp, ENT_QUOTES)."\" />\n";
                    $str .= "<tr><td>" . (is_array($v) ? $v["label"] : $v) . "</td></tr>\n";
                }
            }
        }
        $str .= "</table>\n";
    
        $count = $x;
        return $str;
    }

    function self_get_js($ndx_array) {
        $str = "";
    
        if (!$this->multiple && $this->valid_e) {
            $str .= "if (f.$this->name.selectedIndex == 0) {\n";
            $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->valid_e))."\");\n";
            $str .= "  f.$this->name.focus();\n";
            $str .= "  return(false);\n";
            $str .= "}\n";
        }
    
        return $str;
    }

    function self_validate($val) {
        if (!$this->multiple && $this->valid_e) {
            reset($this->options);
            $o = current($this->options);
            if ($val==$o["value"] || $val==$o) return $this->valid_e;
        }
        return false;
    }

} // end SELECT

?>
