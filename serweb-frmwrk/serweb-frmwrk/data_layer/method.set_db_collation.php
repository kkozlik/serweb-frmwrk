<?php
/**
 *	@author     Karel Kozlik
 *	@version    $Id: method.set_db_collation.php,v 1.2 2007/02/14 16:36:38 kozlik Exp $
 *	@package    serweb
 */ 

/**
 *	Data layer container holding the method for ser DB collation
 * 
 *	@package    serweb
 */ 
class CData_Layer_set_db_collation {
	var $required_methods = array();
	
	/**
	 * set collation - for MySQL >= 4.1
	 */

	function set_db_collation($collation, $opt){
	 	global $config;

		$this->db_collation = $collation;

		/* if connection to db is estabilished run sql query setting the collation */		
		if ($this->db){
			$q="set collation_connection='".$this->db_collation."'";
	
			$res=$this->db->query($q);
			if ($this->dbIsError($res))   throw new DBException($res);
		}
		
		/* otherwise do nothing, collation will be set after connect to DB */

		return true;
	}
}

?>
