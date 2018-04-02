<?php
/**
 *	@author     Karel Kozlik
 *	@version    $Id: method.get_did_by_realm.php,v 1.7 2007/02/14 16:36:38 kozlik Exp $
 *	@package    serweb
 */ 

/**
 *	Data layer container holding the method for lookup domain ID by realm
 * 
 *	@package    serweb
 */ 
class CData_Layer_get_did_by_realm extends CData_Layer_Common{
	/**
	 *  Look for domain with same realm (or domainname) as given parameter
	 *
	 *	On error this method returning FALSE. I domian is not found return NULL
	 *
	 *  Possible options:
	 *	 - check_disabled_flag (bool) - If true, flag 'disabled' is checked 
	 *	                                and records with this flag set 
	 *	                                are ignored (default: true)
	 *
	 *	@return string		domain id
	 */ 
	 
	function get_did_by_realm($realm, $opt){
		global $config;

		$dl = $this->dl;

		if (!$config->multidomain) {
			return ($realm == $config->domain) ? $config->default_did : null;
		}
		
		if (!$dl->connect_to_db($errors)) return false;

		/* table's name */
		$t_d  = &$config->data_sql->domain->table_name;
		$t_da = &$config->data_sql->domain_attrs->table_name;
		/* col names */
		$c_d  = &$config->data_sql->domain->cols;
		$c_da = &$config->data_sql->domain_attrs->cols;
		/* flags */
		$f_d  = &$config->data_sql->domain->flag_values;
		$f_da = &$config->data_sql->domain_attrs->flag_values;


		$opt_check_disabled = isset($opt['check_disabled_flag']) ? (bool)$opt['check_disabled_flag'] : true;

		$out = array();
		$errors = array();

		/*
		 *	look for domain with digest_realm same as $realm
		 */

		$flags_set   = $f_da['DB_FOR_SERWEB'];

		if ($opt_check_disabled)
			$flags_clear = $f_da['DB_DISABLED'] | $f_da['DB_DELETED'];
		else
			$flags_clear = $f_da['DB_DELETED'];

		$q="select ".$c_da->did."
		    from ".$t_da."
			where  ".$c_da->name." = '".$config->attr_names['digest_realm']."' and 
			       ".$c_da->value." = '".$realm."' and
				   ".$c_da->flags." & ".$flags_set." = ".$flags_set." and
				   ".$c_da->flags." & ".$flags_clear." = 0 ";
		
		$res=$dl->db->query($q);
		if ($dl->dbIsError($res)) {
			log_errors($res, $errors); 
			ErrorHandler::add_error($errors);
			return false;
		}

		if ($row=$res->fetchRow(DB_FETCHMODE_ASSOC)){
			$res->free();
			return $row[$c_da->did];
		}

		$res->free();

		/*
		 *	look for domain with name same as $realm
		 */

		$flags_set   = $f_d['DB_FOR_SERWEB'];

		if ($opt_check_disabled)
			$flags_clear = $f_d['DB_DISABLED'] | $f_d['DB_DELETED'];
		else
			$flags_clear = $f_d['DB_DELETED'];
		
		$q="select ".$c_d->did."
		    from ".$t_d."
			where ".$c_d->name." = ".$dl->sql_format($realm, "s")." and 
			      ".$c_d->flags." & ".$flags_set." = ".$flags_set." and
				  ".$c_d->flags." & ".$flags_clear." = 0 ";

		$res=$dl->db->query($q);
		if ($dl->dbIsError($res)) {
			log_errors($res, $errors); 
			ErrorHandler::add_error($errors);
			return false;
		}

		if ($row=$res->fetchRow(DB_FETCHMODE_ASSOC)){
			$res->free();
			return $row[$c_d->did];
		}

		$res->free();

		sw_log("Domain ID for '".$realm."' not found. There should be either ".
		   "domain '".$realm."' in table '".$t_d."' or domain attribute ".
		   "'".$config->attr_names['digest_realm']."' with value '".$realm."'.".
		   "But it isn't.", 
		   PEAR_LOG_INFO);

		return null;
	}
}
?>
