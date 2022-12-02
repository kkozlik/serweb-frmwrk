<?php
/**
 * Page controler
 *
 * @author     Karel Kozlik
 * @package    serweb
 * @subpackage framework
 */

/**
 *  Page controler
 *
 *  Page controler process input data, execute all registeret APU objects for
 *  an page a create the HTML output.
 *
 *  <b>Configuration:</b>
 *
 *  This class currently not do not use any options
 *
 *
 *  <b>Exported smarty variables:</b>
 *
 *  - <i>parameters</i>
 *    assigned to global variable $page_attributes
 *  - <i>lang_str</i>
 *    assigned to global variable $lang_str
 *  - <i>lang_set</i>
 *    assigned to global variable $lang_set
 *  - <i>come_from_admin_interface</i>
 *    assigned to {@link $come_from_admin_interface}
 *  - <i>cfg</i>
 *    assigned to variable $config holding configuration.
 *    Contain only following properties: img_src_path, js_src_path,
 *    style_src_path, user_pages_path, admin_pages_path, domains_path
 *  - <i>user_auth</i>
 *    assigned to variable {@link $user_id}
 *    which is associative array containing username, domain and uid
 *    of user loged in or of user which datails admin is examining
 *  - <i>form</i>
 *    contain elements of HTML form. Name of this variable could be changed.
 *    There also could be more variables containing HTML forms. Form more
 *    details see: {@link assign_form_name()}
 *    and {@link set_name_of_smarty_var_for_form()}
 *
 *
 * @package    serweb
 * @subpackage framework
 */

class page_controller{
    /** array of application units */
    var $apu_objects=array();
    /** file with smarty template */
    var $template_name="_default.tpl";
    /** flag which indicated that user come from admin interface */
    var $come_from_admin_interface=false;

    /** auth info of user with which setting we are working.
     *  Usualy is same as $_SESSION['auth']->get_logged_user(), only admin can change it
     */
    var $user_id = null;

    /** id of domain with which setting we are working.
     *  Only admin can change it
     */
    var $domain_id = null;

    /** associative array of controller options */
    protected $opt=array(
        'print_html_head'           => true,
        'print_html_body_begin'     => true,
        'print_html_body_end'       => true,
        'print_html_document_end'   => true,
    );
    /** array of html forms */
    var $f = array();
    /** flags which says if header 'location' will be send and if html form should be validated */
    var $send_header_location = false;
    /** flag if should be some html form validated */
    var $validate_html_form = false;
    /** flag if is used html form */
    var $shared_html_form = false;
    /** array contain required javascript files */
    var $required_javascript = array();
    /** flag - is timezone already set? */
    var $is_set_timezone = null;
    /** js which should be placed after document body */
    var $js_after_document = array();
    /** url to which header location will redirect - default is self */
    var $url_for_reload = null;
    /** array of GET parameters added to each URL created by page controler */
    var $global_get_params = array();
    /** flag determining if standard html output will be generated.
     *  Sometimes an APU need generate other output than HTML. In this case it
     *  should call method disable_html_output() and the smarty template will
     *  not be generated.
     *  It is recomended use this only if 'alone' flag of executed action is set.
     */
    var $standard_html_output = true;

    /** instance of Creg class
     *  for backward compatibility, shouldn't be used
     *  instead of this use your own variable and Creg::singleton method
     *  @deprec
     */
    var $reg;

    /** flag if the check to permissions to user should be performed */
    var $check_perms_to_user = false;
    /** flag if the check to permissions to domain should be performed */
    var $check_perms_to_domain = false;
    /** obsoleted */
    var $errors=array();
    /** info messages */
    var $messages=array();
    /** list of interapu vars */
    var $interapu_vars = array();
    /** reference to container inside session variable */
    protected $session;
    /** post init function */
    var $post_init = null;

    /** listeners of events and their priorities */
    private $listeners = array();

    private $nonce = null;

    /* constructor */
    function __construct(){

        $this->reg = Creg::singleton();             // create regular expressions class

        $eh = &ErrorHandler::singleton();
        $this->errors = clone_array($eh->get_errors_array());
        $eh -> set_errors_ref($this->errors);

        if (PHPlib::$session) PHPlib::$session->register_and_call_init_fn([$this, "session_init"]);
        $this->messages_from_get_param();
        $this->init_this_uid_and_did();
        $this->set_interapu_vars();

        $this->set_timezone();

    }

    /**
     * Attach an event listener.
     * As of now following events are supported:
     *
     *  - pre_init                  - before init() methods of APUs are executed
     *  - post_init                 - after init() methods of APUs are executed
     *  - post_determine_actions
     *  - post_create_html_form
     *  - post_validate
     *  - pre_execute               - before actions are executed (only if validation succeeded)
     *  - post_execute              - after actions are executed (only if validation succeeded)
     *  - pre_invalid               - before form_invalid methods of APU are executed (only if validation failed)
     *  - post_invalid              - after form_invalid methods of APU are executed (only if validation failed)
     *  - pre_form_smarty           - before form is assigned to smarty variable
     *  - post_form_smarty          - after form is assigned to smarty variable
     *  - pre_html_output
     *  - post_html_output
     *
     * The callback function shall accept one parameter of the page_controller_event object
     *
     * @param string   $event_name
     * @param callback $callback
     * @param integer  $priority
     * @return void
     */
    public function attach_listener($event_name, $callback, $priority=50)
    {
        $this->listeners[$event_name][] = array("priority" => $priority,
                                                "callback" => $callback);
    }

    /**
     * Trigger event of given name.
     * This function execute all listeners of given event in their priority order.
     *
     * @param string $event_name
     * @return void
     */
    public function trigger_event($event_name)
    {
        if (empty($this->listeners[$event_name])) return;

        $listeners = $this->listeners[$event_name];

        usort($listeners, function($a, $b){
            if ($a['priority'] == $b['priority']) return 0;
            return ($a['priority'] < $b['priority']) ? -1 : 1;
        });

        $event = new page_controller_event($event_name);
        $event->controller = $this;

        foreach($listeners as $listener){
            $listener["callback"]($event);
            if ($event->stop_propagation) break;
        }
    }


    /**
     *  This function is obsoleted. All errors from ErrorHandler are transfered
     *  via session and GET params when $this->reload() function is executed
     *
     *  @obsoleted since 2014-04-28
     */
    function errors_to_get_array(){
        return array();
    }

    /**
     *  Take info/error messages from session variable specified by GET param
     *  "pctl_msg_id" and put them into $this->messages array and into error_handler
     */
    private function messages_from_get_param(){

        if (isset($_GET['pctl_msg_id'])){
            $msg_id = $_GET['pctl_msg_id'];

            if (isset($this->session['messages'][$msg_id])){
                if (!empty($this->session['messages'][$msg_id]['info'])){
                    $this->messages = array_merge($this->messages,
                                                  $this->session['messages'][$msg_id]['info']);
                }

                if (!empty($this->session['messages'][$msg_id]['err'])){
                    foreach($this->session['messages'][$msg_id]['err'] as $err){
                        ErrorHandler::add_error($err);
                    }
                }

                unset($this->session['messages'][$msg_id]);
                unset($this->session['messages_time'][$msg_id]);
            }
        }

        if (isset($this->session['messages_time'])){
            $time = time() - 300; //delete session messages older then 5 min
            foreach($this->session['messages_time'] as $msg_id => $msg_time){
                if ($msg_time < $time){
                    unset($this->session['messages'][$msg_id]);
                    unset($this->session['messages_time'][$msg_id]);
                }
            }
        }
    }

    /**
     *  This function is obsoleted. All messages in $this->message array are transfered
     *  via session and GET params when $this->reload() function is executed
     *
     *  @obsoleted since 2014-04-28
     */
    function message_to_get_param($msg){
        $this->add_message($msg);
        return "";
    }

    function session_init(){

        /* create container in session variable if does not exists */
        if (!isset($_SESSION['page_ctl'])) $_SESSION['page_ctl'] = array();

        $this->session = &$_SESSION['page_ctl'];
    }

    public function get_nonce(){
        if (is_null($this->nonce)){
            $this->nonce = sha1(rfc4122_uuid());
        }

        return $this->nonce;
    }

    /**
     *  Initialy set $this->user_id and $this->domain_id
     */
    function init_this_uid_and_did(){
        global $perm;

        // get $user_id if admin want work with some setting of user
        if (isset($perm) and $perm->have_perm("admin")){
            $this->init_this_uid();
            $this->init_this_did();
        }
        else {
            if (!empty($_SESSION['auth']))
                $this->user_id = $_SESSION['auth']->get_logged_user();
            else
                $this->user_id = null;
        }
    }

    /**
     *  Initialy set $this->user_id
     */
    function init_this_uid(){

        //first try get user_id from session variable
        if (isset($_SESSION['page_controller_user_id'])){
            $this->user_id = $_SESSION['page_controller_user_id'];
            $this->come_from_admin_interface=true;
        }

        //second if userauth param is given, get user_id from it
        if (!empty($_GET[$this->ch_user_param_name()])) {
            $serwebUserClass = "SerwebUser";

            // if 'auth' object exists, get name "SerwebUser" class from this object
            if (isset($_SESSION['auth']) and is_a($_SESSION['auth'], "Auth")) {
                $serwebUserClass = $_SESSION['auth']::$user_class;
            }

            $uid = &call_user_func(array($serwebUserClass, 'recreate_from_get_param'),
                                   $_GET[$this->ch_user_param_name()]);

            if (is_a($uid, 'SerwebUser')){
                $this->check_perms_to_user = true;

                $this->user_id = $_SESSION['page_controller_user_id'] = $uid;
                $this->come_from_admin_interface=true;
            }
        }

        //if still user_id is null, get it from $_SESSION['auth'] object
        if (is_null($this->user_id) and isset($_SESSION['auth']) and is_a($_SESSION['auth'], "Auth"))
            $this->user_id=$_SESSION['auth']->get_logged_user();
    }

    /**
     *  Initialy set $this->domain_id
     */
    function init_this_did(){

        /* get id of administrated domain */
        //first try get domain id from session variable
        if (isset($_SESSION['page_controller_domain_id'])){
            $this->domain_id = $_SESSION['page_controller_domain_id'];
        }

        //second if domain_id param is given, get domain id from it
        if (isset($_GET['pc_domain_id'])){
            $this->check_perms_to_domain = true;

            $this->set_domain_id($_GET['pc_domain_id']);
        }
    }


    /**
     *  Return name of get param used to change user
     *
     *  @return string
     */
    function ch_user_param_name(){
        return "ctl_user";
    }

    /**
     *  return string which can be used as $_GET param containing id of domain
     *
     *  @param  string $domain_id       id of domain
     *  @return string                  $_GET param
     */
    function domain_to_get_param($domain_id){
        return "pc_domain_id=".RawURLEncode($domain_id);
    }

    /**
     *  set $this->domain_id and session variable to given value
     *
     *  @param  string $domain_id       id of domain
     */
    function set_domain_id($domain_id){
        $this->domain_id = $_SESSION['page_controller_domain_id'] = $domain_id;
    }

    /**
     *  set $this->user_id and session variable to given value
     *
     *  @param  SerwebUser $user_id
     */
    function set_user_id($user_id){
        $this->user_id = $_SESSION['page_controller_user_id'] = $user_id;
    }

    /**
     * return GET param which set interapu variable named $name to given $value
     * @return string
     */
    function get_interapu_url_param($name, $value){
        return "pctlia_".$name."=".RawURLEncode($value);
    }

    /**
     * return value of interapu variable
     * @return mixed
     */
    function get_interapu_var($name){
        if (isset($this->session['interapu'][$name])) return $this->session['interapu'][$name];
        else return null;
    }

    /**
     * set value of interapu variable
     * @access private
     */
    function set_interapu_var($name, $value){
        $this->session['interapu'][$name] = $value;
    }

    /**
     * set interapu variables by GET params
     * @access private
     */
    function set_interapu_vars(){
        foreach ($this->interapu_vars as $v){
            if (isset($_GET['pctlia_'.$v])) $this->session['interapu'][$v] = $_GET['pctlia_'.$v];
        }
    }

    /**
     * return required data layer methods - static class
     * @static
     */
    function get_required_data_layer_methods(){
        return array();
    }

    /* add application unit to $apu_objects array*/
    function add_apu(&$class){
        if (!is_a($class, "apu_base_class"))
            die(__FILE__.":".__LINE__." - given class is not instance of apu_base_class");

        $this->apu_objects[] = &$class;
    }

    /* remove application unit from $apu_objects array*/
    function del_apu(&$class){
        if (!is_a($class, "apu_base_class"))
            die(__FILE__.":".__LINE__." - given class is not instance of apu_base_class");

        foreach($this->apu_objects as $k=>$v){
            if ($this->apu_objects[$k]->get_instance_id() == $class->get_instance_id()) {
                unset($this->apu_objects[$k]);
                return;
            }
        }
    }

    /* set nam1e of template */
    function set_template_name($template){
        $this->template_name = $template;
    }

    /* set option $opt_name to value $val */
    function set_opt($opt_name, $val){
        $this->opt[$opt_name]=$val;
    }

    /* set function called after all apu are initialized */
    function set_post_init_func($func_name){
        $this->post_init = $func_name;
    }

    /**
     *  add massage which will be displayed on page
     *
     *  @param array $msg   message - associative array with keys 'short' and 'long'
     */
    function add_message($msg){
        $this->messages[] = &$msg;
    }

    /**
     *  set timezone which is used by date/time formating function to timezone
     *  of user
     *
     *  @param string $uid  user to which timezone should be set - if not given $this->user_id is used
     */
    function set_timezone($uid = null){
        global $config;

        // if $uid is not provided and a user is logged, set the $uid by the user
        if (is_null($uid) and $this->user_id) $uid = $this->user_id->get_uid();

        if (is_null($uid)){
            if ($config->timezone){
                // if we still do not have $uid and default timezone is set in the config use it.
                date_default_timezone_set($config->timezone);
                return true;
            }
            else{
                // We do not have $uid and no timezone is not set in config file.
                return false;
            }
        }

        // get the timezone from user attributes
        $an = &$config->attr_names;

        /* if timezone is already set for this user, do not set it again */
        if (is_null($this->is_set_timezone) or $this->is_set_timezone != $uid){

            if ($config->get_user_timezone_fn){
                $tz = call_user_func($config->get_user_timezone_fn, $uid);

                if ($tz){
                    date_default_timezone_set($tz);
                    $this->is_set_timezone = $uid;
                }
            }
        }

        return true;
    }

    function set_onload_js($js){
        $this->js_after_document[]=$js;
    }

    /**
     *  Do not check if admin have perms to manage user
     *  @access public
     */
    function do_not_check_perms_of_admin(){
        $this->check_perms_to_user = false;
    }

    /**
     *  Change url to which browser will be redirected by location header
     *
     *  Important: this method should be called _ONLY_ from methods action_*()
     *  of APU
     *
     *  @param string $url
     */
    function change_url_for_reload($url){
        $this->url_for_reload = $url;
    }

    /**
     *  Add GET parameter to each URL created by page controller
     *
     *  @param string $name     name of GET parameter
     *  @param string $value    value of GET parameter
     */
    function add_get_param($name, $value){
        $this->global_get_params[$name] = $value;
    }

    /**
     *  Set GET parameter to add to each URL created by page controller.
     *  If GET parameter with given name exists, it is added to all URLs.
     *
     *  @param string $name     name of GET parameter
     */
    function set_get_param($name){
        if (isset($_GET[$name])){
            $this->global_get_params[$name] = $_GET[$name];
        }
    }

    /**
     *  Unset GET parameter to be added to each URL created by page controller.
     *
     *  @param string $name     name of GET parameter
     */
    function unset_get_param($name){
        unset($this->global_get_params[$name]);
    }

    /**
     *  Return $this->global_get_params as array of strings. The array fields
     *  are in form "foo=bar"
     *
     *  @return array
     */
    function global_get_params_to_str_array(){
        $str = array();
        foreach($this->global_get_params as $k=>$v){
            $str[] = RawURLEncode($k)."=".RawURLEncode($v);
        }
        return $str;
    }

    /**
     *  Format URL - enhance it of global GET params, session ID if needed and
     *  unique identifier.
     *
     *  @param  string  $url
     *  @param  bool    $unique     Make the URL unique - add some random param
     *  @return string
     */
    function url($url, $unique = true){
        global $sess;

        /* collect all get params to one string */
        $get_param = implode('&', $this->global_get_params_to_str_array());

        $param_separator = strpos($url, "?") !== false ?  "&" : "?";

        if ($unique){
            $url .= $param_separator."kvrk=".uniqID("").
                    ($get_param ? '&'.$get_param : '');
        }
        else{
            $url .= ($get_param ? $param_separator.$get_param : '');
        }

        if ($sess instanceof Session) return $sess->url($url);
        else return $url;
    }

    /**
     *  Disabling generation of HTML output
     *
     *  Sometimes an APU need generate other output than HTML. In this case it
     *  should call this method and the smarty template will not be generated.
     *
     *  It is recomended use this only if 'alone' flag of executed action is set.
     */
    function disable_html_output(){
        $this->standard_html_output = false;
    }

    /**
     *  Add file to set of required javascript files
     *
     *  @param string $file name of javascript file
     */
    function add_required_javascript($file){
        $this->required_javascript[] = $file;
    }

    /**
     *  Same as add_required_javascript() function. This function was here
     *  for a long time with typo in its name. So leaving it here for backward
     *  compatibility, but obsoleted now.
     *
     *  @obsoleted by add_required_javascript()
     */
    function add_reqired_javascript($file){
        $this->add_required_javascript($file);
    }

    /**
     * Format javascript URL "module:MODULENAME:FILE" into URL using get_js.php script
     *
     * @param string $url
     * @return string
     */
    public function get_js_module_url($url){
        if (preg_match("/^module:(?P<module>[-_0-9a-z]+):(?P<file>.+)$/i",
                        $url, $matches)){

            $url = $this->js_from_mod_getter($matches['module'], $matches['file']);
        }
        return $url;
    }

    /**
     * Postprocess list of required javascript:
     *  - format the module related URLS
     *  - make it unique
     *
     * @param array $js_files
     * @return array
     */
    public function postprocess_js_url_list($js_files){
        global $config;

        foreach($js_files as &$file){
            $file = $this->get_js_module_url($file);
            $file = $config->js_src_path.$file;
        }
        return array_unique($js_files);
    }

    /**
     *  Return URL that access given javascript file to given module.
     *  Module directories are not usualy accessible via html directory tree.
     *  So there is getter script inside the javascript directory that access the
     *  javascript file and return its content to HTML browser.
     *
     *  In rare cases this might not work. I.e. webserwer from webmin execute
     *  only files with .cgi extension. To solve it this method could be
     *  overriden with another one that will return URL to customized getter
     *  script.
     *
     *  @param  string  $module     The module from which we require a javascript file
     *  @param  string  $file       The filename of the required file
     */
    function js_from_mod_getter($module, $file){
        return "core/get_js.php?mod=".RawURLEncode($module).
                               "&js=".RawURLEncode($file);
    }

    /**
     *  Return URL that access given css file from templates directory.
     *  Templates directory is not usualy accessible via html directory tree.
     *  So there is getter script inside the styles directory that access the
     *  css file and return its content to HTML browser.
     *
     *  In rare cases this might not work. I.e. webserwer from webmin execute
     *  only files with .cgi extension. To solve it this method could be
     *  overriden with another one that will return URL to customized getter
     *  script.
     *
     *  @param  string  $file       The filename of the required file with path
     *                              relatively to templates directory
     */
    function css_from_tpl_getter($file){
        global $config;
        return $config->style_src_path."core/get_css.php?css=".RawURLEncode($file);
    }


    /**
     *  Create new shared html form if still not exists and assign APU to it
     *
     *  @param string $form_name    name of html form
     *  @param object $apu          instance of APU
     */
    function assign_form_name($form_name, &$apu){
        global $lang_str, $config;
        /* we are useing shared html forms */
        $this->shared_html_form = true;
        /* if form of this name still not exists, create initial values */
        if (!isset($this->f[$form_name])){
            $this->f[$form_name]['validate'] = false;                   // should be this form validated?
            $this->f[$form_name]['submit'] = array('type' => 'button',   // submit element
                                                    'text' => $lang_str['b_submit']);

            $this->f[$form_name]['smarty_name'] = 'form_'.$form_name;   // name of smarty variable
            $this->f[$form_name]['form'] = new OohForm();              // form object
            $this->f[$form_name]['apu_names'] = array();                // set of apu names added to hidden form element of this form
            $this->f[$form_name]['js_before'] = "";
            $this->f[$form_name]['js_after'] = "";
            $this->f[$form_name]['get_param'] = array();
            $this->f[$form_name]['msg_apu'] = array($apu->opt['instance_id']);  //APUs from which are messages displayed - default first APU assigned to this form
        }
        $this->f[$form_name]['apu'][] = &$apu;
        $apu->form_name = $form_name;
    }

    /**
     *  Change name of smarty variable used for html form
     *
     *  @param string $form_name    name of existing html form
     *  @param string $smarty_var   name of smarty variable - default value is form_&lt;$form_name&gt;
     *  @return bool                FALSE if form with given name still not exists, TRUE otherwise
     */
    function set_name_of_smarty_var_for_form($form_name, $smarty_var){
        if (!isset($this->f[$form_name])){
            sw_log("Form with name '".$form_name."' is not set. Use method assign_form_name() first.", PEAR_LOG_DEBUG);
            return false;
        }

        $this->f[$form_name]['smarty_name'] = $smarty_var;
        return true;
    }

    /**
     *  Change submit element for html form
     *
     *  Example creating submit element:
     *  <code>
     *      array('type' => 'image',
     *            'text' => $lang_str['b_submit'],
     *            'src'  => get_path_to_buttons("btn_submit.gif", $_SESSION['lang']))
     *  </code>
     *
     *  @param string $form_name    name of existing html form
     *  @param array  $submit       assotiative array describe submit element of shared form. For details see description of method add_submit in class OohForm
     *  @return bool                FALSE if form with given name still not exists, TRUE otherwise
     */
    function set_submit_for_form($form_name, $submit){
        if (!isset($this->f[$form_name])){
            sw_log("Form with name '".$form_name."' is not set. Use method assign_form_name() first.", PEAR_LOG_DEBUG);
            return false;
        }

        $this->f[$form_name]['submit'] = $submit;
        return true;
    }

    /**
     *  Select APUs from which will be displayed messages
     *
     *  @param string $form_name    name of existing html form
     *  @param array $apu_id        array of instance_ids of APUs from which may be displayed messages
     *  @return bool                FALSE if form with given name still not exists, TRUE otherwise
     */
    function set_apu_for_msgs($form_name, $apu_id){
        if (!isset($this->f[$form_name])){
            sw_log("Form with name '".$form_name."' is not set. Use method assign_form_name() first.", PEAR_LOG_DEBUG);
            return false;
        }

        $this->f[$form_name]['msg_apu'] = $apu_id;
        return true;
    }


    /**
     *  Do a reload of current page
     *
     *  This function send header "Location" and finish execution of script
     *
     *  @param  array   $get_param      array of GET parameters send in the URL
     *  @return none                    this function finish execution of script
     */
    function reload($get_param=array()){
        global $sess;

        $errors = ErrorHandler::get_errors_array();

        if ($this->messages or $errors){
            $msg_id = uniqID();
            $this->session['messages'][$msg_id]['info'] = $this->messages;
            $this->session['messages'][$msg_id]['err'] = $errors;
            $this->session['messages_time'][$msg_id] = time();
            $get_param[] = "pctl_msg_id=".RawURLEncode($msg_id);
        }

        /* collect all get params to one string */
        $get_param = implode('&', array_merge($this->global_get_params_to_str_array(), $get_param));

        /* send header */
        if (!$this->url_for_reload) $this->url_for_reload = $_SERVER['PHP_SELF'];

        $param_separator = strpos($this->url_for_reload, "?") !== false ?  "&" : "?";

        $url = $this->url_for_reload.$param_separator."kvrk=".uniqID("").
                            ($get_param ? '&'.$get_param : '');

        if ($sess instanceof Session)   Header("Location: ".$sess->url($url));
        else                            Header("Location: ".$url);

        /* break the script execution */
        page_close();
        exit;
    }


    /**
     *  Redirect browser to new URL and exit the script execution
     *
     *  @param  string  $url            URL for redirect
     *  @param  array   $get_param      array of GET parameters send in the URL
     *  @return none                    this function finish execution of script
     */
    function redirect($url, $get_params = array()){
        $this->url_for_reload = $url;
        $this->reload($get_params);
    }

    /**
     *  determine actions of all application units
     *  and check if some APU needs validate form or send header 'location'
     *
     *  @access private
     */
    function _determine_actions(){
        $this->send_header_location=false;
        $this->validate_html_form=false;

        foreach($this->apu_objects as $key=>$val){
            $this->apu_objects[$key]->determine_action();

            if (isset($this->apu_objects[$key]->action['reload']) and $this->apu_objects[$key]->action['reload'])
                $this->send_header_location=true;
            if (isset($this->apu_objects[$key]->action['validate_form']) and $this->apu_objects[$key]->action['validate_form']){
                $this->validate_html_form=true;

                if ($this->shared_html_form) {
                    $this->f[$val->form_name]['validate'] = true;
                }
            }

            /* if should be this APU processed alone */
            if (isset($this->apu_objects[$key]->action['alone']) and $this->apu_objects[$key]->action['alone']){
                /* set send_header_location and validate_html_form flags according to action array of this apu */
                $this->send_header_location = isset($this->apu_objects[$key]->action['reload'])?$this->apu_objects[$key]->action['reload']:false;
                $this->validate_html_form = isset($this->apu_objects[$key]->action['validate_form'])?$this->apu_objects[$key]->action['validate_form']:false;

                /* set validate flag to all forms false */
                if ($this->shared_html_form) {
                    foreach($this->f as $fk => $fv){
                        $this->f[$fk]['validate'] = false;
                    }
                    /* only to form vith this apu set validate flag according to action['validate_form'] */
                    $this->f[$val->form_name]['validate'] = $this->validate_html_form;
                }

                /* save this APU */
                $temp = &$this->apu_objects[$key];
                /* unset all other APUs */
                $this->apu_objects = array();
                $this->add_apu($temp);

                break;
            }
        }
    }

    /**
     *  call post_determine_action method for each APU
     *
     *  @access private
     */
    function _post_determine_actions(){
        foreach($this->apu_objects as $key=>$val){
            $this->apu_objects[$key]->post_determine_action();
        }
    }

    /**
     *  create html form by all application units
     *
     *  @access private
     */
    function _create_html_form(){
        foreach($this->apu_objects as $key=>$val){
            $this->apu_objects[$key]->create_html_form(ErrorHandler::get_errors_array());
        }

        if ($this->shared_html_form) {
            foreach($this->f as $key => $val){
                $this->f[$key]['form']->add_element(array("type"=>"hidden",
                                         "name"=>"apu_name",
                                         "multiple"=>true,
                                         "value"=>$this->f[$key]['apu_names']));

                $this->f[$key]['form']->add_submit($this->f[$key]['submit']);
            }
        }
    }

    /**
     *  validate html form
     *
     *  @access private
     */
    function _validate_html_form(){

        if ($this->validate_html_form){

            /* if is used shared html form, valk throught all forms and validate them */
            if ($this->shared_html_form) {
                foreach($this->f as $key=>$val){
                    if ($val['validate']){
                        if ($err = $this->f[$key]['form']->validate()) {            // Is the data valid?
                            foreach($err as $e) ErrorHandler::add_error($e);        // No!
                            return false;
                        }
                    }
                }
            }

            /* validate html form by all application units */
            foreach($this->apu_objects as $key=>$val){
                if (isset($this->apu_objects[$key]->action['validate_form']) and $this->apu_objects[$key]->action['validate_form']){
                    if (false === $this->apu_objects[$key]->validate_form(ErrorHandler::get_errors_array())) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     *  call form_invalid method of each apu
     *
     *  @access private
     */
    function _form_invalid(){
        foreach($this->apu_objects as $key=>$val){
            $this->apu_objects[$key]->form_invalid();
        }
    }

    /**
     *  load default values to form
     *
     *  @access private
     */
    function _form_load_defaults(){
        /* if is used shared html form, load defaults to forms which were submited */
        if ($this->shared_html_form) {
            foreach($this->f as $key=>$val){
                if (isset($_POST['apu_name']) and
                    count(array_intersect($_POST['apu_name'], $this->f[$key]['apu_names']))){

                    $this->f[$key]['form']->load_defaults();
                }
            }
        }
        /* otherwise load defaults to form of APU which form has been submited*/
        else{
            foreach($this->apu_objects as $key=>$val){
                if ($val->was_form_submited()) $this->apu_objects[$key]->f->load_defaults();
            }
        }

    }

    /**
     *  execute actions of all application units
     *
     *  @access private
     */
    function _execute_actions(){

        $this->trigger_event("pre_execute");

        $send_get_param = array();
        foreach($this->apu_objects as $key=>$val){
            /* if location header will be send, skip APUs which action['reload']
               is not set - this APUs doesn't made any DB update */
            if ($this->send_header_location and
                 !(isset($this->apu_objects[$key]->action['reload']) and $this->apu_objects[$key]->action['reload']))
               continue;

            /* call the action method */
            $_apu = &$this->apu_objects[$key];
            $_method = "action_".$this->apu_objects[$key]->action['action'];
            $err_ref = ErrorHandler::get_errors_array();
            $_retval = call_user_func_array(array(&$_apu, $_method), array(&$err_ref));

            /* check for the error */
            if (false === $_retval) return false;

            /* join GET parameters that will be send */
            if (is_array($_retval)) $send_get_param = array_merge($send_get_param, $_retval);
        }


        $this->trigger_event("post_execute");


        /* if header location should be send */
        if ($this->send_header_location){
            /* send header and break the script execution */
            $this->reload($send_get_param);
        }

        /* if standard html output should not be generated */
        if (!$this->standard_html_output){
            /* break the script execution */
            page_close();
            exit;
        }

        return true;
    }

    /**
     *  Return URL used in html form 'action' param
     *
     *  The URL always paints to self page
     *
     *  @param  array   $get_param      array of GET parameters send in the URL
     *  @return string                  the URL
     */
    function get_form_action($get_param){

        $url = $_SERVER['PHP_SELF'];

        $get_param = array_merge($this->global_get_params_to_str_array(), $get_param);

        if ($get_param){
            /* collect all get params to one string */
            $get_param = implode('&', $get_param);

            $param_separator = strpos($url, "?") !== false ?  "&" : "?";
            $url .= $param_separator.$get_param;
        }

        return $url;
    }

    /**
     *  assign values and form(s) to smarty
     *
     *  @access private
     */
    function _smarty_assign(){
        global $smarty;

        /** assign values to smarty **/
        foreach($this->apu_objects as $key=>$val){
            $this->apu_objects[$key]->pass_values_to_html();
        }

        /** assign html form(s) to smarty **/
        $js_before = "";
        $js_after  = "";
        foreach($this->apu_objects as $key=>$val){
            $form_array = $this->apu_objects[$key]->pass_form_to_html();

            /* if this APU didn't use html form, skip it */
            if ($form_array === false) continue;

            if (!isset($form_array['smarty_name'])) $form_array['smarty_name'] = '';
            if (!isset($form_array['form_name']))   $form_array['form_name'] = '';
            if (!isset($form_array['before']))      $form_array['before'] = '';
            if (!isset($form_array['after']))       $form_array['after'] = '';
            if (!isset($form_array['get_param']))   $form_array['get_param'] = array();

            /* if html form is shared, collect after and before javascript from all APUs */
            if ($this->shared_html_form){
                $this->f[$val->form_name]['js_before'] .= $form_array['before'];
                $this->f[$val->form_name]['js_after'] .= $form_array['after'];
                $this->f[$val->form_name]['get_param'] =
                            array_merge($this->f[$val->form_name]['get_param'],
                                        $form_array['get_param']);
            }
            /* otherwise create forms for all APUs */
            else {
                $smarty->assign_phplib_form($form_array['smarty_name'],
                                            $this->apu_objects[$key]->f,
                                            array('jvs_name'  => 'form_'.$this->apu_objects[$key]->opt['instance_id'],
                                                  'form_name' => $form_array['form_name'],
                                                  'action'    => $this->get_form_action($form_array['get_param'])),
                                            array('before'    => $form_array['before'],
                                                  'after'     => $form_array['after']));
            }
        }

        /* if html form is shared, create it */
        if ($this->shared_html_form){
            foreach($this->f as $key=>$val){
                $smarty->assign_phplib_form($val['smarty_name'],
                                            $this->f[$key]['form'],
                                            array('jvs_name'  => 'form_'.$key,
                                                  'form_name' => $key,
                                                  'action'    => $this->get_form_action($this->f[$key]['get_param'])),
                                            array('before'    => $this->f[$key]['js_before'],
                                                  'after'     => $this->f[$key]['js_after']));
            }

        }
    }

    function _get_messages(){
        $apu_for_display_mesgs = array();

        // if useing shared html form, make list of APUs from which will be displayed messages
        if ($this->shared_html_form){
            foreach($this->f as $k => $v){
                $apu_for_display_mesgs = array_merge($apu_for_display_mesgs, $v['msg_apu']);
            }
            $apu_for_display_mesgs = array_unique($apu_for_display_mesgs);
        }

        //walk trought all APUs
        foreach($this->apu_objects as $key=>$val){
            $tmp_arr = array();
            // get messages from each APU
            $this->apu_objects[$key]->return_messages($tmp_arr);

            // assign message to output only if shared html form isn't used
            // or if APU is among ones in $apu_for_display_mesgs
            if(!$this->shared_html_form or
                in_array($val->opt['instance_id'], $apu_for_display_mesgs)){
                $this->messages = array_merge($this->messages, $tmp_arr);
            }
        }
    }

    /**
     *  Check if admin has permissions to manage the user
     *
     *  This method should be redefined in a child if needed
     *
     *  @return bool
     */
    function check_perms_to_user(){
        return true;
    }

    /**
     *  Check if admin has permissions to manage the domain
     *
     *  This method should be redefined in a child if needed
     *
     *  @return bool
     */
    function check_perms_to_domain(){
        return true;
    }

    /*****************  start processing of page *******************/
    function start(){
        global $smarty, $lang_str, $lang_set, $page_attributes, $config;

        try{
            /* check if admin have perms to manage user */
            if ($this->check_perms_to_user){
                if (! $this->check_perms_to_user()){
                    page_close();
                    die("You haven't permissions to manage user '".$this->user_id->get_username()."@".$this->user_id->get_realm()."'");
                }
            }

            /* check if admin have perms to manage domain */
            if ($this->check_perms_to_domain){
                if (! $this->check_perms_to_domain()){
                    page_close();
                    die("You haven't permissions to manage domain with id:'".$this->domain_id."'");
                }
            }

            /* do not allow change parameters of default domain */
            if ($this->domain_id == '0'){
                $_SESSION['page_controller_domain_id'] = null;
                page_close();
                die("Change parameters of default domain is not possible");
            }

            /* propagate user_id and reference to this to all application units */
            foreach($this->apu_objects as $key=>$val){
                $this->apu_objects[$key]->user_id=$this->user_id;
                $this->apu_objects[$key]->domain_id=$this->domain_id;
                $this->apu_objects[$key]->controler=&$this;
            }

            $this->trigger_event("pre_init");

            /* run all init methods */
            foreach($this->apu_objects as $key=>$val){
                $this->apu_objects[$key]->init();
            }

            if (!empty($this->post_init)) call_user_func_array($this->post_init, array(&$this));
            $this->trigger_event("post_init");

            /* determine actions of all application units
               and check if some APU needs validate form or send header 'location'
             */
            $this->_determine_actions();

            /* call post_determine_action methods for each APU */
            $this->_post_determine_actions();
            $this->trigger_event("post_determine_actions");

            /* create html form by all application units */
            $this->_create_html_form();
            $this->trigger_event("post_create_html_form");

            /* validate html form */
            $form_valid = $this->_validate_html_form();
            $this->trigger_event("post_validate");

            /* if form(s) valid, execute actions of all application units */
            if ($form_valid)
                $this->_execute_actions();
            /* otherwise load defaults to the form(s) */
            else {
                $this->trigger_event("pre_invalid");
                $this->_form_invalid();
                $this->_form_load_defaults();
                $this->trigger_event("post_invalid");
            }

            /** get messages **/
            $this->_get_messages();

            /** assign values and form(s) to smarty **/
            $this->_smarty_assign();

            if (is_object($this->user_id)){
                $smarty->assign('user_auth', $this->user_id->to_smarty());
            }


            $cfg=new stdclass();
            $cfg->img_src_path =        $config->img_src_path;
            $cfg->js_src_path =         $config->js_src_path;
            $cfg->style_src_path =      $config->style_src_path;
            $cfg->user_pages_path =     $config->user_pages_path;
            $cfg->admin_pages_path =    $config->admin_pages_path;
            $cfg->domains_path =        $config->domains_path;
            $smarty->assign("cfg", $cfg);


            $errors = ErrorHandler::get_errors_array();
            $page_attributes['errors']=&$errors;
            $page_attributes['message']=&$this->messages;
            $page_attributes['nonce'] = $this->get_nonce();

            $this->trigger_event("pre_html_output");

            /* obtain list of required javascripts */
            foreach($this->apu_objects as $val){
                $this->required_javascript = array_merge($this->required_javascript, $val->get_required_javascript());
            }

            $js_files = array();
            if (isset($page_attributes['required_javascript']) and
                is_array($page_attributes['required_javascript'])){

                $js_files = $page_attributes['required_javascript'];
            }
            $js_files = array_merge($js_files, $this->required_javascript);

            $page_attributes['required_javascript'] = $this->postprocess_js_url_list($js_files);


            $smarty->assign('parameters', $page_attributes);
            $smarty->assign('lang_str', $lang_str);
            $smarty->assign('lang_set', $lang_set);
            $smarty->assign('come_from_admin_interface', $this->come_from_admin_interface);

            /* ----------------------- HTML begin ---------------------- */

            if ($this->opt['print_html_head'])              print_html_head($page_attributes);
            $this->trigger_event("print_html_head");

            if ($this->opt['print_html_body_begin'])        print_html_body_begin($page_attributes);
            $this->trigger_event("print_html_body_begin");


            $smarty->display($this->template_name);

            if ($this->opt['print_html_body_end'])          print_html_body_end($page_attributes);
            $this->trigger_event("print_html_body_end");


            if (count($this->js_after_document)){
                echo "\n<script type=\"text/javascript\" nonce=\"{$this->get_nonce()}\">\n<!--\n";
                foreach($this->js_after_document as $v){
                    echo $v;
                }
                echo "\n// -->\n</script>\n";
            }

            if ($this->opt['print_html_document_end'])      echo "</html>\n";

            $this->trigger_event("post_html_output");

            page_close();
        }
        catch(PearErrorException $e){

            $userInfo = "";
            if (method_exists($e->pear_err, 'getUserInfo')) $userInfo = $e->pear_err->getUserInfo();
            $log_message = $e->pear_err->getMessage()." - ".$userInfo;

            sw_log($log_message, PEAR_LOG_ALERT, ['file' => $e->getFile(), 'line' => $e->getLine()]);

            $this->internal_server_error($e);
        }
        catch(PDOException $e){

            $log_message = "DB query failed";
            if ($e->query){
                $log_message .= ":\n{$e->query}";
            }

            sw_log($log_message, PEAR_LOG_ALERT, ['file' => $e->getFile(), 'line' => $e->getLine()]);
            sw_log_exception($e, PEAR_LOG_ALERT);

            $this->internal_server_error($e);
        }
        catch(Exception $e){
            sw_log_exception($e, PEAR_LOG_ALERT);

            $this->internal_server_error($e);
        }

        // All DB transactions should be commited now. If there is any
        // transacton in progress - most probably because of an error that
        // throwed exception - rollback the transaction.
        if ($GLOBALS['data']->is_transaction_in_progress()){
            $GLOBALS['data']->transaction_rollback();
        }
    }

    /**
     * Generate 'internal server error' screen and exit script execution.
     *
     * @param mixed $param Can be instance of Exception class or string message
     */
    public function internal_server_error($param){

        sw_log("INTERNAL SERVER ERROR REPORTED", PEAR_LOG_ALERT);

        if (php_sapi_name() == 'cli'){
            fwrite(STDERR, "AN INTERNAL ERROR HAS OCCURED\n");
            exit(9);
        }

        // Send http response 500 Internal Server Error
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol.' 500 Internal Server Error', true, 500);

        echo <<<EOT
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>An internal error has occurred</title>
</head>
<body>
  <h2>An internal error has occurred.</h2>
</body>
</html>
EOT;
        exit(9);

    }
}

class page_controller_event{
    public $name;
    public $controller;
    public $stop_propagation;

    public function __construct($name){
        $this->name = $name;
        $this->stop_propagation = false;
    }
}

/** page_controller just for backward compatibility */
class page_conroler extends page_controller {}
