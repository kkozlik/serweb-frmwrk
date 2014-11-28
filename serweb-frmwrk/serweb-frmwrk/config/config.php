<?php

global $config;

/* ------------------------------------------------------------*/
/* modules                                                     */
/* ------------------------------------------------------------*/

/* modules that are loaded on every page
 */

$config->modules = array();
    
/* ------------------------------------------------------------*/
/* Language settings                                           */
/* ------------------------------------------------------------*/

/* Default language to use, if not browser-defined or user-defined
 */

$config->default_lang = 'en-utf-8';

/* Force: always use this language - must be defined in
   config/config_lang.php

   $config->force_lang = 'en-iso-8859-1';
*/
$config->force_lang = '';

$config->do_not_set_lang_by_domain=true;
    
/* ------------------------------------------------------------*/
/* Default timezone                                            */
/* ------------------------------------------------------------*/

/** List of supported timezones:
 *  http://www.php.net/manual/en/timezones.php
 */ 
$config->timezone = null;

/* ------------------------------------------------------------*/
/* serweb appearance                                           */
/* ------------------------------------------------------------*/


/* Use serweb in multidomain setup 
 * Serweb can work in two diferent modes. 
 * - singledomain - Use if your SER control only one domain.
 *     In this case the name of the apache host under which the serweb is 
 *     running is always used as domain. If the serweb is running on a 
 *     virtual host, this will be the value defined for that virtual host.
 *     The name of domain may be a bit changed (for example initial 'sip.'
 *     may be stripped). See file set_domain.php in config directory.
 *     
 * - multidomain - Use if your SER control more domains.
 *     In this case the domain is checked against the 'domain' sql table.
 *     Also tabs for manage domains are enabled in admin interface.
 */
$config->multidomain = true;

/* Default id of domains in single domain setups 
 * Probably you does not need change this value.
 */
$config->default_did = "_default";

/* DID used for global tel uri */
$config->global_tel_uri_did = $config->default_did;
    
$config->num_of_showed_items=20;    /* num of showed items in the list of users */
$config->max_showed_rows=50;        /* maximum of showed items in "user find" */

$config->html_doctype='transitional';


/* Regular expressions for check if username (username part of sip address) 
   entered by user is valid.

   By default username may be either a numerical address starting with 
   '8' (e.g., '8910') or a lower-case alphanumerical address starting 
   with an alphabetical character (e.g., john.doe01).

   $config->username_regex = '^((8[0-9]*)|([a-zA-Z][a-zA-Z0-9.]*))$';
*/

$config->username_regex = '^((8[0-9]*)|([a-zA-Z][a-zA-Z0-9.]*))$';

/* Regular expressions for check if phonenumber entered by user is valid
   (is used only if serweb is workong with phonenumbers instead of sip addresses)
   The diferent between phonenumber_regex and strict_phonenumber_regex is that 
   phonenumber_regex can contain chars as '-' '/' ' ' (which will be removed
   after form submition)
*/

$config->phonenumber_regex = "\\+?[-/ ()0-9]+";
$config->strict_phonenumber_regex = "\\+?[0-9]+";


$config->identifier_regex = "[0-9A-Za-z_]+";

/* ------------------------------------------------------------*/
/* Logging                                                      */
/* ------------------------------------------------------------*/

/* You can specify custom function for loging
 *
 * $config->custom_log_function = "my_log";
 * $config->custom_act_log_function = array("MY_logging", "action_log");
 */

$config->custom_log_function = null;
$config->custom_act_log_function = null;

/* When you enable logging be sure if you have instaleld PEAR package
   Log. See http://pear.php.net/manual/en/installation.getting.php 
   for more information
*/

$config->enable_logging = false;

/* Name of file where the log messages will be written.
   For logging to syslog, set the $config->log_file to "syslog". It is also 
   possible to set the facility. In this case set $config->log_file to 
   "syslog:<facility>" E.g.: "syslog:LOG_LOCAL7". The facility is set 
   to LOG_LOCAL0 by default. 
   For other possible values check PHP manual: http://php.net/openlog. 
*/
$config->log_file = "/var/log/serweb";

/* Log messages up to and including this level. Possible values:
      PEAR_LOG_EMERG, PEAR_LOG_ALERT, PEAR_LOG_CRIT, PEAR_LOG_ERR, 
      PEAR_LOG_WARNING, PEAR_LOG_NOTICE, PEAR_LOG_INFO, PEAR_LOG_DEBUG
   see http://www.indelible.org/pear/Log/guide.php#log-levels for more info
 */
$config->log_level = "PEAR_LOG_WARNING";

/* If location where error was occured should be returned to user
   html output, set this to true
 */
$config->log_error_return_location_of_error_to_html = false;

            
/* is the sql database query for user authentication formed
   with clear text password or a hashed one; the former is less
   secure the latter works even if password hash is incorrect,
   which sometimes happens, when it is calculated from an
   incorrect domain during installation process
*/
$config->clear_text_pw=true;

/* program used to validate regular expressions in pcre format. It should 
   return "1" to stdout when regexp given by first parameter is valid. 
 */
$config->external_regexp_validator_pcre  = "/usr/sbin/regexp_validator pcre";

/* program used to validate regular expressions in posix format. It should 
   return "1" to stdout when regexp given by first parameter is valid. 
 */
$config->external_regexp_validator_posix = "/usr/sbin/regexp_validator posix";


?>
