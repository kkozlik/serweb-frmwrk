<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */


/**
 * Smarty {array_count} function plugin
 *
 * Type:     function<br>
 * Name:     array_count<br>
 * Purpose:  count the length of array
 * Input:<br>
 *         - array = name of smarty variable (required)
 *         - assign = if is set, assign result to another smarty variable
 *         - print = boolean - print result or not. default is true
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version  1.0
 * @param array parameters
 * @param Smarty
 * @return string|null
 */
function smarty_function_array_count($params, &$smarty)
{

    if (!in_array('array', array_keys($params))) {
		$smarty->trigger_error("array_count: missing 'array' parameter");
		return;
	}

	$length = count($params['array']);

    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $length);
    }
    
    if (isset($params['print'])) {
        $print = (bool)$params['print'];
    } else {
        $print = true;
    }

    if ($print) {
        $retval = $length;
    } else {
        $retval = null;
    }
    
    return $retval;
}

/* vim: set expandtab: */

?>
