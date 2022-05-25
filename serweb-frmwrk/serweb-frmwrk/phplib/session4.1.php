<?php
/**
 * PHPLib Sessions using PHP 4 built-in Session Support.
 *
 * @copyright 1998,1999 NetUSE AG, Boris Erdmann, Kristian Koehntopp
 *            2000 Teodor Cimpoesu <teo@digiro.net>
 * @author    Teodor Cimpoesu <teo@digiro.net>, Ulf Wendel <uw@netuse.de>, Maxim Derkachev <kot@books.ru
 * @author    Karel Kozlik
 * @version   $Id: session4.1.php,v 1.5 2007/02/14 16:46:31 kozlik Exp $
 * @access    public
 * @package   PHPLib
 */

/**
 * @package   PHPLib
 */
class Session {


  /**
  * Session name
  *
  */
  var $classname = "Session";


  /**
  * Name of the autoinit-File, if any.
  *
  * @var  string
  */
  var $auto_init = "";


  /**
  * Depreciated! There's no need for page_close in PHP4 sessions.
  * @deprec $Id: session4.1.php,v 1.5 2007/02/14 16:46:31 kozlik Exp $
  * @var  integer
  */
  var $secure_auto_init = 1;


  /**
   * Marker: Did we already include the autoinit file?
   *
   * @var  boolean
   */
  var $in = false;

  /**
   * This Array contains the registered things
   *
   * @var  array
   */
  var $pt = array();

  /**
  * Current session id.
  *
  * @var  string
  * @see  id(), Session()
  */
  var $id = "";


  /**
  * [Current] Session name.
  *
  * @var  string
  * @see  name(), Session()
  */
  var $name = "";

  /**
  *
  * @var  string
  */
  var $cookie_path = '/';


  /**
  *
  * @var  strings
  */
  var $cookiename;


  /**
  *
  * @var  int
  */
  var $lifetime = 0;


  /**
  * If set, the domain for which the session cookie is set.
  *
  * @var  string
  */
  var $cookie_domain = '';

  var $cookie_secure = true;
  var $cookie_httponly = true;
  var $cookie_samesite = 'Lax';

  /**
  * Propagation mode is by default set to cookie
  * The other parameter, fallback_mode, decides wether
  * we accept ONLY cookies, or cookies and eventually get params
  * in php4 parlance, these variables cause a setting of either
  * the php.ini directive session.use_cookie or session.use_only_cookie
  * The session.use_only_cookie possibility was introdiced in PHP 4.2.2, and
  * has no effect on previous versions
  *
  * @var    string
  * @deprec $Id: session4.1.php,v 1.5 2007/02/14 16:46:31 kozlik Exp $
  */
  var $mode = "cookie";               ## We propagate session IDs with cookies

  /**
  * If fallback_mode is set to 'cookie', php4 will impose a cookie-only
  * propagation policy, which is a safer  propagation method that get mode
  *
  * @var    string
  * @deprec $Id: session4.1.php,v 1.5 2007/02/14 16:46:31 kozlik Exp $
  */
  var $fallback_mode;                 ## if fallback_mode is also 'ccokie'
                                      ## we enforce session.use_only_cookie


  /**
  * Was the PHP compiled using --enable-trans-sid?
  *
  * PHP 4 can automatically rewrite all URLs to append the session ID
  * as a get parameter if you enable the feature. If you've done so,
  * the old session3.inc method url() is no more needed, but as your
  * application might still call it you can disable it by setting this
  * flag to false.
  *
  * @var  boolean
  */
  var $trans_id_enabled = false;


  /**
  * See the session_cache_limit() options
  *
  * @var  string
  */
  var $allowcache = 'nocache';


  protected $on_init = [];


  /**
  * Sets the session name before the session starts.
  *
  * Make sure that all derived classes call the constructor
  *
  * @see  name()
  */
  function __construct() {
    $this->name($this->name);
  } // end constructor


  /**
  * Start a new session or recovers from an existing session
  *
  * @return boolean   session_start() return value
  * @access public
  */
  function start() {

    if ( $this->mode=="cookie"
        && $this->fallback_mode=="cookie")  {
      ini_set ("session.use_only_cookie","1");
    }

    $this->set_tokenname();
    $this->put_headers();

    $ok = true;
    // check whether the session is already started and
    // start the session if it is not
    if (session_id() === ""){
        @$ok = session_start();
    }

    $this->id = session_id();
	$this->set_cookie();

    # set the  mode for this run
    if ( isset($this->fallback_mode)
      && ("get" == $this->fallback_mode)
      && ("cookie" == $this->mode)
      && (! isset($_COOKIE[$this->name])) ) {
	      $this->mode = $this->fallback_mode;
      }


    $this->thaw();

    return $ok;
  } // end func start


  /**
   * Register callback function executed when session is reopened
   *
   * @param [callable] $callback
   */
  public function register_init_fn($callback){
    $this->on_init[] = $callback;
  }

  /**
   * Execute and register callback function executed when session is reopened
   *
   * @param [callable] $callback
   */
  public function register_and_call_init_fn($callback){
    $callback();
    $this->register_init_fn($callback);
  }

  public function reopen_session(){
    // workaround for the problem that session_start() creates multiple session cookies headers
    // Set "use_cookies" to zero shall disable creation of the cookie header
    ini_set ("session.use_cookies","0");
    session_start(['use_cookies' => 0]);

    foreach($this->on_init as $listener) $listener();
  }

  // CLose the session and release the session lock
  public function close_session(){
    session_write_close();
  }


  /**
   * Sets cookie if it is not set yet
   */
  function set_cookie(){
    if ("cookie" == $this->mode) {
      if ($this->lifetime > 0) $lifetime = time()+$this->lifetime*60;
      else $lifetime = 0;

      serwebSetCookie(
        $this->name,
        $this->id,
        [
          'expires' =>  $lifetime,
          'path' =>     $this->cookie_path,
          'domain' =>   $this->cookie_domain,
          'secure' =>   $this->cookie_secure,
          'httponly' => $this->cookie_httponly,
          'samesite' => $this->cookie_samesite,
        ]);
	  }
  }

  /**
   * Sets or returns the name of the current session
   *
   * @param  string  If given, sets the session name
   * @return string  session_name() return value
   * @access public
   */
  function name($name = '') {

    if ($name = (string)$name) {

      $this->name = $name;
      $ok = session_name($name);

    } else {

      $ok = session_name();

    }

    return $ok;
  } // end func name


  /**
   * Returns the session id for the current session.
   *
   * If id is specified, it will replace the current session id.
   *
   * @param  string  If given, sets the new session id
   * @return string  current session id
   * @access public
   */
  function id($sid = '') {

    if (!$sid)
      $sid = ("" == $this->cookiename) ? $this->classname : $this->cookiename;

    if ($sid = (string)$sid) {

      $this->id = $sid;
      $ok = session_id($sid);

    } else {

      $ok = session_id();

    }

    return $ok;
  } // end func id


  /**
   * @see id()
   * @deprec  $Id: session4.1.php,v 1.5 2007/02/14 16:46:31 kozlik Exp $
   * @access public
   */
  function get_id($sid = '') {
    return $this->id($sid);
  } // end func get_id


  /**
   * Register the variable(s) that should become persistent.
   *
   * @param   mixed String with the name of one or more variables seperated by comma
   *                 or a list of variables names: "foo"/"foo,bar,baz"/{"foo","bar","baz"}
   * @access public
   */
  function register ($var_names) {
    if (!is_array($var_names)) {
      // spaces spoil everything
      $var_names = trim($var_names);
      $var_names=explode(",", $var_names);
    }

    reset($var_names);
    while ( list(,$thing) = each($var_names) ) {
      $thing=trim($thing);
      if ( $thing ) {
        $this->pt[$thing] = true;
      }
    }
  } // end func register


  /**
   * see if a variable is registered in the current session
   *
   * @param  $name a string with the variable name
   * @return false if variable not registered true on success.
   * @access public
   */
  function is_registered($name) {
    if (isset($this->pt[$name]) && $this->pt[$name] == true)
      return true;
    return false;
  } // end func is_registered



  /**
   * Recall the session registration for named variable(s)
   *
   * @param	  mixed   String with the name of one or more variables seperated by comma
   *                   or a list of variables names: "foo"/"foo,bar,baz"/{"foo","bar","baz"}
   * @access public
   */
  function unregister($var_names) {
    if (!is_array($var_names)) {
      // spaces spoil everything
      $var_names = trim($var_names);
      $var_names=explode(",", $var_names);
    }

    reset($var_names);
    while (list(,$var_name) = each($var_names)) {
      $var_name = trim($var_name);
      if ($var_name) {
        unset($this->pt[$var_name]);
      }
    }
  } // end func unregister


  /**
   * Delete the cookie holding the session id.
   *
   * RFC: is this really needed? can we prune this function?
   * 		 the only reason to keep it is if one wants to also
   *		 unset the cookie when session_destroy()ing,which PHP
   *		 doesn't seem to do (looking @ the session.c:940)
   * uw: yes we should keep it to remain the same interface, but deprec.
   *
   * @deprec $Id: session4.1.php,v 1.5 2007/02/14 16:46:31 kozlik Exp $
   * @access public
   */
  function put_id() {
    global $_COOKIE;

    if (get_cfg_var ('session.use_cookies') == 1) {
      $cookie_params = session_get_cookie_params();
      serwebSetCookie(
        $this->name,
        '',
        [
          'expires' =>  0,
          'path' =>     $cookie_params['path'],
          'domain' =>   $cookie_params['domain'],
          'secure' =>   $cookie_params['secure'],
          'httponly' => $cookie_params['httponly'],
          'samesite' => $cookie_params['samesite'],
        ]);

      $_COOKIE[$this->name] = "";
    }

  } // end func put_id

  /**
   * Delete the current session destroying all registered data.
   *
   * Note that it does more but the PHP 4 session_destroy it also
   * throws away a cookie is there's one.
   *
   * @return boolean session_destroy return value
   * @access public
   */
  function delete() {

    $this->put_id();

    return session_destroy();
  } // end func delete


  /**
  * Helper function: returns $url concatenated with the current session id
  *
  * Don't use this function any more. Please use the PHP 4 build in
  * URL rewriting feature. This function is here only for compatibility reasons.
  *
  * @param	$url	  URL to which the session id will be appended
  * @return string  rewritten url with session id included
  * @see    $trans_id_enabled
  * @access public
  */
  function url($url) {
     global $_COOKIE;

    if ($this->trans_id_enabled)
      return $url;

    // Remove existing session info from url
    $url = preg_replace(
              "/([&?])".quotemeta(urlencode($this->name))."=(.)*(&|$)/",
              "\\1",
              $url
           ); # we clean any(also bogus) sess in url
    // Remove trailing ?/& if needed
    $url = preg_replace("/[&?]+$/", "", $url);

    switch ($this->mode) {
      case "get":
        $url .= ( strpos($url, "?") != false ?  "&" : "?" ).
                urlencode($this->name)."=".$this->id;
      break;
      default:
        ;
      break;
    }

    return $url;
  } // end func url


  /**
   * @see url()
   */
  function purl($url) {
    print $this->url($url);
  } // end func purl


  /**
   * Get current request URL.
   *
   * WARNING: I'm not sure with the $this->url() call. Can someone check it?
   * WARNING: Apache variable $REQUEST_URI used -
   * this it the best you can get but there's warranty the it's set beside
   * the Apache world.
   *
   * @return string
   * @access public
   */
  function self_url() {
    global $_SERVER;

    return $this->url($_SERVER["PHP_SELF"] .
      ((isset($_SERVER["QUERY_STRING"]) && ("" != $_SERVER["QUERY_STRING"]))
        ? "?" . $_SERVER["QUERY_STRING"] : ""));
    # return $this->url(getenv('REQUEST_URI'));
  } // end func self_url


  /**
   * Print the current URL
   * @return void
   */
  function pself_url() {
    print $this->self_url();
  } // end func pself_url


  /**
   * Stores session id in a hidden variable (part of a form).
   *
   * @return string
   * @access public
   */
  function get_hidden_session() {

    if ($this->trans_id_enabled)
      return "";
    else
      return sprintf('<input type="hidden" name="%s" value="%s">',
                    $this->name,
                    $this->id
      );

  } // end fun get_hidden_session


  /**
   * @see  get_hidden_session
   * @return   void
   */
  function hidden_session() {
    print $this->get_hidden_session();
  } // end func hidden_session



  /**
   * Prepend variables passed into an array to a query string.
   *
   * @param  array   $qarray an array with var=>val pairs
   * @param  string  $query_string probably getenv ('QUERY_STRING')
   * @return string  the resulting quetry string, of course :)
   * @access public
   */
  function add_query($qarray, $query_string = '') {

    ('' == $query_string) && ($query_string = getenv ('QUERY_STRING'));
    $qstring = $query_string . (strrpos ($query_string, '?') == false ? '?' : '&');

    foreach ($qarray as $var => $val) {
      $qstring .= sprintf ( '%s=%s&', $var, urlencode ($val)) ;
    }

    return $qstring;
  } // end func add_query


  /**
   * @see  add_query()
   */
  function padd_query ($qarray, $query_string = '') {
    print $this->add_query($qarray, $query_string);
  } // end func padd_query

  /**
   * appends a serialized representation of $$var
   * at the end of $str.
   *
   * To be able to serialize an object, the object must implement
   * a variable $classname (containing the name of the class as string)
   * and a variable $persistent_slots (containing the names of the slots
   * to be saved as an array of strings).
   *
   * @return string
   */
  function serialize($var, &$str) {
    static $t,$l,$k;

    ## Determine the type of $$var
    eval("\$t = gettype(\$$var);");
    switch ( $t ) {

      case "array":
        ## $$var is an array. Enumerate the elements and serialize them.
        eval("reset(\$$var); \$l = gettype(list(\$k)=each(\$$var));");
        $str .= "\$$var = array(); ";
        while ( "array" == $l ) {
          ## Structural recursion
          $this->serialize($var."['".preg_replace("/([\\'])/", "\\\\1", $k)."']", $str);
          eval("\$l = gettype(list(\$k)=each(\$$var));");
        }

      break;
      case "object":
        ## $$var is an object. Enumerate the slots and serialize them.
        eval("\$k = \$${var}->classname; \$l = reset(\$${var}->persistent_slots);");
        $str.="\$$var = new $k; ";
        while ( $l ) {
          ## Structural recursion.
          $this->serialize($var."->".$l, $str);
          eval("\$l = next(\$${var}->persistent_slots);");
        }

      break;

      case "integer":
      case "double":
      case "float":
        eval("\$l = \$$var;");
        $str.="\$$var = ".$l."; ";

      break;

      case "boolean":
        eval("\$l = \$$var;");
        $str.="\$$var = ".($l ? "TRUE" : "FALSE")."; ";
      break;

      case "NULL":
        $str.="\$$var = NULL; ";

      break;

      case "string":
      default:
        ## $$var is an atom. Extract it to $l, then generate code.
        eval("\$l = \$$var;");
        $str.="\$$var = '".preg_replace("/([\\'])/", "\\\\1", $l)."'; ";
      break;
    }
  } // end func serialze



  /**
   * freezes all registered things ( scalar variables, arrays, objects )
   * by saving all registered things to $_SESSION.
   *
   * @access public
   *
   *
   */
  function freeze() {
    $str="";

    $this->serialize("this->in", $str);
    $this->serialize("this->pt", $str);

    reset($this->pt);
    while ( list($thing) = each($this->pt) ) {
      $thing=trim($thing);
      if ( $thing ) {
        $this->serialize("GLOBALS['".$thing."']", $str);
      }
    }

    $_SESSION[$this->name] = $str;
  }

  /**
   * Reload frozen variables and microwave them.
   *
   */
  function thaw() {
	if (isset($_SESSION[$this->name])){
		eval(sprintf(";%s",$_SESSION[$this->name]));
	}

  }

  /**
   * ?
   *
   */
  function set_tokenname(){

      $this->name = ("" == $this->cookiename) ? $this->classname : $this->cookiename;
      session_name ($this->name);

      if (!$this->cookie_domain) {
        $this->cookie_domain = get_cfg_var ("session.cookie_domain");
      }

      if (!$this->cookie_path && get_cfg_var('session.cookie_path')) {
        $this->cookie_path = get_cfg_var('session.cookie_path');
      } elseif (!$this->cookie_path) {
        $this->cookie_path = "/";
      }

      if ($this->lifetime > 0) {
        $lifetime = time()+$this->lifetime*60;
      } else {
        $lifetime = 0;
      }

      session_set_cookie_params($lifetime, $this->cookie_path, $this->cookie_domain, $this->cookie_secure, $this->cookie_httponly);
  } // end func set_tokenname


  /**
   * ?
   *
   */
  function put_headers() {
    # set session.cache_limiter corresponding to $this->allowcache.

    switch ($this->allowcache) {

      case "passive":
      case "public":
        session_cache_limiter ("public");
        break;

      case "private":
        session_cache_limiter ("private");
        break;

      default:
        session_cache_limiter ("nocache");
        break;
    }
  } // end func put_headers


  /**
   * Reimport _GET into the global namespace previously overriden by session variables.
   * @see  reimport_post_vars(), reimport_cookie_vars()
   */
  function reimport_get_vars() {
    $this->reimport_any_vars("_GET");
  } // end func reimport_get_vars


  /**
   * Reimport _POST into the global namespace previously overriden by session variables.
   * @see  reimport_get_vars(), reimport_cookie_vars()
   */
  function reimport_post_vars() {
    $this->reimport_any_vars("_POST");
  } // end func reimport_post_vars


  /**
   * Reimport _COOKIE into the global namespace previously overriden by session variables.
   * @see  reimport_post_vars(), reimport_fwr_vars()
   */
  function reimport_cookie_vars() {
    $this->reimport_any_vars("_COOKIE");
  } // end func reimport_cookie_vars


  /**
   *
   * @var  array
   */
  function reimport_any_vars($arrayname) {
    global $$arrayname;
    $GLOBALS = array_merge ($GLOBALS, $$arrayname);
  } // end func reimport_any_vars


} // end func session
