<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */

/**
 * Smarty {setvar} function plugin
 *
 * Type:     function<br>
 * Name:     setvar<br>
 * Date:     Jun 6, 2004<br>
 * Purpose:  Easier way to assign variables in templates.<br>
 * Input:<br>
 *         - <variable name> = <variable value> (required)
 *
 * Examples: {setvar key='one two'}
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_setvar($params, &$smarty)
{
    $smarty->assign($params);
}

?>
