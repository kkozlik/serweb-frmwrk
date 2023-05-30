<?php
/**
 * @author    Karel Kozlik
 * @version   $Id: auth4.1.php,v 1.7 2007/02/14 16:46:31 kozlik Exp $
 * @package   PHPLib
 */

/**
 * @package   PHPLib
 */
class Auth {

    /**
     *  Name of class holding info about user.
     *  It could be overriden in child classes.
     */
    static $user_class = "SerwebUser";

	var $lifetime = 15;           ## Max allowed idle time before
	                              ## reauthentication is necessary.
	                              ## If set to 0, auth never expires.

	var $refresh = 0;             ## Refresh interval in minutes.
	                              ## When expires auth data is refreshed
	                              ## from db using auth_refreshlogin()
	                              ## method. Set to 0 to disable refresh

	var $mode = "log";            ## "log" for login only systems,
	                              ## "reg" for user self registration - obsoleted and may not work

	var $nobody = false;          ## If true, a default auth is created... - obsoleted and may not work

	var $cancel_login = "cancel_login"; ## The name of a button that can be
	                                    ## used to cancel a login form

	## End of user qualifiable settings.

	var $auth = array();            ## Data array
	var $serweb_auth;

	/**
	 * constructor
	 */

	function Auth(){
	}

	function check_feature($f){
		if (get_class($this) != $f){
			$clone=new $f;
			$clone->auth=$this->auth;
			return $clone;
		}
		else return $this;
	}

	/**
	 *	authenticate user - uid, uname and realm have to be previously set
	 */

	function authenticate(){

		$this->auth['authenticated']	= true;
		$this->auth["exp"] 				= time() + (60 * $this->lifetime);
		$this->auth["refresh"] 			= time() + (60 * $this->refresh);
	}

	/**
	 *	authenticate user by given uid, uname and relam
	 *
	 *	@param	string	$uid
	 *	@param	string	$uname
	 *	@param	string	$realm
	 */

	function authenticate_as($uid, $uname, $did, $realm){

		$this->auth['uid']				= $uid;
		$this->auth['did']				= $did;
		$this->auth['uname']			= $uname;
		$this->auth['realm']			= $realm;

		$this->authenticate();
		$this->create_serweb_auth_references();
	}

	/**
	 *	Set permissions
	 *
	 *	@param	array	array of permissions
	 */

	function set_perms($perms){
		$this->auth["perm"] = implode(",", $perms);
	}

	/**
	 *	create references to auth info for backward compatibility
	 */

	function create_serweb_auth_references(){

        $this->serweb_auth = &call_user_func_array(array(static::$user_class, 'instance_by_refs'),
                                        array(&$this->auth['uid'],
                                              &$this->auth['uname'],
                                              &$this->auth['did'],
                                              &$this->auth['realm']));


/*		if (! is_object($this->serweb_auth)){
			$this->serweb_auth = new SerwebUser();
		}

		$this->serweb_auth->uid       = &$this->auth['uid'];
		$this->serweb_auth->did       = &$this->auth['did'];
		$this->serweb_auth->username  = &$this->auth['uname'];
		$this->serweb_auth->realm     = &$this->auth['realm'];
*/
	}

	function &get_logged_user(){
		if (! is_object($this->serweb_auth)){
			$this->create_serweb_auth_references();
		}

		return $this->serweb_auth;
	}


	########################################################################
	##
	## Initialization
	##

	function start() {
		$cl = $this->cancel_login;
		global $$cl;

		$this->create_serweb_auth_references();

		# Check current auth state. Should be one of
		#  1) Not logged in (no valid auth info or auth expired)
		#  2) Logged in (valid auth info)
		#  3) Login in progress (if $$cl, revert to state 1)
		if (false !== $this->is_authenticated()) {
			# User is authenticated and auth not expired
			$state = 2;

		} elseif(! empty($this->auth['in_progress'])) {
			# Login in progress
			if ($$cl) {
				# If $$cl is set, delete all auth info
				# and set state to "Not logged in", so eventually
				# default or automatic authentication may take place
				$this->unauth();
				$state = 1;
			} else {
				# Set state to "Login in progress"
				$state = 3;
			}

		} else {
			# User is not (yet) authenticated
			$this->unauth();
			$state = 1;
		}

		switch ($state) {
		case 1:
			# No valid auth info or auth is expired

			# Check for user supplied automatic login procedure
			if ( $this->auth_preauth() and (false !== $this->is_authenticated())) {
				$this->auth['in_progress'] = false; // to be sure
				return true;
			}

			# Check for "log" vs. "reg" mode
			switch ($this->mode) {
			case "yes":
			case "log":
				if ($this->nobody) {
					# Authenticate as nobody
					$this->auth["uid"] = "nobody";
					# $this->auth["uname"] = "nobody";
					$this->auth["exp"] = 0x7fffffff;
					$this->auth["refresh"] = 0x7fffffff;
					return true;
				} else {
					# Show the login form
					$this->auth_loginform();
					$this->auth['in_progress'] = true;
					exit;
				}
				break;

			case "reg":
				if ($this->nobody) {
					# Authenticate as nobody
					$this->auth["uid"] = "nobody";
					# $this->auth["uname"] = "nobody";
					$this->auth["exp"] = 0x7fffffff;
					$this->auth["refresh"] = 0x7fffffff;
					return true;
				} else {
					# Show the registration form
					$this->auth_registerform();
					$this->auth['in_progress'] = true;
					exit;
				}
				break;
			default:
				# This should never happen. Complain.
				echo "Error in auth handling: no valid mode specified.\n";
				exit;
			}
			break;

		case 2:
			# Valid auth info
			# Refresh expire info
			## DEFAUTH handling: do not update exp for nobody.
			if ($this->auth["uid"] != "nobody")
				$this->auth["exp"] = time() + (60 * $this->lifetime);
			break;

		case 3:
			# Login in progress, check results and act accordingly
			switch ($this->mode) {
			case "yes":
			case "log":
				if ( $this->auth_validatelogin() and (false !== $this->is_authenticated())) {
					$this->auth['in_progress'] = false;
					return true;
				} else {
					$this->auth_loginform();
					$this->auth['in_progress'] = true;
					exit;
				}
				break;
			case "reg":
				if ( $this->auth_doregister() and (false !== $this->is_authenticated())) {
					$this->auth['in_progress'] = false;
					return true;
				} else {
					$this->auth_registerform();
					$this->auth['in_progress'] = true;
					exit;
				}
				break;
			default:
				# This should never happen. Complain.
				echo "Error in auth handling: no valid mode specified.\n";
				exit;
				break;
			}
			break;

		default:
			# This should never happen. Complain.
			echo "Error in auth handling: invalid state reached.\n";
			exit;
			break;
		}
	}

	function login_if( $t ) {
		if ( $t ) {
		  $this->unauth();  # We have to relogin, so clear current auth info
		  $this->nobody = false; # We are forcing login, so default auth is
		                         # disabled
		  $this->start(); # Call authentication code
		}
	}


	function unauth() {
		$this->auth["authenticated"]   = false;
		$this->auth["perm"]  = "";
		$this->auth["exp"]   = 0;
	}


	function logout() {
		$this->auth = array();
		$this->unauth();
	}

	function is_authenticated() {
		if (
			isset($this->auth["uid"]) &&
			!is_null($this->auth["uid"]) &&
			$this->auth["authenticated"] &&
			(($this->lifetime <= 0) || (time() < $this->auth["exp"]))
		) {
			# If more than $this->refresh minutes are passed since last check,
			# perform auth data refreshing. Refresh is only done when current
			# session is valid (registered, not expired).
			if (
				($this->refresh > 0) &&
				($this->auth["refresh"]) &&
				($this->auth["refresh"] < time())
			) {
				if ( $this->auth_refreshlogin() ) {
					$this->auth["refresh"] = time() + (60 * $this->refresh);
				} else {
					return false;
				}
			}

			return $this->auth["uid"];
		} else {
			return false;
		}
	}

	########################################################################
	##
	## Accessors
	##

	function set_did($did){
		$this->auth['did'] = $did;
	}

	function get_did(){
		return isset($this->auth['did']) ? $this->auth['did'] : null;
	}

	function get_uid(){
		return isset($this->auth['uid']) ? $this->auth['uid'] : null;
	}

	########################################################################
	##
	## Helper functions
	##

	function url() {
		return $GLOBALS["sess"]->self_url();
	}

	function purl() {
		print $GLOBALS["sess"]->self_url();
	}

	########################################################################
	##
	## Authentication dummies. Must be overridden by user.
	##

	/**
	 *	This method can authenticate a user before the loginform is being displayed.
	 *
	 *	If it does, it must call method $this->authenticate(...) and return true.
	 *	Else it shall return false.
	 *
	 *	@return 	bool
	 */

	function auth_preauth() { return false; }

	/**
	 *	This function should validate given credentials and return UID if they are valid
	 *
	 *	This function has to be static - do not use the '$this' reference inside
	 *	function body
	 *
	 *	@static
	 *	@param	string	$username
	 *	@param	string	$did
	 *	@param	string	$password
	 *	@param	array	$opt
	 *	@return	string				UID if credentials are valid, false otherwise
	 */

	static function validate_credentials($username, $did, $password, &$opt){
		$opt['realm'] = $did;
		return $username."@".$did;
	}

	/**
	 *	This functioun should find out permissinos of user with given UID and return the permissons as array
	 *
	 *	This function has to be static - do not use the '$this' reference inside
	 *	function body
	 *
	 *	@static
	 *	@param	string	$uid
	 *	@param	array	$opt
	 *	@return	array				array of permissions or FALSE on error
	 */

	static function find_out_perms($uid, $opt){
		return array();
	}

	/**
	 *	This function is called when the user submits the login form created by auth_loginform().
	 *
	 *  It must validate the user input. If the user authenticated successfully,
	 *  it must call method $this->authenticate(...) and return true.
	 *	Else it shall return false.
	 *
	 *	@return 	bool
	 */

	function auth_validatelogin() { return false; }

	/**
	 *	This function should output HTML that creates a login screen for the user.
	 *
	 *  It must be overridden by a subclass to Auth.
	 */

	function auth_loginform() { ; }

	/**
	 *	This function must refresh the authentication informations stored in auth array by auth_validatelogin() method.
	 *
	 *	It is called every refresh minutes and is not called if the user is logged in as nobody.
	 *	It must return true on success, false otherwise (i.e.: the userid is no longer valid).
	 *
	 *  It must be overridden by a subclass to Auth.
	 *
	 *	@return 	bool
	 */

	function auth_refreshlogin() { return true; }

	/**
	 *	This function is called when the user submits the register form created by auth_registerform().
	 *
	 *  It must validate the user input. If the user registered successfully,
	 *  it must call method $this->authenticate(...) and return true.
	 *	Else it shall return false.
	 *
	 *	@return 	bool
	 */

	function auth_doregister() { return false; }

	/**
	 *	This function should output HTML that creates a register screen for the user.
	 *
	 *  It must be overridden by a subclass to Auth.
	 */

	function auth_registerform() { ; }
}

?>
