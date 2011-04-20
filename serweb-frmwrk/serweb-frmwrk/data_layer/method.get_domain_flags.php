<?php
/**
 *	@author     Karel Kozlik
 *	@version    $Id: method.get_domain_flags.php,v 1.3 2007/02/14 16:36:38 kozlik Exp $
 *	@package    serweb
 */ 

/**
 *	Data layer container holding the method for get domain flags
 * 
 *	@package    serweb
 */ 
class CData_Layer_get_domain_flags {
	var $required_methods = array();
	
	/**
	 *  return flags of domain with given domain ID as associative array
	 *
	 *  Keys of associative arrays:
	 *   - disabled
	 *   - deleted
	 *
	 *  Possible options:
	 *	 - none
	 *
	 *	@param string $did		domain ID
	 *	@param array $opt		associative array of options
	 *	@return array			domain flags or FALSE on error
	 */ 
	function get_domain_flags($did, $opt){
		global $config;

		$errors = array();
		if (!$this->connect_to_db($errors)) {
			ErrorHandler::add_error($errors);
			return false;
		}

		/* table's name */
		$td_name = &$config->data_sql->domain->table_name;
		/* col names */
		$cd = &$config->data_sql->domain->cols;
		/* flags */
		$fd = &$config->data_sql->domain->flag_values;


		$q="select ".$cd->flags."
		    from ".$td_name."
			where ".$cd->did."=".$this->sql_format($did, "s"); 

		$res=$this->db->query($q);
		if (DB::isError($res)) {ErrorHandler::log_errors($res); return false;}
		
		$disabled = true;
		$deleted = true;
		while ($row=$res->fetchRow(DB_FETCHMODE_ASSOC)){
			$disabled = ($disabled and ($row[$cd->flags] & $fd['DB_DISABLED']));
			$deleted  = ($deleted  and ($row[$cd->flags] & $fd['DB_DELETED']));
		}
		$res->free();

		return array('disabled' => $disabled,
		             'deleted'  => $deleted);			
	}
	
}
?>
