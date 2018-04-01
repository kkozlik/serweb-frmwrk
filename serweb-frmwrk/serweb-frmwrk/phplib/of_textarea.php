<?php
/** 
 *	OOHForms: textarea
 *
 *	@author   Copyright (c) 1998 by Jay Bloodworth
 *	@author   Karel Kozlik
 *	@version  $Id: of_textarea.inc,v 1.9 2007/11/16 15:04:51 kozlik Exp $
 *	@package  PHPLib
 */

/**
 *	@package  PHPLib
 */

class of_textarea extends of_element {

  var $rows;
  var $cols;
  var $wrap;
  var $minlength=0;
  var $maxlength=null;
  var $length_e;
  var $min_length_e;
  var $max_length_e;
  var $valid_regex;
  var $valid_icase;
  var $valid_e;

  // Constructor
  function of_textarea($a) {
    $this->setup_element($a);
  }

  function self_get($val,$which, &$count) {
    $str  = "";
    $str .= "<textarea name='$this->name' id='$this->name'";
    $str .= " rows='$this->rows' cols='$this->cols'";
    if ($this->wrap) 
      $str .= " wrap='$this->wrap'";
    if ($this->extrahtml) 
      $str .= " $this->extrahtml";

    if (!empty($this->class)){
        $str .= " class=\"".implode(" ", (array)$this->class)."\"";
    }

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

    $str .= ">" . htmlspecialchars($this->value) ."</textarea>";
    
    $count = 1;
    return $str;
  }

  function self_get_frozen($val,$which, &$count) {
    $str  = "";
    $str .= "<input type='hidden' name='$this->name'";
    $str .= " value=\"";
    $str .= htmlspecialchars($this->value);
    $str .= "\" />\n";
    $str .= "<table border=1><tr><td>\n";
    $str .=  nl2br($this->value);
    $str .= "\n</td></tr></table>\n";
    
    $count = 1;
    return $str;
  }

  function self_get_js($ndx_array) {
    $str = "";

      if ($this->length_e || $this->min_length_e || $this->max_length_e || $this->valid_e) {
        $str .= "  var val=f.elements['".$this->name."'].value.replace('\\n', '\\r\\n')\n";
      }

      if ($this->length_e) {
        $str .= "if (val.length < ".$this->minlength."\n";
		if (!is_null($this->maxlength)){
			$str .= "  ||  val.length > ".$this->maxlength;
		}
		$str .= ") {\n";
        $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->length_e))."\");\n";
        $str .= "  f.elements['".$this->name."'].focus();\n";
        $str .= "  return(false);\n}\n";
      }
      if ($this->min_length_e) {
        $str .= "if (val.length < ".$this->minlength.") {\n";
        $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->min_length_e))."\");\n";
        $str .= "  f.elements['".$this->name."'].focus();\n";
        $str .= "  return(false);\n}\n";
      }
      if ($this->max_length_e) {
        $str .= "if (val.length > ".$this->maxlength.") {\n";
        $str .= "  alert(\"".str_replace("\n", '\n', addslashes($this->max_length_e))."\");\n";
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
        $str .= "  if (!reg.test(val)) {\n";
        $str .= "    alert(\"".str_replace("\n", '\n', addslashes($this->valid_e))."\");\n";
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
      if ($this->length_e && 
            ((strlen($v) < $this->minlength) || 
             (!is_null($this->maxlength) && (strlen($v) > $this->maxlength))))
        return $this->length_e;
        
      if ($this->min_length_e && (strlen($v) < $this->minlength))
        return $this->min_length_e;
        
      if ($this->max_length_e && (strlen($v) > $this->maxlength))
        return $this->max_length_e;
        
      if ($this->valid_e && (((isset($this->icase) and $this->icase) && 
            !preg_match('/'.$this->valid_regex.'/i', $v)) ||
           (!(isset($this->icase) and $this->icase) &&
            !preg_match('/'.$this->valid_regex.'/', $v))))
        return $this->valid_e;
    }
    return false;
  } 
} // end TEXTAREA

?>
