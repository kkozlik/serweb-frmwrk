<?php
/**
 *	Object Oriented HTML Forms
 *
 *	@author   Copyright (c) 1998 by Jay Bloodworth
 *	@author   Karel Kozlik
 *	@version  $Id: oohforms.inc,v 1.14 2008/01/09 15:26:00 kozlik Exp $
 *	@package  PHPLib
 */

/**
 *	@package  PHPLib
 */
class of_element {

  var $name;
  var $value;
  var $multiple;
  var $extrahtml;
  var $js_validate = true;
  var $php_validate = true;
  var $skip_validation = false;
  var $form; //reference to the html form (set during element creation)
  var $js_trim_value = false; // register javascript event handler trimming
                              // whitespaces at beginning and end of value

  function marshal_dispatch($m,$func) {
    $vname = $this->name;

	if (0 == strcasecmp($m, 'post'))
	    $val = isset($_POST[$vname]) ? $_POST[$vname] : null;
	elseif (0 == strcasecmp($m, 'get'))
	    $val = isset($_GET[$vname]) ? $_GET[$vname] : null;
	else
	    $val = isset($_REQUEST[$vname]) ? $_REQUEST[$vname] : null;

    return $this->$func($val);

  }

  function self_get($val, $which, &$count) {
  }

  function self_show($val, $which) {
    $count = 0;
    print $this->self_get($val, $which, $count);
    return $count;
  }

  function self_get_frozen($val, $which, &$count) {
    return $this->self_get($val, $which, $count);
  }

  function self_show_frozen($val, $which) {
    $count = 0;
    print $this->self_get_frozen($val, $which, $count);
    return $count;
  }

  function self_validate($val) {
    return false;
  }

  function self_get_js($ndx_array) {
  }

  function self_print_js($ndx_array) {
    print $this->self_get_js($ndx_array);
  }

  // Note that this function is generally quite simple since
  // most of the work of dealing with different types of values
  // is now done in show_self.  It still needs to be overidable,
  // however, for elements like checkbox that deal with state
  // differently
  function self_load_defaults($val) {
    $this->value = $val;
  }

  // Helper function for compatibility
  function setup_element($a) {
    $cv_tab = array("type"=>"ignore",
        "min_l"=>"minlength",
        "max_l"=>"maxlength",
        "extra_html"=>"extrahtml");
    reset($a);
    while (list($k,$v) = each($a)) {
      if (isset($cv_tab[$k]) and $cv_tab[$k]=="ignore") continue;
      else $k = (isset($cv_tab[$k]) ? $cv_tab[$k] : $k);
      $this->$k = $v;
    }
  }

} // end ELEMENT

/**
 *	@package  PHPLib
 */
class of_hidden extends of_element {

  var $hidden=1;

  public function __construct($a) {
    $this->setup_element($a);
  }

  function self_get($val,$which, &$count) {
    $str = "";

    $v = (is_array($this->value) ? $this->value : array($this->value));
    $n = $this->name . ($this->multiple ? "[]" : "");
    reset($v);
    while (list($k,$tv) = each($v)) {
      $str .= "<input type='hidden' name='$n' value=\"".htmlspecialchars($tv, ENT_QUOTES)."\"";
      if ($this->extrahtml)
        $str .=" $this->extrahtml";
      $str .= " />";
    }

    return $str;
  }
} // end HIDDEN

/**
 *	@package  PHPLib
 */
class of_reset extends of_element {

  var $src;

  public function __construct($a) {
    $this->setup_element($a);
  }

  function self_get($val, $which, &$count) {
    $str = "<input name='$this->name' type=reset value=\"".htmlspecialchars($val, ENT_QUOTES)."\"";
    if ($this->extrahtml)
      $str .= " $this->extrahtml";
    $str .= " class=\"inpReset";
    if (!empty($this->class)){
		$str .= " ".implode(" ", (array)$this->class);
	}
    $str .= "\"";

    if (!empty($this->disabled)){
	    $str .= " disabled";
    }

    $str .= " />";

    return $str;
  }
} // end RESET

/**
 *	@package  PHPLib
 */
class of_submit extends of_element {

  var $src;

  public function __construct($a) {
    $this->setup_element($a);
  }

  function self_get($val, $which, &$count) {
    $str = "";

    $sv = empty($val) ? $this->value : $val;
    $str .= "<input name='$this->name' value=\"".htmlspecialchars($sv, ENT_QUOTES)."\"";
    if ($this->src)
      $str .= " type='image' src='$this->src'";
    else
      $str .= " type='submit'";
    if ($this->extrahtml)
      $str .= " $this->extrahtml";
    $str .= $this->src ? " class=\"inpImage" : " class=\"inpSubmit";
    if (!empty($this->class)){
		$str .= " ".implode(" ", (array)$this->class);
	}
    $str .= "\"";

    if (!empty($this->disabled)){
	    $str .= " disabled";
    }

    $str .= " />";

    return $str;
  }

  function self_load_defaults($val) {
    // SUBMIT will not change its value
  }
} // end SUBMIT

/**
 *	@package  PHPLib
 */
class of_button extends of_element {

  var $src;

  public function __construct($a) {
    $this->setup_element($a);
  }

  function self_get($val, $which, &$count) {
    $str = "";

    $sv = empty($val) ? $this->value : $val;
    $str .= "<button name='$this->name' id='$this->name' value=\"".htmlspecialchars($sv, ENT_QUOTES)."\"";

    if (empty($this->button_type)) $this->button_type="submit";

    switch ($this->button_type){
    case "reset":
        $str .= ' type="reset" class="inpReset';
        break;
    case "button":
        $str .= ' type="button" class="inpButton';
        break;
    default:
        $str .= ' type="submit" class="inpSubmit';
    }

    if (!empty($this->class)){
		$str .= " ".implode(" ", (array)$this->class);
	}
    $str .= "\""; //end of class attribute

    if ($this->extrahtml)
        $str .= " $this->extrahtml";

    if (!empty($this->disabled)){
	    $str .= " disabled";
    }

    $str .= " >";
    $str .= !empty($this->content) ? $this->content : $this->value;
    $str .= "</button>";

    return $str;
  }

  function self_load_defaults($val) {
    // BUTTON will not change its value
  }
} // end BUTTON

/**
 *	@package  PHPLib
 */
class form {
  var $elements;
  var $hidden;
  var $jvs_name;
  var $isfile;
  var $n;

  public function __construct(){
  	$this->elements=array();
  }

  function get_start($jvs_name="",$method="",$action="",$target="",$form_name="") {
    global $PHP_SELF;

    $str = "";

    /* if form name is not set and jvs_name is, use it for form name*/
    if ($jvs_name and !$form_name) $form_name = $jvs_name;
    /* form name still is not set - use some value for it  */
    if (!$form_name) $form_name = "oohform";

    $this->jvs_name = "";
    $this->n = 0;
    if (!$method) $method = "POST";
    if (!$action) $action = $PHP_SELF;
    if (!$target) $target = "_self";

    $str .= "<form name='$form_name' id='$form_name' ";
    if ($this->isfile) {
      $str .= " enctype='multipart/form-data'";
      $method = "POST";
    }
    $str .= " method='".strtolower($method)."'";
    $str .= " action='".htmlspecialchars($action, ENT_QUOTES)."'";
    $str .= " target='$target'";
    if ($jvs_name) {
      $this->jvs_name = $jvs_name;
      $str .= " onsubmit=\"return ${jvs_name}_Validator(this)\"";
    }

    $str .= ">";

    return $str;
  }

  function start($jvs_name="",$method="",$action="",$target="",$form_name="") {
    print $this->get_start($jvs_name,$method,$action,$target,$form_name);
  }

  function get_finish($after="",$before="") {
    $str = "";

    if ($this->hidden) {
      reset($this->hidden);
      while (list($k,$elname) = each($this->hidden))
        $str .= $this->get_element($elname);
    }
    if (is_object(PHPlib::$session) && (PHPlib::$session->mode == "get")) {
      $str .= sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\" />\n", PHPlib::$session->name, PHPlib::$session->id);
    }
    $str .= "</form>";

    if ($this->jvs_name) {
      $jvs_name = $this->jvs_name;
      $str .= "<script type='text/javascript' >\n<!--\n";

      foreach($this->elements as $k=>$v){
            $el = $v["ob"];
            //print_r($el);
            if ($el->type=="of_textarea" and $el->maxlength){

                // register event listeners to textarea
                $str .="
                    var el=document.getElementById('".$el->name."');
                    el.my_max_length = ".$el->maxlength.";
                    phplib_ctl.add_event(el, 'keyup', phplib_ctl.oh_textarea_max_length);
                    phplib_ctl.add_event(el, 'keypress', phplib_ctl.oh_textarea_max_length);
                    phplib_ctl.add_event(el, 'change', phplib_ctl.oh_textarea_max_length);
                    phplib_ctl.add_event(el, 'cut', phplib_ctl.oh_textarea_max_length);
                    phplib_ctl.add_event(el, 'paste', phplib_ctl.oh_textarea_max_length);
                ";
            }

            if ($el->js_trim_value){
                $str .="
                    phplib_ctl.add_event(document.getElementById('".$el->name."'), 'blur', phplib_ctl.oh_trim);";
            }
      }

      $str .= "\nfunction ${jvs_name}_Validator(f) {\n";

      if (strlen($before))
        $str .= "$before\n";
      reset($this->elements);
      while (list($k,$elrec) = each($this->elements)) {
        $el = $elrec["ob"];

        if ($el->js_trim_value){
            $str .="
                phplib_ctl.trim(document.getElementById('".$el->name."'));
            ";
        }

        if (!$el->skip_validation and $el->js_validate)
            $str .= $el->self_get_js($elrec["ndx_array"]);
      }
      if (strlen($after))
        $str .= "$after\n";

      $str .= "return true;\n";

      $str .= "}\n//-->\n</script>";
    }

    return $str;
  }

  function finish($after="",$before="") {
    print $this->get_finish($after, $before);
  }

  function add_element($el) {

    if (!is_array($el))
      return false;

    $cv_tab = array("select multiple"=>"select", "image"=>"submit");
    if (isset($cv_tab[$el["type"]]))
      $t = ("of_" . $cv_tab[$el["type"]]);
    else
      $t = ("of_" . $el["type"]);

    // translate names like $foo[int] to $foo{int} so that they can cause no
    // harm in $this->elements
    # Original match
    # if (preg_match("/(\w+)\[(d+)\]/i", $el[name], $regs)) {
    if (preg_match("/([a-zA-Z_]+)\[([0-9]+)\]/", $el["name"], $regs)) {
       $el["name"] = sprintf("%s{%s}", $regs[1], $regs[2]);
       $el["multiple"] = true;
    }
    $el = new $t($el);
    $el->type = $t; # as suggested by Michael Graham (magog@the-wire.com)
    $el->form = &$this;

    if (isset($el->isfile) and $el->isfile)
      $this->isfile = true;
    $this->elements[$el->name]["ob"] = $el;
    if (isset($el->hidden) and $el->hidden)
      $this->hidden[] = $el->name;
  }

  function get_element($name,$value=false) {
    $str = "";
    $x   = 0;
    $flag_nametranslation = false;

    // see add_element: translate $foo[int] to $foo{int}
#   Original pattern
#   if (preg_match("/(w+)\[(\d+)\]/i", $name, $regs) {
    if (preg_match("/([a-zA-Z_]+)\[([0-9]+)\]/", $name, $regs)) {
       $org_name = $name;
       $name = sprintf("%s{%s}", $regs[1], $regs[2]);
       $flag_nametranslation = true;
    }

    if (!isset($this->elements[$name]))
      return false;

    if (!isset($this->elements[$name]["which"]))
      $this->elements[$name]["which"] = 0;

    $el = $this->elements[$name]["ob"];
    if (true == $flag_nametranslation)
      $el->name = $org_name;

    if (false === $value)
       $value = $el->value;

    if (isset($this->elements[$name]["frozen"]) and $this->elements[$name]["frozen"])
      $str .= $el->self_get_frozen($value,$this->elements[$name]["which"]++, $x);
    else
      $str .= $el->self_get($value,$this->elements[$name]["which"]++, $x);
    $this->elements[$name]["ndx_array"][] = $this->n;
    $this->n += $x;

    return $str;
  }

  function show_element($name, $value="") {
    print $this->get_element($name, $value);
  }

  function ge($name, $value="") {
    return $this->get_element($name, $value);
  }

  function se($name, $value="") {
    $this->show_element($name, $value);
  }

  function ae($el) {
    $this->add_element($el);
  }

  function validate($default=false,$vallist="") {
    if ($vallist) {
      reset($vallist);
      $elrec = $this->elements[current($vallist)];
    } else {
      reset($this->elements);
      $elrec = current($this->elements);
    }
    while ($elrec) {
      $el = $elrec["ob"];
      if (!$el->skip_validation and $el->php_validate){
        if ($res = $el->marshal_dispatch(isset($this->method)?$this->method:"","self_validate")){
          $err[]=$res;
        }
      }
      if ($vallist) {
        next($vallist);
        $elrec = $this->elements[current($vallist)];
      } else {
        next($this->elements);
        $elrec = current($this->elements);
      }
    }
    return isset($err)?$err:$default;
  }

  function load_defaults($deflist="") {
    if ($deflist) {
      reset($deflist);
      $elrec = $this->elements[current($deflist)];
    } else {
      reset($this->elements);
      $elrec = current($this->elements);
    }
    while ($elrec) {
      $el = $elrec["ob"];
      //if default value of this element should be loaded
      if (empty($el->skip_load_default)) {
	      $el->marshal_dispatch(isset($this->method)?$this->method:"", "self_load_defaults");
    	  $this->elements[$el->name]["ob"] = $el;  // no refs -> must copy back
	  }
      if ($deflist) {
        next($deflist);
        $elrec = $this->elements[current($deflist)];
      } else {
        next($this->elements);
        $elrec = current($this->elements);
      }
    }
  }

  function freeze($flist="") {
    if ($flist) {
      reset($flist);
      $elrec = $this->elements[current($flist)];
    } else {
      reset($this->elements);
      $elrec = current($this->elements);
    }
    while ($elrec) {
      $el = $elrec["ob"];
      $this->elements[$el->name]["frozen"]=1;
      if ($flist) {
        next($flist);
        $elrec = $this->elements[current($flist)];
      } else {
        next($this->elements);
        $elrec = current($this->elements);
      }
    }
  }

} /* end FORM */

global $_SERWEB;

include($_SERWEB["phplibdir"] . "of_text.php");
include($_SERWEB["phplibdir"] . "of_select.php");
include($_SERWEB["phplibdir"] . "of_radio.php");
include($_SERWEB["phplibdir"] . "of_checkbox.php");
include($_SERWEB["phplibdir"] . "of_textarea.php");
include($_SERWEB["phplibdir"] . "of_file.php");

?>
