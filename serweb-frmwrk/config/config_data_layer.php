<?php
/**
 *  This file holding configuration of database
 *
 */

global $config;


        ////////////////////////////////////////////////////////////////
        //            configure database

        $config->data_sql=new stdClass();

        $config->data_sql->abstraction_layer="PDO";         //database abstraction layer. Use:
                                                            // * "DB" for pear-db
                                                            // * "MDB2" for pear-mdb2
                                                            // * "PDO" for PHP Data Objects (built in)


        $config->data_sql->type="mysql";            //type of db host, enter "mysql" for MySQL or "pgsql" for PostgreSQL

        $i=0;
        $config->data_sql->host[$i]['host']= "localhost";   //database host
        $config->data_sql->host[$i]['port']= "";            //database port - leave empty for default
        $config->data_sql->host[$i]['name']= "serweb";      //database name
        $config->data_sql->host[$i]['user']= "user";        //database conection user
        $config->data_sql->host[$i]['pass']= "pass";        //database conection password
        $config->data_sql->host[$i]['dsn']=  "";            //for PDO abstraction layer this coult be used to specify data source name
                                                            //If used, it has higher precedence than the settings above.
                                                            //See PHP documentation for the syntax: https://www.php.net/manual/en/pdo.construct.php

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
         *
         *  Note: When $config->data_sql->set_charset is set to true, the mysql
         *  should implicitly set collation to defautl collation for the charset.
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

        $config->data_sql->set_charset = true;


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


        $config->data_layer_always_required_functions=array('set_db_charset',
                                                            'set_db_collation');
