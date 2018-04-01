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
	public $dl; //reference to data layer object

	/**
	 * set collation - for MySQL >= 4.1
	 */

	function set_db_collation($collation, $opt){
	 	global $config;

		$dl = $this->dl;
		$dl->db_collation = $collation;

		/* if connection to db is estabilished run sql query setting the collation */		
		if ($dl->db){
			$q="set collation_connection='".$dl->db_collation."'";
	
			$res=$dl->db->query($q);
			if ($dl->dbIsError($res))   throw new DBException($res);
		}
		
		/* otherwise do nothing, collation will be set after connect to DB */

		return true;
	}
}

?>
