<?php
/**
 *	@author    Karel Kozlik
 *	@version   $Id: local.inc,v 1.21 2007/02/14 16:46:32 kozlik Exp $
 *	@package   PHPLib
 */


/**
 *	main session class
 *
 *	@package   PHPLib
 */

class phplib_Session extends Session {
	var $classname = "phplib_Session";

	var $trans_id_enabled = false;
	var $cookiename     = "";                ## defaults to classname
	var $mode           = "cookie";          ## We propagate session IDs with cookies
	var $fallback_mode  = "get";
	var $allowcache     = "no";              ## "public", "private", or "no"
	var $lifetime       = 0;                 ## 0 = do session cookies, else minutes
	var $cookie_secure   = true;
	var $cookie_httponly = false;
}


/**
 *	default auth class
 *
 *	@package   PHPLib
 */

class phplib_Auth extends Auth {
	var $classname      = "phplib_Auth";
	var $lifetime       = 20;

	/**
	 *	contructor
	 */

	function phplib_Auth(){
		global $config;
		/* call parent's constructor */
		$this->Auth();

		$this->lifetime = $config->auth_lifetime;
		$this->auth['adm_domains'] = null;
	}

	/**
	 *	Function is called when user is not authenticated
	 *
	 *	If user is logged in and authentication expired, this function
	 *	display relogin page. Otherwise if user is not logged in yet, is
	 *	redirected to login page
	 */

	function auth_loginform() {
		global $sess;
		global $_SERWEB;

		$this->auth['adm_domains'] = null;

		//user is not logged in, forward to login screen
		if (!isset($this->auth["uid"]) or is_null($this->auth["uid"])){
			Header("Location: ".$sess->url("index.php"));
			exit;
		}

		//else display relogin form
		include($_SERWEB["serwebdir"] . "relogin.php");
	}

	/**
	 *	Function validate password obtained from re-login form
	 *
	 *	If password is valid, function authenticate user again and return true,
	 *	otherwise return false.
	 *
	 *	@return 	bool
	 */

	function auth_validatelogin() {

		$password = "";
		if (isset($_POST['password'])) $password = $_POST['password'];

		$opt = array();
		if (false === $this->validate_credentials($this->auth['uname'], $this->auth['did'], $password, $opt)){
			return false;
		}

		$this->authenticate();

		$perms = $this->find_out_perms($this->auth['uid'], array());
		if (false === $perms) return false;
		$this->set_perms($perms);

		return true;
	}

	/**
	 *	Validate given credentials and return UID if they are valid
	 *
	 *	@static
	 *	@param	string	$username
	 *	@param	string	$did
	 *	@param	string	$password
	 *	@param	array	$optionals
	 *	@return	string				UID if credentials are valid, false otherwise
	 */

	static function validate_credentials($username, $did, $password, &$optionals){
		global $lang_str, $data_auth, $config;

		$o_check_pw = isset($optionals['check_pw']) ? (bool)$optionals['check_pw'] : true;

		$data_auth->add_method('check_credentials');
		$data_auth->add_method('get_domain_flags');


		// check flags of domain
		if (false === $flags = $data_auth->get_domain_flags($did, null)) return false;

		if ($flags['disabled']){
			sw_log("validate_credentials: authentication failed: domain with id '".$did."' is disabled", PEAR_LOG_INFO);
			ErrorHandler::add_error($lang_str['account_disabled']);
			return false;
		}

		if ($flags['deleted']){
			sw_log("validate_credentials: authentication failed: domain with id '".$did."' is deleted", PEAR_LOG_INFO);
			ErrorHandler::add_error($o_check_pw ? $lang_str['bad_username'] : $lang_str['err_no_user']);
			return false;
		}


		// find the realm
		sw_log("validate_credentials: looking for realm of domain with did: ".$did, PEAR_LOG_DEBUG);

		$opt=array("did"=>$did);
		if (false === $realm = Attributes::get_attribute($config->attr_names['digest_realm'], $opt)) return false;

		$optionals['realm'] = $realm;

		// chceck credentials
		sw_log("validate_credentials: checking credentials (username:did:realm): ".$username.":".$did.":".$realm, PEAR_LOG_DEBUG);

		$opt = array();
		$opt['check_pass'] = $o_check_pw;

		if ($config->clear_text_pw)	{
			$opt['hash'] = 'clear';
			$ha = $password;
		}
		else{
			$opt['hash'] = 'ha1';
			$ha = md5($username.":".$realm.":".$password);
		}

		$uid = $data_auth->check_credentials($username, $did, $realm, $ha, $opt);

		if (is_int($uid) and $uid == -3){
			sw_log("validate_credentials: authentication failed: account disabled ", PEAR_LOG_INFO);
			ErrorHandler::add_error($lang_str['account_disabled']);
			return false;
		}

		if (is_int($uid) and $uid <= 0) {
			sw_log("validate_credentials: authentication failed: bad username, did, realm or password ", PEAR_LOG_INFO);
			ErrorHandler::add_error($o_check_pw ? $lang_str['bad_username'] : $lang_str['err_no_user']);
			return false;
		}


		if (is_null($uid)){
			sw_log("validate_credentials: authentication failed: no user ID", PEAR_LOG_INFO);
			ErrorHandler::add_error($o_check_pw ? $lang_str['bad_username'] : $lang_str['err_no_user']);
			return false;
		}

		return $uid;
	}


	/**
	 *	Get domain id of domain
	 *
	 *	@static
	 *	@param	string	$realm
	 *	@param	array	$opt
	 *	@return	string				domain ID, FALSE on error
	 */

	static function find_out_did($realm, $opt){
		global $config;

		if (!$config->multidomain) return $config->default_did;

		$dh = &Domains::singleton();
		if (false === $did = $dh->get_did($realm)) return false;

		if (is_null($did)) return null;

		return $did;

	/*
		global $data_auth, $lang_str, $config;

		$data_auth->add_method('get_did_by_realm');

		$opt = array('check_disabled_flag' => false);
		if (false === $did = $data_auth->get_did_by_realm($realm, $opt)) return false;

		if (is_null($did)){
			sw_log("find_out_did: domain id for realm '".$realm."' not found", PEAR_LOG_INFO);
			ErrorHandler::add_error($lang_str['domain_not_found']);
			return false;
		}

		return $did;
	*/
	}

	/**
	 *	Get permissions of user with given UID
	 *
	 *	This function return the permissions of user in array
	 *
	 *	@static
	 *	@param	string	$uid
	 *	@param	array	$opt
	 *	@return	array				array of permissions or FALSE on error
	 */

	static function find_out_perms($uid, $opt){
		global $lang_str, $data_auth, $config;

		$an = $config->attr_names;

		$perms = array();

		$attrs = &User_Attrs::singleton($uid);

		if (false === $attrib = $attrs->get_attribute($an["is_admin"])) return false;
		if ($attrib) $perms[] = 'admin';

		if (false === $attrib = $attrs->get_attribute($an["is_hostmaster"])) return false;
		if ($attrib) $perms[] = 'hostmaster';

		return $perms;
	}

	/**
	 *	Get array of domains administrated by user
	 *
	 *	@param	string	$uid
	 *	@param	array	$opt
	 *	@return	array				array of domain IDs or FALSE on error
	 */

	function get_administrated_domains(){
		global $data_auth;

		if (!is_null($this->auth['adm_domains'])) return $this->auth['adm_domains'];

		$data_auth->add_method('get_domains_of_admin');
		if (false === $domains = $data_auth->get_domains_of_admin($this->auth['uid'], null)) return false;

		return $this->auth['adm_domains'] = $domains;
	}
}

/**
 *	@package   PHPLib
 *	@deprec
 */
class phplib_Pre_Auth extends phplib_Auth {
}


/**
 *	default perm class
 *
 *	@package   PHPLib
 */

class phplib_Perm extends Perm {
	var $classname = "phplib_Perm";

	var $permissions = array(
							"admin"      => 1,
							"change_priv"=> 2,
							"hostmaster" => 4
						);

	/**
	 *	Function is called when permission of user is invalid
	 *
	 *	This function should display page with "permissions invalid" message
	 */

	function perm_invalid($does_have, $must_have) {
		global $_SERWEB;
		include($_SERWEB["serwebdir"] . "perm_invalid.php");
	}
}

?>
