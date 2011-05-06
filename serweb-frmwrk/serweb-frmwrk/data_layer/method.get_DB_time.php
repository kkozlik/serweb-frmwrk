<?php
/**
 *  @author     Karel Kozlik
 *  @version    $Id: method.get_DB_time.php,v 1.1 2007/09/27 12:22:25 kozlik Exp $
 *  @package    serweb
 */ 

/**
 *  Data layer container holding the method for get current timestamp on DB machine
 *
 *  @package    serweb
 */ 
class CData_Layer_get_DB_time {
    var $required_methods = array();
    
    /**
     *  Get current timestamp on DB machine
     *
     *  DB machine could be uset as time ethalon for setups with more servers
     *  On error this method returning FALSE.
     *
     *  Possible options:
     *   - none
     *
     *  @param  array       $opt    Array of options
     *  @return integer
     */ 

    function get_DB_time($opt){
        global $config;

        $this->connect_to_db();
        
        $q="select unix_timestamp(now())";
        $res=$this->db->query($q);
        if (DB::isError($res)) throw new DBException($res);
        $row=$res->fetchRow(DB_FETCHMODE_ORDERED);
        $res->free();
    
        return $row[0];
    }
    
}
?>
