<?php
/**
 *  Definitions of common classes
 *
 *  @author     Karel Kozlik
 *  @version    $Id: class_definitions.php,v 1.34 2009/12/17 12:11:55 kozlik Exp $
 *  @package    serweb
 */


/**
 *  Class representating tabs on html page
 *
 *  @package    serweb
 */
class Ctab{
    var $name, $page, $enabled;

    /**
     *  Constructor
     *
     *  @param  bool    $enabled    Should be tab displayed?
     *  @param  string  $name       Name of tab. If starting by '@' is translated by $lang_str array
     *  @param  string  $page       Script which generate html page after click on this tab
     */
    public function __construct($enabled, $name, $page){
        $this->name = $name;
        $this->page = $page;
        $this->enabled = $enabled;
    }

    /**
     *  Return name of the tab
     *
     *  If the name starting by "@" translate it by $lang_str array - internationalization
     *
     *  @return string
     */
    function get_name(){
        return Lang::internationalize($this->name);
    }

    /**
     *  Return script which generate content of this tab
     *
     *  @return string
     */
    function get_page(){
        return $this->page;
    }

    /**
     *  Is tab enabled?
     *
     *  @return bool
     */
    function is_enabled(){
        return (bool)$this->enabled;
    }

    /**
     *  Enable tab
     */
    function enable(){
        $this->enabled = true;
    }

    /**
     *  Disable tab
     */
    function disable(){
        $this->enabled = false;
    }
}


/**
 *  @package    serweb
 */
class Cconfig{
}


/**
 *  @package    serweb
 */
class SerwebUser {
    var $classname = "SerwebUser";
    var $persistent_slots = array('uid', 'did', 'username', 'realm');

    var $uid;
    var $username;
    var $realm;
    var $did = null;
    var $domainname = null;
    var $uri = null;

    static function &instance($uid, $username, $did, $realm){

        $class = get_called_class();

        $obj = new $class();
        $obj->uid = $uid;
        $obj->username = $username;
        $obj->did = $did;
        $obj->realm = $realm;

        return $obj;
    }

    static function &instance_by_refs(&$uid, &$username, &$did, &$realm){

        $class = get_called_class();

        $obj = new $class();
        $obj->uid = &$uid;
        $obj->username = &$username;
        $obj->did = &$did;
        $obj->realm = &$realm;

        return $obj;
    }

    static function &recreate_from_get_param($val){

        $val.=":"; //add stop mark to input string
        $parts = array();

        for ($i=0, $j=0; $i<strlen($val); $i++){
            if ($val[$i] != ":") continue;

            //skip quoted ":"
            if (isset($val[$i+1]) and $val[$i+1] == "'" and
                isset($val[$i-1]) and $val[$i-1] == "'"){
                $i++;
                continue;
            }

            // at $i position is single ":"
            $parts[] = substr($val, $j, $i-$j);
            $j = $i+1;

        }

        foreach ($parts as $k=>$v) $parts[$k] = str_replace("':'", ":", $v);

        if ($parts[0]=="") $parts[0] = null;    //if UID is empty, set it to null
        if ($parts[1]=="") $parts[1] = null;    //if DID is empty, set it to null

        $obj = &static::instance($parts[0], $parts[2], $parts[1], $parts[3]);
        return $obj;
    }

    function get_uid(){
        return $this->uid;
    }

    function get_username(){
        return $this->username;
    }

    function get_realm(){
        return $this->realm;
    }

    function to_get_param($param = null){

        if (is_null($param)){
            if (isset($GLOBALS['controler']) and
                is_a($GLOBALS['controler'], 'page_controller')){
                $param = $GLOBALS['controler']->ch_user_param_name();
            }
            else{
                $param = "user";
            }
        }

        /* single quote all ":" */
        $uid = str_replace(":", "':'", $this->uid);
        $did = str_replace(":", "':'", $this->did);
        $realm = str_replace(":", "':'", $this->realm);
        $username = str_replace(":", "':'", $this->username);

        return $param."=".RawURLencode($uid.":".$did.":".$username.":".$realm);
    }


    function to_smarty(){
        return array('uname' => $this->username,
                     'realm' => $this->realm);
    }
}

/**
 *  @package    serweb
 */
class ErrorHandler{
    var $errors = array();

    /**
     * Return a reference to a ErrorHandler instance, only creating a new instance
     * if no ErrorHandler instance currently exists.
     *
     * You should use this if there are multiple places you might create a
     * ErrorHandler, you don't want to create multiple instances, and you don't
     * want to check for the existance of one each time. The singleton pattern
     * does all the checking work for you.
     *
     * <b>You MUST call this method with the $var = &ErrorHandler::singleton()
     * syntax. Without the ampersand (&) in front of the method name, you will
     * not get a reference, you will get a copy.</b>
     *
     * @access public
     */

    public static function &singleton() {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new ErrorHandler();
        }
        return $instance;
    }

    /**
     *  Add an error message to the array of error messages
     *
     *  This method may be called staticaly e.g.: ErrorHandler::add_error($message);
     *  or dynamicaly e.g. $e = &ErrorHandler::singleton(); $e->add_error($message);
     *
     *  @param  mixed   $message    string or array of strings
     *  @return none
     */

    public static function add_error($message){

        $in = &ErrorHandler::singleton();

        if (is_array($message)) $in->errors = array_merge($in->errors, $message);
        else                    $in->errors[] = $message;
    }

    /**
     *  get error message from PEAR_Error object and write it to $errors array and to error log
     *
     *  This method may be called staticaly e.g.: ErrorHandler::log_errors($err_object);
     *  or dynamicaly e.g. $e = &ErrorHandler::singleton(); $e->log_errors($err_object);
     *
     *  @param object $err_object PEAR_Error object
     *  @return none
     */

    public static function log_errors($err_object){

        $in = &ErrorHandler::singleton();

        log_errors($err_object, $in->errors);
    }



    /**
     *  Set internal variable containing error messages to be a reference to given array
     *
     *  @param  array   $errors
     *  @return none
     */

    function set_errors_ref(&$errors){
        $this->errors = &$errors;
    }

    /**
     *  Return array of error messages (as reference))
     *
     *  @return array
     */

    function &get_errors_array(){
        return $this->errors;
    }
}


/**
 *  OO extension for IPC semaphores
 *
 *  Usage example:
 *  --------------
 *
 *      $sem = new Shm_Semaphore(__FILE__, "s", 1, 0600);
 *      if (!$sem->acquire()){
 *          report error
 *          return;
 *      }
 *
 *      ---- critical section ----
 *
 *      $sem->release();
 *
 *  @package    serweb
 */
class Shm_Semaphore{
    var $max_acquire;
    var $perm;
    var $sem_id;

    /**
     *  Constructor
     *
     *  @param  string  $path_name
     *  @param  string  $proj           project identifier - one character
     *  @param  int     $max_acquire    The number of processes that can acquire the semaphore simultaneously
     *  @param  int     $perm           permission bits
     */
    public function __construct($path_name, $proj, $max_acquire = 1, $perm=0666){
        $key = ftok($path_name, $proj);
        $this->max_acquire = $max_acquire;
        $this->perm = $perm;
        $this->sem_id = sem_get($key, $this->max_acquire, $this->perm, true);
    }

    /**
     *  blocks (if necessary) until the semaphore can be acquired
     *
     *  @return bool    Returns TRUE on success or FALSE on failure.
     */
    function acquire(){
        if (!sem_acquire($this->sem_id)) {
            ErrorHandler::log_errors(PEAR::raiseError("cannot acquire semaphore"));
            return false;
        }
        return true;
    }

    /**
     *  releases the semaphore if it is currently acquired by the calling process
     *
     *  @return bool    Returns TRUE on success or FALSE on failure.
     */
    function release(){
        if (!sem_release($this->sem_id)) {
            ErrorHandler::log_errors(PEAR::raiseError("cannot release semaphore"));
            return false;
        }
        return true;
    }
}

/**
 *  @package    serweb
 */
class Filter {
    var $name;
    var $value="";
    /**
     *  supported operators: "=", "!=", ">", ">=", "<", "<=", "like", "is_null", "in"
     *  Note: operator 'in' expects an array in $this->value
     */
    var $op="like";
    var $asterisks=true;
    var $case_sensitive = false;

    public function __construct($name, $value=null, $op="=", $asterisks=false, $case_sensitive=false){
        $this->name = $name;
        $this->value = $value;
        $this->op = $op;
        $this->asterisks = $asterisks;
        $this->case_sensitive = $case_sensitive;
    }

    function to_sql($var=null, $int=false){
        global $data;

        if (is_null($var)) $var = $this->name;
        if ($this->op == "is_null")     return $var." is null";

        $val = $this->value;
        if ($this->op == "like"){
            /* escape '%' and '_' characters - these are not wildcards */
            $val = str_replace('%', '\%', $val);
            $val = str_replace('_', '\_', $val);

            /* replace '*' and '?' with their wildcard equivalent  */
            $val = str_replace('*', '%', $val);
            $val = str_replace('?', '_', $val);

            if ($this->asterisks) $val = "%".$val."%";
        }

        if ($this->op == "in"){
            return $data->get_sql_in($var, $val, !$int);
        }


        if ($int)   return $var." ".$this->op." ".(int)$val;
        else {
            if ($this->case_sensitive){
                return $var." ".$this->op." BINARY '".addslashes($val)."'";
            }
            else{
                return "lower(".$var.") ".$this->op." lower('".addslashes($val)."')";
            }
        }

    }

    function to_sql_bool($var=null){

        if (is_null($var)) $var = $this->name;
        if ($this->op == "is_null")     return $var." is null";

        $val = $this->value;
        if ($val){
            return "(".$var.")";
        }
        else{
            return "!(".$var.")";
        }
    }

    function to_sql_float($var=null){

        if (is_null($var)) $var = $this->name;
        if ($this->op == "is_null")     return $var." is null";

        $val = $this->value;
        if ($this->op == "like"){
            /* escape '%' and '_' characters - these are not wildcards */
            $val = str_replace('%', '\%', $val);
            $val = str_replace('_', '\_', $val);

            /* replace '*' and '?' with their wildcard equivalent  */
            $val = str_replace('*', '%', $val);
            $val = str_replace('?', '_', $val);

            if ($this->asterisks) $val = "%".$val."%";
        }

        return $var." ".$this->op." ".(float)$val;
    }

    public function ts_to_sql_datetime($var=null){
        global $data;

        if (is_null($var)) $var = $this->name;
        if ($this->op == "is_null")     return $var." is null";

        $val = $data->from_unixtime($this->value);

        return $var." ".$this->op." '".addslashes($val)."'";
    }

}

/**
 *  Helper class used to store static class variables
 */
class StaticVarHandler{

    /**
     *  Get or clear the class variable
     *
     *  @param  string $class   name of class requesting the variable
     *  @param  string $key     name of variable or another index - for use by the class
     *  @param  bool   $free    if true, free memory ocupied by the variable
     *  @return mixed
     */
    function &getvar($class, $key, $free){
        static $vars;
        $dummy = null;

        if ($free) {
            if (isset($vars[$class][$key])) unset($vars[$class][$key]);
            return $dummy;
        }
        else{
            if (!isset($vars[$class][$key])) $vars[$class][$key]=null;
            return $vars[$class][$key];
        }
    }
}


class ModuleCallback{

    var $module;
    /** callback function */
    var $fn;

    public function __construct($module, $fn){
        $this->module = $module;
        $this->fn = $fn;
    }
}

class CallbackCaller{

    function call_array($callback_arr, $param, &$opt, $break_on_error = false){

        $ok = true;

        if (is_array($callback_arr)){
            foreach($callback_arr as $v){
                $opt['all_calls_ok'] = $ok;
                if (!$this->call($v, $param, $opt)){
                    $ok = false;
                    if ($break_on_error) return false;
                }
            }
        }

        return $ok;
    }


    function call($callback, $param, &$opt){
        include_module($callback->module);

        if (false === call_user_func_array($callback->fn, array($param, &$opt))) return false;

        return true;
    }


    /**
     *  Call all calbacks given in $callback_arr and return array of returned
     *  values.
     */
    function call_array_rv($callback_arr, $param, &$opt, $break_on_error = false){
        $ret_values = array();

        if (is_array($callback_arr)){
            foreach($callback_arr as $k => $v){
                $ret_values[$k] = $this->call_rv($v, $param, $opt);
            }
        }

        return $ret_values;
    }


    function call_rv($callback, $param, &$opt){
        include_module($callback->module);
        return call_user_func_array($callback->fn, array($param, &$opt));
    }
}


/**
 *  Various validators of values
 */
class Validator{

    /**
     *  Validate that given integer is in given range
     *
     *  @param  int     $value      the value to validate
     *  @param  int     $min        min boundary
     *  @param  int     $max        max boundary
     *  @param  string  $err_msg    error message to display
     *  @param  string  $var_name   name of variable (to be used in the err_msg)
     *  @return bool
     */
    function validate_int_range($value, $min, $max, $err_msg, $var_name=null){
        $reg = Creg::singleton();  // get instance of Creg class
        if (!$reg->is_natural_num($value)) return 1;
        $value = (int)$value;

        if ($value < $min or $value > $max){
            ErrorHandler::add_error(
                str_replace(array("#VALUE#", "<min>", "<max>", "<name>"),
                            array($value, $min, $max, $var_name),
                            $err_msg));
            return false;
        }
        return true;
    }

    /**
     *  Validate given value to IPv4 address (only regular expression match)
     *
     *  @param  string  $value      the value to validate
     *  @param  string  $err_msg    error message to display
     *  @param  string  $var_name   name of variable (to be used in the err_msg)
     *  @return bool
     */
    function validate_IPv4_reg($value, $err_msg, $var_name=null){
        $reg = Creg::singleton();  // get instance of Creg class
        if (!preg_match("/^".$reg->get_ipv4address_regex()."$/", $value)){
            ErrorHandler::add_error(
                str_replace(array("#VALUE#", "<name>"),
                            array($value, $var_name),
                            $err_msg));
            return false;
        }
        return true;
    }

    /**
     *  Validate if all parts of given IPv4 address are in range 0-255
     *
     *  @param  string  $value      the value to validate
     *  @param  string  $err_msg    error message to display
     *  @param  string  $var_name   name of variable (to be used in the err_msg)
     *  @return bool
     */
    function validate_IPv4_range($value, $err_msg, $var_name=null){
        $reg = Creg::singleton();  // get instance of Creg class
        if (!$reg->is_ipv4address($value)) return 1;

        if (!$reg->ipv4address_check_part_range($value)){
            ErrorHandler::add_error(
                str_replace(array("#VALUE#", "<name>"),
                            array($value, $var_name),
                            $err_msg));
            return false;
        }
        return true;
    }

    /**
     *  Validate given value to IPv4 address
     *
     *  @param  string  $value      the value to validate
     *  @param  string  $err_msg    error message to display
     *  @param  string  $var_name   name of variable (to be used in the err_msg)
     *  @return bool
     */
    function validate_IPv4($value, $err_msg, $var_name=null){
        if (false === self::validate_IPv4_reg($value, $err_msg, $var_name)) return false;
        if (false === self::validate_IPv4_range($value, $err_msg, $var_name)) return false;
        return true;
    }

    /**
     *  Validate given value to IPv4 address and mask {IP}/{mask}
     *
     *  @param  string  $value      the value to validate
     *  @param  string  $err_msg    error message to display
     *  @param  string  $var_name   name of variable (to be used in the err_msg)
     *  @return bool
     */
    function validate_IPv4mask($value, $err_msg, $var_name=null){
        $reg = Creg::singleton();  // get instance of Creg class
        if (empty($value)) return false;

        $value_a = explode("/", $value, 2);
        if (!isset($value_a[1])) $value_a[1] = "";

        if (!self::validate_IPv4($value_a[0], $err_msg, $var_name)) return false;

        if (!$reg->check_netmask($value_a[1])){
            ErrorHandler::add_error(
                str_replace(array("#VALUE#", "<name>"),
                            array($value, $var_name),
                            $err_msg));
            return false;
        }

        return true;
    }

}
