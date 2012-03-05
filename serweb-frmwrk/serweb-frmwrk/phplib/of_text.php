<?php
/** 
 *	OOHForms: text
 *
 *	@author   Copyright (c) 1998 by Jay Bloodworth
 *	@author   Karel Kozlik
 *	@version  $Id: of_text.inc,v 1.9 2007/09/17 18:56:32 kozlik Exp $
 *	@package  PHPLib
 */

/**
 *	@package  PHPLib
 */

class of_text extends of_element {

  var $maxlength;
  var $minlength;
  var $length_e;
  var $valid_regex;
  var $valid_icase;
  var $valid_e;
  var $pass;
  var $size;

  // Constructor
  function of_text($a) {
    $this->setup_element($a);
    if ($a["type"]=="password")
      $this->pass=1;
  }

  function self_get($val,$which, &$count) {
    $str = "";
    
    if (is_array($this->value))
      $v = htmlspecialchars($this->value[$which], ENT_QUOTES);
    else 
      $v = htmlspecialchars($this->value, ENT_QUOTES);
    $n = $this->name . ($this->multiple ? "[]" : "");
    $str .= "<input name='$n' id='$n' value=\"$v\"";
    $str .= ($this->pass)? " type='password'" : " type='text'";
    if ($this->maxlength)
      $str .= " maxlength='$this->maxlength'";
    if ($this->size) 
      $str .= " size='$this->size'";
    if ($this->extrahtml) 
      $str .= " $this->extrahtml";

    $str .= ($this->pass)? " class=\"inpPassword" : " class=\"inpText";
    if (!empty($this->class)){
        $str .= " ".implode(" ", (array)$this->class);
    }
    $str .= "\"";

    if (!empty($this->disabled)){
        $str .= " disabled";
    }

    if (!empty($this->title)){
        $str .= " title='".htmlspecialchars($this->title, ENT_QUOTES)."'";
    }

    if (!empty($this->placeholder)){
        $str .= " placeholder='".htmlspecialchars(
                str_replace(array("\n", "\r"),
                            array("", ""),
                            $this->placeholder), ENT_QUOTES)."'";
    }


    $str .= " />";
    
    $count = 1;
    return $str;
  }

  function self_get_frozen($val,$which, &$count) {
    $str = "";
    
    if (is_array($this->value))
      $v = $this->value[$which];
    else 
      $v = $this->value;
    $n = $this->name . ($this->multiple ? "[]" : "");
    $str .= "<input type='hidden' name='$n' value=\"".htmlspecialchars($v, ENT_QUOTES)."\" />\n";
    $str .= "<table border=1><tr><td>$v</td></tr></table>\n";
    
    $count = 1;
    return $str;
  }

  function self_get_js($ndx_array) {
    $str = "";
/* 	rewrited by KK indexing elements by $ndx_array is unusable for me

    
    reset($ndx_array);
    while (list($k,$n) = each($ndx_array)) {
      if ($this->length_e) {
        $str .= "if (f.elements[${n}].value.length < $this->minlength) {\n";
        $str .= "  alert(\"$this->length_e\");\n";
        $str .= "  f.elements[${n}].focus();\n";
        $str .= "  return(false);\n}\n";
      }
      if ($this->valid_e) {
        $flags = ((isset($this->icase) and $this->icase) ? "gi" : "g");
        $str .= "if (window.RegExp) {\n";
        $str .= "  var reg = /".str_replace('/','\/',$this->valid_regex)."/$flags;\n";
//        $str .= "  var reg = /$this->valid_regex/$flags;\n";
        $str .= "  if (!reg.test(f.elements[${n}].value)) {\n";
        $str .= "    alert(\"$this->valid_e\");\n";
        $str .= "    f.elements[${n}].focus();\n";
        $str .= "    return(false);\n";
        $str .= "  }\n}\n";
      }
    }
*/    
      if ($this->length_e) {
        $str .= "if (f.elements['".$this->name."'].value.length < $this->minlength) {\n";
        $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->length_e))."\".replace('#VALUE#', f.elements['".$this->name."'].value));\n";
        $str .= "  f.elements['".$this->name."'].focus();\n";
        $str .= "  return(false);\n}\n";
      }
      if ($this->valid_e) {
        $flags = ((isset($this->icase) and $this->icase) ? "i" : "");
        $str .= "if (window.RegExp) {\n";
        if (!empty($this->valid_regex_js)){
            $str .= "  var reg = /".str_replace('/','\/',$this->valid_regex_js)."/$flags;\n";
        }
        else{
            $str .= "  var reg = /".str_replace('/','\/',$this->valid_regex)."/$flags;\n";
        }
//        $str .= "  var reg = /$this->valid_regex/$flags;\n";
        $str .= "  if (!reg.test(f.elements['".$this->name."'].value)) {\n";
        $str .= "    alert(\"".str_replace("\n", '\n', addslashes($this->valid_e))."\".replace('#VALUE#', f.elements['".$this->name."'].value));\n";
        $str .= "    f.elements['".$this->name."'].focus();\n";
        $str .= "    return(false);\n";
        $str .= "  }\n}\n";
      }


    return $str;
  }

  function self_validate($val) {
    if (!is_array($val)) $val = array($val);
    reset($val);
    while (list($k,$v) = each($val)) {
      if ($this->length_e && (strlen($v) < $this->minlength))
        return str_replace("#VALUE#", $v, $this->length_e);
      if ($this->valid_e && (((isset($this->icase) and $this->icase) && 
            !eregi($this->valid_regex,$v)) ||
           (!(isset($this->icase) and $this->icase) &&
            !ereg($this->valid_regex,$v))))
        return str_replace("#VALUE#", $v, $this->valid_e);
    }
    return false;
  } 

} // end TEXT

?>
