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
  * Current session id.
  *
  * @var  string
  * @see  id(), Session()
  */
  protected $id = "";


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

  /**
   * Whether the session is started
   *
   * @var boolean
   */
  protected $active = false;

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

    $this->active = true;

    if ( $this->mode=="cookie"
        && $this->fallback_mode=="cookie")  {
      ini_set ("session.use_only_cookie","1");
    }

    $this->set_tokenname();
    $this->put_headers();

    if ( $this->mode=="cookie" and !isset($_COOKIE[$this->name])){
      $referer = "NO REFERER";
      if (!empty($_SERVER['HTTP_REFERER'])) $referer = $_SERVER['HTTP_REFERER'];
      sw_log(__CLASS__."::".__FUNCTION__."(): Session cookie is not set!!! New session will be started. Referer: $referer", PEAR_LOG_DEBUG);
    }

    $ok = true;
    // check whether the session is already started and
    // start the session if it is not
    if ("" === $session_id = session_id()){
      @$ok = session_start();
      $this->id = session_id();

      sw_log(__CLASS__."::".__FUNCTION__."(): New session started: $ok, Session ID: {$this->id}", PEAR_LOG_DEBUG);
    }
    else{
      $this->id = $session_id;
      sw_log(__CLASS__."::".__FUNCTION__."(): Session already exists. ID: {$this->id}", PEAR_LOG_DEBUG);
    }

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

    $this->active = true;

    foreach($this->on_init as $listener) $listener();
  }

  // CLose the session and release the session lock
  public function close_session(){
    $this->active = false;
    session_write_close();
  }

  public function is_active(){
    return $this->active;
  }

  /**
   * Sets cookie if it is not set yet
   */
  function set_cookie(){
    if ("cookie" == $this->mode) {
      if ($this->lifetime > 0) $lifetime = time()+$this->lifetime*60;
      else $lifetime = 0;

      sw_log(__CLASS__."::".__FUNCTION__."(): Setting cookie: '{$this->name}' to '{$this->id}'", PEAR_LOG_DEBUG);

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
   * @param  string       If given, sets the new session id
   * @return string|bool  current session id, alse on failure
   */
  public function id(?string $sid = null) {
    if ($sid) {
      if (!session_id($sid)) return false;

      $this->id = $sid;
      return $this->id;
    }
    else {
      return session_id();
    }
  }


  /**
   * @see id()
   * @deprec  $Id: session4.1.php,v 1.5 2007/02/14 16:46:31 kozlik Exp $
   * @access public
   */
  function get_id($sid = '') {
    return $this->id($sid);
  } // end func get_id


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
   * freezes all registered things ( scalar variables, arrays, objects )
   * by saving all registered things to $_SESSION.
   *
   * @access public
   *
   *
   */
  function freeze() {
  }

  /**
   * Reload frozen variables and microwave them.
   *
   */
  function thaw() {
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

} // end func session
