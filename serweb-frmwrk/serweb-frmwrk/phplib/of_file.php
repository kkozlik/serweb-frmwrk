<?php
/** 
 *	OOHForms: file
 *
 *	@author   Copyright (c) 1998 by Jay Bloodworth
 *	@author   Karel Kozlik
 *	@version  $Id: of_file.inc,v 1.6 2007/09/17 18:56:32 kozlik Exp $
 *	@package  PHPLib
 */

/**
 *	@package  PHPLib
 */

class of_file extends of_element {

  var $isfile = true;
  var $size;

  function of_file($a) {
    $this->setup_element($a);
  }

  function self_get($val,$which, &$count) {
    $str = "";
    
    $str .= "<input type='hidden' name='MAX_FILE_SIZE' value=$this->size />\n";
    $str .= "<input type='file' name='$this->name' id='$this->name'";
    if ($this->extrahtml)
      $str .= " $this->extrahtml";
    $str .= " class=\"inpFile";
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
    
    $count = 2;
    return $str;
  }

} // end FILE
?>
