<?php
/**
 *	OOHForms: checkbox
 *
 *	@author   Copyright (c) 1998 by Jay Bloodworth
 *	@author   Karel Kozlik
 *	@version  $Id: of_checkbox.inc,v 1.7 2007/09/17 18:56:32 kozlik Exp $
 *	@package  PHPLib
 */

/**
 *	@package  PHPLib
 */

class of_checkbox extends of_element {

  var $checked;

  // Constructor
  public function __construct($a) {
    $this->setup_element($a);
  }

  function self_get($val, $which, &$count) {
    $str = "";

    if ($this->multiple) {
      $n = $this->name . "[]";
      $str .= "<input type='checkbox' name='$n' value=\"".htmlspecialchars($val, ENT_QUOTES)."\" id='$this->name'";
      if (is_array($this->value)) {
        reset($this->value);
        while (list($k,$v) = each($this->value)) {
          if ($v==$val) {
            $str .= " checked";
            break;
          }
        }
      }
    } else {
      $str .= "<input type='checkbox' name='$this->name' id='$this->name'";
      $str .= " value=\"".htmlspecialchars($this->value, ENT_QUOTES)."\"";
      if ($this->checked)
        $str .= " checked";
    }
    if ($this->extrahtml)
      $str .= " $this->extrahtml";
    $str .= " class=\"inpCheckbox";
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

    $str .= " />";

    $count = 1;
    return $str;
  }

  function self_get_frozen($val, $which, &$count) {
    $str = "";

    $x = 0;
    $t="";
    if ($this->multiple) {
      $n = $this->name . "[]";
      if (is_array($this->value)) {
        reset($this->value);
        while (list($k,$v) = each($this->value)) {
          if ($v==$val) {
	          $x = 1;
            $str .= "<input type='hidden' name='$this->name' value=\"".htmlspecialchars($v, ENT_QUOTES)."\" />\n";
            $t =" bgcolor=#333333";
            break;
          }
        }
      }
    } else {
      if ($this->checked) {
        $x = 1;
        $t = " bgcolor=#333333";
        $str .= "<input type='hidden' name='$this->name'";
        $str .= " value=\"".htmlspecialchars($this->value, ENT_QUOTES)."\" />";
      }
    }
    $str .= "<table$t border=1><tr><td>&nbsp</td></tr></table>\n";

    $count = $x;
    return $str;
  }

  function self_load_defaults($val) {
    if ($this->multiple)
      $this->value = $val;
    elseif (isset($val) && (!$this->value || $val==$this->value))
      $this->checked=1;
    else
      $this->checked=0;

    if (!is_null($val)) $this->value = $val;
  }

} // end CHECKBOX

?>
