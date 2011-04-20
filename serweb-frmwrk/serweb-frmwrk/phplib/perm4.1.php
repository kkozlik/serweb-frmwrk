<?php
/**
 * @author    Karel Kozlik
 * @version   $Id: perm4.1.php,v 1.2 2007/02/14 16:46:31 kozlik Exp $
 * @package   PHPLib
 */ 

/**
 * @package   PHPLib
 */
class Perm {
	var $classname = "Perm";
	
	## Hash ("Name" => Permission-Bitmask)
	var $permissions = array ();
	
	var $auth_obj = null;

	function set_auth_obj(&$auth_obj){
		$this->auth_obj = &$auth_obj;
	}
	
	##
	## Permission code
	##
	function check($p) {
		if (! $this->have_perm($p)) {    
			if (! isset($this->auth_obj->auth["perm"]) ) {
				$this->auth_obj->auth["perm"] = "";
			}
			$this->perm_invalid($this->auth_obj->auth["perm"], $p);
			exit();
		}
	}
	
	function have_perm($p) {
		if (! isset($this->auth_obj->auth["perm"]) ) {
			$this->auth_obj->auth["perm"] = "";
		}
		$pageperm = split(",", $p);
		$userperm = split(",", $this->auth_obj->auth["perm"]);
		
		list ($ok0, $pagebits) = $this->permsum($pageperm);
		list ($ok1, $userbits) = $this->permsum($userperm);
		
		$has_all = (($userbits & $pagebits) == $pagebits);
		if (!($has_all && $ok0 && $ok1) ) {
			return false;
		} else {
			return true;
		}
	}
	
	##
	## Permission helpers.
	##
	function permsum($p) {
		if (!is_array($p)) {
			return array(false, 0);
		}
		$perms = $this->permissions;
		
		$r = 0;
		reset($p);
		while(list($key, $val) = each($p)) {
			if (!isset($perms[$val])) {
				return array(false, 0);
			}
			$r |= $perms[$val];
		}
		
		return array(true, $r);
	}
	
	## Look for a match within an list of strints
	## I couldn't figure out a way to do this generally using ereg().
	
	function perm_islisted($perms, $look_for) {
		$permlist = explode( ",", $perms );
		while( list($a,$b) = each($permlist) ) {
			if( $look_for == $b ) { return true; };
		};
		return false;
	}
	
	## Return a complete <select> tag for permission
	## selection.
	
	function perm_sel($name, $current = "", $class = "") {
		reset($this->permissions);
		
		$ret = sprintf("<select multiple name=\"%s[]\"%s>\n",
						$name,
						($class!="")?" class=$class":"");
		while(list($k, $v) = each($this->permissions)) {
			$ret .= sprintf(" <option%s%s>%s\n",
							$this->perm_islisted($current,$k)?" selected":"",
							($class!="")?" class=$class":"",
							$k);
		}
		$ret .= "</select>";
		
		return $ret;
	}
	
	##
	## Dummy Method. Must be overridden by user.
	##
	function perm_invalid($does_have, $must_have) { 
		printf("Access denied.\n"); 
	}
}
?>
