<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */


/**
 * Smarty empty2nbsp modifier plugin
 *
 * Type:     modifier<br>
 * Name:     empty2nbsp<br>
 * Purpose:  substitue empty string by &nbsp;
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_empty2nbsp($str)
{
	if (preg_match('/^[[:space:]]*$/', $str)) return "&nbsp;";
	else return $str;
}

/* vim: set expandtab: */

?>
