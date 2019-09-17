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
    var $rpc=null;                      //PEAR XML_RPC object

    var $act_row=0, $num_rows=0;        //used when result returns too many rows
    var $showed_rows;                   //how many rows from result display

    var $_data_layer_loaded_methods=array();

    var $name = "auth_user";            //name of instance

    var $db_collation = null;
    var $db_charset = null;

    /** Contain DSN (Data Source Name) of DB Host in string and in parsed form */
    var $db_host = array();


    var $transaction_rollback = false;
    var $transaction_semaphore = 0;

    static $method_class_map = array();


    /**
     *  Handle call of unknow methods
     */
    function __call($method, $args){

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

    function CData_Layer(){
        global $config;
        $this->showed_rows = &$config->num_of_showed_items;
    }

    /**
     *  Create a new DataLayer object and agregate aditional methods
     *
     *  @return object CData_Layer      instance of CData_Layer class
     *  @static
     *  @access public
     *  @deprec
     */

    function &create(){
        global $config;

        $obj = new CData_Layer();

        $obj->require_and_agregate_methods();
        return $obj;
    }

    /**
     *  Attempts to return a reference to a concrete CData_Layer instance of name
     *  $instance_name, only creating a new instance if no data_layer instance with
     *  the same name currently exists.
     *
     *  @param string $instance_name    name of data_layer instance, if empty "auth_user" is used
     *  @return object CData_Layer      instance of CData_Layer class
     *  @static
     *  @access public
     */
    function &singleton($instance_name){
        static $instances = array();

        if (!$instance_name) $instance_name = "auth_user";

        if (!isset($instances[$instance_name])){
            $instances[$instance_name] = &CData_Layer::create();
            $instances[$instance_name]->name = $instance_name;
        }

        if (!isset($_SESSION['data_conn'][$instance_name]))
            $_SESSION['data_conn'][$instance_name] = array('proxy' => null,
                                                           'db_dsn' => null);

        return $instances[$instance_name];
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

        reset($_data_layer_required_methods);
        while (list(, $item) = each($_data_layer_required_methods)) {

            if (false ===  array_search($item, $this->_data_layer_loaded_methods)){ //if required method isn't loaded yet, load it

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

                //agregate methods of required class to this object
                my_aggregate_methods($this, "CData_Layer_".$item);

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

            }
        }
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
     *  Set proxy to which should be this instance connected
     *  @access public
     */

    function set_home_proxy($proxy){

        /* if proxy is changed, unset db_dsn */
        if ($_SESSION['data_conn'][$this->name]['proxy'] != $proxy){
            $_SESSION['data_conn'][$this->name]['db_dsn'] = null;
        }

        $_SESSION['data_conn'][$this->name]['proxy'] = $proxy;
    }



    /**
     *  Get dsn of DB used by given sip proxy
     *
     *  @param string $proxy_uri    uri of sip proxy
     *  @return string              dsn of DB
     */
    function get_db_uri($proxy_uri){
        $this->connect_to_xml_rpc(array("uri" => $proxy_uri));

        $msg = new XML_RPC_Message('get_db_uri');
        $res = $this->rpc->send($msg);

        if ($this->rpc_is_error($res)){
            throw new XMLRPCException($res);
//          log_errors($res, $errors); return false;
        }

        $val = $res->value();
        $val = trim($val->scalarval());

        sw_log("Get_db_uri for: ".$proxy_uri." returned: ".$val, PEAR_LOG_DEBUG);

        return $val;
    }


    /**
     *  Sends XML RPC message to all proxies
     *
     *  Possible options parameters:
     *    break_on_error            (bool)  default: true
     *      Break sending messages when some error occur. If false messages
     *      are always sent to all proxies
     *
     *  @param  object  $msg    XML_RPC_Message
     *  @param  array   $opt    array of options
     *  @return object
     */
    function rpc_send_to_all($msg, $opt){
        global $config;

        $opt_break_on_error = isset($opt['break_on_error']) ? (bool)$opt['break_on_error'] : true;

        if (!empty($config->sip_proxies) and is_array($config->sip_proxies)){
            $proxies = &$config->sip_proxies;
        }
        else{
            $proxies = array();
            $proxies[] = array('host'=>$config->ser_rpc['host']);
        }

        $i=0;
        $out = new stdclass();
        $out->ok = true;
        $out->results = array();

        foreach($proxies as $v){
            $client = $this->make_rpc_client('/', $v);

            $res = $client->send($msg);

            if ($this->rpc_is_error($res, $client)){
                $out->ok = false;
                if ($opt_break_on_error){
                    $out->results[$i] = &$res;
                    return $out;
                }
            }
            $out->results[$i] = $res;
            $i++;
        }

        return $out;
    }


    /**
     *  Check if there was error during xml_rpc request
     *
     *  @param object $resp     result of XML_RPC_Client::send
     *  @param object $client   instance of XML_RPC_Client, $this->rpc is used by default
     *  @return bool            true on error, false on everything is OK
     */
    function rpc_is_error(&$resp, $client=null){
        if (is_null($client)) $client = $this->rpc;

        if (!$resp) {
            $resp = PEAR::raiseError("xml_rpc communication error",
                                     null, null, null, $client->errstr);
            return true;
        }

        if ($resp->faultCode()){
            $resp = PEAR::raiseError("xml_rpc request error",
                                     $resp->faultCode(), null, null, $resp->faultCode().":".$resp->faultString());
            return true;
        }

/** disabled - errors should be correctly returned in UR version */
/*
        $val = $resp->value();
        $val = $val->scalarval();

        if (is_numeric(substr($val, 0, 3))){
            if (substr($val, 0, 1) >= 4){
                $lines = explode("\n", $val);

                $resp = PEAR::raiseError("xml_rpc request error",
                                         substr($val, 0, 3), null, null, $lines[0]);
                return true;
            }
        }
*/
        return false;
    }

    /**
     *  Set object variable (@see db_host) by given dsn (Data Source Name)
     *
     *  @param string $dsn      Data Source Name
     *  @access private
     */
    function set_this_db_host($dsn){
        global $config;

        $this->db_host['dsn'] = $dsn;
        if ($config->data_sql->abstraction_layer=="MDB2")
            $this->db_host['parsed'] = MDB2::parseDSN($dsn);
        else
            $this->db_host['parsed'] = DB::parseDSN($dsn);
    }

    /**
     *  connect to sql database
     */

    function connect_to_db(){
        global $config;

        if ($this->db) return $this->db;


        if ($config->use_rpc and $config->get_db_uri_from_ser ){

            //if not set DSN of DB to connect, get it
            if (! $_SESSION['data_conn'][$this->name]['db_dsn']){
                $_SESSION['data_conn'][$this->name]['db_dsn'] =
                        $this->get_db_uri("sip:".$config->ser_rpc['host']);
            }

            $dsn = $_SESSION['data_conn'][$this->name]['db_dsn'];
            $this->set_this_db_host($dsn);

            if ($config->data_sql->abstraction_layer=="MDB2"){
                $db = MDB2::factory($this->db_host['parsed'], true);

                if (MDB2::isError($db)) {
                    throw new DBException($db);
//              log_errors($db, $errors); return false;
                }
            }
            else{
                $db = DB::connect($this->db_host['parsed'], true);

                if (DB::isError($db)) {
                    throw new DBException($db);
//              log_errors($db, $errors); return false;
                }
            }
        }
        else{

            $cfg=&$config->data_sql;

            $num=count($cfg->host); //get number of SQL servers that we can use

            if ($num>1) $serv=mt_rand(0, $num-1);
            else $serv=0;

            $tries=0;
            do{
                $cont=0;
                $dsn =  $cfg->type."://".
                        $cfg->host[$serv]['user'].":".
                        $cfg->host[$serv]['pass']."@".
                        $cfg->host[$serv]['host'].
                            (empty($cfg->host[$serv]['port'])?
                                "":
                                ":".$cfg->host[$serv]['port'])."/".
                        $cfg->host[$serv]['name'];

                $this->set_this_db_host($dsn);

                if ($config->data_sql->abstraction_layer=="MDB2"){
                    $db = MDB2::connect($this->db_host['parsed'], true);
                    $isError = MDB2::isError($db);
                    $codeFailed = MDB2_ERROR_CONNECT_FAILED;
                }
                else{
                    $db = DB::connect($this->db_host['parsed'], true);
                    $isError = DB::isError($db);
                    $codeFailed = DB_ERROR_CONNECT_FAILED;
                }

                if ($isError) {
                    //if connect failed and multiple servers is defined
                    if (($db->getCode() == $codeFailed) and ($num>1)){
                        //try another server
                        $tries++;
                        $serv++; $serv %= $num;
                        if ($tries<$num) {
                            $cont=1; continue;
                        }
                    }
                    throw new DBException($db);
//                  log_errors($db, $errors); return false;
                }
            }while($cont);
        }

        $this->db=$db;

        if ($this->db_charset) $this->set_db_charset($this->db_charset, null);
        if ($this->db_collation) $this->set_db_collation($this->db_collation, null);

        return $db;
    }

    /**
     *  Make instance of XML_RPC_CLient
     *
     *  @access private
     *  @param  string  $path   path to RPC server script
     *  @param  array   $my_cfg alternative configs to use instead of $config->ser_rpc
     */
    function &make_rpc_client($path, $my_cfg=null){
        global $config;

        $cfg = $config->ser_rpc;

        /* replace default config by alternative configs */
        if (is_array($my_cfg)){
            foreach($my_cfg as $k => $v) $cfg[$k] = $v;
        }

        $port = isset($cfg['port']) ? $cfg['port'] : 5060;
        $host = empty($cfg['use_ssl']) ? $cfg['host'] : "https://".$cfg['host'];

        $rpc = new XML_RPC_Client_curl($path, $host, $port);

        /* set credentials */
        if (isset($cfg['user']) and isset($cfg['pass'])){
            $rpc->setCredentials($cfg['user'], $cfg['pass']);
        }

        /* set SSL settings */
        if (!empty($cfg['use_ssl'])){
            if (isset($cfg['ssl_vh'])){
                if ($cfg['ssl_vh']) $rpc->setSSLVerifyHost(2);
                else                $rpc->setSSLVerifyHost(0);
            }
            if (isset($cfg['ssl_ver'])) $rpc->setSSLVersion($cfg['ssl_ver']);
            if (isset($cfg['ssl_ca']))  $rpc->setSSLCA($cfg['ssl_ca']);
            if (isset($cfg['ssl_cert'])){
                $pw = isset($cfg['ssl_cert_pass']) ? $cfg['ssl_cert_pass'] : null;
                $rpc->setSSLCert($cfg['ssl_cert'], $pw);
            }
            if (isset($cfg['ssl_key'])){
                $pw = isset($cfg['ssl_key_pass']) ? $cfg['ssl_key_pass'] : null;
                $rpc->setSSLKey($cfg['ssl_key'], $pw);
            }
        }

        return $rpc;
    }

    /**
     *  create instance of xml rpc client
     */
    function connect_to_xml_rpc($opt){
        global $config;

        $cfg = &$config->ser_rpc;

        if (isset($opt['cluster'])) {
            $proxy = "sip:".$config->ser_rpc['host'];
        }
        else if (isset($opt['uri'])){
            $proxy = $opt['uri'];
        }
        else {
            if (! $_SESSION['data_conn'][$this->name]['proxy']){
                $this->set_home_proxy("sip:".$config->ser_rpc['host']);
            }

            $proxy = $_SESSION['data_conn'][$this->name]['proxy'];
        }

        if ($this->rpc and $this->rpc->path == '/'.$proxy) return $this->rpc;

        $this->rpc = $this->make_rpc_client('/'.$proxy);
        return $this->rpc;
    }


    function set_ret_val($val){
        $this->ret_val = $val;
    }

    function get_ret_val(){
        return $this->ret_val;
    }

    function set_num_rows($num_rows){
        $this->num_rows=$num_rows;
    }

    function get_num_rows(){
        return $this->num_rows;
    }

    function set_act_row($act_row){
        $this->act_row=$act_row;
    }

    function get_act_row(){
        return $this->act_row;
    }

    function get_showed_rows(){
        return $this->showed_rows;
    }

    function set_showed_rows($showed_rows){
        $this->showed_rows=$showed_rows;
    }

    function get_res_from(){
        // Modified for PR 118789
        if($this->get_num_rows())
            return $this->get_act_row()+1;
        else
            return $this->get_act_row();
    }

    function get_res_to(){
        global $config;
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
        global $config;

        if ($config->data_sql->abstraction_layer=="MDB2"){
            return MDB2::isError($res);
        }
        else{
            return DB::isError($res);
        }
    }

    /**
     *  Start a transaction on the current connection
     */
    function transaction_start(){
        $this->connect_to_db();

        /* initialize variables after rollback */
        if ($this->transaction_rollback){
            $this->transaction_rollback = false;
            $this->transaction_semaphore = 0;
        }

        /* don't call "start transaction" in nested transactions */
        if ($this->transaction_semaphore == 0){
            $res=$this->db->query("start transaction");
            if ($this->dbIsError($res)) throw new DBException($res);
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
            $res=$this->db->query("commit");
            if ($this->dbIsError($res)) throw new DBException($res);
        }

        return true;
    }

    /**
     *  Rollback changes done due the transaction
     */
    function transaction_rollback(){

        $this->transaction_rollback = true;

        $res=$this->db->query("rollback");
        if ($this->dbIsError($res)) throw new DBException($res);

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
            foreach($set as $k=>$v) $set[$k] = "'".addslashes($v)."'";
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
            return "'".addslashes($val)."'";

        case "N":
            if (is_null($val)) return "NULL";
        case "n":
            return is_float($val) ? (float) $val : (int)$val;

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
                return "'".$this->db->escapeSimple($val)."'";
            }

        default:
            return "";
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

}

/**
 * Common ancestor class for the data layer methods
 */
class CData_Layer_Common{
    public $dl; //reference to data layer object

    public static function _get_required_methods(){
        return array();
    }
}
