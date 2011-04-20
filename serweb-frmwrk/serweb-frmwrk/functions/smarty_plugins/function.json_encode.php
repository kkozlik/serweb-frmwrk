<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */


/**
 * Smarty {json_encode} function plugin
 *
 * Type:     function<br>
 * Name:     json_encode<br>
 * Purpose:  encode given object to JSON format
 * Input:<br>
 *         - obj = object to encode (required)
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version  1.0
 * @param array parameters
 * @param Smarty
 * @return string|null
 */
function smarty_function_json_encode($params, &$smarty){

    if (!in_array('obj', array_keys($params))) {
		$smarty->trigger_error("array_count: missing 'obj' parameter");
		return;
	}

    return JSON_encode($params['obj']);
}

/* vim: set expandtab: */

?>
