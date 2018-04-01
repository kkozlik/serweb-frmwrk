<?php
/*
 * $Id: set_domain.php,v 1.2 2004/09/22 09:58:30 kozlik Exp $
 */ 

global $config;

/* set domain name */

/* if set_domain.developer is present, require this instead of setting domain by server name */
$set_domain_developer = dirname(__FILE__) . "/set_domain.developer.php";
if (file_exists($set_domain_developer)){
    require_once ($set_domain_developer);
}
else{

//  automatical setting of domain by server name from http request
//    $config->domain = preg_replace( "/(www\.|sip\.)?(.*)/", "\\2",  $_SERVER['SERVER_NAME']);
//    $config->domain = "mydomain.org";
    $config->domain = null;

}
unset($set_domain_developer);

