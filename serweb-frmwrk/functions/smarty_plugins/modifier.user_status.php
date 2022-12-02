<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */

/* $Id: modifier.user_status.php,v 1.2 2007/02/14 16:46:32 kozlik Exp $ */


/**
 * Smarty string_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     user_status<br>
 * Purpose:  format status of user to span and internationalize it
 * 
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version 1.0
 * @param string	$status			status of user, one from: 'unknown', 'nonlocal', 'notexists', 'offline', 'online'
 * @param array		$lang_strings	array of internationalized messages
 * @param array		$css_classes	array of css classes
 * @return string
 */
function smarty_modifier_user_status($status, $lang_strings=null, $css_classes=null)
{
	global $lang_str;
	
	if (is_null($lang_strings)){
		$lang_strings = array("unknown"   => $lang_str['status_unknown'],
		                      "nonlocal"  => $lang_str['status_nonlocal'],
		                      "notexists" => $lang_str['status_nonexists'],
		                      "offline"   => $lang_str['status_offline'],
		                      "online"    => $lang_str['status_online']
							  );
	}

	if (is_null($css_classes)){
		$css_classes = array("unknown"   => "statusunknown",
		                     "nonlocal"  => "statusnonlocal",
		                     "notexists" => "statusnonexists",
		                     "offline"   => "statusoffline",
		                     "online"    => "statusonline"
							 );
	}

    return '<span class="'.$css_classes[$status].'">'.$lang_strings[$status].'</span>';
}

/* vim: set expandtab: */

?>
