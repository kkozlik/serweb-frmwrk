<?php
/**
 *  This file holding configuration of database
 *
 */

global $config;


        ////////////////////////////////////////////////////////////////
        //            configure connection to SER

        /**
         *  Use XML RPC instead of FIFO for manage SER.
         *
         *  If this and get_db_uri_from_ser is set to true the database 
         *  setting is irelevant. In this case, this is obtained from 
         *  SER automaticaly.
         */
        $config->use_rpc = true;

        /**
         * These are options for connect to XML-RPC interface of SER
         */

        /**
         * The hostname for xml-rpc requests
         */
        $config->ser_rpc['host']        = "localhost";

        /**
         * The port for xml-rpc requests. 5060 is used by default
         */
        //$config->ser_rpc['port']      = 5060;

        /**
         * Username for xml-rpc authentication (if required)
         */
        //$config->ser_rpc['user']      = "xmlrpc";

        /**
         * Password for xml-rpc authentication (if required)
         */
        //$config->ser_rpc['pass']      = "heslo";

        /**
         * Use encrypted connections for xml-rpc requests
         */
        //$config->ser_rpc['use_ssl']       = true;

        /**
         * Verify host - check if the common name in SSL peer
         * certificate matches the hostname provided
         * Is true by default
         */
        //$config->ser_rpc['ssl_vh']        = true;

        /**
         * The SSL version to use. By default PHP will try
         * to determine this itself.
         */
        //$config->ser_rpc['ssl_ver']       = 1;

        /**
         * The name of a file holding one or more PEM formated certificates
         * to verify the peer with.
         */
        //$config->ser_rpc['ssl_ca']        = "/etc/serweb/ca.crt";

        /**
         * The name of a file containing a PEM formatted client certificate.
         */
        //$config->ser_rpc['ssl_cert']  = "/etc/serweb/serweb.crt";

        /**
         * The secret password needed to use the client certificate
         */
        //$config->ser_rpc['ssl_cert_pass'] = "abrakadabra";

        /**
         * The name of a file containing a PEM formatted private SSL key.
         */
        //$config->ser_rpc['ssl_key']       = "/etc/serweb/serweb.key";

        /**
         * The secret password needed to use the private SSL key
         */
        //$config->ser_rpc['ssl_key_pass']  = "abrakadabra";



        /**
         *  Following array contain list of all sip proxies. Uncoment this if
         *  there are more sip proxies in your environment. This list is used
         *  for xml-rpc comunication.
         *
         *  Each array may contains the same keys as $config->ser_rpc. Only the
         *  'host' key is required. If some keys are not specified in some
         *  array, the default values from $config->ser_rpc are used.
         */
/*
        $config->sip_proxies[] = array('host'=>'proxy1.mydomain.org');
        $config->sip_proxies[] = array('host'=>'proxy2.mydomain.org');
        $config->sip_proxies[] = array('host'=>'proxy3.mydomain.org');
        $config->sip_proxies[] = array('host'=>'proxy4.mydomain.org');
        $config->sip_proxies[] = array('host'=>'proxy5.mydomain.org');
*/

        /**
         *  Obtain setting of database from SER - *** EXPERIMENTAL ***
         *  To enable this option must be $config->use_rpc = true
         */

        $config->get_db_uri_from_ser = false;

        ////////////////////////////////////////////////////////////////
        //            configure database

        /* these are the defaults with which SER installs; if you changed
           the SER account for SQL database, you need to update here

           If $config->use_rpc = true you need not set data_sql values, it is
           obtained from SER automaticaly
        */

        $config->data_sql=new stdClass();

        $config->data_sql->type="mysql";            //type of db host, enter "mysql" for MySQL or "pgsql" for PostgreSQL

        $i=0;
        $config->data_sql->host[$i]['host']= "localhost";   //database host
        $config->data_sql->host[$i]['port']= "";            //database port - leave empty for default
        $config->data_sql->host[$i]['name']= "serweb";      //database name
        $config->data_sql->host[$i]['user']= "user";        //database conection user
        $config->data_sql->host[$i]['pass']= "pass";        //database conection password

        // If you want to configure additional backup SQL servers, do so below.
        /*
        $i++;
        $config->data_sql->host[$i]['host']="localhost";    //database host
        $config->data_sql->host[$i]['port']="";             //database port - leave empty for default
        $config->data_sql->host[$i]['name']="ser";          //database name
        $config->data_sql->host[$i]['user']="ser";          //database conection user
        $config->data_sql->host[$i]['pass']="heslo";        //database conection password
        */
        // If you want to configure more SQL backup servers, copy and paste the above (including the "$i++;")


        /**
         *  Needs to be set when you are useing MySQL >= 4.1
         *  see mysql manual for more info
         *
         *  $config->data_sql->collation = "utf8_general_ci";
         */

        $config->data_sql->collation = "";

        /**
         *  Set to true when you are useing MySQL >= 4.1
         *  This option set mysql system variables character_set_client,
         *  character_set_connection, and character_set_results to charset
         *  used in serweb
         *
         *  $config->data_sql->set_charset = true;
         */

        $config->data_sql->set_charset = false;


        /**
         *  Lifetime of deleted records. (in days)
         *  Deleted domains and subscribers will be kept in DB for given time
         *  interval. After expiring it, records will be permanently deleted.
         */
        $config->keep_deleted_interval = 30;

        /**
         *  Lifetime of pending records. (in hours)
         *  Pending subscribers will be kept in DB for given time
         *  interval. After expiring it, records will be permanently deleted.
         */
        $config->keep_pending_interval = 24;

        /**
         *  Lifetime of acc records. (in days)
         *  Accounting records will be kept in DB for given time
         *  interval. After expiring it, records will be permanently deleted.
         *
         *  If $config->keep_acc_interval is 0, accounting records are not
         *  deleted.
         */
        $config->keep_acc_interval = 0;

        /**
         *  Number of kept versions of one file
         *  This variable tells how many versions of one file (from directory
         *  with domain specific config) is stored. If is set to zero files
         *  are not backuped on update of them.
         */
        $config->backup_versions_nr = 10;

        /**
         *  Set to true if SER caching domain table
         *
         *  modparam("domain", "db_mode", 1) is set in ser.cfg
         *
         *  Otherwise set this option to false
         */
        $config->ser_domain_cache = true;

        /**
         *  Set to false if SER useing did column of credentials table
         *
         *  modparam("auth_db", "use_did", 0) is set in ser.cfg
         *
         *  Otherwise set this option to true
         */
        $config->auth['use_did'] = true;


        /* these are setting required by ldap, you need to change it only if you are using ldap to
           store some data. If you are using ldap, you need to instal PEAR package db_ldap2 by command:

           pear install -f db_ldap2
        */

        $config->data_ldap=new stdClass();

        $config->data_ldap->version=3;                          //version of LDAP protocol, can be 2 or 3
        $config->data_ldap->base_dn="dc=mydomain,dc=org";       // The base DN of your LDAP server

        $i=0;
        $config->data_ldap->host[$i]['host']="localhost";       //ldap host
        $config->data_ldap->host[$i]['port']="";                //ldap port - leave empty for default
                                                                //ldap conection user
        $config->data_ldap->host[$i]['login_dn']="cn=admin,dc=mydomain,dc=org";
        $config->data_ldap->host[$i]['login_pass']="heslo";     //ldap conection password

        // If you want to configure additional backup LDAP servers, do so below.
        /*
        $i++;
        $config->data_ldap->host[$i]['host']="localhost";       //ldap host
        $config->data_ldap->host[$i]['port']="";                //ldap port - leave empty for default
                                                                //ldap conection user
        $config->data_ldap->host[$i]['login_dn']="cn=admin,dc=mydomain,dc=org";
        $config->data_ldap->host[$i]['login_pass']="heslo";     //ldap conection password
        */
        // If you want to configure more LDAP backup servers, copy and paste the above (including the "$i++;")




        $config->data_layer_always_required_functions=array('set_db_charset',
                                                            'set_db_collation');


?>
