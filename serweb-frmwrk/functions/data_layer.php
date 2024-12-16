<?php
/**
 *  Data layer
 *
 *  @author     Karel Kozlik
 *  @version    $Id: data_layer.php,v 1.27 2007/02/14 16:36:39 kozlik Exp $
 *  @package    serweb
 *  @subpackage framework
 */

// variable $_data_layer_required_methods should be defined at beginning of each php script
// $_data_layer_required_methods=array();

/**
 *  Data layer
 *
 *  class providing data access to database and other storages
 *
 *  @package    serweb
 *  @subpackage framework
 */
class CData_Layer{

    /** Return value that can be set by any method. It's used to get rid of
     *  parameters passe by reference
     */
    var $ret_val = null;

    var $db=null;                       //PEAR DB object

    var $act_row=0, $num_rows=0;        //used when result returns too many rows
    var $showed_rows;                   //how many rows from result display

    private $_data_layer_loaded_methods=array();

    private $name = "auth_user";        //name of instance

    var $db_collation = null;
    var $db_charset = null;

    /** Contain DSN (Data Source Name) of DB Host in string and in parsed form */
    private $db_host = array();

    private $db_config;

    protected $transaction_rollback = false;
    protected $transaction_semaphore = 0;

    static $method_class_map = array();

    /** listeners of events and their priorities */
    private static $static_listeners = array();
    private $listeners = array();

    /**
     *  Handle call of unknow methods
     */
    public function __call($method, $args){

        // check if the method is known and retrieve class containing it
        if (!isset(self::$method_class_map[$method])){
            // raise an error if the method is unknown, just like PHP would
            trigger_error(sprintf('Call to undefined function: %s::%s().', get_called_class(), $method), E_USER_ERROR);
        }

        $class = self::$method_class_map[$method];

        $obj = new $class;
        $obj->dl = $this; // inject data_layer object into the $obj
        return call_user_func_array([$obj, $method], $args);
    }


    /*
     *   Constructor
     */

    public function __construct(){
        global $config;
        $this->showed_rows = &$config->num_of_showed_items;
        $this->set_dbconfig($config->data_sql);
    }

    /**
     *  Create a new DataLayer object and agregate aditional methods
     *
     *  @return object CData_Layer      instance of CData_Layer class
     *  @static
     *  @access public
     *  @deprec
     */

    public static function &create(){

        $obj = new CData_Layer();

        $obj->require_and_agregate_methods();
        return $obj;
    }

    /**
     *  Attempts to return a reference to a concrete CData_Layer instance of name
     *  $instance_name, only creating a new instance if no data_layer instance with
     *  the same name currently exists.
     *
     *  @param string $instance_name                    name of data_layer instance, if empty "auth_user" is used
     *  @param string|CData_Layer $template_instance    if is set, the internal variables (like charset or collation)
     *                                                  of newly created CData_Layer instance is set according to another
     *                                                  existing instance.
     *
     *  @return object CData_Layer      instance of CData_Layer class
     *  @static
     *  @access public
     */
    public static function &singleton($instance_name = null, $template_instance = null){
        static $instances = array();

        if (!$instance_name) $instance_name = "auth_user";

        if (!isset($instances[$instance_name])){
            $instances[$instance_name] = &CData_Layer::create();
            $instances[$instance_name]->name = $instance_name;

            if ($template_instance){
                if (is_a($template_instance, "CData_Layer")){
                    $instances[$instance_name]->setup_by_another_instance($template_instance);
                }
                else{
                    if (isset($instances[$template_instance])){
                        $instances[$instance_name]->setup_by_another_instance($instances[$template_instance]);
                    }
                    else{
                        throw new UnexpectedValueException("Data layer instance named '$template_instance' does not exists");
                    }
                }
            }

        }

        return $instances[$instance_name];
    }

    public function get_instance_name(){
        return $this->name;
    }

    public function get_dbconfig() : ?object{
        return $this->db_config;
    }

    public function set_dbconfig(object $cfg) : void{
        $this->db_config = $cfg;

        // Load DB abstraction layer
        if ($this->is_MDB2_used())  require_once 'MDB2.php';
        if ($this->is_DB_used())    require_once 'DB.php';
        if ($this->is_PDO_used())   require_once 'pdo_extension.php';
    }

    public function is_DB_used() : bool {
        return $this->db_config->abstraction_layer == "DB";
    }

    public function is_MDB2_used() : bool {
        return $this->db_config->abstraction_layer == "MDB2";
    }

    public function is_PDO_used() : bool {
        return $this->db_config->abstraction_layer == "PDO";
    }

    /**
     *   dynamicaly agregate aditional methods
     *   $m is string or array
     */

    function add_method($m){
        global $_data_layer_required_methods;

        if (is_array($m)) {
            /* next line is optimalization: don't call
             * require_and_agregate_methods() if all required methods
             * are already loaded
             */
            if (!count(array_diff($m, $this->_data_layer_loaded_methods))) return;

            $_data_layer_required_methods = array_merge($_data_layer_required_methods, $m);
        }
        else{
            /* next line is optimalization: don't call
             * require_and_agregate_methods() method is already loaded
             */
            if (in_array($m, $this->_data_layer_loaded_methods)) return;

            $_data_layer_required_methods[] = $m;
        }

        $this->require_and_agregate_methods();
    }

    /**
     *   dynamicaly agregate aditional methods
     */

    function require_and_agregate_methods(){
        global $_data_layer_required_methods;
        global $_SERWEB;
        global $config;

        if (!isset($_data_layer_required_methods) or !is_array($_data_layer_required_methods))
            $_data_layer_required_methods = array();

        $_data_layer_required_methods = array_merge($config->data_layer_always_required_functions, $_data_layer_required_methods);


        $loaded_modules = getLoadedModules();

        $item = reset($_data_layer_required_methods);
        do{
            if (in_array($item, $this->_data_layer_loaded_methods)) continue;  //skip already loaded methods

            $file_found = false;
            //require class with method definition
            if (file_exists($_SERWEB["datadir"] . "customized/method.".$item.".php")){
                //if exists customized version of method, require it
                require_once ($_SERWEB["datadir"] . "customized/method.".$item.".php");

                $file_found = true;
            }

            if (!$file_found){
                //try found file in modules
                foreach($loaded_modules as $module){
                    if (file_exists($_SERWEB["modulesdir"] . $module."/method.".$item.".php")){
                        require_once ($_SERWEB["modulesdir"] . $module."/method.".$item.".php");
                        $file_found = true;
                        break;
                    }
                    elseif (file_exists($_SERWEB["coremodulesdir"] . $module."/method.".$item.".php")){
                        require_once ($_SERWEB["coremodulesdir"] . $module."/method.".$item.".php");
                        $file_found = true;
                        break;
                    }
                }
            }

            if (!$file_found){
                //otherwise require default version
                require_once ($_SERWEB["datadir"] . "method.".$item.".php");
            }

            //add method to $_data_layer_loaded_methods array
            $this->_data_layer_loaded_methods[] = $item;

            //add methods required by currently loaded method to $_data_layer_required_methods array
            $class_methods = get_class_methods("CData_Layer_".$item);

            if (in_array('_get_required_methods', $class_methods)){
                $_data_layer_required_methods =
                    array_merge($_data_layer_required_methods,
                                call_user_func(array("CData_Layer_".$item, '_get_required_methods')));
            }
            else{
                $class_vars = get_class_vars("CData_Layer_".$item);
                if (isset($class_vars['required_methods']) and is_array($class_vars['required_methods'])){
                    $_data_layer_required_methods =
                        array_merge($_data_layer_required_methods,
                                    $class_vars['required_methods']);
                }
            }

            // build map of data layer methods, so we can later easily found
            // class where the method is defined
            foreach($class_methods as $method){
                // skip special method: '_get_required_methods'
                if ($method == '_get_required_methods') continue;

                $class_name = "CData_Layer_".$item;

                // check for duplicities
                if (isset(self::$method_class_map[$method])){
                    if (self::$method_class_map[$method] == $class_name) continue;

                    throw new Exception("Cannot redefine data layer function 'CData_Layer_$item:$method' already defined in class: ".self::$method_class_map[$method]);
                }

                self::$method_class_map[$method] = $class_name;
            }
        } while (false !== ($item = next($_data_layer_required_methods)));
    }

    /**
     *  Set internar variables by another instance of CData_Layer
     *
     *  @param CData_Layer $d
     */
    function setup_by_another_instance(&$d){
        $this->db_charset   = $d->db_charset;
        $this->db_collation = $d->db_collation;
    }

    /**
     * Attach an event listener.
     * As of now following events are supported:
     *
     *  - pre_connect                  - before connecting to DB
     *  - post_connect                 - after connecting to DB
     *  - connect_failed               - when connecting to DB failed
     *
     * The callback function shall accept one parameter of the CData_Layer_Event object
     *
     * @param string   $event_name
     * @param callback $callback
     * @param integer  $priority
     * @return void
     */
    public function attach_listener($event_name, $callback, $priority=0)
    {
        $this->listeners[$event_name][] = array("priority" => $priority,
                                                "callback" => $callback);
    }
    public static function attach_common_listener($event_name, $callback, $priority=0)
    {
        static::$static_listeners[$event_name][] = array("priority" => $priority,
                                                         "callback" => $callback);
    }

    /**
     * Trigger event of given name.
     * This function execute all listeners of given event in their priority order.
     *
     * @param string $event_name
     * @return void
     */
    private function trigger_event($event_name, $arguments=[])
    {
        $listeners = array();
        if (!empty(static::$static_listeners[$event_name])) $listeners = array_merge($listeners, static::$static_listeners[$event_name]);
        if (!empty($this->listeners[$event_name]))          $listeners = array_merge($listeners, $this->listeners[$event_name]);
        if (!$listeners) return;

        usort($listeners, function($a, $b){
            if ($a['priority'] == $b['priority']) return 0;
            return ($a['priority'] < $b['priority']) ? -1 : 1;
        });

        $event = new CData_Layer_Event($event_name);
        $event->dl = $this;

        foreach($listeners as $listener){
            $listener["callback"]($event, $arguments);
            if ($event->stop_propagation) break;
        }
    }

    private function make_MDB2_dsn(string $db_type, array $cfg) : string{
        return $db_type."://".
                $cfg['user'].":".
                $cfg['pass']."@".
                $cfg['host'].
                    (empty($cfg['port'])?
                        "":
                        ":".$cfg['port'])."/".
                $cfg['name'];
    }

    /**
     *  Set object variable (@see db_host) by given config values
     *
     *  @param string $db_type  Type of Data Source
     *  @param array  $cfg      $config->data_sql->host
     */
    private function set_this_db_host(string $db_type, array $cfg) : void{

        if ($this->is_MDB2_used()){
            $this->db_host['dsn'] =  $this->make_MDB2_dsn($db_type, $cfg);
            $this->db_host['parsed'] = MDB2::parseDSN($this->db_host['dsn']);
        }
        elseif($this->is_DB_used()){
            $this->db_host['dsn'] =  $this->make_MDB2_dsn($db_type, $cfg);
            $this->db_host['parsed'] = DB::parseDSN($this->db_host['dsn']);
        }
        else{ // assume PDO is used
            if ($cfg['dsn']) $this->db_host['pdo_dsn'] = $cfg['dsn'];
            else{
                $this->db_host['pdo_dsn'] = $db_type.
                                ':dbname='.$cfg['name'].
                                ';host='.$cfg['host'].
                                    (empty($cfg['port'])?
                                        "":
                                        ";port=".$cfg['port']);
            }

            $this->db_host['parsed']['phptype'] = $db_type;
            $this->db_host['parsed']['username'] = isset($cfg['user']) ? $cfg['user'] : null;
            $this->db_host['parsed']['password'] = isset($cfg['pass']) ? $cfg['pass'] : null;
            $this->db_host['parsed']['database'] = isset($cfg['name']) ? $cfg['name'] : null;
        }
    }


    /**
     *  connect to sql database
     */
    function connect_to_db(){

        if ($this->db) return $this->db;

        $this->trigger_event("pre_connect");

        $cfg = $this->get_dbconfig();

        $num=count($cfg->host); //get number of SQL servers that we can use

        if ($num>1) $serv=mt_rand(0, $num-1);
        else $serv=0;

        $tries=0;
        do{
            $cont=0;
            $this->set_this_db_host($cfg->type, $cfg->host[$serv]);

            $codeFailed = null;
            $isError = false;
            $exception = null;

            if ($this->is_MDB2_used()){
                $db = MDB2::connect($this->db_host['parsed'], true);
                $isError = MDB2::isError($db);
                $codeFailed = MDB2_ERROR_CONNECT_FAILED;
            }
            elseif ($this->is_DB_used()){
                $db = DB::connect($this->db_host['parsed'], true);
                $isError = DB::isError($db);
                $codeFailed = DB_ERROR_CONNECT_FAILED;
            }
            else{ // assume PDO
                try {
                    $db = new Serweb_PDO($this->db_host['pdo_dsn'],
                                         $this->db_host['parsed']['username'],
                                         $this->db_host['parsed']['password']);
                } catch (PDOException $e) {
                    $isError = true;
                    $exception = $e;
                }
            }

            if ($isError) {
                //if connect failed and multiple servers is defined
                if ($num > 1 and ($codeFailed ? $db->getCode() == $codeFailed : true)){
                    //try another server
                    $tries++;
                    $serv++; $serv %= $num;
                    if ($tries<$num) {
                        $cont=1; continue;
                    }
                }

                if (!$exception) $exception = new DBException($db);
                $this->trigger_event("connect_failed", ['exception' => $exception]);

                throw $exception;
            }
        }while($cont);

        $this->db=$db;

        if ($this->db_host['parsed']['phptype'] !== 'sqlite') { // charset is per-db in sqlite
            if ($this->db_charset) $this->set_db_charset($this->db_charset, null);
            if ($this->db_collation) $this->set_db_collation($this->db_collation, null);
        }

        $this->trigger_event("post_connect");

        return $db;
    }

    function set_ret_val($val){
        $this->ret_val = $val;
    }

    function get_ret_val(){
        return $this->ret_val;
    }

    function set_num_rows($num_rows){
        $this->num_rows=(int)$num_rows;
    }

    function get_num_rows(){
        return $this->num_rows;
    }

    function set_act_row($act_row){
        $this->act_row=(int)$act_row;
    }

    function get_act_row(){
        return $this->act_row;
    }

    function get_showed_rows(){
        return $this->showed_rows;
    }

    function set_showed_rows($showed_rows){
        $this->showed_rows=(int)$showed_rows;
    }

    function get_res_from(){
        // Modified for PR 118789
        if($this->get_num_rows())
            return $this->get_act_row()+1;
        else
            return $this->get_act_row();
    }

    function get_res_to(){
        return ((($this->get_act_row()+$this->get_showed_rows())<$this->get_num_rows())?
                ($this->get_act_row()+$this->get_showed_rows()):
                $this->get_num_rows());
    }


    /* if act_row is bigger then num_rows, correct it */
    function correct_act_row(){
        if ($this->get_act_row() >= $this->get_num_rows())
            $this->set_act_row(max(0, $this->get_num_rows()-$this->get_showed_rows()));
    }

    /**
     *  Return pager info for use by smarty pager plugin
     */
    function get_pager(){
        global $controler;
        $pager = array();

        $pager['url']   = $controler->url($_SERVER['PHP_SELF']."?act_row=<pager>");
        $pager['pos']   = $this->get_act_row();
        $pager['items'] = $this->get_num_rows();
        $pager['limit'] = $this->get_showed_rows();
        $pager['from']  = $this->get_res_from();
        $pager['to']    = $this->get_res_to();

        return $pager;
    }


    function dbIsError($res){

        if ($this->is_MDB2_used()){
            return MDB2::isError($res);
        }
        elseif ($this->is_DB_used()){
            return DB::isError($res);
        }
        else{   // PDO
            return $res===false ? true : false;
        }
    }

    /**
     *  Start a transaction on the current connection
     */
    function transaction_start(array $opts=[]){

        $this->connect_to_db();

        /* initialize variables after rollback */
        if ($this->transaction_rollback){
            $this->transaction_rollback = false;
            $this->transaction_semaphore = 0;
        }

        /* don't call "start transaction" in nested transactions */
        if ($this->transaction_semaphore == 0){
            if ($this->is_PDO_used()){
                if ($this->db_host['parsed']['phptype'] == 'sqlite'){
                    $type = "";
                    if (isset($opts['sqlite_lock_type'])){
                        if (in_array(
                                strtolower($opts['sqlite_lock_type']),
                                ['deferred', 'immediate', 'exclusive'])
                        ){
                            $type = $opts['sqlite_lock_type'];
                        }
                        else{
                            throw new Exception("Invalid value {$opts['sqlite_lock_type']} for 'sqlite_lock_type' option");
                        }
                    }
                    $q = "begin $type transaction";
                    $this->db->query($q);
                }
                else{
                    $res=$this->db->beginTransaction();
                }
            }
            else{
                $res=$this->db->query("start transaction");
                if ($this->dbIsError($res)) throw new DBException($res);
            }
        }

        $this->transaction_semaphore++;

        return true;
    }

    /**
     *  Commit the transaction on the current connection
     */
    function transaction_commit(){

        $this->transaction_semaphore--;

        /* Value of semaphore never should be negative. This line is for the
         * case it is negative by some error.
         */
        if ($this->transaction_semaphore < 0) $this->transaction_semaphore = 0;

        /* don't call "commit" in nested transactions or if rollback was called */
        if ($this->transaction_semaphore == 0 and !$this->transaction_rollback){
            if ($this->is_PDO_used()){
                if ($this->db_host['parsed']['phptype'] == 'sqlite'){
                    $this->db->query("commit");
                }
                else{
                    $res=$this->db->commit();
                }
            }
            else{
                $res=$this->db->query("commit");
                if ($this->dbIsError($res)) throw new DBException($res);
            }
        }

        return true;
    }

    /**
     *  Rollback changes done due the transaction
     */
    function transaction_rollback(){

        $this->transaction_rollback = true;

        if ($this->is_PDO_used()){
                if ($this->db_host['parsed']['phptype'] == 'sqlite'){
                    $this->db->query("rollback");
                }
                else{
                    $res=$this->db->rollBack();
                }
        }
        else{
            $res=$this->db->query("rollback");
            if ($this->dbIsError($res)) throw new DBException($res);
        }

        return true;
    }

    /**
     *  Return TRUE if there is a transaction in progress
     */
    function is_transaction_in_progress(){
        return ($this->transaction_semaphore > 0);
    }

    /**
     *  Return a limit phrase for SQL queries depending on which DB host is useing
     *
     *  If $limit  is omited, the value returned by $this->get_showed_rows() is used
     *  If $offset is omited, the value returned by $this->get_act_row() is used
     *
     *  @param int $offset  says to skip that many rows before beginning to return rows
     *  @param int $limit   maximum of rows that should be returned
     *  @return string      limit sql phrase
     */
    function get_sql_limit_phrase($offset = NULL, $limit = NULL){
        if (is_null($offset)) $offset = $this->get_act_row();
        if (is_null($limit))  $limit  = $this->get_showed_rows();

        if ($this->db_host['parsed']['phptype'] == 'pgsql'){
            return " limit ".$limit." offset ".$offset;
        }
        else {
            return " limit ".$offset.", ".$limit;
        }
    }

    /**
     *  Return a concatenation function which may be used in SQL queries depending on which DB host is useing
     *
     *  @param array $arguments     array of string which should be concatenated in SQL query
     *  @return string              concatenation function
     */
    function get_sql_concat_funct($arguments = array()){

        if ($this->db_host['parsed']['phptype'] == 'pgsql'){
            $start     = "";
            $separator = " || ";
            $finish    = "";
        }
        else {
            $start     = "concat(";
            $separator = ", ";
            $finish    = ")";
        }

        $out = $start;;
        foreach ($arguments as $v){
            $out .= $v;
            $out .= $separator;
        }

        $out = substr($out, 0, 0 - strlen($separator)); //trim last separator
        $out .= $finish;

        return $out;
    }

    /**
     *  Return a integer cast function which may be used in SQL queries depending on which DB host is useing
     *
     *  @param string $argument
     *  @return string
     */
    function get_sql_cast_to_int_funct($argument){
        if ($this->db_host['parsed']['phptype'] == 'mysql'){
            return " cast(".$argument." as signed integer) ";
        }
        else {
//          return " cast(".$argument." as integer) ";                  // working in pgsql 8.0
            return " cast(cast(".$argument." as text) as integer) ";    // change for pgsql 7.*
        }
    }

    /**
     *  Return a regular expression matching which may be used in SQL queries depending on which DB host is useing
     *
     *  @param string $patern
     *  @param string $string
     *  @param array $opt
     *  @return string
     */
    function get_sql_regex_match($patern, $string, $opt = null){
        if ($this->db_host['parsed']['phptype'] == 'mysql'){
            return $string." REGEXP \"".$patern."\"";
        }
        else {
            return $string." ~* '".$patern."'";
        }
    }

    /**
     *  Return a boolean constant which may be used in SQL queries depending on which DB host is useing
     *
     *  @deprecated             Deprecated by method sql_format()
     *  @param bool $argument   if true return "true" or "1", if false return "false" or "0"
     *  @return string
     */
    function get_sql_bool($argument){
        if ($this->db_host['parsed']['phptype'] == 'mysql'){
            return $argument ? "1" : "0";
        }
        else {
            return $argument ? "true" : "false";
        }
    }

    /**
     *  Return expression " expr IN (value,...) " where list of values are constructed from $set
     *
     *  If $set conatain no elements expression " false " is returned instead of this.
     *  If $quote is true, elements are quoted
     *
     *  @param  string  $expr
     *  @param  array   $set
     *  @param  bool    $quote
     *  @return string
     */
    function get_sql_in($expr, $set, $quote = true){

        if (!count($set)) return (" ".$this->get_sql_bool(false)." ");

        if ($quote){
            foreach($set as $k=>$v) $set[$k] = "'".$this->escape_string($v)."'";
        }

        $set = implode(", ", $set);

        return (" ".$expr." IN (".$set.") ");

    }

    /**
     *  Return expression " CASE expr WHEN [compare_value] THEN result
     *  [WHEN [compare_value] THEN result ...] [ELSE result] END " where the
     *  list of pairs: compare_value:result is generated from given
     *  asociative array
     *
     *  @param  string  $expr
     *  @param  array   $array
     *  @param  string  $else     if set, the '[ELSE result]' part is included
     *  @return string
     */
    function get_sql_switch($expr, $array, $else=null){

        $out = "CASE ".$expr;
        foreach($array as $k => $v)
            $out .= " WHEN ".$this->sql_format($k, 's').
                    " THEN ".$this->sql_format($v, 's');

        if (!is_null($else))  $out .= " ELSE ".$this->sql_format($else, 's');
        $out .= " END";

        return $out;
    }

    /**
     *  Format value to it can be used in sql query
     *
     *  Type of value is one of:
     *    "n" - number (int or float)
     *    "N" - number (int or float) - allow NULL values
     *    "s" - string
     *    "S" - string - allow NULL values
     *    "b" - bool
     *    "B" - bool - allow NULL values
     *    "i" - image (binary data)
     *    "I" - image (binary data) - allow NULL values
     *
     *  @param  mixed   $val
     *  @param  string  $type
     *  @return string
     */

    function sql_format($val, $type){

        switch ($type){
        case "S":
            if (is_null($val)) return "NULL";
        case "s":
            return "'".$this->escape_string($val)."'";

        case "N":
            if (is_null($val)) return "NULL";
        case "n":
            return ($val == (int) $val) ? (int) $val : (float) $val;

        case "B":
            if (is_null($val)) return "NULL";
        case "b":
            if ($this->db_host['parsed']['phptype'] == 'mysql'){
                return $val ? "1" : "0";
            }
            else {
                return $val ? "true" : "false";
            }

        case "I":
            if (is_null($val)) return "NULL";
        case "i":
            if ($this->db_host['parsed']['phptype'] == 'pgsql'){
                return "'".pg_escape_bytea($val)."'::bytea";
            }
            else {
                return "'".str_replace("'", "''", $val)."'";
            }

        default:
            return "";
        }

    }

    public function escape_string(?string $str) : string {
        if ($this->db_host['parsed']['phptype'] == 'sqlite'){
            return SQLite3::escapeString($str);
        }
        else {
            return addslashes($str);
        }
    }

    /**
     *  Unescape binary data obtained from database
     *
     *  @param  string  $val
     *  @return string
     */

    function sql_unescape_binary($val){

        if ($this->db_host['parsed']['phptype'] == 'pgsql'){
            return pg_unescape_bytea($val);
        }
        else {
            return $val;
        }
    }

    /**
     * This is replacement of sql function from_unixtime(). It converts unix
     * timestamp to datetime in format 'YYYY-MM-DD HH:MM:SS'
     *
     * The from_unixtime() implementation in MariaDB is limited to 31bits so
     * it have issues with time after '2038-01-19 05:14:07'
     *
     * @param int $ts
     * @return string
     */
    public static function from_unixtime($ts){
        return gmdate("Y-m-d H:i:s", $ts);
    }

    /**
     * This is replacement of sql function UNIX_TIMESTAMP(). It converts datetime
     * in format 'YYYY-MM-DD HH:MM:SS.ffffff' to unix timestamp.
     *
     * The UNIX_TIMESTAMP() implementation in MariaDB is limited to 31bits so
     * it have issues with time after '2038-01-19 05:14:07'
     *
     * @param string $datetime
     * @return int
     */
    public static function unix_timestamp($datetime){
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s+', $datetime, new DateTimeZone('GMT'));
        if (false === $date){
            sw_log(__CLASS__."::".__FUNCTION__."() - Error while parsing '$datetime' time");
            return null;
        }

        return $date->format('U');
    }

}

/**
 * Common ancestor class for the data layer methods
 */
class CData_Layer_Common{
    /** @var CData_Layer */
    public $dl; //reference to data layer object

    public static function _get_required_methods(){
        return array();
    }
}

class CData_Layer_Event{
    /** @var string */
    public $name;

    /** @var CData_Layer */
    public $dl;

    /** @var bool */
    public $stop_propagation;

    public function __construct(string $name){
        $this->name = $name;
        $this->stop_propagation = false;
    }
}
